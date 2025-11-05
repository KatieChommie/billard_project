@extends('layouts.app')
@section('title', 'เมนูอาหาร')
@section('content')

<section class='menu-container'>
    @if(session('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 0 auto 20px auto; max-width: 800px; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

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
                <p class='description'>{{ $item->description ?? '' }}</p>
                <p class='price'>{{ number_format($item->price, 2) }}฿</p>

                {{-- Add-to-cart form (POST) --}}
                <form action="{{ route('cart.add') }}" method="POST" style="padding: 0 15px; margin-top:10px;">
                    @csrf
                    <input type="hidden" name="menu_id" value="{{ $item->menu_id }}">
                    <input type="hidden" name="name" value="{{ $item->menu_name }}">
                    <input type="hidden" name="price" value="{{ $item->price }}">
                    <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <label for="menu_qty-{{ $item->menu_id }}" style="white-space: nowrap;">จำนวน:</label>
                        <input type="number" id="menu_qty-{{ $item->menu_id }}" name="menu_qty" value="1" min="1"
                            style="width: 100%; border: 1px solid #ccc; border-radius: 5px; padding: 5px;">
                    </div>

                    {{-- Stock check --}}
                    @if(isset($item->stock_qty) && $item->stock_qty <= 0)
                        <button type="button" class="add-to-cart-btn" disabled style="background-color: #999;">
                            สินค้าหมด
                        </button>
                    @else
                        <button type="submit" class="add-to-cart-btn">
                            Add to Cart
                        </button>
                    @endif
                </form>
            </div>
        @endforeach
        
        </div>
        
    @endforeach

</section>

@endsection