<?php

namespace App\Http\Controllers\UserController;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * ต้องใช้ Middleware 'auth' เพื่อให้แน่ใจว่าผู้ใช้ล็อกอินแล้ว
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * แสดงหน้า User Dashboard
     */
    public function index()
    {
        // ** (Mock Data/Logic ที่คุณจะต้องเพิ่มในอนาคต) **
        // ดึงข้อมูลประวัติการจอง, การซื้อ, ฯลฯ จากฐานข้อมูล
        
        $user = Auth::user();
        
        // ส่งข้อมูลไปยัง View
        return view('user.dashboard', [
            'user' => $user,
            // 'latest_booking' => Booking::where('user_id', $user->id)->latest()->first(),
            // 'purchase_history' => Purchase::where('user_id', $user->id)->limit(5)->get(),
        ]);
    }
}