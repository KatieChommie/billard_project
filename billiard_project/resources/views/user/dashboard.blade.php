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


            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>รหัสจอง</th>
                            <th>บริการ</th>
                            <th>สาขา</th>
                            <th>วัน-เวลา</th>
                            <th>สถานะ</th>
                            <th>ยอดชำระ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                
                        @forelse ($bookings as $booking)
                        <tr>
                            <td>#{{ $booking->order_id }}</td>
                            <td>
                                @php
                                    $services = [];
                                    if ($booking->has_table) { $services[] = 'จองโต๊ะ'; }
                                    if ($booking->has_food) { $services[] = 'สั่งอาหาร'; }
                                    if (empty($services)) { $services[] = 'บริการไม่ระบุ'; }
                                @endphp
                                {{ implode(' + ', $services) }}
                            </td>
                            <td>{{ $booking->branch_name ?? '-' }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($booking->display_time)->format('d/m/Y - H:i') }}
                            </td>

                            @php
                                $statusClass = '';
                                $statusText = '';
                                if ($booking->order_status == 'confirmed') {
                                    $statusClass = 'status-confirmed'; 
                                    $statusText = 'ชำระแล้ว';
                                } elseif ($booking->order_status == 'pending') {
                                    $statusClass = 'status-upcoming';
                                    $statusText = 'รอ';
                                } elseif ($booking->order_status == 'cancelled') {
                                    $statusClass = 'status-cancelled';
                                    $statusText = 'ยกเลิก';
                                } elseif ($booking->order_status == 'completed') {
                                    $statusClass = 'status-completed';
                                    $statusText = 'เสร็จสิ้น';
                                }
                            @endphp
                            <td class="{{ $statusClass }}">{{ $statusText }}</td>

                            <td>{{ number_format($booking->final_amount, 2) }} บาท</td>
                            <td>
                                @if ($booking->order_status == 'pending')
                                    <a href="{{ route('checkout.page', ['order_id' => $booking->order_id]) }}" 
                                        class="pay-button">ชำระเงิน</a>
                                    <form action="{{ route('dashboard.booking.cancel') }}" method="POST" 
                                        onsubmit="return confirm('คุณต้องการยกเลิกการจองนี้ใช่หรือไม่?');" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $booking->order_id }}">
                
                                        <button type="submit" class="cancel-btn-custom">ยกเลิก</button>
                                    </form>
                                @elseif ($booking->order_status == 'completed')
            
                                    {{-- (แก้ไข) เช็คว่า branch_id นี้ อยู่ในลิสต์ที่รีวิวแล้วหรือยัง --}}
                                    @if ($booking->has_table && $booking->branch_id)
                                        @if ($booking->has_reviewed)
                                            <span style="color: #666; font-style: italic;">รีวิวแล้ว</span>
                                        @else
                                            <a href="{{ route('review.create', ['order_id' => $booking->order_id, 'branch_id' => $booking->branch_id]) }}" 
                                                class="review-button" 
                                                style="background: #3182CE; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;">
                                                    รีวิวสาขา
                                            </a>
                                        @endif
                                    @else
                                    -
                                    @endif
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
            
            @forelse ($reviewHistory as $review)
                <div class="review-item" style="border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem;">
                    
                    {{-- (แสดง สาขา และ ดาว) --}}
                    <p class="review-meta">
                        <strong>สาขา:</strong> {{ $review->branch_name }} | 
                        <strong>คะแนน:</strong> 
                        <span style="color: #facc15; font-size: 1.1rem;">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $review->rating) ⭐ @else <span style="color: #ccc;">☆</span> @endif
                        @endfor
                        </span>
                    </p>
                    
                    {{-- (แสดง คอมเมนต์) --}}
                    <p class="review-text" style="font-style: italic; color: #333; margin: 0.5rem 0;">
                        "{{ $review->review_descrpt }}"
                    </p>
                    
                    {{-- (แสดง วันที่) --}}
                    <p class="review-date" style="font-size: 0.85rem; color: #888;">
                        รีวิวเมื่อ: {{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y') }}
                    </p>
                </div>
            @empty
                {{-- (กรณีที่ยังไม่เคยรีวิว) --}}
                <div class="review-item">
                    <p style="text-align: center; color: #777;">คุณยังไม่มีประวัติการรีวิว</p>
                </div>
            @endforelse

        </section>
</div>
@endsection