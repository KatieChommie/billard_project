@extends('layouts.admin')
@section('title', 'Admin - Manage Menus')

@section('content')
<div class="admin-content-wrapper">
    <h1>จัดการเมนู</h1>
    
    <div class="widget">
        <h3 style="font-size: 1.2rem; margin-bottom: 15px;">รายการอาหารและเครื่องดื่ม</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f1f1f1;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ID</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ชื่อเมนู</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">สาขา</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ประเภท</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ราคา (บาท)</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menus as $menu)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $menu->menu_id }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $menu->menu_name }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $menu->branch_name }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $menu->menu_type }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ number_format($menu->price, 2) }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><a href="#" style="color: #007bff;">แก้ไข</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="padding: 20px; text-align: center;">ไม่พบข้อมูลเมนู</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection