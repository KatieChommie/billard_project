@extends('layouts.app')
@section('title', 'เขียนรีวิว')

@extends('layouts.app')
@section('title', 'เขียนรีวิว')

@section('content')
<div class="review-form-container" style="max-width: 600px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">

    {{-- (สำคัญ) เราต้องใช้ $branch->branch_name ไม่ใช่ $branch_name --}}
    <h1 style="text-align: center;">รีวิว Order #{{ $order_id }}</h1>
    <h2 style="text-align: center; color: #555;">สาขา: {{ $branch->branch_name }}</h2>
    
    {{-- (แสดง Error Messages) --}}
    @if ($errors->any())
        <div class="alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 15px 0;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('review.store') }}" method="POST">
        @csrf
        
        {{-- (ซ่อนข้อมูลที่จำเป็น) --}}
        <input type="hidden" name="order_id" value="{{ $order_id }}">
        <input type="hidden" name="branch_id" value="{{ $branch->branch_id }}">

        {{-- 1. ให้คะแนน (Rating) --}}
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="rating" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">คะแนน (1-5):</label>
            <select name="rating" id="rating" required style="width: 100%; padding: 0.75rem; border-radius: 5px; border: 1px solid #ccc; font-family: 'Prompt', sans-serif;">
                <option value="" disabled selected>-- กรุณาให้คะแนน --</option>
                <option value="5">⭐⭐⭐⭐⭐ (5 ดาว - ยอดเยี่ยม)</option>
                <option value="4">⭐⭐⭐⭐ (4 ดาว - ดี)</option>
                <option value="3">⭐⭐⭐ (3 ดาว - ปานกลาง)</option>
                <option value="2">⭐⭐ (2 ดาว - พอใช้)</option>
                <option value="1">⭐ (1 ดาว - ต้องปรับปรุง)</option>
            </select>
        </div>

        {{-- 2. ความคิดเห็น (Comment) --}}
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="comment" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">ความคิดเห็น (ไม่บังคับ):</label>
            {{-- (สำคัญ) name="comment" ต้องตรงกับ store() --}}
            <textarea name="comment" id="comment" rows="5" placeholder="บรรยากาศ, การบริการ, ความสะอาด, ฯลฯ" style="width: 100%; padding: 0.75rem; border-radius: 5px; border: 1px solid #ccc; font-family: 'Prompt', sans-serif;"></textarea>
        </div>

        {{-- 3. ปุ่ม Submit --}}
        <div style="text-align: center;">
            <button type="submit" class="submit-review-btn" style="background-color: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; font-size: 1.1rem; cursor: pointer; font-family: 'Prompt', sans-serif; font-weight: 500;">
                ส่งรีวิว
            </button>
        </div>
    </form>
    
    <div style="text-align: center; margin-top: 1rem;">
        <a href="{{ route('user.dashboard') }}" style="color: #888; text-decoration: underline;">ยกเลิก</a>
    </div>

</div>
@endsection