<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CancelExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-expired-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
    $this->info('Checking for expired orders...'); // (ข้อความไว้ดูตอนเทส)

    // 1. กำหนดเวลาหมดอายุ (ค่าอ่านจาก config หรือ default = 2 ชั่วโมง)
    $expirationHours = config('orders.expiration_hours', 2);
    $this->info("Using expiration window: {$expirationHours} hour(s)");
    $expirationTime = Carbon::now()->subHours($expirationHours);

        // 2. ค้นหา Orders ที่ "pending" และ "เก่าเกิน 1 ชั่วโมง"
        $expiredOrders = DB::table('orders')
            ->join('payment', 'orders.order_id', '=', 'payment.order_id')
            ->where('orders.order_status', 'pending')
            ->where('orders.created_at', '<=', $expirationTime) // (จุดสำคัญ)
            ->select('orders.order_id', 'payment.reward_id', 'payment.pay_id')
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No expired orders found.');
            return;
        }

        foreach ($expiredOrders as $order) {
            
            $this->warn("Cancelling Order ID: {$order->order_id}");
            
            try {
                // 3. ใช้ Transaction เพื่อความปลอดภัย
                DB::transaction(function () use ($order) {
                    
                    // 3a. อัปเดต Order เป็น 'cancelled'
                    DB::table('orders')
                        ->where('order_id', $order->order_id)
                        ->update(['order_status' => 'cancelled']);

                    // 3b. อัปเดต Payment เป็น 'cancelled'
                    DB::table('payment')
                        ->where('pay_id', $order->pay_id)
                        ->update(['pay_status' => 'cancelled']);

                    // 3c. (สำคัญมาก) คืนคูปอง (Reward) ถ้ามีการใช้
                    if ($order->reward_id) {
                        DB::table('reward')
                            ->where('reward_id', $order->reward_id)
                            ->update(['reward_status' => 'active']); // (คืนสิทธิ์)
                    }
                    
                    // 3d. อัปเดต reservation.reserve_status เป็น 'cancelled' สำหรับ order นี้
                    DB::table('reservation')
                        ->where('order_id', $order->order_id)
                        ->update(['reserve_status' => 'cancelled']);
                    
                });
                
            } catch (\Exception $e) {
                $this->error("Failed to cancel Order ID: {$order->order_id}. Error: " . $e->getMessage());
            }
        }
        
        $this->info('Expired orders processed.');
    }
}
