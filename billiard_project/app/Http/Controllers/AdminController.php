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
        $userCount = DB::table('users')->whereNot('email', 'like', 'admin%')->count();
        $todayBookings = DB::table('reservation')
                            ->whereDate('start_time', today())
                            ->count();
        $reviewCount = DB::table('review')->count();

        $ordersToComplete = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.user_id')
            ->join('payment', 'orders.order_id', '=', 'payment.order_id')
            ->join('reservation', 'orders.order_id', '=', 'reservation.order_id')
            ->where('orders.order_status', 'confirmed') // <-- ดึงเฉพาะ Order ที่ 'confirmed'
            ->orderBy('reservation.start_time', 'asc') // (เรียงตามเวลานัด)
            ->select(
                'orders.order_id',
                'orders.order_status',
                'users.first_name',
                'users.last_name',
                'reservation.start_time',
                'payment.pay_method',
                'payment.final_amount'
            )
            ->groupBy(
                'orders.order_id', 'orders.order_status', 'users.first_name', 'users.last_name',
                'reservation.start_time', 'payment.pay_method', 'payment.final_amount'
            )
            ->get();


        // 2. (แก้ไข) ส่งตัวแปรทั้งหมดไปให้ View
        return view('admin.dashboard', [
            'userCount'     => $userCount,
            'todayBookings' => $todayBookings,
            'reviewCount'   => $reviewCount,
            'ordersToComplete' => $ordersToComplete, // <-- (ส่งตัวแปรใหม่นี้ไป)
        ]);
    }

public function markAsCompleted($order_id)
{
    // (กัน Admin กดพลาด)
    // อนุญาตให้อัปเดตเฉพาะ Order ที่จ่ายเงินแล้ว (confirmed)
    DB::table('orders')
        ->where('order_id', $order_id)
        ->where('order_status', 'confirmed') 
        ->update(['order_status' => 'completed']);

    return back()->with('success', 'Order #' . $order_id . ' marked as completed!');
}

public function manageUsers(Request $request)
    {
        $validSortColumns = ['user_id', 'first_name', 'loyalty_points', 'created_at'];
        $sortColumn = $request->input('sort', 'created_at'); // Default: เรียงตามวันที่สมัคร
        $sortDirection = $request->input('direction', 'desc'); // Default: ใหม่ไปเก่า

        if (!in_array($sortColumn, $validSortColumns)) {
            $sortColumn = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $users = DB::table('users')
            ->whereNot('email', 'like', 'admin%') //ที่ไม่ใช่แอดมิน
            ->select(
                'user_id', 
                'first_name', 
                'last_name', 
                'email', 
                'phone_number',
                'loyalty_points',
                'created_at'
            )
            ->orderBy($sortColumn, $sortDirection) // เรียงจากใหม่ไปเก่า
            ->get();

        return view('admin.users', [
            'users' => $users,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection
        ]);
    }
public function manageBranches(Request $request)
    {
        $validSortColumns = ['branch_id', 'branch_name', 'time_open'];
        $sortColumn = $request->input('sort', 'branch_id');
        $sortDirection = $request->input('direction', 'asc'); 

        if (!in_array($sortColumn, $validSortColumns)) {
            $sortColumn = 'branch_id';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        $branches = DB::table('branches')
            ->orderBy($sortColumn, $sortDirection)
            ->get();
            
        return view('admin.branches', [
            'branches' => $branches,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection
        ]);
    }
public function manageMenus()
    {
        $menus = DB::table('menus')
            // (Join กับ branches เพื่อเอาชื่อสาขามาแสดง)
            ->join('branches', 'menus.branch_id', '=', 'branches.branch_id')
            ->select('menus.*', 'branches.branch_name')
            ->orderBy('menus.branch_id')
            ->orderBy('menus.menu_type')
            ->orderBy('menus.menu_name')
            ->get();
            
        return view('admin.menus', [
            'menus' => $menus
        ]);
    }

public function manageBookings(Request $request)
    {
        // คอลัมน์ที่อนุญาตให้เรียง (รวมชื่อจากตารางที่ Join มาด้วย)
        $validSortColumns = ['orders.order_id', 'start_time', 'first_name', 'final_amount', 'order_status', 'pay_status'];
        $sortColumn = $request->input('sort', 'orders.order_id');
        $sortDirection = $request->input('direction', 'desc');

        if (!in_array($sortColumn, $validSortColumns)) $sortColumn = 'orders.order_id';
        if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'desc';

        $allBookings = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.user_id')
            ->join('payment', 'orders.order_id', '=', 'payment.order_id')
            ->join('reservation', 'orders.order_id', '=', 'reservation.order_id')
            ->orderBy($sortColumn, $sortDirection)
            ->select(
                'orders.order_id', 'orders.order_status', 'users.first_name', 'users.last_name',
                'reservation.start_time', 'payment.pay_method', 'payment.final_amount',
                'payment.pay_status'
            )
            ->groupBy(
                'orders.order_id', 'orders.order_status', 'users.first_name', 'users.last_name',
                'reservation.start_time', 'payment.pay_method', 'payment.final_amount',
                'payment.pay_status'
            )
            ->paginate(20); // (แบ่งหน้า หน้าละ 20)
            
        return view('admin.bookings', [
            'bookings' => $allBookings,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection
        ]);
    }
}
