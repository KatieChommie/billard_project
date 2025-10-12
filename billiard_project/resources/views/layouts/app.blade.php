<!DOCTYPE html>
<html lang='th'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    
    {{-- ✅ 1. Title: ใช้ @yield('title') --}}
    <title>Billiard Reservation - @yield('title', 'หน้าหลัก')</title>
    
    {{-- ✅ 2. Asset Link: ใช้ {{ asset('path') }} และระบุพาธจาก public/ --}}
    {{-- ไฟล์ pool-table.png ต้องอยู่ใน public/images/ --}}
    <link rel="icon" type="image/png" href="{{ asset('images/pool-table.png') }}">
    
    {{-- ✅ 2. Asset Link: ไฟล์ style.css ต้องอยู่ใน public/css/ --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <link rel='preconnect' href='https://fonts.googleapis.com'>
    <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
    <link href='https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap' rel='stylesheet'>
</head>
<body>
    <!--<header class = "header">
        <a href="{{ route('home') }}" class = "nav__logo" style='color: inherit; text-decoration: none;'>Let's Billiard</a>
         
        <div class = "nav__menu" id = "nav-menu"></div>
            <ul class='nav__list'>
                <li class="nav__item"><a href="{{ route('home') }}" class = "nav__link">หน้าหลัก</a></li>
                <li class="nav__item"><a href="{{ route('booking.branches') }}" class = "nav__link">สาขาของเรา</a></li>
                <li class="nav__item"><a href="{{ route('booking.reservation') }}" class = "nav__link">จองโต๊ะ</a></li>
                <li class="nav__item"><a href="{{ route('menu') }}" class = "nav__link">เมนูอาหาร</a></li>
                <li class="nav__item"><a href="{{ route('reviews') }}" class = "nav__link">รีวิว</a></li>
                
                <div class = "nav__right-area">
                    <a href="{{ route('login') }}" class = "nav__button">เข้าสู่ระบบ</a>
                </div>
            </ul>
        </div>
    </header>-->
    <header class="header">
        <div class="header__container">
        
            {{-- โลโก้ --}}
            <a href="{{ route('home') }}" class="header__logo">Let's Billiard</a>

            {{-- กล่องเมนูหลัก --}}
            <nav class="header__nav">
                <ul class="nav__list">
                
                    {{-- ลิงก์หลักทั้งหมด --}}
                    <li class="nav__item"><a href="{{ route('home') }}" class="nav__link">หน้าหลัก</a></li>
                    <li class="nav__item"><a href="{{ route('menu') }}" class="nav__link">เมนูอาหาร</a></li>
                
                    {{-- ✅ ลิงก์จองโต๊ะที่ถูกต้อง --}}
                    <li class="nav__item"><a href="{{ route('booking.reservation') }}" class="nav__link">จองโต๊ะ</a></li>
                
                    <li class="nav__item"><a href="{{ route('reviews') }}" class="nav__link">รีวิว</a></li>
                
                </ul>
            </nav>
        
            {{-- ส่วนปุ่ม Login/Logout/Cart --}}
            <div class="header__actions">
            
                {{-- ปุ่มตะกร้า (Cart) --}}
                <a href="{{ route('carts.cart') }}" class="action__cart">
                    <i class='bx bx-cart'></i>
                </a>

                {{-- Logic Login/Logout/Points --}}
                @guest
                    <a href="{{ route('login') }}" class="nav__button">เข้าสู่ระบบ</a>
                @endguest
            
                @auth
                    <a href="{{ route('points') }}" class="nav__button nav__button--user">{{ Auth::user()->username }}</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav__button nav__button--logout">ออกจากระบบ</a>
                @endauth
            </div>
        
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        <p style='text-align: center'>&copy; 2025 Billiard Reservation System</p>
    </footer>

</body>
</html>