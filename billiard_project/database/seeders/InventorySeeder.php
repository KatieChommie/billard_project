<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $defaultStock = 50;
        $inventoryRecords = [];

        // 1. ดึงข้อมูลเมนูที่ถูกสร้างแล้วจากตาราง 'menus'
        //    (ดึงเฉพาะ branch_id และ menu_id)
        $existingMenuBranches = DB::table('menus')
            ->select('branch_id', 'menu_id')
            ->get();
        
        // 2. วนลูปตามรายการเมนู+สาขา ที่มีอยู่จริงในตาราง 'menus'
        foreach ($existingMenuBranches as $record) {
            // 3. สร้างรายการ Inventory สำหรับทุกคู่ branch_id และ menu_id ที่ถูกสร้างไว้แล้ว
            $inventoryRecords[] = [
                'branch_id' => $record->branch_id,
                'menu_id' => $record->menu_id,
                'stock_qty' => $defaultStock,
            ];
        }

        DB::table('inventory')->insert($inventoryRecords);
    }
}
