@extends('layouts.app')
@section('title', 'แดชบอร์ดผู้ใช้')

@section('content')
<div class="user-dashboard">
    <h1>สวัสดีคุณ {{ Auth::user()->username }}!</h1>
    <div class="info-flex">
       <div class="info">
        <p class="title">ชื่อสำหรับการจอง:</p> {{ Auth::user()->name }}
       </div>
       <div class="info">
        <p class="title">คะแนนสะสม:</p> {{ Auth::user()->loyalty_points }}
       </div>
    </div>
</div>
@endsection