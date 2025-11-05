@extends('layouts.app')
@section('title', 'หน้าหลัก')
@section('content')
<section class='hero-section'>
    <h1>Let's Billiard!</h1>

    <p>เราเป็นบริการจองโต๊ะบิลเลียดทั่วอาณาบริเวณเทคโน ฯ ลาดกระบัง</p>

    <div class = 'services'>
        <h2>เราทำอะไรได้บ้าง</h2>
        
        <div class="li-of-services">
            <ul>
                <li>จองโต๊ะบิลเลียด</li>
                <li>ซื้ออาหาร/เครื่องดื่มระหว่างการเล่น</li>
                <li>รีวิวร้านบิลเลียดที่คุณเคยเล่น</li>
                <li>สะสมแต้มเพื่อแลกส่วนลด</li>
            </ul>
        </div>
        
    </div>
    <div class='index-buttons'>
        <a href="{{ route('menu') }}" class='button' style='color: inherit;'>ดูเมนูอาหาร</a>
        <a href="{{ route('booking.branches') }}" class='button' style='color: inherit;'>เลือกสาขาเพื่อทำการจอง</a>
    </div>
    
    <section class="reviews">
            <h2>รีวิวล่าสุดจากลูกค้า</h2>

            <div class="review-list-container" style="margin-top: 1.5rem;">
                
                @forelse ($reviews as $review)
                    <div class="review-item" style="background: #f9f9f9; border: 1px solid #eee; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                        <div class="review-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <h4 style="font-weight: bold;">
                                {{ $review->username }} 
                                {{-- (ตอนนี้ $review->branch_name ใช้ได้แล้ว!) --}}
                                <span style="font-weight: normal; color: #555;">(สาขา: {{ $review->branch_name }})</span>
                            </h4>
                            <span class="rating" style="font-size: 1.2rem; color: #facc15;">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $review->rating) ⭐ @else <span style="color: #ccc;">☆</span> @endif
                                @endfor
                            </span>
                        </div>
                        <p class="review-comment" style="margin-top: 0.5rem; color: #333;">
                            {{-- (ใช้ $review->comment เพราะเราตั้งชื่อแฝงใน Controller) --}}
                            "{{ $review->comment ?? 'ไม่มีความคิดเห็น' }}"
                        </p>
                        <small style="display: block; text-align: right; color: #999; margin-top: 0.5rem;">
                            รีวิวเมื่อ: {{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y') }}
                        </small>
                    </div>
                @empty
                    <p style="text-align: center; color: #777;">ยังไม่มีรีวิวในขณะนี้</p>
                @endforelse

            </div>

            {{-- (ปุ่มนี้จะลิงก์ไปหน้า Dashboard เพื่อให้ User เขียนรีวิว) --}}
            <a href="{{ route('user.dashboard') }}" class="button" style="display: block; width: 200px; margin: 2rem auto 0; text-align: center; text-decoration: none;">
                ไปที่ Dashboard เพื่อเขียนรีวิว
            </a>
            
    </section>
    

</section>

@endsection