<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reward')->insert([
            [
                'reward_id' => 30001, 
                'user_id' => 1, // สมมติ user_id เป็น 1
                'reward_descrpt' => 'สุขสันต์วันเกิด!!!', 
                'reward_type' => 'birthday', 
                'reward_value' => 15, 
                'reward_discount' => 'percent', 
                'reward_status' => 'active', 
                'issued_date' => '2025-01-01', 
                'expired_date' => '2025-02-01'
            ],
            [
                'reward_id' => 30002, 
                'user_id' => 2, // สมมติ user_id เป็น 2
                'reward_descrpt' => 'ครบ 300 แต้ม', 
                'reward_type' => 'points', 
                'reward_value' => 60, 
                'reward_discount' => 'baht', 
                'reward_status' => 'active', 
                'issued_date' => '2025-09-01', 
                'expired_date' => '2025-09-08'
            ],
        ]);
        
    }
}
