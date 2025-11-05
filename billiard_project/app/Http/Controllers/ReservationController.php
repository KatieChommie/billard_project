<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
 
    // ฟังก์ชันสำหรับแสดงหน้าฟอร์มเริ่มต้น (GET /booking/form/{branchId})
    public function showBookingForm($branchId)
    {
        // 1. ตรวจสอบว่ามี \"ผลลัพธ์จากการค้นหา\" (ที่ส่งมาจาก checkTableAvailability) หรือไม่
        if (session()->has('search_results')) {
            
            // 1a. ถ้ามี, ให้ใช้ข้อมูลนั้นแสดงผล
            $allBranches = DB::table('branches')->get();
            $viewData = session('search_results');
            
            // ผสานข้อมูลสาขาทั้งหมดเข้าไปใน viewData (เพื่อให้ Dropdown สาขาทำงานได้)
            $viewData['branches'] = $allBranches;
            
            // (เพิ่ม) ส่ง $branchId ไปด้วย (สำหรับ Form)
            $viewData['branchId'] = $branchId; 

            // (เพิ่ม) ถ้าผู้ใช้ส่ง selectedBranch มา ให้ตั้งชื่อสาขาให้ view ใช้งานได้
            if (isset($viewData['selectedBranch']) && is_object($viewData['selectedBranch'])) {
                $viewData['branchName'] = $viewData['selectedBranch']->branch_name ?? null;
            } else {
                $branchObj = $allBranches->firstWhere('branch_id', $branchId);
                $viewData['branchName'] = $branchObj ? $branchObj->branch_name : null;
            }

            // (เพิ่ม) ให้แน่ใจว่า tables เป็น Collection/array ที่ view จะ iterate ได้
            if (isset($viewData['tables'])) {
                $viewData['tables'] = collect($viewData['tables']);
            }
            
            return view('booking.table', $viewData);
        }

        // --- (แก้ไขส่วนนี้) ---
        // 2. ถ้าไม่มี (คือการโหลดหน้าครั้งแรกปกติ)
        
        // 2a. ค้นหาสาขาทั้งหมด และสาขาที่เลือก
        $branches = DB::table('branches')->get();
        $selectedBranch = $branches->where('branch_id', $branchId)->first();
        if (!$selectedBranch) {
          $selectedBranch = $branches->first();
          // (สำคัญ) ถ้าสาขาไม่ถูก ให้ใช้ ID ของสาขาแรกแทน
          $branchId = $selectedBranch->branch_id; 
        }
        
        // 2b. (เพิ่ม) ค้นหาโต๊ะทั้งหมดในสาขานั้น
        $tablesInBranch = DB::table('tables')
                            ->where('branch_id', $selectedBranch->branch_id)
                            ->get();

        // 2c. ตั้งค่าสถานะเริ่มต้นให้โต๊ะทั้งหมด (แก้ Bug)
        $tablesWithStatus = $tablesInBranch->map(function ($table) {
            $table->is_available = true; // (เพิ่ม property ที่หายไป)
            return $table;
        });

        // 3. (แก้ไข) ส่งข้อมูลไปที่ View
        return view('booking.table', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'tables' => $tablesWithStatus, 
            'userInput' => null,
            
            // --- (เพิ่มบรรทัดนี้ที่หายไป) ---
            'branchId' => $branchId 
        ]);
    }
    // ฟังก์ชันหลัก: ตรวจสอบสถานะโต๊ะและแสดงผล (POST /booking/check)
    // (ฟังก์ชันนี้อยู่ประมาณบรรทัด 74)
    // (ฟังก์ชันนี้อยู่ประมาณบรรทัด 74)
    public function checkTableAvailability(Request $request)
    {
        // 1. ตรวจสอบข้อมูล Input
        $request->validate([
            'branch_id' => 'required|integer|exists:branches,branch_id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i', // (ต้องเป็น H:i เช่น 14:00)
            'duration' => 'required|integer|min:30', // (เช่น 120 นาที)
        ]);

        $branchId = $request->input('branch_id');
        $selectedDate = $request->input('date'); // (Y-m-d)
        $selectedTime = $request->input('time'); // (H:i)
        $duration = (int)$request->input('duration');

        // 2. (ใหม่) คำนวณช่วงเวลาที่ User ต้องการจอง
        try {
            // (Carbon คือ Library จัดการเวลาของ Laravel)
            $userStartTime = Carbon::parse($selectedDate . ' ' . $selectedTime);
            $userEndTime = $userStartTime->copy()->addMinutes($duration);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['time' => 'รูปแบบเวลาไม่ถูกต้อง']);
        }

        // 3. (ใหม่) ค้นหา "ID โต๊ะ" ที่ "ถูกจองแล้ว" ใน "ช่วงเวลานั้น"
        $bookedTableIds = DB::table('reservation')
            ->join('orders', 'reservation.order_id', '=', 'orders.order_id')
            ->join('tables', 'reservation.table_id', '=', 'tables.table_id') // (Join tables เพื่อเอา branch_id)
            ->where('tables.branch_id', $branchId) // (เช็คสาขาจาก tables)
            
            // (สำคัญ) เช็คสถานะที่ยัง Active หรือเพิ่งเล่นจบ
            ->whereIn('orders.order_status', ['pending', 'confirmed', 'completed']) 
            
            // --- นี่คือ Logic ใหม่ที่แก้ไข Bug (Time Overlap) ---
            ->where(function ($query) use ($userStartTime, $userEndTime) {
                // ตรรกะคือ: (เวลาเริ่มจอง < เวลาจบของเรา) AND (เวลาจบจอง > เวลาเริ่มของเรา)
                // มันจะจับทุกการจองที่คาบเกี่ยวกัน
                $query->where('reservation.start_time', '<', $userEndTime)
                      ->where('reservation.end_time', '>', $userStartTime);
            })
            // --- จบ Logic ใหม่ ---
            
            ->pluck('reservation.table_id')
            ->unique();

        // 4. ค้นหาโต๊ะทั้งหมดในสาขานั้น
        $allTablesInBranch = DB::table('tables')
            ->where('branch_id', $branchId)
            ->get();

        // 5. (ใหม่) แยกโต๊ะ (ว่าง / ไม่ว่าง)
        // (เราจะเพิ่ม property 'is_available' เข้าไป)
        $tables = $allTablesInBranch->map(function ($table) use ($bookedTableIds) {
            // โต๊ะนี้จะ "ว่าง" (is_available = true)
            // ก็ต่อเมื่อ ID ของมัน "ไม่" อยู่ในลิสต์ $bookedTableIds
            $table->is_available = !$bookedTableIds->contains($table->table_id);
            return $table;
        });

        // 6. (แก้ไข) ส่งข้อมูลผลลัพธ์กลับไป
        $selectedBranch = DB::table('branches')->where('branch_id', $branchId)->first();
        
        $results = [
            'selectedBranch' => $selectedBranch,
            'tables' => $tables,
            'userInput' => [ // (ส่งค่าที่ User กรอกกลับไปด้วย)
                'branch_id' => (int)$branchId,
                'date' => $selectedDate,
                'time' => $selectedTime,
                'duration' => $duration
            ]
        ];

        // 7. (แก้ไข) ส่งผลลัพธ์กลับไปที่หน้าเดิม
        return redirect()->route('booking.form', ['branchId' => $branchId])
                         ->with('search_results', $results);
    }
    public function reserveBooking(Request $request) 
    {
        // 0. ตรวจสอบ Auth
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'กรุณาเข้าสู่ระบบก่อนทำการจอง');
        }

        // 1. Validation
        $request->validate([
            'selected_tables' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s',
            'duration' => 'required|integer',
            'branch_id' => 'required|integer|exists:branches,branch_id',
        ]);

        // --- (นี่คือ Logic ใหม่ที่ขาดไป) ---
        $tableIds = explode(',', $request->input('selected_tables'));
        $branchId = $request->branch_id;

        // 2. ดึงชื่อสาขา
        $branch = DB::table('branches')->where('branch_id', $branchId)->first();
        $branchName = $branch ? $branch->branch_name : 'ไม่พบสาขา';

        // 3. ดึงหมายเลขโต๊ะ (table_number)
        $tables = DB::table('tables')->whereIn('table_id', $tableIds)->pluck('table_number');
        $tableNumbersStr = $tables->implode(', '); // (ผลลัพธ์เช่น: "1, 2, 5")
        // ---------------------------------

        // 4. คำนวณราคาโต๊ะ
        $durationMins = (int) $request->duration;
        $totalTables = count($tableIds);
        $tablePrice = (50 * ($durationMins / 30)) * $totalTables;

        // 5. สร้างข้อมูลโต๊ะสำหรับใส่ตะกร้า (ใช้ข้อมูลใหม่)
        $tableReservationData = [
            'branch_id' => $request->branch_id,
            'table_ids' => $tableIds, // (เก็บ ID จริงไว้สำหรับบันทึกลง DB)
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $durationMins,
            'price' => $tablePrice,
            
            // (ข้อมูลสำหรับแสดงผลที่ถูกต้อง)
            'display_branch_name' => $branchName,
            'display_table_numbers' => $tableNumbersStr,
            'display_time' => Carbon::parse($request->start_time)->format('d/m/Y H:i')
        ];

        // 6. ล้างตะกร้าเก่า และเพิ่มโต๊ะลงตะกร้าใหม่
        session()->forget('cart');
        session()->put('cart.table', $tableReservationData); 

        // 7. Redirect ไปหน้าตะกร้า (ไม่ใช่ checkout)
        return redirect()->route('cart.index')->with('success', 'เพิ่มการจองโต๊ะลงในตะกร้าแล้ว! คุณสามารถสั่งอาหารต่อได้');
    }

    public function showCheckoutPage(Request $request, $order_id = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'กรุณาเข้าสู่ระบบ');
        }
        
        $userId = Auth::id();

        // ค้นหา Payment จาก $order_id ที่ส่งมา
        // หรือค้นหาอันล่าสุดที่ค้าง ถ้า $order_id ไม่ได้ส่งมา
        $paymentQuery = DB::table('payment')
            ->join('orders', 'payment.order_id', '=', 'orders.order_id')
            ->where('orders.user_id', $userId)
            ->where('payment.pay_status', 'pending')
            ->select(
                'payment.pay_id',
                'payment.order_id',
                'payment.total_amount',
                'payment.discount_amount',
                'payment.final_amount',
                'payment.reward_id',
                'orders.order_date'
            )
            ->latest('orders.order_date');

        if ($order_id) {
            $latestPayment = $paymentQuery->where('payment.order_id', $order_id)->first();
        } else {
            $latestPayment = $paymentQuery->first();
        }

        if (!$latestPayment) {
            return redirect()->route('user.dashboard')->with('info', 'ไม่พบรายการที่รอชำระเงิน');
        }

        $availableRewards = DB::table('reward')
            ->where('user_id', $userId)
            ->where('reward_status', 'active')
            ->where('expired_date', '>=', now()->toDateString())
            ->select(
                'reward_id',
                'reward_descrpt',
                'reward_value',
                'reward_discount'
            )
            ->get();

        $appliedReward = null;
        if ($latestPayment->reward_id) {
            $appliedReward = DB::table('reward')->where('reward_id', $latestPayment->reward_id)->first();
        }
        
        // (สร้าง QR Code) ...
        $qrCodeData = "PROMPTPAY-DATA-FOR-" . $latestPayment->final_amount . "-ORDER-" . $latestPayment->order_id;
        $qrCodeImage = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrCodeData);


        return view('checkout.page', [
            'payment' => $latestPayment,
            'qrCodeImage' => $qrCodeImage,
            'availableRewards' => $availableRewards, // (ส่งคูปองไป)
            'appliedReward' => $appliedReward     // (ส่งคูปองที่ใช้แล้วไป)
        ]);
    }
    
    // (เพิ่ม) 3. เมธอดใหม่ applyReward
    public function applyReward(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'pay_id' => 'required|integer|exists:payment,pay_id',
            'reward_choice' => 'required',
        ]);

        $userId = Auth::id();
        $orderId = $request->input('order_id');
        $payId = $request->input('pay_id');
        $selectedRewardId = $request->input('reward_choice');

        $payment = DB::table('payment')
            ->where('pay_id', $payId)
            ->where('order_id', $orderId)
            ->first();
        
        if (!$payment) {
            return back()->withErrors(['message' => 'ไม่พบรายการชำระเงิน']);
        }

        try {
            DB::transaction(function () use ($payment, $selectedRewardId, $userId) {
                
                // A: ถ้า User เคยใช้ส่วนลดไปแล้ว (reward_id ไม่ใช่ null)
                // เราต้องคืนค่า (reset) ส่วนลดเก่าก่อน
                if ($payment->reward_id) {
                    DB::table('reward')
                        ->where('reward_id', $payment->reward_id)
                        ->where('user_id', $userId)
                        // (สมมติว่าถ้าเคย 'used' จะไม่สามารถ 'active' กลับมาได้จนกว่า admin จะแก้)
                        // (แต่ถ้า logic คือสลับคูปองได้ ก็ต้อง update)
                        ->update(['reward_status' => 'active']); // (ตั้งกลับเป็น 'active')
                }
                
                // B: ตรวจสอบตัวเลือกใหม่
                if ($selectedRewardId == 'none') {
                    // --- B1: User เลือก "ไม่ใช้ส่วนลด" ---
                    DB::table('payment')
                        ->where('pay_id', $payment->pay_id)
                        ->update([
                            'discount_amount' => 0.00,
                            'final_amount' => $payment->total_amount, // (กลับไปราคาเต็ม)
                            'reward_id' => null
                        ]);
                    
                } else {
                    // --- B2: User เลือกใช้ส่วนลด ---
                    
                    // ค้นหาคูปอง (reward_transaction) ที่เลือก
                    $reward = DB::table('reward')
                        ->where('reward_id', $selectedRewardId)
                        ->where('user_id', $userId)
                        ->where('reward_status', 'active') // ต้อง 'active' เท่านั้น
                        ->first();
                    
                    if (!$reward) {
                        throw new \Exception('ไม่พบส่วนลดที่ใช้งานได้ หรือส่วนลดถูกใช้ไปแล้ว');
                    }
                    
                    // คำนวณยอดใหม่
                    $discountValue = 0;
                    if ($reward->reward_discount == 'baht') {
                        $discountValue = $reward->reward_value;
                    } 
                    else if ($reward->reward_discount == 'percent') {
                        $discountValue = ($payment->total_amount * $reward->reward_value) / 100;
                    }

                    $newFinalAmount = $payment->total_amount - $discountValue;
                    if ($newFinalAmount < 0) {
                        $newFinalAmount = 0; // (กันยอดติดลบ)
                    }
                    
                    // อัปเดต Payment
                    DB::table('payment')
                        ->where('pay_id', $payment->pay_id)
                        ->update([
                            'discount_amount' => $discountValue,
                            'final_amount' => $newFinalAmount,
                            'reward_id' => $reward->reward_id // (เก็บ ID ของ reward ที่ใช้)
                        ]);
                        
                    // อัปเดตคูปอง (Reward Transaction)
                    DB::table('reward') 
                        ->where('reward_id', $reward->reward_id)
                        ->update(['reward_status' => 'used']);
                }
            });
            
            return redirect()->route('checkout.page', ['order_id' => $orderId])
                             ->with('success', 'ใช้ส่วนลดเรียบร้อย!');

        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    // --- (เพิ่มใหม่) Method 2: ประมวลผลการยืนยันชำระเงิน ---
    public function processPayment(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'pay_id' => 'required|integer|exists:payment,pay_id',
            'pay_method' => 'required|string|in:QR,cash', // (อ้างอิง schema 'payment')
        ]);

        $userId = Auth::id();
        $orderId = $request->input('order_id');
        $payId = $request->input('pay_id');
        $payMethod = $request->input('pay_method');

        // ตรวจสอบซ้ำว่าเป็นเจ้าของ Order จริง
        $order = DB::table('orders')
                    ->where('order_id', $orderId)
                    ->where('user_id', $userId)
                    ->where('order_status', 'pending') // (ควรเช็คว่ายัง pending)
                    ->first();

        if (!$order) {
            return back()->withErrors(['message' => 'ไม่พบคำสั่งซื้อนี้ หรือคำสั่งซื้อนี้ถูกดำเนินการไปแล้ว']);
        }

        try {

            if ($payMethod === 'QR') {
                
                // --- Logic A: กรณีจ่ายด้วย QR Code ---
                // (User ยืนยันเอง สถานะเปลี่ยนเป็น 'paid')

                DB::transaction(function () use ($orderId, $payId, $payMethod) {
                    
                    // 1. อัปเดต Payment เป็น 'paid'
                    DB::table('payment')
                        ->where('pay_id', $payId)
                        ->where('order_id', $orderId)
                        ->update([
                            'pay_status' => 'paid', // (จ่ายแล้ว)
                            'pay_method' => $payMethod,
                            'updated_at' => now()
                        ]);
                    
                    // 2. อัปเดต Order เป็น 'confirmed'
                    DB::table('orders')
                        ->where('order_id', $orderId)
                        ->update([
                            'order_status' => 'confirmed' // (ยืนยันแล้ว)
                        ]);
                });
                
                return redirect()->route('user.dashboard')->with('success', 'ชำระเงินสำเร็จ!');

            } 
            elseif ($payMethod === 'cash') {

                // --- Logic B: กรณีเลือกจ่ายเงินสด ---
                // (User แค่เลือกไว้ สถานะยังคงเป็น 'pending' รอ Admin)

                // 1. อัปเดต Payment (แค่บอกว่าเลือก 'cash' แต่ยัง 'pending')
                DB::table('payment')
                    ->where('pay_id', $payId)
                    ->where('order_id', $orderId)
                    ->update([
                        'pay_status' => 'pending', // (สำคัญ: ยังคง pending)
                        'pay_method' => $payMethod,
                        'updated_at' => now()
                    ]);
                
                // 2. (ไม่ทำ) เรา "ไม่" อัปเดต Order, ปล่อยให้เป็น 'pending'
                
                return redirect()->route('user.dashboard')->with('success', 'จองสำเร็จ! กรุณาชำระเงินสดที่เคาน์เตอร์');
            }

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()]);
        }
    }
}
