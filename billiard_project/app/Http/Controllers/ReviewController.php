<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * 1. (แก้ไข) แสดงฟอร์ม (รับทั้ง order_id และ branch_id)
     */
    public function create($order_id, $branch_id)
    {
        $userId = Auth::id();

        // 1. ตรวจสอบ Order ว่าเป็นของ User นี้จริง, confirmed
        $order = DB::table('orders')
                    ->where('order_id', $order_id)
                    ->where('user_id', $userId)
                    ->where('order_status', 'completed')
                    ->first();

        // (กัน) ถ้าไม่เจอ Order
        if (!$order) {
            return redirect()->route('user.dashboard')->withErrors(['message' => 'ไม่สามารถรีวิว Order นี้ได้']);
        }

        // 2. (แก้ไข) ตรวจสอบว่า "Order นี้" ถูกรีวิวไปแล้วหรือยัง
        $existingReview = DB::table('review')
                            ->where('order_id', $order_id) // (เช็คด้วย order_id)
                            ->where('user_id', $userId)
                            ->first();

        if ($existingReview) {
            return redirect()->route('user.dashboard')->withErrors(['message' => 'คุณได้รีวิว Order นี้ไปแล้ว']);
        }
        
        // 3. ดึงข้อมูลสาขา (สำหรับแสดงชื่อ)
        $branch = DB::table('branches')->where('branch_id', $branch_id)->first();
        if (!$branch) {
            return redirect()->route('user.dashboard')->withErrors(['message' => 'ไม่พบข้อมูลสาขา']);
        }
        
        // 4. ส่งไปที่ View (ที่เราจะสร้าง)
        return view('review.create', [
            'order_id' => $order_id,
            'branch' => $branch
        ]);
    }

    /**
     * 2. (แก้ไข) บันทึกข้อมูล (ใช้ order_id)
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        
        $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'branch_id' => 'required|integer|exists:branches,branch_id', // (เก็บไว้เช็คเฉยๆ)
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500', // (ใช้ 'comment' จาก form)
        ]);
        
        $orderId = $request->input('order_id');

        // (กัน) ตรวจสอบสิทธิ์อีกครั้ง
        $order = DB::table('orders')
                    ->where('order_id', $orderId)
                    ->where('user_id', $userId)
                    ->where('order_status', 'completed')
                    ->first();
        
        if (!$order) {
            return back()->withInput()->withErrors(['message' => 'คุณไม่มีสิทธิ์รีวิว Order นี้']);
        }

        // (กัน Double Click)
        $existingReview = DB::table('review')
                            ->where('order_id', $orderId)
                            ->where('user_id', $userId)
                            ->first();
        
        if ($existingReview) {
            return redirect()->route('user.dashboard')->withErrors(['message' => 'คุณได้รีวิว Order นี้ไปแล้ว']);
        }

        // 4. บันทึกข้อมูล (ใช้ชื่อคอลัมน์จริงจาก DB)
        try {
            DB::table('review')->insert([
                'order_id' => $orderId,
                'user_id' => $userId,
                'rating' => $request->input('rating'),
                'review_descrpt' => $request->input('comment'), // (สำคัญ!)
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('user.dashboard')->with('success', 'ขอบคุณสำหรับรีวิวครับ!');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
}