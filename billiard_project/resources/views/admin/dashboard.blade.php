@extends('layouts.admin') 

@section('title', 'Admin Dashboard')

@section('content')
    <h1>ภาพรวม (Dashboard)</h1>

    {{-- อนาคต คุณสามารถวาง "วิดเจ็ต" (Widgets) ตรงนี้ได้ --}}
    
    <div class="widget-grid">
        <div class="widget">
            <h3>ผู้ใช้ทั้งหมด</h3>
            <p class="widget-value">150</p>
        </div>
        <div class="widget">
            <h3>ยอดจองวันนี้</h3>
            <p class="widget-value">30</p>
        </div>
        <div class="widget">
            <h3>รีวิวที่รออนุมัติ</h3>
            <p class="widget-value">5</p>
        </div>
    </div>

@endsection
