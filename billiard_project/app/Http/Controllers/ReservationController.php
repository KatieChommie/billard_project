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
            'tables' => $tablesInBranch, // <-- $tables ที่ blade คาดหวัง
            'branchName' => $selectedBranch->branch_name,
            'branchId' => $selectedBranch->branch_id,
            'startTime' => null,
            'duration' => 60,
            'date' => null, 
            'endTime' => null, 
            'checked' => false, // <-- (สำคัญ) เพิ่มตัวแปรนี้เพื่อแก้ Error
        ]);
    }

    // ฟังก์ชันหลัก: ตรวจสอบสถานะโต๊ะและแสดงผล (POST /booking/check)
    public function checkTableAvailability(Request $request) 
    {
        // 1. Validation (เหมือนเดิม)
        $request->validate([
            'branch_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:30',
        ]);

        // 2. Logic การคำนวณเวลา (เหมือนเดิม)
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
        
        // (ส่วนโค้ดตรวจสอบเวลาเปิด-ปิดร้าน... หากคุณมี ก็ให้คงไว้)

        // 3. (แก้ไข) Logic การค้นหาโต๊ะ
        // 3a. ค้นหา "ID โต๊ะที่ติดจอง" ในช่วงเวลานี้
        $conflictingTableIds = DB::table('reservation')
            ->join('tables', 'reservation.table_id', '=', 'tables.table_id')
            ->join('orders', 'reservation.order_id', '=', 'orders.order_id') // (เพิ่ม) ต้องเช็คสถานะ Order
            ->where('tables.branch_id', $branchId) 
            ->whereIn('orders.order_status', ['pending', 'confirmed']) // (เพิ่ม) เอาเฉพาะ Order ที่ยัง Active
            ->where('reservation.start_time', '<', $endTime)
            ->where('reservation.end_time', '>', $startTimeCarbon)
            ->pluck('reservation.table_id')
            ->unique(); // (เอา ID โต๊ะที่ติดจอง)
        
        // 3b. (แก้ไข) ดึงโต๊ะ "ทั้งหมด" ในสาขานี้
        $allTablesInBranch = DB::table('tables')
            ->where('branch_id', $branchId)
            ->get();

        // 3c. (ใหม่) วน Loop โต๊ะทั้งหมด เพื่อ "ปั๊มสถานะ"
        $tablesWithStatus = $allTablesInBranch->map(function ($table) use ($conflictingTableIds) {
            
            // ตรวจสอบว่า ID โต๊ะนี้ อยู่ในลิสต์ "ติดจอง" (conflicting) หรือไม่
            if ($conflictingTableIds->contains($table->table_id)) {
                
                // ---- นี่คือโต๊ะที่ "จองแล้ว" (ในช่วงเวลานี้) ----
                $table->tailwind_color = 'bg-red-600 cursor-not-allowed'; 
                $table->status_for_user = 'จองแล้ว';
            
            } else {
                // ---- นี่คือโต๊ะที่ "ว่าง" (ในช่วงเวลานี้) ----
                // แต่ต้องเช็คสถานะถาวรของโต๊ะด้วย (เช่น ปิดซ่อม)
                
                if ($table->table_status === 'available') {
                    $table->tailwind_color = 'bg-green-500 hover:bg-green-400'; 
                    $table->status_for_user = 'ว่าง'; 
                } else { 
                    // (โต๊ะนี้อาจจะ 'unavailable' หรือ 'reserved' ถาวร)
                    $table->tailwind_color = 'bg-gray-500 cursor-not-allowed'; 
                    $table->status_for_user = 'ไม่ว่าง';
                }
            }
            
            return $table;
        });

        // 4. รวบรวมข้อมูลทั้งหมดเพื่อส่งกลับ
        $viewData = [
            'tables' => $tablesWithStatus, // <-- (สำคัญ) ส่ง $tablesWithStatus ในชื่อ $tables
            'branchName' => $branch->branch_name,
            'branchId' => $branch->branch_id,
            'date' => $request->date,
            'startTime' => $request->start_time,
            'duration' => $request->duration,
            'endTime' => $endTime->format('Y-m-d H:i:s'),
        ];
        
        // 5. Redirect กลับไปที่ GET route พร้อมฝากข้อมูล
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
        $newOrderId = null;
        DB::transaction(function () use ($request, &$newOrderId) {
                
                $userId = Auth::id();

                // *** แก้ไข: แปลง String "101,102" เป็น Array ***
                $tableIds = explode(',', $request->input('selected_tables'));
            
            // a. สร้าง Order หลัก
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'order_date' => now(),
                'order_status' => 'pending', 
            ]);

            $newOrderId = $orderId;

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
                'discount_amount' => 0.00,      // (เพิ่ม)
                'final_amount' => $totalAmount, // (ยอดเริ่มต้น = ยอดเต็ม)
                'pay_status' => 'pending',
                'reward_id' => null,
            ]);
        });

        // 3. นำทางไปยังหน้า Checkout
        return redirect()->route('checkout.page', ['order_id' => $newOrderId])
                             ->with('success', 'จองโต๊ะสำเร็จ! โปรดชำระเงิน');

    } catch (\Exception $e) {
        // จัดการ Error (Foreign Key, DB Fails)
        return back()->withInput()->withErrors(['message' => 'เกิดข้อผิดพลาดในการบันทึกการจอง: ' . $e->getMessage()]);
    }
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
                    DB::table('reward_transaction')
                        ->where('id', $reward->transaction_id)
                        ->update(['status' => 'used']); // (ใช้แล้ว)
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
