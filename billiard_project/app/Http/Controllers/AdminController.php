<?php

namespace App\Http\Controllers;

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
}
