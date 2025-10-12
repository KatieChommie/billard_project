@extends('layouts.app')
@section('title', 'เมนูอาหาร')
@section('content')

<section class='menu-container'>
    <h2>เมนูอาหารและเครื่องดื่ม</h2>
    <div class='menu-grid'>
        <!--Menus-->
        <div class='menu-item'>
            <img src= {{ asset('images\a_can_of_coke.webp') }} alt='เฟรนช์ฟรายส์'>
            <h3>เฟรนช์ฟรายส์ (French Fries)</h3>
            <p class='description'></p>
            <p class='price'>฿ 69</p>
            <button class='add-to-cart-btn'>สั่งเลย</button>
        </div>

        <div class='menu-item'>
            <img src={{ asset('images\a_plate_of_ff.webp') }} alt='โค้ก'>
            <h3>โค้ก (Coke)</h3>
            <p class='description'>เครื่องดื่มเย็นชื่นใจ เหมาะกับเกมบิลเลียด</p>
            <p class='price'>฿ 30</p>
            <button class='add-to-cart-btn'>สั่งเลย</button>
        </div>

        </div>
</section>

@endsection