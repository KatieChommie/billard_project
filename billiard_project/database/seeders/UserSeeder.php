<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Import Hash facade for password hashing
use Carbon\Carbon; // Import Carbon for date handling

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            // **แก้ไขให้ตรงกับคอลัมน์ในฐานข้อมูลจริงของคุณ:**
            'name' => 'Test User One', 
            'email' => 'test1@example.com', 
            'password' => Hash::make('password'), 
            
            // คอลัมน์อื่น ๆ ที่มีอยู่ในตารางจริง
            'username' => 'KatieChommie', 
            'phone_number' => '0811111111', 
            'date_of_birth' => Carbon::parse('2000-01-15'), 
            'loyalty_points' => 100, 
            
            // คอลัมน์ Timestamp มาตรฐานของ Laravel
            'created_at' => now(), 
            'updated_at' => now(), 
            
            // หมายเหตุ: คอลัมน์ email_verified_at, remember_token ควรมีค่าเป็น null ถ้าไม่ได้ใส่
        ]);

    }
}
