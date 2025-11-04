<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class AdminController extends Controller
{
    /**
     * Display the Admin Dashboard view.
     */
    public function dashboard()
{
    $userCount = DB::table('users')->count();
    $todayBookings = DB::table('reservation')
                        ->whereDate('start_time', today()) // กรองเฉพาะ 'start_time' ที่เป็นวันนี้
                        ->count();
    $reviewCount = DB::table('review')->count();

    // 2. ส่งตัวแปร 3 ตัวนี้ไปให้ View
    return view('admin.dashboard', [
        'userCount'     => $userCount,
        'todayBookings' => $todayBookings,
        'reviewCount'   => $reviewCount,
    ]);
}
public function markAsCompleted(Request $request)
    {
        // 1. ตรวจสอบสิทธิ์ (สมมติว่าคุณมี Admin Middleware แล้ว)
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return redirect('/admin/login')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
        ]);

        $orderId = $request->input('order_id');

        try {
            DB::transaction(function () use ($orderId) {
                
                // 1. อัปเดต Order Status เป็น 'completed'
                DB::table('orders')
                    ->where('order_id', $orderId)
                    ->update(['order_status' => 'completed']);
                
                // 2. อัปเดต Reservation Status (ถ้ามี) เป็น 'completed'
                DB::table('reservation')
                    ->where('order_id', $orderId)
                    ->update(['reserve_status' => 'completed']);
            });

            return back()->with('success', "Order ID #{$orderId} ถูกทำเครื่องหมายว่าเสร็จสิ้นแล้ว!");

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'เกิดข้อผิดพลาดในการทำเครื่องหมายเสร็จสิ้น: ' . $e->getMessage()]);
        }
    }
}
