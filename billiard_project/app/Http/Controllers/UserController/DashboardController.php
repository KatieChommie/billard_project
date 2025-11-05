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

        // --- 1. ดึงข้อมูลสำหรับ "ตารางประวัติการจอง" (Bookings) ---
        
        // 1a. ค้นหา "Order ID" ที่รีวิวไปแล้ว (เพื่อซ่อนปุ่ม)
        $reviewedOrderIds = DB::table('review')
                                ->where('user_id', $userId)
                                ->pluck('order_id') 
                                ->unique();

        // 1b. ค้นหาประวัติการจอง (Bookings)
        $bookings = DB::table('orders as o')
            ->join('payment as p', 'o.order_id', '=', 'p.order_id')
            ->leftJoin('purchase as pur', 'o.order_id', '=', 'pur.order_id')
            ->leftJoin('reservation as res', 'o.order_id', '=', 'res.order_id')
            ->leftJoin('tables as t', 'res.table_id', '=', 't.table_id')
            ->leftJoin('branches as b', 't.branch_id', '=', 'b.branch_id')
            ->where('o.user_id', $userId)
            ->whereIn('o.order_status', ['pending', 'confirmed', 'completed', 'cancelled'])
            ->select(
                'o.order_id',
                'o.order_status',
                'p.final_amount',
                DB::raw('COALESCE(res.start_time, o.order_date) as display_time'),
                DB::raw('COUNT(DISTINCT res.order_id) > 0 as has_table'),
                DB::raw('COUNT(DISTINCT pur.purchase_id) > 0 as has_food'),
                'b.branch_id',
                DB::raw("COALESCE(b.branch_name, 'N/A') as branch_name")

            )
            ->groupBy( 
                'o.order_id', 'o.order_status', 'p.final_amount', 'o.order_date',
                'res.start_time', 'b.branch_id', 'b.branch_name'
            ) 
            ->orderBy('display_time', 'desc')
            ->get();

        // 1c. เพิ่มสถานะ 'has_reviewed' เข้าไปใน bookings
        $bookings = $bookings->map(function ($booking) use ($reviewedOrderIds) {
            $booking->has_reviewed = $reviewedOrderIds->contains($booking->order_id);
            return $booking;
        });
        
        // --- 2. (ใหม่) ดึงข้อมูลสำหรับ "ประวัติการรีวิว" (Review History) ---
        $reviewHistory = DB::table('review as r')
            ->join('users as u', 'r.user_id', '=', 'u.user_id')
            ->join('orders as o', 'r.order_id', '=', 'o.order_id')
            ->leftJoin('reservation as res', 'o.order_id', '=', 'res.order_id')
            ->leftJoin('tables as t', 'res.table_id', '=', 't.table_id')
            ->leftJoin('branches as b', 't.branch_id', '=', 'b.branch_id')
            ->where('r.user_id', $userId)
            ->select(
                'r.rating',
                'r.review_descrpt', // (ใช้ชื่อคอลัมน์จริง)
                'r.created_at',
                DB::raw("COALESCE(b.branch_name, 'สั่งกลับบ้าน/ไม่ระบุ') as branch_name")
            )
            ->orderBy('r.created_at', 'desc')
            ->get();

        // --- 3. ส่งข้อมูลทั้งหมดไปที่ View ---
        return view('user.dashboard', [
            'bookings' => $bookings,
            'reviewHistory' => $reviewHistory // (ส่งตัวแปรใหม่ไปด้วย)
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