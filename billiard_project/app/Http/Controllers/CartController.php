<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    /**
     * แสดงหน้าตะกร้าสินค้า
     */
    public function index()
    {
        $cartTable = session()->get('cart.table', null); // (ดึงโต๊ะ)
        $cartFood = session()->get('cart.food', []);   // (ดึงอาหาร)
        
        $totalTable = $cartTable['price'] ?? 0;
        
        $totalFood = 0;
        foreach ($cartFood as $id => $details) {
            $totalFood += $details['price'] * $details['quantity'];
        }

        $total = $totalTable + $totalFood;

        return view('carts.cart', [
            'cartTable' => $cartTable, // (ส่งข้อมูลโต๊ะ)
            'cartItems' => $cartFood,  // (ส่งข้อมูลอาหาร)
            'total' => $total
        ]);
    }

    /**
     * เพิ่มสินค้าลงในตะกร้า (จากหน้า Menu)
     */
    public function add(Request $request)
    {
        $request->validate([ /* ... (validation เดิม) ... */
            'menu_id' => 'required|integer|exists:menus,menu_id',
            'menu_qty' => 'required|integer|min:1',
            'menu_name' => 'required|string',
            'price' => 'required|numeric',
            'branch_id' => 'required|integer|exists:branches,branch_id',
        ]);

        $branchId = $request->branch_id;
        
        // (ใหม่) ตรวจสอบ Branch ID
        $cartTable = session()->get('cart.table', null);
        
        if ($cartTable && $cartTable['branch_id'] != $branchId) {
            return redirect()->back()->with('error', 'คุณสามารถสั่งอาหารได้เฉพาะสาขาที่จองโต๊ะไว้เท่านั้น');
        }
        
        // (ถ้าไม่มีโต๊ะ หรือ สาขาตรงกัน)
        $cartFood = session()->get('cart.food', []);
        $menuId = $request->menu_id;

        if (isset($cartFood[$menuId])) {
            $cartFood[$menuId]['quantity'] += $request->quantity;
        } else {
            $cartFood[$menuId] = [
                "menu_name" => $request->menu_name,
                "menu_qty" => (int)$request->menu_qty,
                "price" => $request->price,
                "branch_id" => $branchId,
            ];
        }

        session()->put('cart.food', $cartFood); // (บันทึกใน cart.food)

        // (แก้ไข) เปลี่ยน redirect ไปหน้า Cart
        return redirect()->route('cart.index')->with('success', 'เพิ่มสินค้าลงในตะกร้าเรียบร้อย!');
    }

    /**
     * อัปเดตจำนวนสินค้าในตะกร้า
     */
    public function update(Request $request)
    {
        // ... (Logic เดิมถูกต้อง แต่ต้องแน่ใจว่าใช้ 'cart.food')
        $cartFood = session()->get('cart.food', []);
        $menuId = $request->menu_id;
        if (isset($cartFood[$menuId])) {
            $cartFood[$menuId]['quantity'] = $request->quantity;
            session()->put('cart.food', $cartFood);
        }
        return redirect()->route('cart.index')->with('success', 'อัปเดตจำนวนสินค้าแล้ว');
    }

    /**
     * ลบสินค้าออกจากตะกร้า
     */
    public function remove(Request $request)
    {
        // ... (Logic เดิมถูกต้อง แต่ต้องแน่ใจว่าใช้ 'cart.food')
        $cartFood = session()->get('cart.food', []);
        $menuId = $request->menu_id;
        if (isset($cartFood[$menuId])) {
            unset($cartFood[$menuId]);
            session()->put('cart.food', $cartFood);
        }
        return redirect()->route('cart.index')->with('success', 'ลบสินค้าออกจากตะกร้าแล้ว');
    }

    public function processCheckout(Request $request)
    {
        $cartTable = session()->get('cart.table', null);
        $cartFood = session()->get('cart.food', []);
        
        if (!Auth::check()) { /* ... (Auth check) ... */ }
        
        // (แก้ไข) ต้องมีอย่างน้อย 1 อย่าง (โต๊ะ หรือ อาหาร)
        if (empty($cartTable) && empty($cartFood)) {
            return redirect()->route('cart.index')->with('error', 'ตะกร้าของคุณว่างเปล่า');
        }

        $userId = Auth::id();

        // (แก้ไข) คำนวณยอดรวมใหม่
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
                            ->decrement('quantity', $item['menu_qty']);
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
            return redirect()->route('cart.index')->withErrors(['message' => 'เกิดข้อผิดพลาดในการสร้าง Order: ' . $e->getMessage()]);
        }
    }    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'ล้างตะกร้าสินค้าทั้งหมดแล้ว');
    }
}