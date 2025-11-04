<?php


namespace Database\Seeders;


use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        DB::table('tables')->insert([
            //branch_id = 101
            ['table_id' => 10101, 'branch_id' => 101, 'table_number' => 1, 'table_status' => 'reserved'],
            ['table_id' => 10102, 'branch_id' => 101, 'table_number' => 2, 'table_status' => 'unavailable'],
            ['table_id' => 10103, 'branch_id' => 101, 'table_number' => 3, 'table_status' => 'available'],
            ['table_id' => 10104, 'branch_id' => 101, 'table_number' => 4, 'table_status' => 'available'],
            ['table_id' => 10105, 'branch_id' => 101, 'table_number' => 5, 'table_status' => 'reserved'],
            ['table_id' => 10106, 'branch_id' => 101, 'table_number' => 6, 'table_status' => 'available'],
            //branch_id = 102
            ['table_id' => 10201, 'branch_id' => 102, 'table_number' => 1, 'table_status' => 'reserved'],
            ['table_id' => 10202, 'branch_id' => 102, 'table_number' => 2, 'table_status' => 'available'],
            ['table_id' => 10203, 'branch_id' => 102, 'table_number' => 3, 'table_status' => 'unavailable'],
            ['table_id' => 10204, 'branch_id' => 102, 'table_number' => 4, 'table_status' => 'available'],
            ['table_id' => 10205, 'branch_id' => 102, 'table_number' => 5, 'table_status' => 'reserved'],
            ['table_id' => 10206, 'branch_id' => 102, 'table_number' => 6, 'table_status' => 'available'],
            ['table_id' => 10207, 'branch_id' => 102, 'table_number' => 7, 'table_status' => 'unavailable'],
            ['table_id' => 10208, 'branch_id' => 102, 'table_number' => 8, 'table_status' => 'unavailable'],
            ['table_id' => 10209, 'branch_id' => 102, 'table_number' => 9, 'table_status' => 'available'],
            ['table_id' => 10210, 'branch_id' => 102, 'table_number' => 10, 'table_status' => 'available'],
            //branch_id = 103
            ['table_id' => 10301, 'branch_id' => 103, 'table_number' => 1, 'table_status' => 'available'],
            ['table_id' => 10302, 'branch_id' => 103, 'table_number' => 2, 'table_status' => 'reserved'],
            ['table_id' => 10303, 'branch_id' => 103, 'table_number' => 3, 'table_status' => 'reserved'],
            ['table_id' => 10304, 'branch_id' => 103, 'table_number' => 4, 'table_status' => 'available'],
            ['table_id' => 10305, 'branch_id' => 103, 'table_number' => 5, 'table_status' => 'reserved'],
            ['table_id' => 10306, 'branch_id' => 103, 'table_number' => 6, 'table_status' => 'available'],
            //branch_id = 104
            ['table_id' => 10401, 'branch_id' => 104, 'table_number' => 1, 'table_status' => 'reserved'],
            ['table_id' => 10402, 'branch_id' => 104, 'table_number' => 2, 'table_status' => 'available'],
            ['table_id' => 10403, 'branch_id' => 104, 'table_number' => 3, 'table_status' => 'available'],
            ['table_id' => 10404, 'branch_id' => 104, 'table_number' => 4, 'table_status' => 'available'],
            ['table_id' => 10405, 'branch_id' => 104, 'table_number' => 5, 'table_status' => 'available'],
            ['table_id' => 10406, 'branch_id' => 104, 'table_number' => 6, 'table_status' => 'reserved'],
            //branch_id = 105
            ['table_id' => 10501, 'branch_id' => 105, 'table_number' => 1, 'table_status' => 'available'],
            ['table_id' => 10502, 'branch_id' => 105, 'table_number' => 2, 'table_status' => 'unavailable'],
            ['table_id' => 10503, 'branch_id' => 105, 'table_number' => 3, 'table_status' => 'reserved'],
            ['table_id' => 10504, 'branch_id' => 105, 'table_number' => 4, 'table_status' => 'reserved'],
            ['table_id' => 10505, 'branch_id' => 105, 'table_number' => 5, 'table_status' => 'unavailable'],
            ['table_id' => 10506, 'branch_id' => 105, 'table_number' => 6, 'table_status' => 'available'],
            ['table_id' => 10507, 'branch_id' => 105, 'table_number' => 7, 'table_status' => 'available'],
            ['table_id' => 10508, 'branch_id' => 105, 'table_number' => 8, 'table_status' => 'reserved'],
            //branch_id = 106
            ['table_id' => 10601, 'branch_id' => 106, 'table_number' => 1, 'table_status' => 'reserved'],
            ['table_id' => 10602, 'branch_id' => 106, 'table_number' => 2, 'table_status' => 'available'],
            ['table_id' => 10603, 'branch_id' => 106, 'table_number' => 3, 'table_status' => 'unavailable'],
            ['table_id' => 10604, 'branch_id' => 106, 'table_number' => 4, 'table_status' => 'reserved'],
            ['table_id' => 10605, 'branch_id' => 106, 'table_number' => 5, 'table_status' => 'reserved'],
            ['table_id' => 10606, 'branch_id' => 106, 'table_number' => 6, 'table_status' => 'available'],
        ]);
        

    }
}
