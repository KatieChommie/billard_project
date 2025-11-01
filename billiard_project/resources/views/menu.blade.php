@extends('layouts.app')
@section('title', 'เมนูอาหาร')
@section('content')

<section class='menu-container'>
    <h1>เมนูอาหาร ของว่าง และเครื่องดื่ม</h1>

    <div class="branch-selector">
        <label for="branch-select">เลือกสาขา:</label>
        <select id="branch-select" onchange="window.location.href='/menu/' + this.value">
            @foreach ($branches as $branch)
                <option value="{{ $branch->branch_id }}" 
                    {{ $branch->branch_id == $selectedBranchId ? 'selected' : '' }}>
                    {{ $branch->branch_name }}
                </option>
            @endforeach
        </select>
    </div>

    <nav class="category-tabs">
        <ul class="tab-list">
            <li class="tab-item active" data-filter="All">ทั้งหมด</li>
            <li class="tab-item" data-filter="Meal">อาหารจานหลัก</li>
            <li class="tab-item" data-filter="Snack">ของว่าง</li>
            <li class="tab-item" data-filter="Drink">เครื่องดื่ม</li>
        </ul>
    </nav>

    @foreach ($groupedMenu as $type => $menuItems)
        
        <div class='menu-grid' data-category="{{ ucfirst($type) }}"> 
        
        {{-- วนลูปตามรายการสินค้าในแต่ละประเภท --}}
        @foreach ($menuItems as $item)
            <div class='menu-item' data-menu-id="{{ $item->menu_id }}">
                <img src="{{ asset('images/' . $item->image_path) }}" alt="{{ $item->menu_name }}">
                <span class='tag'>{{ ucfirst($item->menu_type) }}</span> 
                <h3>{{ $item->menu_name }}</h3>
                <p class='description'></p>
                <p class='price'>{{ $item->price }}฿</p>
                
                {{-- โค้ดที่แก้ไข: แสดงปุ่ม "สั่งเลย" โดยไม่ต้องเช็คสต็อก --}}
                <button class='add-to-cart-btn'>สั่งเลย</button>
            </div>
        @endforeach
        
        </div>
        
    @endforeach {{-- สิ้นสุด loop ประเภทสินค้า --}}

</section>

@endsection