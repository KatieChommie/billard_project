@extends('layouts.app')
@section('title', 'สมัครสมาชิก')

@section('content')
<div class="regis-page-container">
    <div class="regis-card">
        <h2 class="regis-card-title">สร้างบัญชีใหม่</h2>
        
        <form method="POST" action="{{ route('register') }}" autocomplete="off">
            @csrf

            {{-- ** แสดงข้อผิดพลาด Validation ** --}}
            @if ($errors->any())
                <div class="error-message mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="input-group">
                <input type="text" id="name" name="name" placeholder=" " value="{{ old('name') }}" required autofocus autocomplete="off" maxlength="30">
                <label for="name" class="input-label">ชื่อ</label>
            </div>

            <!-- 1. ชื่อผู้ใช้งาน (user_name) -->
            <div class="input-group">
                <input type="text" id="username" name="username" placeholder=" " value="{{ old('username') }}" required autofocus autocomplete="off" maxlength="30">
                <label for="username" class="input-label">ชื่อผู้ใช้งาน </label>
            </div>
            
            <!-- 2. เบอร์โทรศัพท์ (user_phone) -->
            <div class="input-group">
                <input type="tel" id="phone_number" name="phone_number" placeholder=" " value="{{ old('phone_number') }}" required autocomplete="off" maxlength="10">
                <label for="phone_number" class="input-label">เบอร์โทรศัพท์ (10 หลัก)</label>
            </div>

            <!-- 3. อีเมล (user_email) -->
            <div class="input-group">
                <input type="email" id="email" name="email" placeholder=" " value="{{ old('email') }}" required autocomplete="off" maxlength="45">
                <label for="email" class="input-label">อีเมล</label>
            </div>
            
            <!-- 4. วันเกิด (user_dob) -->
            <div class="input-group">
                <!-- ใช้ type="date" สำหรับวันเกิด -->
                <input type="date" id="date_of_birth" name="date_of_birth" placeholder=" " value="{{ old('date_of_birth') }}" required>
                <label for="date_of_birth" class="input-label">วัน/เดือน/ปีเกิด</label>
            </div>

            <!-- 5. รหัสผ่าน (user_password) -->
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder=" " required autocomplete="new-password">
                <label for="password" class="input-label">รหัสผ่าน</label>
                <button type="button" id="togglePassword" class="password-toggle-btn" aria-label="Toggle password visibility" aria-pressed="false">
                    <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Eye outline -->
                        <path d="M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.95 7-9.54 7S3.73 16.06 2.46 12z" />
                        <circle cx="12" cy="12" r="3" />
                        <!-- Slash (visible when password is hidden) -->
                        <line id="iconSlash" x1="3" y1="3" x2="21" y2="21" style="display:inline;" />
                    </svg>
                </button>
            </div>

            <!-- 6. ยืนยันรหัสผ่าน (user_password_confirmation) -->
            <div class="input-group">
                <!-- Laravel ต้องการชื่อฟิลด์นี้สำหรับยืนยันรหัสผ่าน -->
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder=" " required autocomplete="new-password">
                <label for="password_confirmation" class="input-label">ยืนยันรหัสผ่าน</label>
                
            </div>
            
            <button type="submit" class="regis-btn mt-3 mb-3">ลงทะเบียน</button>
        </form>
        
        <p class="text-center mt-3">
            <a class="link-footer" href="{{ route('login') }}">มีบัญชีอยู่แล้วเหรอ? เข้าสู่ระบบสิ</a>
        </p>
    </div>
</div>
@endsection
