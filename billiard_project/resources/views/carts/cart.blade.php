@extends('layouts.app') 

@section('title', 'ตะกร้าสินค้า (รอชำระเงิน)')

@section('content')
<div class="container mx-auto p-6 lg:p-12">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">🛒 รายการรอชำระเงิน</h1>
    <p class="text-gray-500 mb-6">รายการจองและอาหารที่รอการยืนยันชำระเงินจะปรากฏที่นี่</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- คอลัมน์ซ้าย: รายการสินค้า --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- ************************************************* --}}
            {{-- 1. รายการจองโต๊ะ (Reservation Items) --}}
            {{-- ************************************************* --}}
            <h2 class="text-xl font-bold text-indigo-700 border-b pb-2">จองโต๊ะ (Pending)</h2>
            <div class="bg-white p-4 rounded-xl shadow-md border border-indigo-200 space-y-3">
                
                {{-- Mock Item: รายการจอง --}}
                <div class="flex items-center justify-between">
                    <div class="flex-grow">
                        <p class="font-semibold text-gray-800">สาขา: เกสี่ (โต๊ะ 1)</p>
                        <p class="text-sm text-gray-500">วันที่: 25/09/2568 | เวลา: 18:00 - 19:30 น. (90 นาที)</p>
                    </div>
                    <p class="font-semibold text-right w-24">฿ 150</p>
                    <button class="text-red-500 hover:text-red-700 ml-4">ยกเลิก</button>
                </div>
                
                {{-- Mock Item: รายการจอง 2 --}}
                 <div class="flex items-center justify-between">
                    <div class="flex-grow">
                        <p class="font-semibold text-gray-800">สาขา: KLLC (โต๊ะ 3)</p>
                        <p class="text-sm text-gray-500">วันที่: 26/09/2568 | เวลา: 20:00 - 21:00 น. (60 นาที)</p>
                    </div>
                    <p class="font-semibold text-right w-24">฿ 100</p>
                    <button class="text-red-500 hover:text-red-700 ml-4">ยกเลิก</button>
                </div>
            </div>
            
            {{-- ************************************************* --}}
            {{-- 2. รายการอาหาร (Food Items) --}}
            {{-- ************************************************* --}}
            <h2 class="text-xl font-bold text-green-700 border-b pb-2 pt-4">อาหารและเครื่องดื่ม</h2>
            <div class="space-y-4">
                
                {{-- รายการสินค้าแต่ละชิ้น (Mocked Data) --}}
                <div class="flex items-center bg-white p-4 rounded-xl shadow-md border">
                    <img src="{{ asset('images/a_can_of_coke.webp') }}" alt="Coke Can" class="w-16 h-16 object-cover rounded-md mr-4">
                    <div class="flex-grow">
                        <p class="font-semibold text-gray-800">โค้กกระป๋อง 325 มิลลิลิตร</p>
                        <p class="text-sm text-gray-500">฿ 20</p>
                    </div>
                    <div class="flex items-center">
                        <button class="text-gray-500 hover:text-red-500 text-lg mx-2">-</button>
                        <span class="font-semibold">1</span>
                        <button class="text-gray-500 hover:text-green-500 text-lg mx-2">+</button>
                        <p class="w-16 text-right font-semibold">฿ 20</p>
                        <button class="text-red-500 hover:text-red-700 ml-4">ลบ</button>
                    </div>
                </div>

                 <div class="flex items-center bg-white p-4 rounded-xl shadow-md border">
                    <img src="{{ asset('images/a_plate_of_ff.webp') }}" alt="French Fries" class="w-16 h-16 object-cover rounded-md mr-4">
                    <div class="flex-grow">
                        <p class="font-semibold text-gray-800">เฟรนช์ฟรายส์ (French Fries)</p>
                        <p class="text-sm text-gray-500">฿ 69</p>
                    </div>
                     <div class="flex items-center">
                        <button class="text-gray-500 hover:text-red-500 text-lg mx-2">-</button>
                        <span class="font-semibold">2</span>
                        <button class="text-gray-500 hover:text-green-500 text-lg mx-2">+</button>
                        <p class="w-16 text-right font-semibold">฿ 138</p>
                        <button class="text-red-500 hover:text-red-700 ml-4">ลบ</button>
                    </div>
                </div>
                
                <a href="{{ route('menu') }}" class="text-blue-500 hover:text-blue-700 text-sm block mt-4">⬅️ กลับไปเลือกซื้ออาหารต่อ</a>
            </div>

        </div>

        {{-- คอลัมน์ขวา: สรุปยอด --}}
        <div class="lg:col-span-1 bg-gray-50 p-6 rounded-xl shadow-lg h-fit">
            <h2 class="text-xl font-bold mb-4 text-gray-800">สรุปยอดชำระ</h2>
            
            <div class="space-y-2 border-b pb-4">
                <div class="flex justify-between">
                    <p class="text-gray-600">ค่าจองโต๊ะ (2 รายการ)</p>
                    <p class="font-semibold">฿ 250</p>
                </div>
                <div class="flex justify-between">
                    <p class="text-gray-600">ค่าอาหารและเครื่องดื่ม</p>
                    <p class="font-semibold">฿ 158</p>
                </div>
                 <div class="flex justify-between font-semibold">
                    <p class="text-gray-700">รวมก่อนส่วนลด</p>
                    <p>฿ 408</p>
                </div>
                 <div class="flex justify-between">
                    <p class="text-gray-600">ส่วนลดแต้ม/คูปอง</p>
                    <p class="font-semibold text-red-500">- ฿ 60</p>
                </div>
            </div>

            <div class="flex justify-between mt-4">
                <p class="text-lg font-bold">ยอดที่ต้องชำระทั้งหมด</p>
                <p class="text-2xl font-extrabold text-green-700">฿ 348</p>
            </div>
            
            {{-- ลิงก์ไปยังหน้าชำระเงิน (ต้องสร้าง Route และ View สำหรับหน้านี้) --}}
            <button onclick="window.location.href='/checkout'" class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-md shadow-lg hover:bg-indigo-700 mt-6 transition duration-150">
                ไปยังหน้าชำระเงิน
            </button>
        </div>
    </div>
</div>

@endsection
