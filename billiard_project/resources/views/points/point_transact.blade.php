@extends('layouts.app')
@section('title', 'ประวัติคะแนน')

@section('content')
<div class="points-container">
    <a href="{{ route('user.dashboard') }}" class="back-link" aria-label="ย้อนกลับ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span style="color: white;">กลับไปยังแดชบอร์ดผู้ใช้</span>
    </a>
    {{-- ส่วนที่ 1: แสดงคะแนนปัจจุบัน (เหมือนเดิม) --}}
    <div class="current-points-box">
        <p>คะแนนสะสมของคุณ</p>
        <span class="points-value">{{ $currentPoints }}</span>
        <span class="points-unit">แต้ม</span>
    </div>

    {{-- ส่วนที่ 2: แถบนำทาง (เหมือนเดิม) --}}
    <div class="points-navigation">
        <a href="{{ route('points.index') }}" class="nav-button" >แลกคะแนน</a>
        <a href="{{ route('points.history') }}" class="nav-button active">ประวัติ</a>
    </div>

    {{-- ส่วนที่ 3: แท็บสลับ (ส่วนที่เพิ่มใหม่) --}}
    <div class="history-tabs">
        {{-- ปุ่มสลับแท็บ --}}
        <button class="tab-link active" onclick="openTab(event, 'received-content')">ได้รับ (Received)</button>
        <button class="tab-link" onclick="openTab(event, 'redeemed-content')">ใช้ไป (Used)</button>
    </div>

    {{-- ส่วนที่ 4: เนื้อหาของแท็บ (ส่วนที่แก้ไข) --}}

    <div id="received-content" class="tab-content active">
        <div class="transaction-list">
            @forelse ($received as $tx)
                <div class="transaction-item">
                    <div class="transaction-details">
                        <p class="description">{{ $tx->transact_descrpt }}</p>
                        <p class="date">{{ \Carbon\Carbon::parse($tx->transact_date)->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="points-change received">+{{ $tx->pts_change }}</span>
                </div>
            @empty
                <p>ยังไม่มีประวัติการได้รับคะแนน</p>
            @endforelse
        </div>
    </div>

    <div id="redeemed-content" class="tab-content">
        <div class="transaction-list">
            @forelse ($redeemed as $tx)
                <div class="transaction-item">
                    <div class="transaction-details">
                        <p class="description">{{ $tx->transact_descrpt }}</p>
                        <p class="date">{{ \Carbon\Carbon::parse($tx->transact_date)->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="points-change redeemed">-{{ $tx->pts_change }}</span>
                </div>
            @empty
                <p>ยังไม่มีประวัติการใช้คะแนน</p>
            @endforelse
        </div>
    </div>

</div>

<script>
    function openTab(evt, tabName) {
        // 1. ซ่อนเนื้อหา .tab-content ทั้งหมด
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // 2. ลบ class 'active' ออกจากปุ่ม .tab-link ทั้งหมด
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // 3. แสดงเนื้อหาแท็บที่เลือก และเพิ่ม class 'active' ให้ปุ่ม
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>

@endsection