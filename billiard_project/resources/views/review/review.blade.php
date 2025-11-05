@extends('layouts.app')
{{-- ... (CSS และ @push styles) ... --}}

@section('content')
<main class="reviews-container">
    <h1>รีวิวจากลูกค้าของเรา</h1>

    <section class="review-form-box">
        @auth
            {{-- ... (แสดงข้อความ Success/Error) ... --}}

            {{-- (แก้ไข) ตรวจสอบว่ามี Booking ให้รีวิวหรือไม่ --}}
            @if ($bookings_to_review->isNotEmpty())
                
                <h3>เขียนรีวิว (การจองที่เสร็จสิ้นแล้ว)</h3>
                <form action="{{ route('reviews.submit') }}" method="POST">
                    @csrf
                    
                    {{-- (ใหม่) Dropdown เลือก Order ที่จะรีวิว --}}
                    <div style="margin-bottom: 1rem;">
                        <label for="order_id">เลือกการจองที่ต้องการรีวิว:</label>
                        <select name="order_id" id="order_id" required 
                                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                            <option value="">-- กรุณาเลือก --</option>
                            @foreach ($bookings_to_review as $booking)
                                <option value="{{ $booking->order_id }}">
                                    Order #{{ $booking->order_id }} 
                                    (เล่นเมื่อ: {{ \Carbon\Carbon::parse($booking->start_time)->format('d/m/Y H:i') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Input ดาว (name="rating") --}}
                    <div style="margin-bottom: 1rem;">
                        <label>ให้คะแนน:</label>
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" required/><label for="star5" title="5 stars">★</label>
                            <input type="radio" id="star4" name="rating" value="4" required/><label for="star4" title="4 stars">★</label>
                            <input type="radio" id="star3" name="rating" value="3" required/><label for="star3" title="3 stars">★</label>
                            <input type="radio" id="star2" name="rating" value="2" required/><label for="star2" title="2 stars">★</label>
                            <input type="radio" id="star1" name="rating" value="1" required/><label for="star1" title="1 star">★</label>
                        </div>
                    </div>

                    {{-- Input ข้อความ (name="review_text" ซึ่ง Controller ใช้บันทึกลง review_descrpt) --}}
                    <div style="margin-bottom: 1rem;">
                        <label for="review_text">ความคิดเห็น:</label>
                        <textarea id="review_text" name="review_text" rows="4" 
                              style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" 
                              placeholder="เล่าประสบการณ์ของคุณ (ขั้นต่ำ 10 ตัวอักษร)"
                              required>{{ old('review_text') }}</textarea>
                    </div>
                    
                    <button type="submit" class="submit-review-btn">ส่งรีวิว</button>
                </form>

            @else
                <p style="text-align: center; font-size: 1.1rem;">
                    คุณยังไม่มีรายการจองที่เสร็จสิ้น (Completed) ที่รอการรีวิว
                </p>
            @endif

        @endauth
        {{-- ... (guest view) ... --}}
    </section>

    {{-- 2. แสดงรีวิวทั้งหมด --}}
    <section class="existing-reviews">
        @forelse ($reviews as $review)
            <div class="review-item">
                <div class="review-header">
                    <span class="review-author">{{ $review->first_name }} {{ Str::substr($review->last_name, 0, 1) }}.</span>
                    {{-- ... (แสดงดาว) ... --}}
                </div>
                <p class="review-body">{{ $review->review_text }}</p>
                <p class="review-date">
                    รีวิวเมื่อ: {{ \Carbon\Carbon::parse($review->review_date)->diffForHumans() }}
                    {{-- (เพิ่ม) ถ้าต้องการแสดงว่ารีวิว Order ไหน --}}
                    @if ($review->order_id) (Order #{{ $review->order_id }}) @endif
                </p>
            </div>
        @empty
            <p style="text-align: center;">ยังไม่มีรีวิวในขณะนี้</p>
        @endforelse
    </section>

</main>
@endsection