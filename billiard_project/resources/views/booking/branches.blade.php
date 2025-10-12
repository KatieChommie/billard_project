@extends('layouts.app')
@section('title', 'สาขาของเรา')
@section('content')
<section class='branches-container'>
    <h2>สาขาทั้งหมดของเรา</h2>
    <div class='branches-grid'>
        <!--Branches-->
        <div class='list-of-branches'>
            <img src= {{ asset('images\k4br.jpg') }} alt='สาขาเกกี 4' >
            <h3>สาขา เกกี 4 (Keki 4)</h3>
            <p class='Available'>Available on: 12.00 PM - 11.00 PM</p>
            <p class='Contact'>Contact: 089-111-1111</p>
            <p class='Address'>Address: ซอยเกกีงาม 4 ถนนฉลองกรุง 1 แขวง/เขต ลาดกระบัง กทมฯ 10520</p>
        </div>

        <div class='list-of-branches'>
            <img src= {{ asset('images\shmbr.jpg') }} alt='สาขาเกกี 4' >
            <h3>สาขา ซอยหอใหม่ (Soi Hor Mai)</h3>
            <p class='Available'>Available on: 10.00 AM - 11.00 PM</p>
            <p class='Contact'>Contact: 089-222-2222</p>
            <p class='Address'>Address: ถนนฉลองกรุง 1 แยก 6</p>
        </div>

        <div class='list-of-branches'>
            <img src= {{ asset('images\mmbr.jpg') }} alt='สาขาเกกี 4' >
            <h3>สาขา มีสมาย (Me Smile)</h3>
            <p class='Available'>Available on: 6.00 PM - 2.00 AM</p>
            <p class='Contact'>Contact: 089-333-3333</p>
            <p class='Address'>Address: แขวง/เขต ลาดกระบัง กทมฯ 10520</p>
        </div>

        <div class='list-of-branches'>
            <img src= {{ asset('images\otrbr.jpg') }} alt='สาขาเกกี 4' >
            <h3>สาขา ออนเดอะรูฟ (On The Roof)</h3>
            <p class='Available'>Available on: 6.00 PM - 2.00 AM</p>
            <p class='Contact'>Contact: 089-444-4444</p>
            <p class='Address'>Address: ซอยลาดกระบัง 13/5</p>
        </div>

        <div class='list-of-branches'>
            <img src= {{ asset('images\vvgbr.jpg') }} alt='สาขาเกกี 4' >
            <h3>สาขา วิด-วะ การ์เด้น (Vidva Garden)</h3>
            <p class='Available'>Available on: 3.00 PM - 0.00 AM</p>
            <p class='Contact'>Contact: 089-555-5555</p>
            <p class='Address'>Address: แขวง/เขต ลาดกระบัง กทมฯ 10520</p>
        </div>

        <div class='list-of-branches'>
            <img src= {{ asset('images\kllcbr.jpg') }} alt='สาขาเกกี 4' >
            <h3>สาขา หอสมุด (KLLC)</h3>
            <p class='Available'>Available on: 9.00 AM - 8.00 PM</p>
            <p class='Contact'>Contact: 089-666-6666</p>
            <p class='Address'>Address: แขวง/เขต ลาดกระบัง กทมฯ 10520</p>
        </div>

    </div>
</section>
@endsection