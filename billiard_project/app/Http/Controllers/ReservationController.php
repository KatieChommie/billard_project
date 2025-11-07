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
    public function showBookingForm($branchId)
    {
        if (session()->has('search_results')) {

            $allBranches = DB::table('branches')->get();
            $viewData = session('search_results');

            $viewData['branches'] = $allBranches;

            $viewData['branchId'] = $branchId; 

            $userInput = $viewData['userInput'] ?? null;
            $date = $userInput['date'] ?? null;
            $startTime = $userInput['time'] ?? null;
            $duration = $userInput['duration'] ?? null;

            if (isset($viewData['selectedBranch']) && is_object($viewData['selectedBranch'])) {
                $viewData['branchName'] = $viewData['selectedBranch']->branch_name ?? null;
            } else {
                $branchObj = $allBranches->firstWhere('branch_id', $branchId);
                $viewData['branchName'] = $branchObj ? $branchObj->branch_name : null;
            }

            if (isset($viewData['tables'])) {
                $viewData['tables'] = collect($viewData['tables']);
            }
            
            $viewData['date'] = $date;
            $viewData['startTime'] = $startTime;
            $viewData['duration'] = $duration;
            
            return view('booking.table', $viewData);
        }

        $branches = DB::table('branches')->get();
        $selectedBranch = $branches->where('branch_id', $branchId)->first();
        if (!$selectedBranch) {
          $selectedBranch = $branches->first();
          $branchId = $selectedBranch->branch_id; 
        }
        
        $selectedDate = date('Y-m-d');
        $selectedTime = date('H:00'); 
        $duration = 60;

        try {
            $userStartTime = Carbon::parse($selectedDate . ' ' . $selectedTime);
            $userEndTime = $userStartTime->copy()->addMinutes($duration);
        } catch (\Exception $e) {
            $userStartTime = now();
            $userEndTime = now()->addMinutes($duration);
        }

        $bookedTableIds = DB::table('reservation')
            ->join('orders', 'reservation.order_id', '=', 'orders.order_id')
            ->join('tables', 'reservation.table_id', '=', 'tables.table_id')
            ->where('tables.branch_id', $branchId)
            ->whereIn('orders.order_status', ['pending', 'confirmed', 'completed']) 
            ->where(function ($query) use ($userStartTime, $userEndTime) {
                $query->where('reservation.start_time', '<', $userEndTime)
                      ->where('reservation.end_time', '>', $userStartTime);
            })
            ->pluck('reservation.table_id')
            ->unique();

        $allTablesInBranch = DB::table('tables')
                            ->where('branch_id', $selectedBranch->branch_id)
                            ->get();

        $tablesWithStatus = $allTablesInBranch->map(function ($table) use ($bookedTableIds) {
            $table->is_available = !$bookedTableIds->contains($table->table_id);
            return $table;
        });

        return view('booking.table', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'branchName' => $selectedBranch->branch_name,
            'tables' => $tablesWithStatus,
            'userInput' => null,
            'branchId' => $branchId,
            'date' => $selectedDate,
            'startTime' => $selectedTime,
            'duration' => $duration
        ]);
    }
    public function checkTableAvailability(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer|exists:branches,branch_id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:30',
        ]);

        $branchId = $request->input('branch_id');
        $selectedDate = $request->input('date');
        $selectedTime = $request->input('start_time');
        $duration = (int)$request->input('duration');

        try {
            $userStartTime = Carbon::parse($selectedDate . ' ' . $selectedTime);
            $userEndTime = $userStartTime->copy()->addMinutes($duration);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['time' => 'รูปแบบเวลาไม่ถูกต้อง']);
        }
        if ($userStartTime->lt(Carbon::now())) { 
        return back()->withInput()->withErrors(['start_time' => 'ไม่สามารถจองในเวลาที่ผ่านมาได้ (เวลาปัจจุบันคือ ' . Carbon::now()->format('H:i') . ' น.)']);
        }       

        $bookedTableIds = DB::table('reservation')
            ->join('orders', 'reservation.order_id', '=', 'orders.order_id')
            ->join('tables', 'reservation.table_id', '=', 'tables.table_id')
            ->where('tables.branch_id', $branchId)
            ->whereIn('orders.order_status', ['pending', 'confirmed', 'completed'])
            ->where(function ($query) use ($userStartTime, $userEndTime) {
                $query->where('reservation.start_time', '<', $userEndTime)
                      ->where('reservation.end_time', '>', $userStartTime);
            })       
            ->pluck('reservation.table_id')
            ->unique();
        $allTablesInBranch = DB::table('tables')
            ->where('branch_id', $branchId)
            ->get();

        $tables = $allTablesInBranch->map(function ($table) use ($bookedTableIds) {
            $table->is_available = !$bookedTableIds->contains($table->table_id);
            return $table;
        });

        $selectedBranch = DB::table('branches')->where('branch_id', $branchId)->first();
        if (!$selectedBranch) {
            return back()->withInput()->withErrors(['branch_id' => 'ไม่พบข้อมูลสาขา']);
        }

        $closeTimeStr = $selectedBranch->time_close;
        $closeDate = Carbon::parse($selectedDate . ' ' . $closeTimeStr);
        $openTimeStr = $selectedBranch->time_open;

        if (Carbon::parse($closeTimeStr)->lt(Carbon::parse($openTimeStr))) {
            if ($userEndTime->gt($closeDate->copy()->subDay())) {
                $closeDate->addDay();
            }
        }

        if ($userEndTime->gt($closeDate)) {
            return back()->withInput()->withErrors(['duration' => "ระยะเวลาที่เลือกเกินเวลาปิดทำการของร้าน ({$closeTimeStr} น.) กรุณาลดระยะเวลา"]);
        }

        $bookedTableIds = DB::table('reservation');
        $selectedBranch = DB::table('branches')->where('branch_id', $branchId)->first();
        
        $results = [
            'selectedBranch' => $selectedBranch,
            'tables' => $tables,
            'userInput' => [
                'branch_id' => (int)$branchId,
                'date' => $selectedDate,
                'start_time' => $selectedTime,
                'duration' => $duration
            ]
        ];

        return redirect()->route('booking.table', ['branchId' => $branchId])
                 ->with('search_results', $results);
    }
    public function reserveBooking(Request $request) 
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'กรุณาเข้าสู่ระบบก่อนทำการจอง');
        }

        $request->validate([
            'selected_tables' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s',
            'duration' => 'required|integer',
            'branch_id' => 'required|integer|exists:branches,branch_id',
        ]);

        $tableIds = explode(',', $request->input('selected_tables'));
        $branchId = $request->branch_id;

        $branch = DB::table('branches')->where('branch_id', $branchId)->first();
        $branchName = $branch ? $branch->branch_name : 'ไม่พบสาขา';

        $tables = DB::table('tables')->whereIn('table_id', $tableIds)->pluck('table_number');
        $tableNumbersStr = $tables->implode(', ');
 
        $durationMins = (int) $request->duration;
        $totalTables = count($tableIds);
        $tablePrice = (50 * ($durationMins / 30)) * $totalTables;

        $tableReservationData = [
            'branch_id' => $request->branch_id,
            'table_ids' => $tableIds,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $durationMins,
            'price' => $tablePrice,

            'display_branch_name' => $branchName,
            'display_table_numbers' => $tableNumbersStr,
            'display_time' => Carbon::parse($request->start_time)->format('d/m/Y H:i')
        ];

        session()->forget('cart');
        session()->put('cart.table', $tableReservationData); 

        return redirect()->route('cart.index')->with('success', 'เพิ่มการจองโต๊ะลงในตะกร้าแล้ว! คุณสามารถสั่งอาหารต่อได้');
    }

    public function showCheckoutPage(Request $request, $order_id = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'กรุณาเข้าสู่ระบบ');
        }
        
        $userId = Auth::id();

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

        $qrCodeData = "PROMPTPAY-DATA-FOR-" . $latestPayment->final_amount . "-ORDER-" . $latestPayment->order_id;
        $qrCodeImage = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrCodeData);


        return view('checkout.page', [
            'payment' => $latestPayment,
            'qrCodeImage' => $qrCodeImage,
            'availableRewards' => $availableRewards, 
            'appliedReward' => $appliedReward 
        ]);
    }

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

                if ($payment->reward_id) {
                    DB::table('reward')
                        ->where('reward_id', $payment->reward_id)
                        ->where('user_id', $userId)
                        ->update(['reward_status' => 'active']);
                }

                if ($selectedRewardId == 'none') {
                    DB::table('payment')
                        ->where('pay_id', $payment->pay_id)
                        ->update([
                            'discount_amount' => 0.00,
                            'final_amount' => $payment->total_amount,
                            'reward_id' => null
                        ]);
                    
                } else {
                    $reward = DB::table('reward')
                        ->where('reward_id', $selectedRewardId)
                        ->where('user_id', $userId)
                        ->where('reward_status', 'active')
                        ->first();
                    
                    if (!$reward) {
                        throw new \Exception('ไม่พบส่วนลดที่ใช้งานได้ หรือส่วนลดถูกใช้ไปแล้ว');
                    }
                    $discountValue = 0;
                    if ($reward->reward_discount == 'baht') {
                        $discountValue = $reward->reward_value;
                    } 
                    else if ($reward->reward_discount == 'percent') {
                        $discountValue = ($payment->total_amount * $reward->reward_value) / 100;
                    }

                    $newFinalAmount = $payment->total_amount - $discountValue;
                    if ($newFinalAmount < 0) {
                        $newFinalAmount = 0;
                    }

                    DB::table('payment')
                        ->where('pay_id', $payment->pay_id)
                        ->update([
                            'discount_amount' => $discountValue,
                            'final_amount' => $newFinalAmount,
                            'reward_id' => $reward->reward_id
                        ]);

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

    public function processPayment(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'pay_id' => 'required|integer|exists:payment,pay_id',
            'pay_method' => 'required|string|in:QR,cash',
        ]);

        $userId = Auth::id();
        $orderId = $request->input('order_id');
        $payId = $request->input('pay_id');
        $payMethod = $request->input('pay_method');

        $order = DB::table('orders')
                    ->where('order_id', $orderId)
                    ->where('user_id', $userId)
                    ->where('order_status', 'pending')
                    ->first();

        if (!$order) {
            return back()->withErrors(['message' => 'ไม่พบคำสั่งซื้อนี้ หรือคำสั่งซื้อนี้ถูกดำเนินการไปแล้ว']);
        }

        try {

            if ($payMethod === 'QR') {
                
                DB::transaction(function () use ($orderId, $payId, $payMethod) {
                    
                    DB::table('payment')
                        ->where('pay_id', $payId)
                        ->where('order_id', $orderId)
                        ->update([
                            'pay_status' => 'paid',
                            'pay_method' => $payMethod,
                            'updated_at' => now()
                        ]);
                    
                    DB::table('orders')
                        ->where('order_id', $orderId)
                        ->update([
                            'order_status' => 'confirmed'
                        ]);
                });
                
                return redirect()->route('user.dashboard')->with('success', 'ชำระเงินสำเร็จ!');

            } 
            elseif ($payMethod === 'cash') {
                DB::table('payment')
                    ->where('pay_id', $payId)
                    ->where('order_id', $orderId)
                    ->update([
                        'pay_status' => 'pending',
                        'pay_method' => $payMethod,
                        'updated_at' => now()
                    ]);
                
                return redirect()->route('user.dashboard')->with('success', 'จองสำเร็จ! กรุณาชำระเงินสดที่เคาน์เตอร์');
            }

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()]);
        }
    }
}
