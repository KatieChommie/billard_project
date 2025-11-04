@extends('layouts.app') 

@section('title', 'แลกคะแนนสะสม')

@section('content')
<div class="points-container">
    <a href="{{ route('user.dashboard') }}" class="back-link" aria-label="ย้อนกลับ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span style="color: white;">กลับไปยังแดชบอร์ดผู้ใช้</span>
    </a>
    @if (session('success'))
        <div class="alert-message success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert-message error">
            {{ session('error') }}
        </div>
    @endif
    {{-- ส่วนที่ 1: แสดงคะแนนปัจจุบัน --}}
    <div class="current-points-box">
        <p>คะแนนสะสมของคุณ</p>
        <span class="points-value">{{ $currentPoints }}</span>
        <span class="points-unit">แต้ม</span>
    </div>

    {{-- ส่วนที่ 2: แถบนำทาง --}}
    <div class="points-navigation">
        <a href="{{ route('points.index') }}" class="nav-button active">แลกคะแนน</a>
        <a href="{{ route('points.history') }}" class="nav-button">ประวัติ</a>
    </div>

    <div class="daily-checkin-box">
        <h3>กิจกรรมพิเศษ</h3>
        <p>เช็คอินรายวัน รับฟรี 25 แต้ม!</p>
        
        <form action="{{ route('points.checkin') }}" method="POST">
            @csrf
            <button type="submit" class="redeem-btn">กดรับคะแนน</button>
        </form>
    </div>

    {{-- ส่วนที่ 3: รายการคูปอง (Redeem Points) --}}
    <div class="reward-list-grid">
        @foreach ($rewards as $reward)
            <div class="reward-card">
                <h3>{{ $reward->reward_descrpt }}</h3>
                <p class="reward-value">
                    ส่วนลด {{ $reward->reward_value }} 
                    {{ $reward->reward_discount == 'baht' ? 'บาท' : '%' }}
                </p>
                <span class="cost-points">ใช้ {{ $reward->points_required ?? 0 }} แต้ม</span>
                
                {{-- Logic: ตรวจสอบว่าแต้มพอหรือไม่ --}}
                @if ($currentPoints >= ($reward->points_required ?? 0))
                    
                        <form action="{{ route('points.redeem') }}" method="POST">
                            @csrf
                            <input type="hidden" name="reward_id" value="{{ $reward->reward_id }}">
                            <button type="submit" class="redeem-btn">แลก</button>
                        </form>
                    
                    <button class="redeem-btn">แลก</button>
                @else
                    <button class="redeem-btn disabled" disabled>คะแนนไม่พอ</button>
                @endif
            </div>
        @endforeach
    </div>

</div>

@endsection
