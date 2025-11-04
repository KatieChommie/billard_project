<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('orders')->insert([
            ['order_id' => 20001, 'user_id' => 1, 'order_date' => '2025-09-06 09:00:00', 'order_status' => 'pending'],
            ['order_id' => 20002, 'user_id' => 2, 'order_date' => '2025-09-09 10:00:00', 'order_status' => 'confirmed'],
            ['order_id' => 20003, 'user_id' => 3, 'order_date' => '2025-09-11 12:30:00', 'order_status' => 'cancelled'],
            ['order_id' => 20004, 'user_id' => 4, 'order_date' => '2025-09-11 14:00:59', 'order_status' => 'completed'],
        ]);
    }
}
