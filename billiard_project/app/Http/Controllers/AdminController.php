<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule; 

class AdminController extends Controller
{
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
            ->where('orders.order_status', 'confirmed')
            ->orderBy('reservation.start_time', 'asc')
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


        return view('admin.dashboard', [
            'userCount'     => $userCount,
            'todayBookings' => $todayBookings,
            'reviewCount'   => $reviewCount,
            'ordersToComplete' => $ordersToComplete,
        ]);
    }

public function markAsCompleted(Request $request)
{
    $order_id = $request->input('order_id');
    if (!$order_id) {
        return back()->with('error', 'Order ID not found.');
    }

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
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        if (!in_array($sortColumn, $validSortColumns)) {
            $sortColumn = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $users = DB::table('users')
            ->whereNot('email', 'like', 'admin%')
            ->select(
                'user_id', 
                'first_name', 
                'last_name', 
                'email', 
                'phone_number',
                'loyalty_points',
                'created_at'
            )
            ->orderBy($sortColumn, $sortDirection)
            ->get();

        return view('admin.users', [
            'users' => $users,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection
        ]);
    }

public function deleteUser($user_id)
{
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

/*public function editBranch($branch_id)
{
    $branch = DB::table('branches')->where('branch_id', $branch_id)->first();
    if (!$branch) {
        return back()->with('error', 'Branch not found.');
    }
    return view('admin.edit-form', [
        'id' => $branch->branch_id,
        'type' => 'Branch',
        'data' => $branch,
        'fields' => ['branch_name', 'branch_info', 'branch_address', 'branch_phone', 'time_open', 'time_close']
    ]);
}*/

/*public function updateBranch(Request $request, $branch_id)
{
    $request->validate([
        'branch_name' => 'required|string|max:255',
        'branch_info' => 'nullable|string',
        'branch_address' => 'required|string',
        'branch_phone' => 'required|string|max:20',
        'time_open' => 'required|date_format:H:i:s',
        'time_close' => 'required|date_format:H:i:s|after:time_open',
    ]);

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
}*/

/*public function manageTables(Request $request)
{
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
        ->join('branches', 'tables.branch_id', '=', 'branches.branch_id')
        ->select('tables.*', 'branches.branch_name')
        ->orderBy($sortColumn, $sortDirection)
        ->get();

    return view('admin.tables', [
        'tables' => $tables,
        'sortColumn' => $sortColumn,
        'sortDirection' => $sortDirection
    ]);
}*/

public function showTableAvailability(Request $request)
{
    $branches = DB::table('branches')->get();

    $branchId = $request->input('branch_id');
    $date = $request->input('date', Carbon::today()->toDateString());
    $time = $request->input('time', '18:00');
    $duration = 1;

    $availableTables = collect();
    $selectedBranch = null;

    if ($branchId) {
        $selectedBranch = $branches->firstWhere('branch_id', $branchId);

        $startTime = Carbon::parse("$date $time");
        $endTime = $startTime->copy()->addHours($duration);

        $allTables = DB::table('tables')
            ->where('branch_id', $branchId)
            ->get();

        $reservedTables = DB::table('reservation')
            ->join('orders', 'reservation.order_id', '=', 'orders.order_id')
            ->join('tables', 'reservation.table_id', '=', 'tables.table_id') 

            ->whereIn('reservation.table_id', $allTables->pluck('table_id'))

            ->whereDate('reservation.start_time', $date) 
            ->where('reservation.reserve_status', '!=', 'cancelled') 
            
            ->select('reservation.*', 'orders.user_id') 
            ->get();

        foreach ($allTables as $table) {
            $isBooked = false;
            $bookingDetails = null;

            $tableReservations = $reservedTables->where('table_id', $table->table_id);


            foreach ($tableReservations as $reservation) {

                $resStartTime = Carbon::parse($reservation->start_time);
                $resEndTime = Carbon::parse($reservation->end_time);

                $hasConflict = $startTime->lt($resEndTime) && $endTime->gt($resStartTime);

                if ($hasConflict) {
                    $isBooked = true;
                    $user = DB::table('users')->where('user_id', $reservation->user_id)->first(); 
                    $bookingDetails = (object)[
                        'reserve_id' => $reservation->reserve_id, 
                        'start_time' => $resStartTime->format('H:i'),
                        'end_time' => $resEndTime->format('H:i'),
                        'user_name' => $user ? "$user->first_name $user->last_name" : 'N/A',
                    ];
                    break;
                }
            }

            $availableTables->push((object)[
                'table_id' => $table->table_id,
                'table_number' => $table->table_number,
                'is_booked' => $isBooked,
                'details' => $bookingDetails,
                'current_status' => $table->table_status, 
            ]);
        }
    }

    return view('admin.table-availability', [
        'branches' => $branches,
        'selectedBranchId' => $branchId,
        'selectedDate' => $date,
        'selectedTime' => $time,
        'availableTables' => $availableTables,
        'selectedBranch' => $selectedBranch,
    ]);
}

public function manageMenus(Request $request)
    {
        $validSortColumns = ['menus.menu_id', 'menus.menu_name', 'menus.price'];
        $sortColumn = $request->input('sort', 'menus.branch_id');
        $sortDirection = $request->input('direction', 'asc');

        if (!in_array($sortColumn, $validSortColumns)) {
            $sortColumn = 'menus.branch_id';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        $menus = DB::table('menus')
            ->join('branches', 'menus.branch_id', '=', 'branches.branch_id')
            ->leftJoin('inventory', function ($join) {
                $join->on('menus.branch_id', '=', 'inventory.branch_id')
                     ->on('menus.menu_id', '=', 'inventory.menu_id');
            })
            ->select('menus.*', 'branches.branch_name', 'inventory.stock_qty') 
            ->orderBy($sortColumn, $sortDirection)
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

public function editMenu($branch_id, $menu_id)
{
    $menu = DB::table('menus')
        ->where('branch_id', $branch_id)
        ->where('menu_id', $menu_id)
        ->first();
    
    if (!$menu) {
        return back()->with('error', 'Menu not found.');
    }

    $inventory = DB::table('inventory')
        ->where('branch_id', $branch_id)
        ->where('menu_id', $menu_id)
        ->first();

    $menu->stock_qty = $inventory->stock_qty ?? 0;

    return view('admin.edit-form', [
        'id' => $menu_id,
        'type' => 'Menu',
        'data' => $menu,
        'fields' => ['menu_name', 'menu_type', 'price', 'stock_qty'],
        'branch_id' => $branch_id
    ]);
}

/*public function updateMenu(Request $request, $branch_id, $menu_id)
{
    $request->validate([
        'menu_name' => 'required|string|max:255',
        'menu_type' => ['required', Rule::in(['meal', 'snack', 'drink'])],
        'price' => 'required|numeric|min:0',
        'stock_qty' => 'required|integer|min:0',
    ]);

    $updatedMenu = DB::table('menus')
        ->where('branch_id', $branch_id)
        ->where('menu_id', $menu_id)
        ->update([
            'menu_name' => $request->menu_name,
            'menu_type' => $request->menu_type,
            'price' => $request->price,
        ]);

    DB::table('inventory')
        ->updateOrInsert(
            ['branch_id' => $branch_id, 'menu_id' => $menu_id],
            ['stock_qty' => $request->stock_qty]
        );
    
    return redirect()->route('admin.menus')->with('success', 'Menu #' . $menu_id . ' (Branch ' . $branch_id . ') data updated successfully, including Inventory.');
}*/

public function manageBookings(Request $request)
    {
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
            ->paginate(20);
            
        return view('admin.bookings', [
            'bookings' => $allBookings,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection
        ]);
    }
}