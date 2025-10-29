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
    
    <div class='cta-buttons'>
        <!--<a href='/billiard/booking.php' class='btn btn-primary' style='color: inherit;'>จองโต๊ะเลย!</a>-->
        <a href="{{ route('menu') }}" class='button' style='color: inherit;'>ดูเมนูอาหาร</a>
        <a href="{{ route('booking.reservation') }}" class='button' style='color: inherit;'>จองโต๊ะ</a>
    </div>

</section>

@endsection