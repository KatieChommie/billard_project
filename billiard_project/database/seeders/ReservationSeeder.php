<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reservation')->insert([
            [
                'reserve_id' => 70001, 
                'order_id' => 20002, 
                'table_id' => 10101, 
                'start_time' => '2025-09-09 15:00:00', 
                'end_time' => '2025-09-09 16:00:00', 
                'reserve_status' => 'confirmed',
                'created_at'=>now(),
            ],
            [
                'reserve_id' => 70002, 
                'order_id' => 20004, 
                'table_id' => 10301, 
                'start_time' => '2025-09-09 18:00:00', 
                'end_time' => '2025-09-09 19:00:00', 
                'reserve_status' => 'completed',
                'created_at'=>now(),
            ],
        ]);
    }
}
