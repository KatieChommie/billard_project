<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// ต้องเพิ่ม Rule สำหรับ Validation ในการอัปเดต
use Illuminate\Validation\Rule; 

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

    DB::table('reservation')
        ->where('order_id', $order_id)
        ->where('reserve_status', 'confirmed') 
        ->update(['reserve_status' => 'completed']);

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

// **New Method: Handle User Deletion (คงไว้)**
public function deleteUser($user_id)
{
    // Safety check to prevent accidental admin deletion
    $deleted = DB::table('users')
                ->where('user_id', $user_id)
                ->whereNot('email', 'like', 'admin%') 
                ->delete();

    if ($deleted) {
        return back()->with('success', 'User #' . $user_id . ' has been deleted.');
    } else {
        return back()->with('error', 'Could not delete User #' . $user_id . '. (Perhaps user is an Admin?)');
    }
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

// **New Method: Display the branch edit form (คงไว้)**
public function editBranch($branch_id)
{
    $branch = DB::table('branches')->where('branch_id', $branch_id)->first();
    if (!$branch) {
        return back()->with('error', 'Branch not found.');
    }
    // ใช้ View เสมือน edit-form 
    return view('admin.edit-form', [
        'id' => $branch->branch_id,
        'type' => 'Branch',
        'data' => $branch,
        'fields' => ['branch_name', 'branch_info', 'branch_address', 'branch_phone', 'time_open', 'time_close']
    ]);
}

// **New Method: Handle Branch Update (คงไว้)**
public function updateBranch(Request $request, $branch_id)
{
    // 1. Validation
    $request->validate([
        'branch_name' => 'required|string|max:255',
        'branch_info' => 'nullable|string',
        'branch_address' => 'required|string',
        'branch_phone' => 'required|string|max:20',
        'time_open' => 'required|date_format:H:i:s', // ฐานข้อมูลใช้ time
        'time_close' => 'required|date_format:H:i:s|after:time_open',
    ]);

    // 2. Update Data
    $updated = DB::table('branches')
        ->where('branch_id', $branch_id)
        ->update([
            'branch_name' => $request->branch_name,
            'branch_info' => $request->branch_info,
            'branch_address' => $request->branch_address,
            'branch_phone' => $request->branch_phone,
            'time_open' => $request->time_open,
            'time_close' => $request->time_close,
        ]);

    if ($updated) {
        return redirect()->route('admin.branches')->with('success', 'Branch #' . $branch_id . ' data updated successfully!');
    }
    return back()->with('error', 'Failed to update branch data.');
}

public function manageTables(Request $request)
{
    // คอลัมน์ที่อนุญาตให้เรียง
    $validSortColumns = ['table_id', 'table_number', 'table_status', 'tables.branch_id'];
    $sortColumn = $request->input('sort', 'tables.branch_id');
    $sortDirection = $request->input('direction', 'asc');

    if (!in_array($sortColumn, $validSortColumns)) {
        $sortColumn = 'tables.branch_id';
    }
    if (!in_array($sortDirection, ['asc', 'desc'])) {
        $sortDirection = 'asc';
    }

    $tables = DB::table('tables')
        // Join เพื่อดึงชื่อสาขา
        ->join('branches', 'tables.branch_id', '=', 'branches.branch_id')
        ->select('tables.*', 'branches.branch_name')
        ->orderBy($sortColumn, $sortDirection)
        ->get();

    return view('admin.tables', [
        'tables' => $tables,
        'sortColumn' => $sortColumn,
        'sortDirection' => $sortDirection
    ]);
}


public function manageMenus(Request $request) // **อัปเดต: รับ Request**
    {
        // **เพิ่ม Logic สำหรับการเรียงลำดับ**
        $validSortColumns = ['menus.menu_id', 'menus.menu_name', 'menus.price'];
        $sortColumn = $request->input('sort', 'menus.branch_id'); // Default: เรียงตามสาขา
        $sortDirection = $request->input('direction', 'asc'); // Default: จากน้อยไปมาก

        // ตรวจสอบความถูกต้องของคอลัมน์และทิศทาง
        if (!in_array($sortColumn, $validSortColumns)) {
            $sortColumn = 'menus.branch_id';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        $menus = DB::table('menus')
            // (Join กับ branches เพื่อเอาชื่อสาขามาแสดง)
            ->join('branches', 'menus.branch_id', '=', 'branches.branch_id')
            // **New Join: เชื่อมกับ inventory เพื่อเอา stock_qty**
            ->leftJoin('inventory', function ($join) {
                $join->on('menus.branch_id', '=', 'inventory.branch_id')
                     ->on('menus.menu_id', '=', 'inventory.menu_id');
            })
            // เพิ่ม stock_qty ใน select
            ->select('menus.*', 'branches.branch_name', 'inventory.stock_qty') 
            // **อัปเดต: ใช้ orderBy จากผู้ใช้ก่อน**
            ->orderBy($sortColumn, $sortDirection) 
            // ตามด้วยการเรียงตาม branch_id เป็น secondary sort (ถ้าคอลัมน์ที่เรียงหลักไม่ใช่ branch_id)
            ->when($sortColumn !== 'menus.branch_id', function ($query) {
                return $query->orderBy('menus.branch_id');
            })
            ->orderBy('menus.menu_type')
            ->orderBy('menus.menu_name')
            ->get();
            
        return view('admin.menus', [
            'menus' => $menus,
            'sortColumn' => $sortColumn, 
            'sortDirection' => $sortDirection
        ]);
    }

// **New Method: Display the menu edit form (คงไว้)**
public function editMenu($branch_id, $menu_id)
{
    $menu = DB::table('menus')
        ->where('branch_id', $branch_id)
        ->where('menu_id', $menu_id)
        ->first();
    
    if (!$menu) {
        return back()->with('error', 'Menu not found.');
    }

    // ดึง stock_qty จาก inventory มาแสดงด้วย
    $inventory = DB::table('inventory')
        ->where('branch_id', $branch_id)
        ->where('menu_id', $menu_id)
        ->first();

    // เพิ่ม Stock Qty ใน object $menu
    $menu->stock_qty = $inventory->stock_qty ?? 0;

    // ใช้ View เสมือน edit-form
    return view('admin.edit-form', [
        'id' => $menu_id,
        'type' => 'Menu',
        'data' => $menu,
        'fields' => ['menu_name', 'menu_type', 'price', 'stock_qty'], // รวม stock_qty
        'branch_id' => $branch_id // จำเป็นสำหรับการอัปเดต Primary Key (menu_id, branch_id)
    ]);
}

// **New Method: Handle Menu Update (คงไว้)**
public function updateMenu(Request $request, $branch_id, $menu_id)
{
    // 1. Validation
    $request->validate([
        'menu_name' => 'required|string|max:255',
        'menu_type' => ['required', Rule::in(['meal', 'snack', 'drink'])],
        'price' => 'required|numeric|min:0',
        'stock_qty' => 'required|integer|min:0', // สำหรับ Inventory
    ]);

    // 2. Update Menu Data
    $updatedMenu = DB::table('menus')
        ->where('branch_id', $branch_id)
        ->where('menu_id', $menu_id)
        ->update([
            'menu_name' => $request->menu_name,
            'menu_type' => $request->menu_type,
            'price' => $request->price,
        ]);

    // 3. Update Inventory Data (ใช้ updateOrInsert เพื่อความมั่นใจว่ามี Stock Qty ในตาราง inventory)
    DB::table('inventory')
        ->updateOrInsert(
            ['branch_id' => $branch_id, 'menu_id' => $menu_id],
            ['stock_qty' => $request->stock_qty]
        );
    
    return redirect()->route('admin.menus')->with('success', 'Menu #' . $menu_id . ' (Branch ' . $branch_id . ') data updated successfully, including Inventory.');
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