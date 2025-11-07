@extends('layouts.app')
@section('title', 'เข้าสู่ระบบ')

@section('content')
<div class="login-page-container">
    <div class="login-card">

        <a href="{{ route('home') }}" class="back-link" aria-label="ย้อนกลับ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
        
        <h2 class="login-card-title">เข้าสู่ระบบ Let's Billiard</h2>
        
        <form method="POST" action="{{ route('login') }}" autocomplete="off">
            @csrf

            @if ($errors->any())
                <div class="alert-message error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li style="list-style: none;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="input-group">
                <input type="text" id="email" name="email" placeholder=" " value="{{ old('email') }}" required autofocus>
                <label for="email" class="input-label">อีเมล</label>
            </div>
            
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder=" " required>
                <label for="password" class="input-label">รหัสผ่าน</label>
                <button type="button" id="togglePassword" class="password-toggle-btn" aria-label="Toggle password visibility" aria-pressed="false">
                    <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.95 7-9.54 7S3.73 16.06 2.46 12z" />
                        <circle cx="12" cy="12" r="3" />
                        <line id="iconSlash" x1="3" y1="3" x2="21" y2="21" style="display:inline;" />
                    </svg>
                </button>
            </div>
            
            
            <button type="submit" class="login-btn mb-3">เข้าสู่ระบบ</button>
        </form>
        
        <p class="text-center mt-3">
            <a class="link-footer" href="{{ route('register') }}">ยังไม่มีบัญชีเหรอ? สร้างบัญชีใหม่สิ</a>
        </p>
    </div>
</div>

@endsection
