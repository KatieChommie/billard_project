<?php

namespace App\Http\Controllers\UserController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- 1. เพิ่ม
use Illuminate\Support\Facades\Auth; // <-- 1. เพิ่ม

class DashboardController extends Controller
{
    /**
     * (แก้ไข) 2. แก้ไขเมธอด index() ให้ดึงข้อมูลจริง
     */
    public function index()
    {
        $userId = Auth::id();

        // ดึงข้อมูลการจอง (Orders) ที่เกี่ยวข้องกับ User นี้
        $bookings = DB::table('orders')
            ->join('payment', 'orders.order_id', '=', 'payment.order_id')
            ->join('reservation', 'orders.order_id', '=', 'reservation.order_id')
            ->where('orders.user_id', $userId)
            // เอาสถานะล่าสุด
            ->whereIn('orders.order_status', ['pending', 'confirmed', 'cancelled']) 
            ->select(
                'orders.order_id',
                'orders.order_status',
                'payment.final_amount',
                'reservation.start_time' // เอาเวลาเริ่มจอง
            )
            ->groupBy( // Group by เพื่อป้องกันการแสดงผลซ้ำ (กรณี 1 Order มีหลายโต๊ะ)
                'orders.order_id', 
                'orders.order_status', 
                'payment.final_amount', 
                'reservation.start_time'
            ) 
            ->orderBy('reservation.start_time', 'desc') // เรียงจากใหม่ไปเก่า
            ->get();

        return view('user.dashboard', [
            'bookings' => $bookings // ส่งตัวแปร $bookings ไปที่ View
        ]);
    }

    /**
     * (เพิ่มใหม่) 3. เพิ่มเมธอด cancelBooking()
     */
    public function cancelBooking(Request $request)
    {
        if (!Auth::check()) { 
            return redirect()->route('login'); 
        }

        $request->validate(['order_id' => 'required|integer|exists:orders,order_id']);

        $userId = Auth::id();
        $orderId = $request->input('order_id');

        // ค้นหา Order (ต้องเป็นของ User นี้ และยัง pending เท่านั้น)
        $order = DB::table('orders')
                    ->where('order_id', $orderId)
                    ->where('user_id', $userId)
                    ->where('order_status', 'pending') 
                    ->first();
        
        if (!$order) {
            return back()->withErrors(['message' => 'ไม่พบการจองนี้ หรือการจองนี้ถูกยืนยัน/ยกเลิกไปแล้ว']);
        }

        // ค้นหา Payment ที่เกี่ยวข้อง (เพื่อคืน Reward)
        $payment = DB::table('payment')->where('order_id', $orderId)->first();
        if (!$payment) {
            return back()->withErrors(['message' => 'ไม่พบข้อมูลการชำระเงิน (DB Error)']);
        }

        try {
            DB::transaction(function () use ($orderId, $payment) {
                
                // 1. อัปเดต Payment เป็น 'failed'
                DB::table('payment')
                    ->where('pay_id', $payment->pay_id)
                    ->update([
                        'pay_status' => 'failed',
                        'updated_at' => now()
                    ]);
                
                // 2. อัปเดต Order เป็น 'cancelled'
                DB::table('orders')
                    ->where('order_id', $orderId)
                    ->update([
                        'order_status' => 'cancelled'
                    ]);
                
                // 3. (สำคัญ!) คืนสิทธิ์ส่วนลด (Reward)
                // (อ้างอิง schema `reward`)
                if ($payment->reward_id) {
                    DB::table('reward')
                        ->where('reward_id', $payment->reward_id)
                        ->where('reward_status', 'used') 
                        ->update(['reward_status' => 'active']);
                }
            });

            return redirect()->route('user.dashboard')->with('success', 'ยกเลิกการจอง (Order ID: ' . $orderId . ') เรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'เกิดข้อผิดพลาดในการยกเลิก: ' . $e->getMessage()]);
        }
    }
}