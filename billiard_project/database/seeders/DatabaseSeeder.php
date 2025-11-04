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
    {   /*อย่าหาใช้ตอนข้อมูลจริง!!!!!
        Schema::disableForeignKeyConstraints();
         
        // ล้างข้อมูลทุกตาราง (จำเป็นเมื่อมีการรันซ้ำ)
        // ล้างตารางลูกก่อนแม่
        DB::table('users')->truncate();                 //1
        DB::table('branches')->truncate();              //2         
        DB::table('tables')->truncate();                //3
        DB::table('menus')->truncate();                 //4
        DB::table('inventory')->truncate();             //5 
        DB::table('orders')->truncate();                //6
        DB::table('review')->truncate();                //7 
        DB::table('reward')->truncate();;               //8
        DB::table('reward_transaction')->truncate();;   //9
        DB::table('payment')->truncate();               //10
        DB::table('purchase')->truncate();              //11
        DB::table('reservation')->truncate();           //12
        
        // เปิดการตรวจสอบ Foreign Key กลับคืน
        Schema::enableForeignKeyConstraints();
        */

        $this->call([
            UserSeeder::class,              //1
            BranchSeeder::class,            //2
            TablesSeeder::class,            //3-Child Table of 2
            MenuSeeder::class,              //4-Child Table of 2
            InventorySeeder::class,         //5-Child Table of 2 and 4
            OrderSeeder::class,             //6-Child Table of 1
            ReviewSeeder::class,            //7-Child Table of 1 and 6
            RewardSeeder::class,            //8-Child Table of 1
            RewardTransactSeeder::class,    //9-Child Table of 1
            PaymentSeeder::class,           //10-Child Table of 6 and 8
            PurchaseSeeder::class,          //11-Child Table of 4 and 6
            ReservationSeeder::class,       //12-Child Table of 3 and 6
            /* เรียก Seeder ทั้งหมดตามลำดับที่ถูกต้อง (แม่ก่อนลูก) ถ้าใช้งานจริงๆค่อยลบ seeder ออก*/
        ]);
    }
}
