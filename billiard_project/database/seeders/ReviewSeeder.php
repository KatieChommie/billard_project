<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('review')->insert([
            [
                'review_id' => 50001, 
                'user_id' => 3, 
                'order_id' => 20003, 
                'review_descrpt' => 'ที่จอดรถกว้าง อาหารอร่อย ห้องน้ำสะอาด พนักงานบริการดี เล่นพูลมันส์มาก', 
                'rating' => 5,
                'created_at' => now(), 'updated_at'=>now()
            ],
            [
                'review_id' => 50002, 
                'user_id' => 2, 
                'order_id' => 20002, 
                'review_descrpt' => 'พนักงานวีนใส่ น้ำแอร์หยดใส่หัว', 
                'rating' => 2,
                'created_at' => now(), 'updated_at'=>now()
            ],
        ]);
    }
}
