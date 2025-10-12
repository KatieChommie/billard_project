@extends('layouts.app') 
@section('title', 'เข้าสู่ระบบ') 

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card p-5 shadow-lg" style="width: 100%; max-width: 400px;">
        <h2 class="text-center mb-4">Log in</h2>
        
        {{-- ✅ Form Submit จะชี้ไปที่ Login Controller ของ Laravel --}}
        <form method="POST" action="{{ route('login') }}"> 
            @csrf

            {{-- ********** แสดงข้อผิดพลาด Validation ********** --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-4">
                <label for="username" class="form-label">ชื่อผู้ใช้งาน / อีเมล</label>
                {{-- ใช้ name="username" หรือ name="email" ตามที่ Laravel Auth ต้องการ --}}
                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                    <label class="form-check-label" for="remember_me">จำฉันไว้</label>
                </div>
                
                {{-- ลิงก์ลืมรหัสผ่าน --}}
                @if (Route::has('password.request'))
                    <a class="text-sm text-decoration-none" href="{{ route('password.request') }}">
                        ลืมรหัสผ่าน?
                    </a>
                @endif
            </div>
            
            <button type="submit" class="btn btn-success w-100 mb-3">Log in</button>
        </form>
        
        <p class="text-center mt-3">
            {{-- ลิงก์ไปหน้าลงทะเบียน --}}
            <a href="{{ route('register') }}">สร้างบัญชีใหม่</a>
        </p>
    </div>
</div>
@endsection
