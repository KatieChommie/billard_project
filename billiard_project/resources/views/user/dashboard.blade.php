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
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>รหัสการจอง</th>
                            <th>บริการ</th>
                            <th>วันที่/เวลา</th>
                            <th>สถานะ</th>
                            <th>ยอดชำระ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>BKG-1234</td>
                            <td>xxx</td>
                            <td>25/10/2025 - 14:00</td>
                            <td class="status-completed">เสร็จสิ้น</td>
                            <td>xxx บาท</td>
                        </tr>
                        <tr>
                            <td>xx</td>
                            <td>xxx</td>
                            <td>05/11/2025 - 10:00</td>
                            <td class="status-upcoming">กำลังจะมาถึง</td>
                            <td>xx บาท</td>
                        </tr>
                        <tr>
                            <td>BKG-9012</td>
                            <td>สปาเท้า</td>
                            <td>15/09/2025 - 11:30</td>
                            <td class="status-cancelled">ยกเลิก</td>
                            <td>800 บาท</td>
                        </tr>
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