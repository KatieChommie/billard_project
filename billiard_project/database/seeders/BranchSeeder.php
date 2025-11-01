<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('branches')->insert([
            
        [
            'branch_id' => 101,
            'branch_name' => 'เกสี่',
            'branch_info' => 'อยู่ข้างร้านผัดไทยในซอยเกกี 4',
            'branch_phone' => '089-111-1111',
            'branch_address' => 'ซอยเกกีงาม 4',
            'image_path' => 'branches/k4br.jpg', 
            'time_open' => '12:00:00',
            'time_close' => '23:00:00',
        ],
        [
            'branch_id' => 102,
            'branch_name' => 'ซอยหอใหม่',
            'branch_info' => 'ข้างร้านล้างรถหยอดเหรียญ',
            'branch_phone' => '089-222-2222',
            'branch_address' => 'ซอยฉลองกรุง 1 แยก 6 (ซอยหอใหม่)',
            'image_path' => 'branches/shmbr.jpg', 
            'time_open' => '10:00:00',
            'time_close' => '23:00:00'
        ],
        [
            'branch_id' => 103,
            'branch_name' => 'มีสมาย',
            'branch_info' => '',
            'branch_phone' => '089-333-3333',
            'branch_address' => 'ซอยบ้านกลางสวน',
            'image_path' => 'branches/mmbr.jpg', 
            'time_open' => '18:00:00',
            'time_close' => '02:00:00'
        ],
        [
            'branch_id' => 104,
            'branch_name' => 'ออนเดอะรูฟ',
            'branch_info' => 'ชั้น 3 ของตึกข้างธ. ออมสิน',
            'branch_phone' => '089-444-4444',
            'branch_address' => 'ซอยลาดกระบัง 13/5',
            'image_path' => 'branches/otrbr.jpg', 
            'time_open' => '18:00:00',
            'time_close' => '02:00:00'
        ],
        [
            'branch_id' => 105,
            'branch_name' => 'ตลาดนัดวิด-วะ การ์เด้น',
            'branch_info' => '',
            'branch_phone' => '089-555-5555',
            'branch_address' => 'ตลาดนัดวิด-วะ การ์เด้น ซอยฉลองกรุง 1',
            'image_path' => 'branches/vvgbr.jpg', 
            'time_open' => '15:00:00',
            'time_close' => '00:00:00'
        ],
        [
            'branch_id' => 106,
            'branch_name' => 'KLLC',
            'branch_info' => 'ชั้น 2 ตึก A',
            'branch_phone' => '089-666-6666',
            'branch_address' => 'สำนักการเรียนรู้ตลอดชีวิตพระจอมเกล้าเจ้าคุณทหารลาดกระบัง',
            'image_path' => 'branches/kllcbr.jpg', 
            'time_open' => '09:00:00',
            'time_close' => '20:00:00'
        ],


    
    ]);
    }
}
