<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function index()
    {
        $cartTable = session()->get('cart.table', null);
        $cartFood = session()->get('cart.food', []);
        
        $totalTable = $cartTable['price'] ?? 0;
        
        $totalFood = 0;
        foreach ($cartFood as $id => $details) {
            $totalFood += $details['price'] * $details['quantity'];
        }

        $total = $totalTable + $totalFood;

        return view('carts.cart', [
            'cartTable' => $cartTable, 
            'cartItems' => $cartFood, 
            'total' => $total
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:menus,menu_id',
            'quantity' => 'required|integer|min:1', 
            'menu_name' => 'required|string', // ถูกแก้ไขใน Blade แล้ว
            'price' => 'required|numeric',
            'branch_id' => 'required|integer|exists:branches,branch_id',
        ]);

        $branchId = $request->branch_id;
        $quantity = (int)$request->quantity;
        $menuId = $request->menu_id;

        $cartTable = session()->get('cart.table', null);
        if ($cartTable && $cartTable['branch_id'] != $branchId) {
            return redirect()->back()->with('error', 'คุณสามารถสั่งอาหารได้เฉพาะสาขาที่จองโต๊ะไว้เท่านั้น');
        }
 
        $inventory = DB::table('inventory')
                    ->where('menu_id', $menuId)
                    ->where('branch_id', $branchId)
                    ->first();

        if ($inventory && $inventory->stock_qty < $quantity) {
             return back()->with('error', 'สินค้าหมด หรือมีไม่เพียงพอสำหรับสาขานี้');
        } 
        
        $cartFood = session()->get('cart.food', []);
        
        if (isset($cartFood[$menuId])) {
            $cartFood[$menuId]['quantity'] += $quantity;
        } else {
            $cartFood[$menuId] = [
                "menu_name" => $request->menu_name,
                "quantity" => $quantity, 
                "price" => $request->price,
                "branch_id" => $branchId,
            ];
        }

        session()->put('cart.food', $cartFood); 

        // 4. Redirect ไปหน้า Cart
        return redirect()->route('cart.index')->with('success', 'เพิ่มสินค้าลงในตะกร้าเรียบร้อย!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|integer',
            'quantity' => 'required|integer|min:1', 
        ]);

        $cartFood = session()->get('cart.food', []);
        $menuId = $request->menu_id;
        if (isset($cartFood[$menuId])) {
            $newQuantity = (int)$request->quantity;
            $branchId = $cartFood[$menuId]['branch_id'];

            $inventory = DB::table('inventory')
                        ->where('menu_id', $menuId)
                        ->where('branch_id', $branchId)
                        ->first();

            if ($inventory && $inventory->stock_qty < $newQuantity) {
                 return back()->with('error', 'ไม่สามารถอัปเดตได้ สินค้ามีไม่เพียงพอ');
            } 

            $cartFood[$menuId]['quantity'] = $newQuantity;
            session()->put('cart.food', $cartFood);
        }
        return redirect()->route('cart.index')->with('success', 'อัปเดตจำนวนสินค้าแล้ว');
    }

    public function remove(Request $request)
    {
        $cartFood = session()->get('cart.food', []);
        $menuId = $request->menu_id;
        if (isset($cartFood[$menuId])) {
            unset($cartFood[$menuId]);
            session()->put('cart.food', $cartFood);
        }
        return redirect()->route('cart.index')->with('success', 'ลบสินค้าออกจากตะกร้าแล้ว');
    }

    public function processCheckout()
    {
        $cartTable = session()->get('cart.table', null);
        $cartFood = session()->get('cart.food', []);
        
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        if (empty($cartTable) && empty($cartFood)) {
            return redirect()->route('cart.index')->with('error', 'ตะกร้าของคุณว่างเปล่า');
        }

        $userId = Auth::id();

        $totalAmount = ($cartTable['price'] ?? 0);
        foreach ($cartFood as $id => $details) {
            $totalAmount += $details['price'] * $details['quantity'];
        }

        try {
            $newOrderId = null; 
            DB::transaction(function () use ($cartTable, $cartFood, $userId, $totalAmount, &$newOrderId) {
                
                $orderId = DB::table('orders')->insertGetId([
                    'user_id' => $userId,
                    'order_date' => now(),
                    'order_status' => 'pending',
                ]);
                $newOrderId = $orderId;

                if ($cartTable) {
                    $reservationItems = [];
                    foreach ($cartTable['table_ids'] as $tableId) {
                        $reservationItems[] = [
                            'order_id' => $orderId,
                            'table_id' => $tableId,
                            'start_time' => $cartTable['start_time'],
                            'end_time' => $cartTable['end_time'],
                            'reserve_status' => 'confirmed', 
                        ];
                    }
                    DB::table('reservation')->insert($reservationItems);
                }

                if (!empty($cartFood)) {
                    $purchaseItems = [];
                    foreach ($cartFood as $id => $details) {
                        $purchaseItems[] = [
                            'order_id' => $orderId,
                            'menu_id' => $id,
                            'branch_id' => $details['branch_id'],
                            'menu_qty' => $details['quantity'],
                            'total_price' => $details['price'] * $details['quantity'],
                        ];
                    }
                    DB::table('purchase')->insert($purchaseItems);

                    foreach ($purchaseItems as $item) {
                        DB::table('inventory')
                            ->where('menu_id', $item['menu_id'])
                            ->decrement('stock_qty', $item['menu_qty']); 
                    }
                    
                }

                DB::table('payment')->insert([
                    'order_id' => $orderId,
                    'total_amount' => $totalAmount,
                    'discount_amount' => 0.00,
                    'final_amount' => $totalAmount,
                    'pay_status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            });

            session()->forget('cart');

            return redirect()->route('checkout.page', ['order_id' => $newOrderId])
                             ->with('success', 'สร้าง Order สำเร็จ! กรุณาชำระเงิน');

        } catch (\Exception $e) {
            Log::error("Checkout Failed for User ID $userId: " . $e->getMessage()); 
            return redirect()->route('cart.index')->withErrors(['message' => 'เกิดข้อผิดพลาดในการสร้าง Order: กรุณาลองใหม่อีกครั้ง']);
        }
    }    
    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'ล้างตะกร้าสินค้าทั้งหมดแล้ว');
    }
}