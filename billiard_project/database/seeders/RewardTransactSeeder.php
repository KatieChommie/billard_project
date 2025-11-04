<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RewardTransactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reward_transaction')->insert([
            [
                'transact_id' => 40001, 
                'user_id' => 1, 
                'transact_type' => 'received', 
                'pts_change' => 100, 
                'transact_descrpt' => 'ผู้ใช้งานใหม่', 
                'transact_date' => '2025-09-01 18:00:00'
            ],
            [
                'transact_id' => 40002, 
                'user_id' => 2, 
                'transact_type' => 'redeemed', 
                'pts_change' => 300, 
                'transact_descrpt' => 'แลกเป็นส่วนลด 300 แต้ม แลกส่วนลด 60 บาท', 
                'transact_date' => now()
            ],
        ]);
    }
}
