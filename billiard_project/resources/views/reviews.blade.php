@extends('layouts.app')
@section('title', 'รีวิว')
@section('content')

<section class = "review-container">
    <h1 style="border-bottom: none; margin-bottom: 30px;">
            🎉 รีวิวจากผู้ใช้งาน Let's Billiard
        </h1>

        {{-- ตรวจสอบว่าผู้ใช้ล็อกอินและมีสิทธิ์เขียนรีวิวหรือไม่ --}}
        @if (Auth::check() && Auth::user()->can_review) 
            <section class="write-review-section">
                <h2 style="border-left: 5px solid #FFC107; color: var(--primary-color);">
                    ✍️ แบ่งปันประสบการณ์ของคุณ
                </h2>
                
                <form action="/submit-review" method="POST" style="padding: 10px 0;">
                    <textarea name="review_text" rows="4" placeholder="เขียนรีวิวประสบการณ์การใช้บริการของคุณ..." 
                              style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: var(--font-thai);" required></textarea>
                    
                    <div style="margin: 10px 0;">
                        <span>ให้คะแนน: </span>
                        {{-- ตัวอย่างการใช้ Icon: <i class="fas fa-star"></i> --}}
                        [Star Rating Components Here]
                    </div>

                    <button type="submit" class="review-submit-button">
                        ส่งรีวิว
                    </button>
                </form>
            </section>
            
            <hr style="margin: 40px 0; border: 0; border-top: 1px solid #eee;">
        @endif


        <section class="all-reviews-section">
            <h2>
                🌟 รีวิวทั้งหมด (25 รายการ)
            </h2>

            {{-- วนลูปแสดงรีวิวจากฐานข้อมูล (สมมติว่ามีตัวแปร $reviews) --}}
            @php
                // นี่คือข้อมูลรีวิวตัวอย่าง
                $reviews = [
                    ['user' => 'Pimon.S', 'rating' => 5, 'text' => 'ห้องสนุ๊กสะอาดมาก โต๊ะได้มาตรฐาน พนักงานบริการดีเยี่ยม อาหารก็อร่อย!', 'date' => '2025-10-25'],
                    ['user' => 'Boon.K', 'rating' => 4, 'text' => 'จองง่ายผ่านระบบออนไลน์ แต่ที่จอดรถค่อนข้างจำกัด แนะนำให้มาวันธรรมดา', 'date' => '2025-10-20'],
                    ['user' => 'Guest.T', 'rating' => 5, 'text' => 'เหมาะกับการมาใช้เวลากับเพื่อนๆ มากค่ะ เพลงดี อาหารเครื่องดื่มครบ', 'date' => '2025-10-18']
                ];
            @endphp

            @foreach ($reviews as $review)
            <div class="review-item">
                <div class="review-meta">
                    <strong>{{ $review['user'] }}</strong> 
                    <span style="float: right; color: #FFC107;">
                         {{-- แสดงดาวตาม rating --}}
                         [Rating: {{ $review['rating'] }}/5] 
                    </span>
                </div>
                <p class="review-text">
                    "{{ $review['text'] }}"
                </p>
                <p class="review-date">
                    รีวิวเมื่อ: {{ $review['date'] }}
                </p>
            </div>
            @endforeach
            
            {{-- ส่วน Pagination (ถ้ามีรีวิวเยอะ) 
            <div style="text-align: center; margin-top: 20px;">
                [Pagination Links Here]
            </div>--}}

        </section>
</section>

@endsection