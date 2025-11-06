<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'Billiard',
            'email' => 'admin@billiard.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // รหัสผ่านคือ 'password'
            'phone_number' => '0999999999',
            'date_of_birth' => '2000-01-01',
            'loyalty_points' => 0,
        ]);
    }
}
