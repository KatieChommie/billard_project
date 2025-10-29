<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Billiard Reservation - @yield('title', 'หน้าหลัก')</title>

        {{-- Your Original Fonts & Favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('images/pool-table.png') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'> 

        {{-- ✅ CORRECT: Use @vite directive from Breeze --}}
        @vite(['resources/css/app.css', 'resources/js/app.js']) 

    </head>
    <body class="font-sans antialiased"> {{-- Keep Breeze classes if needed --}}
        <div class="min-h-screen"> {{-- Simplified outer div --}}
            
            {{-- ✅ Your Original Header/Navbar --}}
            <header class="header">
                <div class="header__container">
                    <a href="{{ route('home') }}" class="header__logo">Let's Billiard</a>


                    <nav class="header__nav"> {{-- Or use nav__menu if needed by JS --}}
                        <ul class="nav__list"> {{-- This list will be toggled by JS --}}
                            <li class="nav__item"><a href="{{ route('home') }}" class="nav__link">หน้าหลัก</a></li>
                            <li class="nav__item"><a href="{{ route('booking.branches') }}" class="nav__link">สาขาของเรา</a></li>
                            <li class="nav__item"><a href="{{ route('booking.reservation') }}" class="nav__link">จองโต๊ะ</a></li>
                            <li class="nav__item"><a href="{{ route('menu') }}" class="nav__link">เมนูอาหาร</a></li>
                            <li class="nav__item"><a href="{{ route('reviews') }}" class="nav__link">รีวิว</a></li>
                        </ul>
                    </nav>

                    <div class="header__actions">
                        <a href="{{ route('carts.cart') }}" class="action__cart"><i class='bx bx-cart'></i></a>
                        @guest
                            <a href="{{ route('login') }}" class="nav__button">เข้าสู่ระบบ</a>
                        @endguest
                        @auth
                            <a href="{{ route('points.points') }}" class="nav__button nav__button--user">{{ Auth::user()->username }}</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;"> @csrf </form>
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav__button nav__button--logout">ออกจากระบบ</a>
                        @endauth
                    </div>

                    <i class='bx bx-menu' id='hamburger-icon'></i>
                </div>
            </header>

            {{-- ✅ Your Original Main Content Area --}}
            <main>
                @yield('content') 
            </main>

            {{-- ✅ Your Original Footer --}}
            <footer>
                <p style='text-align: center'>&copy; 2025 Billiard Reservation System</p>
            </footer>

        </div>
        
    </body>
</html>

