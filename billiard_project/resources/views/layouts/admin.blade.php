{{-- นี่คือไฟล์ resources/views/layouts/admin.blade.php (เวอร์ชัน Navbar บน) --}}
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Let's Billiard Admin</title> 

    {{-- (ลิงก์ CSS และ Fonts จาก app.css) --}}
    <link rel="icon" type="image/png" href="{{ asset('icons/pool-table.png') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    @vite(['resources/css/app.css', 'resources/js/app.js']) 
</head>
<body class="font-sans antialiased">
    
    <div class="min-h-screen">
        
        <header class="admin-header">
            <div class="header__container">
                <a href="{{ route('admin.dashboard') }}" class="header__logo">Admin</a>
                
                <nav class="admin-header__nav">
                    <ul class="admin-nav__list">
                        <li class="nav__item {{ Request::is('admin/dashboard*') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}" class="nav__link">Dashboard</a>
                        </li>
                        <li class="nav__item {{ Request::is('admin/users*') ? 'active' : '' }}">
                            <a href="#" class="nav__link">ผู้ใช้</a>
                        </li>
                        <li class="nav__item {{ Request::is('admin/branches*') ? 'active' : '' }}">
                            <a href="#" class="nav__link">สาขา</a>
                        </li>
                        <li class="nav__item {{ Request::is('admin/menus*') ? 'active' : '' }}">
                            <a href="#" class="nav__link">เมนู</a>
                        </li>
                        <li class="nav__item {{ Request::is('admin/bookings*') ? 'active' : '' }}">
                            <a href="#" class="nav__link">การจอง</a>
                        </li>
                        <li class="nav__item">
                            <a href="{{ route('home') }}" class="nav__link admin-home-link">
                                <span>กลับหน้าหลัก</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="header__actions">
                    @auth
                        {{-- (ปุ่ม Logout เหมือนเดิม) --}}
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;"> @csrf </form>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav__button nav__button--logout">ออกจากระบบ ({{ Auth::user()->username }})</a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="admin-content-area">
            <div class="admin-content-wrapper">
                @yield('content')
            </div>
        </main>

    </div>

</body>
</html>