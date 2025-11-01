@extends('layouts.app')
@section('title', 'สาขาของเรา')
@section('content')
<section class='branches-container'>
    <h1>สาขาทั้งหมดของเรา</h1>
    <div class='branches-grid'>
        @foreach ($branches as $branch)
        <div class='list-of-branches'>
            <img src="{{ asset('images/' . $branch->image_path) }}" alt="สาขา {{ $branch->branch_name }}">
            <div class="Info">
                <h3>{{ $branch->branch_name }}</h3>
                
                {{-- โค้ดที่แก้ไข: ดึงเวลา 2 คอลัมน์มาแสดง --}}
                <p>Available on: 
                    {{-- H:i คือ 13:00, g:i A คือ 1:00 PM --}}
                    {{ $branch->time_open ? \Carbon\Carbon::parse($branch->time_open)->format('H:i') : 'N/A' }} 
                    - 
                    {{ $branch->time_close ? \Carbon\Carbon::parse($branch->time_close)->format('H:i') : 'N/A' }}
                </p>
                
                <p>Contact: {{ $branch->branch_phone }}</p>
                <p>Address: {{ $branch->branch_address }}</p>
            </div>
        </div>
        @endforeach

    </div>
</section>
@endsection