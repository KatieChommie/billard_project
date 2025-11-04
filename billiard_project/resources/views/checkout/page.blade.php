@extends('layouts.app')
@section('title', 'ชำระเงิน')

@section('content')

<main class="checkout-container">
    
    <div class="checkout-header">
        <h2 class="checkout-title">ยืนยันการชำระเงิน</h2>
        <p class="subtitle">ตรวจสอบยอดและเลือกวิธีชำระเงิน</p>
    </div>

    {{-- แสดง Error / Success (ถ้ามีการใช้ส่วนลด) --}}
    @if ($errors->any())
        <div class="alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- 1. สรุปยอด (แก้ไข) --}}
    <div class="summary-box">
        <div class="summary-item">
            <span>Order ID:</span>
            <strong>#{{ $payment->order_id }}</strong>
        </div>
        
        <div class="summary-item total">
            <span>ยอดรวม (Total):</span>
            <strong>{{ number_format($payment->total_amount, 2) }} THB</strong>
        </div>
        
        <div class="summary-item discount">
            <span>ส่วนลด (Discount):</span>
            <strong>- {{ number_format($payment->discount_amount, 2) }} THB</strong>
        </div>

        <div class="summary-item final-total">
            <span>ยอดสุทธิ (Final):</span>
            <strong>{{ number_format($payment->final_amount, 2) }} THB</strong>
        </div>
    </div>

    {{-- 2. (เพิ่ม) ส่วนลด --- --}}
    <div class="reward-box">
        <h3 class="reward-title">ใช้ส่วนลด (Rewards)</h3>
        
        {{-- ถ้ายังไม่ได้ใช้ส่วนลด --}}
        @if (!$payment->reward_id)
            <form action="{{ route('checkout.apply_reward') }}" method="POST" class="reward-form">
                @csrf
                <input type="hidden" name="order_id" value="{{ $payment->order_id }}">
                <input type="hidden" name="pay_id" value="{{ $payment->pay_id }}">
                
                <select name="reward_choice" required>
                    <option value="none">-- ไม่ใช้ส่วนลด --</option>
                    @forelse ($availableRewards as $reward)
                        {{-- (แก้ไข) ใช้ reward_id และแสดงผลที่ถูกต้อง --}}
                        <option value="{{ $reward->reward_id }}">
                            {{ $reward->reward_descrpt }} 
                            (มูลค่า {{ $reward->reward_value }} {{ $reward->reward_discount == 'percent' ? '%' : 'บาท' }})
                        </option>
                    @empty
                        <option value="" disabled>คุณไม่มีส่วนลดที่ใช้ได้</option>
                    @endforelse
                </select>
                
                <button type="submit" class="reward-apply-btn">ใช้</button>
            </form>
        
        {{-- ถ้าใช้ส่วนลดไปแล้ว --}}
        @else
            <p class="applied-reward">
                {{-- (แก้ไข) อ้างอิง $appliedReward->reward_descrpt --}}
                คุณใช้ส่วนลด: {{ $appliedReward->reward_descrpt ?? 'ส่วนลด' }}
            </p>
            {{-- (ฟอร์มสำหรับ "ยกเลิก" ส่วนลด - คงเดิม) --}}
            <form action="{{ route('checkout.apply_reward') }}" method="POST" style="margin-top: 10px;">
                @csrf
                <input type="hidden" name="order_id" value="{{ $payment->order_id }}">
                <input type="hidden" name="pay_id" value="{{ $payment->pay_id }}">
                <input type="hidden" name="reward_choice" value="none">
                <button type="submit" style="color: #E53E3E; background: none; border: none; cursor: pointer;">
                    (ยกเลิกการใช้ส่วนลด)
                </button>
            </form>
        @endif
    </div>


    {{-- 3. ฟอร์มยืนยัน (QR/Cash) (ไม่แก้ไข) --}}
    <form id="payment-confirmation-form" class="payment-form" action="{{ route('checkout.process') }}" method="POST">
        @csrf
        <input type="hidden" name="order_id" value="{{ $payment->order_id }}">
        <input type="hidden" name="pay_id" value="{{ $payment->pay_id }}">
        
        {{-- (โค้ดตัวเลือก QR Code) --}}
        <div class="payment-option-box">
            <h3 class="payment-option-title">1. ชำระด้วย QR Code</h3>
            <div class="qr-code-box">
                {{-- (สำคัญ) QR Code ต้องสร้างจาก $payment->final_amount --}}
                <img src="{{ $qrCodeImage }}" alt="Scan QR Code to Pay">
                <p>สแกนเพื่อชำระยอด {{ $payment->final_amount }} บาท</p>
            </div>
            <button type="submit" name="pay_method" value="QR" class="confirm-payment-btn qr-btn">
                ฉันชำระเงิน (QR) แล้ว
            </button>
        </div>

        <div class="or-divider">--- หรือ ---</div>

        {{-- (โค้ดตัวเลือก เงินสด) --}}
        <div class="payment-option-box">
            <h3 class="payment-option-title">2. ชำระด้วยเงินสด (ที่หน้าร้าน)</h3>
            <p class="cash-description">
                ยืนยันการจองและชำระเงินสด
                <strong>{{ $payment->final_amount }} บาท</strong>
                ที่เคาน์เตอร์
            </p>
            <button type="submit" name="pay_method" value="cash" class="confirm-payment-btn cash-btn">
                ยืนยันการจอง (ชำระเงินสด)
            </button>
        </div>
    </form>
</main>
@endsection