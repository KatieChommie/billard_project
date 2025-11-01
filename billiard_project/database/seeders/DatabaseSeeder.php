<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Import DB
use Illuminate\Support\Facades\Schema; // <-- Import Schema

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // ล้างข้อมูลทุกตาราง (จำเป็นเมื่อมีการรันซ้ำ)
        // ล้างตารางลูกก่อนแม่
        DB::table('users')->truncate(); 
        DB::table('branches')->truncate(); 
        DB::table('tables')->truncate();
        DB::table('menus')->truncate();
        // Note: ควรเพิ่ม truncate สำหรับตารางอื่น ๆ ที่มี (orders, review, payment ฯลฯ)
        
        // เปิดการตรวจสอบ Foreign Key กลับคืน
        Schema::enableForeignKeyConstraints();
        
        // เรียก Seeder ทั้งหมดตามลำดับที่ถูกต้อง (แม่ก่อนลูก)
        $this->call([
            UserSeeder::class,      // User (แม่ของหลายตาราง)
            BranchSeeder::class,    // Branches (แม่ของ tables และ menus)
            TablesSeeder::class,    // Tables (ลูกของ branches)
            MenuSeeder::class,      // Tables (ลูกของ branches)
        ]);
    }
}
