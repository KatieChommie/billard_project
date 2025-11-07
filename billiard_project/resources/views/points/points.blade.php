@extends('layouts.app') 

@section('title', 'แลกคะแนนสะสม')

@section('content')
<main class="points-container">
    
    <a href="{{ route('user.dashboard') }}" class="back-link" aria-label="ย้อนกลับ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span style="color: white;">กลับไปยังแดชบอร์ดผู้ใช้</span>
    </a>

    <div class="current-points-box">
        คะแนนสะสมปัจจุบัน: <span class="points-value">{{ number_format($currentPoints) }}</span> แต้ม
    </div>

    <div class="history-link-box">
        <a href="{{ route('points.history') }}" class="nav-button">
            ดูประวัติการใช้แต้ม
        </a>
        
    </div>

    @if(session('success'))
        <div class="alert-message alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-message alert-error">{{ session('error') }}</div>
    @endif
    
    <section class="reward-redeem-section">
        <h2 class="section-title">รับแต้มสะสม แลกแต้มสะสม</h2>
        <div class="reward-store-grid">
            
            <div class="redeem-card">
                <div>
                    <h3 style="color: #E6A23C;">&#127881; เช็คอินรายวัน</h3>
                    <p class="points-cost" style="font-size: 1rem; margin-top: 15px;">
                        เข้าสู่ระบบเพื่อรับ <strong>25</strong> แต้มฟรี ทุกวัน!
                    </p>
                </div>
                <form action="{{ route('points.checkin') }}" method="POST" style="margin-top: 20px;">
                    @csrf
                    <button type="submit" class="redeem-btn checkin-btn">
                        เช็คอินเลย!
                    </button>
                </form>
            </div>

            @foreach ($redeemableRewards as $rewardDef)
                <div class="redeem-card">
                    <div>
                        <h3>{{ $rewardDef['reward_descrpt'] }}</h3>
                        <p class="points-cost">
                            ใช้ <strong>{{ number_format($rewardDef['points_required']) }}</strong> แต้ม
                        </p>
                    </div>
                    
                    <form action="{{ route('points.redeem') }}" method="POST"
                          onsubmit="return confirm('คุณต้องการใช้ {{ $rewardDef['points_required'] }} แต้ม เพื่อแลกคูปองนี้ใช่หรือไม่?');"
                          style="margin-top: 20px;">
                        @csrf
                        <input type="hidden" name="reward_id" value="{{ $rewardDef['id'] }}"> 
                        
                        <button type="submit" 
                                class="redeem-btn" 
                                @if($currentPoints < $rewardDef['points_required'])
                                    disabled
                                @endif
                                >
                            {{ $currentPoints < $rewardDef['points_required'] ? 'แต้มไม่พอ' : 'แลกเลย' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </section>
    
    <section class="my-rewards-section">
        <h2 class="section-title">คูปองส่วนลดของฉัน (ที่พร้อมใช้งาน)</h2>
        <div class="my-coupons-list">
            @forelse ($myActiveCoupons as $coupon)
                <div class="coupon-item">
                    <h4>{{ $coupon->reward_descrpt }}</h4> 
                    <p class="expiry">
                        (มูลค่า: {{ $coupon->reward_value }} {{ $coupon->reward_discount == 'percent' ? '%' : 'บาท' }})
                    </p>
                    <p class="expiry">
                        หมดอายุ: {{ \Carbon\Carbon::parse($coupon->expired_date)->format('d/m/Y') }}
                    </p>
                </div>
            @empty
                <p style="color: white; text-align: center;">คุณยังไม่มีคูปองส่วนลดที่ใช้งานได้</p>
            @endforelse
        </div>
    </section>

</main>
@endsection
