<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    // ฟังก์ชันสำหรับแสดงหน้าฟอร์มเริ่มต้น (GET /booking/form)
    public function showBookingForm()
    {
        $branches = DB::table('branches')->get();
        
        // กำหนดค่าเริ่มต้น
        $firstBranch = $branches->first();
        
        // 1. ส่งข้อมูล Branch และค่า Default ไปให้ View
        return view('booking.table', [ // ใช้ view('booking.table')
            'branches' => $branches,
            'tables' => collect(), // ส่ง Collection ว่างไปก่อน (ยังไม่มีการค้นหาโต๊ะ)
            'branchName' => $firstBranch->branch_name ?? 'Select Branch', // ส่งชื่อสาขาแรก
            'branchId' => $firstBranch->branch_id ?? 0, // ส่ง ID สาขาแรกเป็น Default
            'startTime' => null,
            'duration' => 60,
        ]); 
    }

    // ฟังก์ชันหลัก: ตรวจสอบสถานะโต๊ะและแสดงผล (POST /booking/check)
    public function checkTableAvailability(Request $request) 
    {
        // 1. รับและตรวจสอบข้อมูล
        $request->validate([
            'branch_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:30' 
        ]);

        $branchId = $request->branch_id;
        $date = $request->date;
        $startTime = $date . ' ' . $request->start_time . ':00'; 
        $endTime = date('Y-m-d H:i:s', strtotime($startTime) + ($request->duration * 60)); 
        
        $allTables = DB::table('tables')
            ->where('branch_id', $branchId)
            ->orderBy('table_number')
            ->get();
        
        $branch = DB::table('branches')->where('branch_id', $branchId)->first();
        $branchName = $branch->branch_name ?? 'สาขา';
        
        $tablesWithStatus = [];

        foreach ($allTables as $table) {
            // ... (Logic Time Overlap และการกำหนด status_for_user) ...
            
            // A. สถานะถาวร (Unavailable)
            if ($table->table_status === 'unavailable') {
                $table->status_for_user = 'Unavailable'; 
                $table->status_color = 'gray';
            } else {
                // B. ตรวจสอบการทับซ้อนของการจอง (Time Overlap Logic)
                $conflictingReservation = DB::table('reservation')
                    ->where('table_id', $table->table_id)
                    ->where('reserve_status', 'confirmed')
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $endTime) 
                              ->where('end_time', '>', $startTime); 
                    })
                    ->exists();

                if ($conflictingReservation) {
                    $table->status_for_user = 'Reserved'; 
                    $table->status_color = 'red';
                } else {
                    $table->status_for_user = 'Available'; 
                    $table->status_color = 'green';
                }
            }
            $tablesWithStatus[] = $table;
        }
        
        // 4. ส่งข้อมูลไปที่ View สำหรับแสดงผลโต๊ะ
        return view('booking.table', [ // ใช้ view('booking.table')
            'tables' => collect($tablesWithStatus),
            'branchName' => $branchName,
            'branchId' => $branchId,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'duration' => $request->duration,
            'branches' => DB::table('branches')->get(), // ส่ง branches กลับไปเพื่อรักษารายการ Dropdown
        ]);
    }
    public function confirmBooking(Request $request) 
{
    // 1. Validation 
    $request->validate([
        'selected_table_ids' => 'required|string', // ตรวจสอบว่ามีการเลือกโต๊ะหรือไม่
        'reserve_name' => 'required|string|max:255',
        'branch_id' => 'required|integer',
        'start_time' => 'required',
        'end_time' => 'required',
        'duration' => 'required|integer'
    ]);

    // 2. Logic การบันทึก (DB Transaction)
    try {
        DB::transaction(function () use ($request) {
            $userId = Auth::id() ?? 1; // ใช้ User ID 1 ถ้ายังไม่ได้ล็อกอิน
            $tableIds = explode(',', $request->selected_table_ids);
            
            // a. สร้าง Order หลัก
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'order_date' => now(),
                'order_status' => 'pending', 
            ]);

            // b. สร้างรายการ Reservation สำหรับทุกโต๊ะที่เลือก
            foreach ($tableIds as $tableId) {
                DB::table('reservation')->insert([
                    'order_id' => $orderId,
                    'table_id' => $tableId,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'reserve_status' => 'confirmed',
                ]);
            }
            
            // c. คำนวณราคาและสร้างรายการ Payment
            $durationMins = $request->duration;
            $totalTables = count($tableIds);
            $totalAmount = (50 * ($durationMins / 30)) * $totalTables; // 50 บาท/30 นาที
            
            DB::table('payment')->insert([
                'order_id' => $orderId,
                'total_amount' => $totalAmount,
                'final_amount' => $totalAmount,
                'pay_status' => 'pending',
            ]);
        });

        // 3. นำทางไปยังหน้า Checkout
        return redirect()->route('checkout.page')->with('success', 'จองโต๊ะสำเร็จ! โปรดชำระเงิน');

    } catch (\Exception $e) {
        // จัดการ Error (Foreign Key, DB Fails)
        return back()->withInput()->withErrors(['message' => 'เกิดข้อผิดพลาดในการบันทึกการจอง: ' . $e->getMessage()]);
    }
}
}