<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the Admin Dashboard view.
     */
    public function dashboard()
    {
        // ในอนาคตจะมีการดึงข้อมูลสถิติจากฐานข้อมูลมาที่นี่
        
        // โหลด resources/views/admin/dashboard.blade.php
        return view('admin.dashboard'); 
    }
}
