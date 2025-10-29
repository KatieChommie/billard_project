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
            'name' => 'Test User One',
            'username' => 'testuser1',
            'email' => 'test1@example.com',
            'phone_number' => '0811111111',
            'date_of_birth' => Carbon::parse('2000-01-15'), // Use Carbon for dates
            'password' => Hash::make('password'), // Use Hash::make for secure password
            'loyalty_points' => 100,
            'email_verified_at' => now(), // Mark email as verified
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
