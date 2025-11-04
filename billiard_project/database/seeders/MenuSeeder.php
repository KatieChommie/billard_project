<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    private function createMenuEntries(array $branchIds, array $menuData): array
    {
        $entries = [];
        foreach ($branchIds as $branchId) {
            // โค้ดที่ถูกต้อง: รวมข้อมูลเมนู + branch_id ใหม่
            $entries[] = array_merge($menuData, ['branch_id' => $branchId]);
        }
        return $entries;
    }
    public function run(): void
    {

        $allBranches = [101, 102, 103, 104, 105, 106];
        $dataToInsert = [];

        $masterMenus = [
            ['menu_id'=> 1001, 'branch_id' => $allBranches,
            'menu_name'=> 'สุกี้แห้งหมู', 'menu_type'=>'meal', 'price'=> 40,
            'image_path'=> 'food/pork-suki.png'],
            ['menu_id'=> 1002, 'branch_id' => $allBranches,
            'menu_name'=> 'ผัดไทยกุ้งสด', 'menu_type'=>'meal', 'price'=> 55,
            'image_path'=> 'food/shrimp-pad-thai.png'],
            ['menu_id'=> 1003, 'branch_id' => $allBranches,
            'menu_name'=> 'หมี่ไก่ฉีก', 'menu_type'=>'meal', 'price'=> 60,
            'image_path'=> 'food/chicken-noodle.jpg'],

            ['menu_id'=> 2001, 'branch_id' => $allBranches,
            'menu_name'=> 'พริงเกิลส์ มั่นฝรั่งรสดั้งเดิม 170 กรัม', 'menu_type'=>'snack', 'price'=> 69,
            'image_path'=> 'snacks/ori-pringles.png'],
            ['menu_id'=> 2002, 'branch_id' => $allBranches,
            'menu_name'=> 'เลย์ แผ่นเรียบ รสโนริสาหร่าย 69 กรัม', 'menu_type'=>'snack', 'price'=> 31,
            'image_path'=> 'snacks/nori-lays.png'],
            ['menu_id'=> 2003, 'branch_id' => $allBranches,
            'menu_name'=> 'คอนเน่ ข้าวโพดกรอบรสเข้มข้น 48 กรัม', 'menu_type'=>'snack', 'price'=> 20,
            'image_path'=> 'snacks/cornae.png'],

            ['menu_id'=> 3001, 'branch_id' => $allBranches,
            'menu_name'=> 'น้ำดื่มคริสตัล 600 มล.', 'menu_type'=>'drink', 'price'=> 6,
            'image_path'=> 'drinks/drinking-water.png'],
            ['menu_id'=> 3002, 'branch_id' => $allBranches,
            'menu_name'=> 'โค้กกระป๋อง 325 มล.', 'menu_type'=>'drink', 'price'=> 20,
            'image_path'=> 'drinks/can-coke.jpg'],
            ['menu_id'=> 3003, 'branch_id' => $allBranches,
            'menu_name'=> 'โออิชิ รสน้ำผึ้งมะนาว 800 มล.', 'menu_type'=>'drink', 'price'=> 30,
            'image_path'=> 'drinks/oishi.png'],

            //บางสาขา
            ['menu_id'=> 1004, 'branch_id'=> [101,104,105],
            'menu_name'=> 'ข้าวแกงกะหรี่ญี่ปุ่น', 'menu_type'=>'meal', 'price'=> 50,
            'image_path'=> 'food/jpn-curry-rice.jpg'],
            ['menu_id'=> 1005, 'branch_id'=> [101,102,103,104],
            'menu_name'=> 'ข้าวหมูกรอบคั่วพริกเกลือ + ไข่ดาว', 'menu_type'=>'meal', 'price'=> 55,
            'image_path'=> 'food/mookrob-salty-chili.jpg'],
            ['menu_id'=> 1006, 'branch_id'=> [101,104,106],
            'menu_name'=> 'ข้าวมันไก่ต้ม', 'menu_type'=>'meal', 'price'=> 50,
            'image_path'=> 'food/chicken-rice.jpg'],
            ['menu_id'=> 1007, 'branch_id'=> [104],
            'menu_name'=> 'สปาเก็ตตี้คาร์โบนารา', 'menu_type'=>'meal', 'price'=> 55,
            'image_path'=> 'food/carbonara.jpg'],
            ['menu_id'=> 1008, 'branch_id'=> [104],
            'menu_name'=> 'รามยอนเผ็ด + ไข่', 'menu_type'=>'meal', 'price'=> 75,
            'image_path'=> 'food/spicy-ramyon-eggs.jpg'],

            ['menu_id'=> 2004, 'branch_id'=> [102,106],
            'menu_name'=> 'ตะวัน มันมัน รสมันฝรั่ง 52 กรัม', 'menu_type'=>'snack', 'price'=> 20,
            'image_path'=> 'snacks/mun-mun.png'],
            ['menu_id'=> 2005, 'branch_id'=> [102,103,106],
            'menu_name'=> 'ซันไบทส์ รสซาวครีมและหัวหอม 50 กรัม', 'menu_type'=>'snack', 'price'=> 20,
            'image_path'=> 'snacks/sour-cream-sunbites.png'],
            ['menu_id'=> 2006, 'branch_id'=> [102],
            'menu_name'=> 'คาราด้า รสชีส 52 กรัม', 'menu_type'=>'snack', 'price'=> 20,
            'image_path'=> 'snacks/cheese-karada.png'],
            ['menu_id'=> 2007, 'branch_id'=> [103,106],
            'menu_name'=> 'ฮาริโบ้ โกลด์แบร์ 80 กรัม', 'menu_type'=>'snack', 'price'=> 20,
            'image_path'=> 'snacks/haribo-summers.png'],
            ['menu_id'=> 2008, 'branch_id'=> [101,102,103,104],
            'menu_name'=> 'จูปาจุบส์ ดริ้งค์ ออเรนจ์ 15 กรัม', 'menu_type'=>'snack', 'price'=> 12,
            'image_path'=> 'snacks/chupa-chups-orange.png'],

            ['menu_id'=> 3004, 'branch_id'=> [101,102,106],
            'menu_name'=> 'เนสกาแฟ ลาเต้กระป๋อง 180 มล.', 'menu_type'=>'drink', 'price'=> 17,
            'image_path'=> 'drinks/can-latte.png'],
            ['menu_id'=> 3005, 'branch_id'=> [101,102,103,104,106],
            'menu_name'=> 'สิงห์ พิงก์ เลมอนโซดา 330 มล.', 'menu_type'=>'drink', 'price'=> 17,
            'image_path'=> 'drinks/can-soda.png'],
            ['menu_id'=> 3006, 'branch_id'=> [102,106],
            'menu_name'=> 'อิโตเอ็น ชาเขียวกลิ่นมะลิ รสหวาน 350 มล.', 'menu_type'=>'drink', 'price'=> 25,
            'image_path'=> 'drinks/jasmine-tea.png'],
            ['menu_id'=> 3007, 'branch_id'=> [101,102,105],
            'menu_name'=> 'แมนซั่ม สูตรน้ำตาลน้อย 430 มล.', 'menu_type'=>'drink', 'price'=> 20,
            'image_path'=> 'drinks/mansome-less-sugar.png'],
            ['menu_id'=> 3008, 'branch_id'=> [101,102,105],
            'menu_name'=> 'พยัคฆ์ ชาอัสสัม รสบราวน์ชูการ์ 250 มล.', 'menu_type'=>'drink', 'price'=> 20,
            'image_path'=> 'drinks/brown-sugar-tea.png'],
        ];

        foreach ($masterMenus as $menuData) {
            $branchIds = $menuData['branch_id']; // ดึง Branch IDs
            $menuId = $menuData['menu_id'];       // ดึง Menu ID

            unset($menuData['branch_id']); 
            unset($menuData['menu_id']);

            foreach ($branchIds as $branchId) {
                $entry = array_merge($menuData, [
                    'branch_id' => $branchId,
                    'menu_id' => $menuId
                ]);
                $dataToInsert[] = $entry;
            }
        }

        DB::table('menus')->insert($dataToInsert);
    }
}
