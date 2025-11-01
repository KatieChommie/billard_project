<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    public function index()
    {

        return view('index');
    }
    //login and registration
    public function login()
    {
        
        return view('login'); 
    }

    public function register()
    {
        
        return view('register');
    }

    public function menu(int $branchId = 101) 
    {
        // 1. รับ Branch ID จาก URL (ตาม Route ที่กำหนด)
        $branches = DB::table('branches')->get();
        
        // 2. กำหนด Branch ID ที่จะแสดงผล
        // ถ้า $branchId เป็น 0 (หรือค่าที่ไม่ถูกต้อง) เราจะใช้ค่า Default 101
        $selectedBranchId = ($branchId == 0 || !$branches->contains('branch_id', $branchId)) ? 101 : $branchId;

        // 2. ดึงข้อมูลเมนูทั้งหมดสำหรับสาขาที่เลือก (ใช้ $selectedBranchId)
        $rawMenuData = DB::table('menus')
            ->where('menus.branch_id', $selectedBranchId) // <-- กรองตาม ID ที่รับมา
            ->get();

        // 3. จัดกลุ่มข้อมูลตามประเภท (Meal, Snack, Drink)
        $groupedMenu = $rawMenuData->groupBy('menu_type');
        
        // 4. ส่งตัวแปร $groupedMenu ไปให้ View
        return view('menu', [
            'groupedMenu' => $groupedMenu,
            'branches' => $branches, // <-- **ส่งตัวแปรนี้**
            'selectedBranchId' => $selectedBranchId
        ]);
    }

    //booking-category
    public function branches() // <-- นี่คือฟังก์ชันใน Route Context
    {
        // 2. ดึงข้อมูลสาขาทั้งหมดจากฐานข้อมูล
        $branches = DB::table('branches')->get();

        // 3. ส่งตัวแปร $branches ไปที่ View
        // (เราใช้ 'booking.branches' เพราะไฟล์ของคุณอยู่ที่ resources/views/booking/branches.blade.php)
        return view('booking.branches', ['branches' => $branches]);
    }

    public function table()
    {

        return view('booking.table');
    }
    public function reservation()
    {

        return view('booking.reservation');
    }

    //review
    public function reviews()
    {
    
        return view('reviews'); 
    }

    //order
    public function order()
    {

        return view('orders.order'); 
    }
    
    //points-category
    public function points()
    {
   
        return view('points.points'); 
    }
    public function point_transact()
    {
        
        return view('points.point_transact'); 
    }

    //cart
    public function cart()
    {
        
        return view('carts.cart'); 
    }
    
    
}