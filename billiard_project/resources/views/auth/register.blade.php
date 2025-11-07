@extends('layouts.app')
@section('title', 'สมัครสมาชิก')

@section('content')
<div class="regis-page-container">
    <div class="regis-card">

        <a href="{{ route('login') }}" class="back-link" aria-label="ย้อนกลับ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
        
        <h2 class="regis-card-title">สร้างบัญชีใหม่</h2>
        
        <form method="POST" action="{{ route('register') }}" autocomplete="off">
            @csrf
            @if ($errors->any())
                <div class="alert-message error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li style="list-style:none;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        <div class="regis-form-grid"> 
        
            <div class="input-group">
                <input type="text" id="first_name" name="first_name" placeholder=" " value="{{ old('first_name') }}" required autofocus autocomplete="off" maxlength="30">
                <label for="first_name" class="input-label">ชื่อ</label>
            </div>

            <div class="input-group">
                <input type="text" id="last_name" name="last_name" placeholder=" " value="{{ old('last_name') }}" required autofocus autocomplete="off" maxlength="30">
                <label for="last_name" class="input-label">นามสกุล</label>
            </div>

            <div class="input-group">
                <input type="text" id="username" name="username" placeholder=" " value="{{ old('username') }}" required autofocus autocomplete="off" maxlength="30">
                <label for="username" class="input-label">ชื่อผู้ใช้งาน </label>
            </div>
        
            <div class="input-group">
                <input type="tel" id="phone_number" name="phone_number" placeholder=" " value="{{ old('phone_number') }}" required autocomplete="off" maxlength="10">
                <label for="phone_number" class="input-label">เบอร์โทรศัพท์ (10 หลัก)</label>
            </div>

            <div class="input-group">
                <input type="email" id="email" name="email" placeholder=" " value="{{ old('email') }}" required autocomplete="off" maxlength="45">
                <label for="email" class="input-label">อีเมล</label>
            </div>
        
            <div class="input-group">
                <input type="date" id="date_of_birth" name="date_of_birth" placeholder=" " value="{{ old('date_of_birth') }}" required>
                <label for="date_of_birth" class="input-label">วัน/เดือน/ปีเกิด</label>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder=" " required autocomplete="new-password">
                <label for="password" class="input-label">รหัสผ่าน</label>
                <button type="button" id="togglePassword" class="password-toggle-btn" aria-label="Toggle password visibility" aria-pressed="false">
                    <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.95 7-9.54 7S3.73 16.06 2.46 12z" />
                        <circle cx="12" cy="12" r="3" />
                        <line id="iconSlash" x1="3" y1="3" x2="21" y2="21" style="display:inline;" />
                    </svg>
                </button>
            </div>

            <div class="input-group">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder=" " required autocomplete="new-password">
                <label for="password_confirmation" class="input-label">ยืนยันรหัสผ่าน</label>
                <button type="button" id="togglePasswordConfirmation" class="password-toggle-btn" aria-label="Toggle password visibility" aria-pressed="false">
                    <svg id="toggleIconConfirmation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.95 7-9.54 7S3.73 16.06 2.46 12z" />
                        <circle cx="12" cy="12" r="3" />
                        <line id="iconSlashConfirmation" x1="3" y1="3" x2="21" y2="21" style="display:inline;" />
                    </svg>
                </button>
            </div>
        
        </div>
            
            <button type="submit" class="regis-btn mt-3 mb-3">ลงทะเบียน</button>
        </form>
        
        <p class="text-center mt-3">
            <a class="link-footer" href="{{ route('login') }}">มีบัญชีอยู่แล้วเหรอ? เข้าสู่ระบบสิ</a>
        </p>
    </div>
</div>
@endsection
