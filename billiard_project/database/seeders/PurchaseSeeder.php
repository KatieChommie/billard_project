<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('purchase')->insert([
        [
            'purchase_id' => 80001, 
            'order_id' => 20002,
            'branch_id' => 101,
            'menu_id' => 1002, 
            'menu_qty' => 1, 
            'total_price' => 69,
            
        ],
        [
            'purchase_id' => 80002, 
            'order_id' => 20002,
            'branch_id' => 101, 
            'menu_id' => 1005, 
            'menu_qty' => 1, 
            'total_price' => 20,
            
        ],
    ]);
    }
}
