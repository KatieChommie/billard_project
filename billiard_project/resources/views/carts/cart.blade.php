@extends('layouts.app')
@section('title', 'ตะกร้าสินค้า')

@section('content')
<main class="cart-container">
    
    <a href="{{ route('booking.branches') }}" class="back-link" aria-label="ย้อนกลับไปหน้าจองโต๊ะ" style="position: static; text-decoration: none; color: #333; display: inline-flex; align-items: center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        
        <span style="font-weight:600;">กลับไปยังหน้าจองเลือกสาขา</span>
    </a>

    <h1 style="text-align: center; margin-bottom: 1.5rem;">ตะกร้าสินค้า</h1>

    @if(session('success'))
        <div class="alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 15px 0;">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 15px 0;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($cartTable)
        <div class="cart-table-booking">
            <h4>การจองโต๊ะของคุณ</h4>
            <p><strong>สาขา:</strong> {{ $cartTable['display_branch_name'] }}</p>
            <p><strong>โต๊ะหมายเลข:</strong> {{ $cartTable['display_table_numbers'] }}</p>
            <p><strong>เวลา:</strong> {{ $cartTable['display_time'] }} ({{ $cartTable['duration'] }} นาที)</p>
            <p style="font-weight: 600; font-size: 1.1rem; margin-top: 10px; margin-bottom: 15px;">
                ราคา: {{ number_format($cartTable['price'], 2) }} THB
            </p>
        </div>
    @endif

    <h4 style="font-size: 1.3rem; font-weight: 600; margin-bottom: 15px;">
        @if ($cartTable)
            สั่งอาหารล่วงหน้าสำหรับโต๊ะนี้
        @else
            รายการอาหาร
        @endif
    </h4>

    <div class="cart-items-list">
        @forelse ($cartItems as $id => $details)
            <div class="cart-item">
                
                <div class="cart-item-details">
                    <h4>{{ $details['menu_name'] }}</h4>
                    <p>ราคา: {{ number_format($details['price'], 2) }} THB</p>
                    <p>รวม: {{ number_format($details['price'] * $details['quantity'], 2) }} THB</p>
                </div>
                
                <div class="cart-item-actions">
                    <form action="{{ route('cart.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="menu_id" value="{{ $id }}">
                        <input type="number" name="quantity" value="{{ $details['quantity'] }}" min="1">
                        <button type="submit" class="update-btn">อัปเดต</button>
                    </form>
                    
                    <form action="{{ route('cart.remove') }}" method="POST">
                        @csrf
                        <input type="hidden" name="menu_id" value="{{ $id }}">
                        <button type="submit" class="remove-btn">ลบ</button>
                    </form>
                </div>
            </div>
        @empty
            @if (!$cartTable)
                <p style="text-align: center; font-size: 1.2rem; color: #555;">
                    ตะกร้าสินค้าของคุณว่างเปล่า
                </p>
            @else
                <p style="text-align: center; font-size: 1rem; color: #555;">
                    คุณยังไม่ได้สั่งอาหาร
                </p>
            @endif
        @endforelse
    </div>

    <div style="margin-top: 20px;">
        <a href="{{ route('menu', ['branchId' => $cartTable['branch_id'] ?? 101]) }}" 
           style="color: var(--primary-color); text-decoration: underline; font-weight: 500;">
            + สั่งอาหารหรือเครื่องดื่มเพิ่ม
        </a>
    </div>

    @if (count($cartItems) > 0 || $cartTable)
        <div class="cart-summary">
            <h3>ยอดรวมทั้งหมด: {{ number_format($total, 2) }} THB</h3>
            
            <form action="{{ route('cart.checkout') }}" method="POST">
                @csrf
                <button type="submit" class="checkout-btn">
                    ดำเนินการชำระเงิน
                </button>
            </form>

            <a href="{{ route('cart.clear') }}" style="display: block; color: #dc3545; margin-top: 10px; text-decoration: underline;">
                ล้างตะกร้าทั้งหมด
            </a>
        </div>
    @endif
</main>
@endsection