<?php

namespace Database\Seeders;

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
            ['user_id' => 1,
            'first_name' => 'Chompoo',
            'last_name' => 'Panyanarupol',
            'email' => 'test1@example.com', 
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'username' => 'KatieChommie', 
            'phone_number' => '0811111111', 
            'date_of_birth' => Carbon::parse('2000-01-15'), 
            'loyalty_points' => 100, 
            'created_at' => now(), 
            'updated_at' => now(),
        ],
        [
            'user_id' => 2, 
            'first_name' => 'Piyawan', 
            'last_name' => 'Test', 
            'email' => 'test2@example.com', 
            'password' => Hash::make('password'),
            'email_verified_at' => null,
            'username' => 'user_piyawan', 
            'phone_number' => '0822222222', 
            'date_of_birth' => '2004-02-02', 
            'loyalty_points' => 100, 
            'created_at' => now(), 'updated_at' => now(),
            ],
            [
            'user_id' => 3, 
            'first_name' => 'Rattanaporn', 
            'last_name' => 'Test', 
            'email' => 'test3@example.com', 
            'password' => Hash::make('password'),
            'email_verified_at' => null,
            'username' => 'user_rat', 
            'phone_number' => '0833333333', 
            'date_of_birth' => '2004-03-03', 
            'loyalty_points' => 100, 
            'created_at' => now(), 'updated_at' => now(),
            ],
            [
            'user_id' => 4, 
            'first_name' => 'Supannapat', 
            'last_name' => 'Test', 
            'email' => 'test4@example.com', 
            'password' => Hash::make('password'),
            'email_verified_at' => null,
            'username' => 'user_sup', 
            'phone_number' => '0844444444', 
            'date_of_birth' => '2004-04-04', 
            'loyalty_points' => 100, 
            'created_at' => now(), 'updated_at' => now(),
            ], 
    ]);

    }
}
