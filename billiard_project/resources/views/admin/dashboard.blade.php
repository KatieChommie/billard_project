@extends('layouts.app') 

@section('title', 'Admin Dashboard')

@section('content')
<div class="container mx-auto p-6 lg:p-12">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">📊 แผงควบคุมผู้ดูแลระบบ</h1>
    <p class="mb-6 text-gray-600">ยินดีต้อนรับกลับสู่ระบบจัดการ Billiard Reservation System</p>

    {{-- สถิติสรุปหลัก (Main Metrics) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        {{-- Card 1: ยอดจองวันนี้ --}}
        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
            <p class="text-sm font-medium text-gray-500">ยอดจองวันนี้</p>
            <p class="text-3xl font-semibold text-gray-900 mt-1">24 รายการ</p>
        </div>
        
        {{-- Card 2: รายได้รวมวันนี้ --}}
        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
            <p class="text-sm font-medium text-gray-500">รายได้รวม (อาหาร/จอง)</p>
            <p class="text-3xl font-semibold text-gray-900 mt-1">฿ 8,500</p>
        </div>
        
        {{-- Card 3: สินค้าคงคลังต่ำ --}}
        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-red-500">
            <p class="text-sm font-medium text-gray-500">สินค้าคงคลังต่ำ</p>
            <p class="text-3xl font-semibold text-red-600 mt-1">3 รายการ</p>
        </div>
    </div>

    {{-- ส่วนจัดการรายการ (Management Sections) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- 1. รายการจองล่าสุด --}}
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">รายการจอง Pending ล่าสุด</h2>
            <ul class="space-y-3 text-sm">
                <li class="p-3 bg-gray-50 rounded-lg border-l-2 border-yellow-500">#2001 | เกสี่ | 16:00 - 17:30</li>
                <li class="p-3 bg-gray-50 rounded-lg border-l-2 border-yellow-500">#2002 | KLLC | 18:00 - 19:00</li>
                <li class="p-3 bg-gray-50 rounded-lg border-l-2 border-yellow-500">#2003 | มีสมาย | 19:30 - 20:00</li>
            </ul>
            <a href="#" class="text-blue-500 hover:text-blue-700 mt-4 block text-sm">ดูรายการจองทั้งหมด</a>
        </div>
        
        {{-- 2. สถานะคลังสินค้า (Inventory Check) --}}
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">คลังสินค้าคงเหลือต่ำ</h2>
            <table class="min-w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3 px-6">สินค้า</th>
                        <th scope="col" class="py-3 px-6">สาขา</th>
                        <th scope="col" class="py-3 px-6">คงเหลือ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white border-b">
                        <td class="py-4 px-6 font-medium text-gray-900">โค้กกระป๋อง</td>
                        <td class="py-4 px-6">เกสี่</td>
                        <td class="py-4 px-6 text-red-500 font-semibold">5</td>
                    </tr>
                     <tr class="bg-white border-b">
                        <td class="py-4 px-6 font-medium text-gray-900">ข้าวแกงกะหรี่</td>
                        <td class="py-4 px-6">ซอยหอใหม่</td>
                        <td class="py-4 px-6 text-yellow-600 font-semibold">10</td>
                    </tr>
                </tbody>
            </table>
            <a href="#" class="text-blue-500 hover:text-blue-700 mt-4 block text-sm">จัดการคลังสินค้า</a>
        </div>
    </div>
</div>

@endsection
