@extends('layouts.app')
@section('title', 'แดชบอร์ดผู้ใช้')

@section('content')
<div class="user-dashboard">
    <a href="{{ route('home') }}" class="back-link" aria-label="ย้อนกลับ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>กลับไปยังหน้าหลัก</span>
    </a>
    <h1>สวัสดีคุณ {{ Auth::user()->username }}!</h1>
    
    <section class="user-info-section">
            <h2>1. ข้อมูลทั่วไป</h2>
            <div class="info-grid">
                <p><strong>ชื่อที่ใช้ในการจอง:</strong> <span>{{ Auth::user()->first_name}} {{ Auth::user()->last_name}}</span></p>
                <p><strong>เบอร์โทรศัพท์:</strong> <span>{{ Auth::user()->phone_number }}</span></p>
                <p><strong>วันเกิด:</strong> <span>{{ Auth::user()->date_of_birth }}</span></p>
                <p><strong>แต้มคะแนนสะสม:</strong> <span class="highlight-points">{{ Auth::user()->loyalty_points }}</span></p>
            </div>
            <a href="{{ route('points.index') }}" class='button'>จัดการคะแนนสะสมและคูปอง</a>
        </section>

        <section class="booking-history-section">
            <h2>2. ประวัติการจอง</h2>

            {{-- (เพิ่ม) แสดง Error/Success Messages --}}
            @if ($errors->any())
            <div class="alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif
            @if (session('success'))
                <div class="alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                {{ session('success') }}
                </div>
            @endif
            {{-- (สิ้นสุดส่วน Alert) --}}


            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>รหัสจอง</th>
                            <th>บริการ</th>
                            <th>วัน-เวลา</th>
                            <th>สถานะ</th>
                            <th>ยอดชำระ</th>
                            <th>จัดการ</th> {{-- (เพิ่มคอลัมน์ Action) --}}
                        </tr>
                    </thead>
                    <tbody>
                
                        @forelse ($bookings as $booking)
                        <tr>
                            <td>#{{ $booking->order_id }}</td>
                            <td>จองโต๊ะ</td> {{-- (ข้อมูลบริการ) --}}
                            <td>
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('d/m/Y - H:i') }}
                            </td>

                            {{-- (Logic) แปลง status เป็น Class CSS ของคุณ --}}
                            @php
                                $statusClass = '';
                                $statusText = '';
                                if ($booking->order_status == 'confirmed') {
                                    $statusClass = 'status-completed'; // (ตรงกับ CSS Mock ของคุณ)
                                    $statusText = 'เสร็จสิ้น';
                                } elseif ($booking->order_status == 'pending') {
                                    $statusClass = 'status-upcoming'; // (ตรงกับ CSS Mock ของคุณ)
                                    $statusText = 'รอชำระเงิน/ยืนยัน';
                                } elseif ($booking->order_status == 'cancelled') {
                                    $statusClass = 'status-cancelled'; // (ตรงกับ CSS Mock ของคุณ)
                                    $statusText = 'ยกเลิก';
                                }
                            @endphp
                            <td class="{{ $statusClass }}">{{ $statusText }}</td>

                            <td>{{ number_format($booking->final_amount, 2) }} บาท</td>
                            <td>
                                {{-- (สำคัญ) แสดงปุ่มเฉพาะ Order ที่ 'pending' เท่านั้น --}}
                                @if ($booking->order_status == 'pending')
                                    <form action="{{ route('dashboard.booking.cancel') }}" method="POST" 
                                        onsubmit="return confirm('คุณต้องการยกเลิกการจองนี้ใช่หรือไม่?');">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $booking->order_id }}">
                                    
                                        {{-- (คุณต้องเพิ่ม Style ให้ปุ่มนี้เอง) --}}
                                        <button type="submit" class="cancel-btn-custom" 
                                                style="background-color: #E53E3E; color: white; padding: 5px 10px; border-radius: 5px; border: none; cursor: pointer;">
                                            ยกเลิก
                                        </button>
                                    </form>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center;">ไม่พบประวัติการจอง</td>
                        </tr>
                    @endforelse

                    </tbody>
                </table>
            </div>
        </section>

        <section class="review-history-section">
            <h2>3. ประวัติการรีวิว</h2>
            <div class="review-item">
                <p class="review-meta"><strong>บริการ:</strong> xxx | <strong>คะแนน:</strong> ⭐⭐⭐⭐⭐</p>
                <p class="review-text">"บรรยากาศดีมาก ผ่อนคลายสุดๆ พนักงานมืออาชีพ"</p>
                <p class="review-date">รีวิวเมื่อ: 25/10/2025</p>
            </div>
            <div class="review-item">
                <p class="review-meta"><strong>บริการ:</strong> ตัดผมชาย | <strong>คะแนน:</strong> ⭐⭐⭐⭐</p>
                <p class="review-text">"ตัดได้ทรงสวย แต่รอนานไปหน่อย"</p>
                <p class="review-date">รีวิวเมื่อ: 06/07/2025</p>
            </div>
        </section>
</div>
@endsection