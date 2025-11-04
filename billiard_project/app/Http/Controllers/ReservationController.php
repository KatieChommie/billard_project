<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ReservationController extends Controller
{
    // ฟังก์ชันสำหรับแสดงหน้าฟอร์มเริ่มต้น (GET /booking/form)
    // ฟังก์ชันสำหรับแสดงหน้าฟอร์มเริ่มต้น (GET /booking/form/{branchId})
    public function showBookingForm($branchId)
    {
        // 1. ตรวจสอบว่ามี "ผลลัพธ์จากการค้นหา" (ที่ส่งมาจาก checkTableAvailability) หรือไม่
        if (session()->has('search_results')) {
            
            // 1a. ถ้ามี, ให้ใช้ข้อมูลนั้นแสดงผล
            $allBranches = DB::table('branches')->get();
            $viewData = session('search_results');
            
            // ผสานข้อมูลสาขาทั้งหมดเข้าไปใน viewData (เพื่อให้ Dropdown สาขาทำงานได้)
            $viewData['branches'] = $allBranches;
            
            return view('booking.table', $viewData);
        }

        // --- (แก้ไขส่วนนี้) ---
        // 2. ถ้าไม่มี (คือการโหลดหน้าครั้งแรกปกติ)
        
        // 2a. ค้นหาสาขาทั้งหมด และสาขาที่เลือก
        $branches = DB::table('branches')->get();
        $selectedBranch = $branches->where('branch_id', $branchId)->first();
        if (!$selectedBranch) {
          $selectedBranch = $branches->first();
        }
        
        // 2b. (เพิ่ม) ค้นหาโต๊ะทั้งหมดในสาขานั้น
        $tablesInBranch = DB::table('tables')
                            ->where('branch_id', $selectedBranch->branch_id)
                            ->get();

        // 2c. (เพิ่ม) "แปล" สถานะโต๊ะให้เป็น Tailwind Class (เหมือนใน checkTableAvailability)
        $tablesInBranch->map(function ($table) {
            
            if ($table->table_status === 'available') {
                $table->tailwind_color = 'bg-green-500 hover:bg-green-400'; 
                $table->status_for_user = 'ว่าง'; 
            
            } elseif ($table->table_status === 'reserved') {
                $table->tailwind_color = 'bg-red-600 cursor-not-allowed'; 
                $table->status_for_user = 'จองแล้ว';
            
            } else { // (unavailable)
                $table->tailwind_color = 'bg-gray-500 cursor-not-allowed'; 
                $table->status_for_user = 'ไม่ว่าง';
            }
            return $table;
        });

        // 2d. (แก้ไข) ส่ง $tablesInBranch (ที่มีโต๊ะ) ไปแทน collect()
        return view('booking.table', [ 
            'branches' => $branches,
            'tables' => $tablesInBranch, // <-- แก้ไขตรงนี้
            'branchName' => $selectedBranch->branch_name,
            'branchId' => $selectedBranch->branch_id,
            'startTime' => null,
            'duration' => 60,
            'date' => null, 
            'endTime' => null, 
        ]);
    }

    // ฟังก์ชันหลัก: ตรวจสอบสถานะโต๊ะและแสดงผล (POST /booking/check)
    public function checkTableAvailability(Request $request) 
    {
        // 1. รับและตรวจสอบข้อมูล (เหมือนเดิม)
        $request->validate([
            'branch_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:30',
        ]);

        // 2. Logic การคำนวณเวลา (เหมือนเดิมเป๊ะ)
        $branchId = $request->branch_id;
        $date = $request->date;
        $startTime = $request->start_time;
        $durationMins = (int) $request->duration;
        
        $startTimeCarbon = Carbon::parse($date . ' ' . $startTime);
        $endTime = $startTimeCarbon->copy()->addMinutes($durationMins);

        $branch = DB::table('branches')->where('branch_id', $branchId)->first();
        if (!$branch) {
            return back()->withErrors(['branch_id' => 'ไม่พบสาขาที่เลือก']);
        }

        $openTime = $branch->time_open; // (12:00:00)
        $closeTime = $branch->time_close; // (02:00:00)

        $shopOpen = Carbon::parse($date . ' ' . $openTime);
        $shopClose = Carbon::parse($date . ' ' . $closeTime);

        if ($shopClose->lt($shopOpen)) {
            $shopClose->addDay();
        }

        // ... (โค้ดตรวจสอบ Error นอกเวลาทำการ... เหมือนเดิม) ...
        if ($startTimeCarbon->lt($shopOpen) || $endTime->gt($shopClose)) {
            return back()->withInput()->withErrors(['start_time' => 'เวลาที่เลือกอยู่นอกเวลาทำการ']);
        }

        // 3. Logic การค้นหาโต๊ะ (เหมือนเดิมเป๊ะ)
        $conflictingReservations = DB::table('reservation')
            ->join('tables', 'reservation.table_id', '=', 'tables.table_id')
            ->where('tables.branch_id', $branchId) // <-- กรองเฉพาะโต๊ะในสาขานี้
            // -----------------------
            ->where('reservation.start_time', '<', $endTime)
            ->where('reservation.end_time', '>', $startTimeCarbon)
            ->where('reservation.reserve_status', '!=', 'cancelled')
            ->pluck('reservation.table_id');
        
        $availableTables = DB::table('tables')
            // ... (โค้ด query ของคุณ... เหมือนเดิม) ...
            ->where('branch_id', $branchId)
            ->whereNotIn('table_id', $conflictingReservations)
            ->get();

        $availableTables->map(function ($table) {
            
            // (อ้างอิงชื่อ class จากไฟล์ app.css ของคุณ)
            if ($table->table_status === 'available') {
                // Tailwind class for "Green" (ว่าง)
                $table->tailwind_color = 'bg-green-500 hover:bg-green-400'; 
                $table->status_for_user = 'ว่าง'; 

            } elseif ($table->table_status === 'reserved') {
                // Tailwind class for "Red" (จองแล้ว)
                $table->tailwind_color = 'bg-red-600 cursor-not-allowed'; 
                $table->status_for_user = 'จองแล้ว';

            } else { // (unavailable)
                // Tailwind class for "Gray" (ไม่ว่าง)
                $table->tailwind_color = 'bg-gray-500 cursor-not-allowed'; 
                $table->status_for_user = 'ไม่ว่าง';
            }
            
            return $table;
        });

        // 4. --- (ส่วนที่เปลี่ยน) ---
        // รวบรวมข้อมูลทั้งหมดเพื่อส่งกลับ
        $viewData = [
            'tables' => $availableTables,
            'branchName' => $branch->branch_name,
            'branchId' => $branch->branch_id,
            'date' => $request->date,
            'startTime' => $request->start_time,
            'duration' => $request->duration,
            'endTime' => $endTime->format('Y-m-d H:i:s'), // ส่ง endTime ที่คำนวณแล้ว
        ];
        
        // 5. --- (ส่วนที่เปลี่ยน) ---
        // แทนที่จะ return view, ให้ Redirect กลับไปที่ GET route
        // พร้อมกับ "ฝาก" ข้อมูลผลลัพธ์ไว้ใน Session (Flash Data)
        return redirect()->route('booking.table', ['branchId' => $branchId]) 
                         ->with('search_results', $viewData);
    }
    public function reserveBooking(Request $request) 
{
    // 0. Ensure user is authenticated
    if (!Auth::check()) {
        // Redirect guests to login before allowing reservation
        return redirect()->route('login')->with('warning', 'กรุณาเข้าสู่ระบบก่อนทำการจอง');
    }

    // 1. Validation 
    $request->validate([
            // *** อย่าลืมแก้ Bug ที่เราคุยกันก่อนหน้านี้ ***
            'selected_tables' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s',
            'duration' => 'required|integer',
        ]);

    // 2. Logic การบันทึก (DB Transaction)
    try {
        DB::transaction(function () use ($request) {
                
                $userId = Auth::id();

                // *** แก้ไข: แปลง String "101,102" เป็น Array ***
                $tableIds = explode(',', $request->input('selected_tables'));
            
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
            $durationMins = (int) $request->duration;
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