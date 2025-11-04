<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*DB::table('payment')->insert([

            [
                'pay_id' => 60001, 
                'order_id' => 20002, 
                'reward_id' => 30002, 
                'total_amount' => 300.00, 
                'discount_amount' => 60.00, 
                'final_amount' => 240.00, 
                'pay_method' => 'cash', 
                'pay_status' => 'paid'
            ],
            [
                'pay_id' => 60002, 
                'order_id' => 20003, 
                'reward_id' => null, 
                'total_amount' => 150.00, 
                'discount_amount' => 0.00, 
                'final_amount' => 150.00, 
                'pay_method' => null, 
                'pay_status' => 'pending'
            ],
        ]);*/
    }
}
