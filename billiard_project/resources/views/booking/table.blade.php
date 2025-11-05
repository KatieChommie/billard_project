@extends('layouts.app')
@section('title', 'จองโต๊ะ')

@section('content')
<main class="booking-page-container">
    {{-- โค้ด CSS/UI ส่วนใหญ่ถูกกำหนดใน app.css --}}
    <div class="booking-card"> 
        
        {{-- 1. Header และ Title --}}
        <div class="booking-header">
            <a href="{{ route('booking.branches') }}" class="back-link" aria-label="ย้อนกลับ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
            
            {{-- ใช้ชื่อสาขาที่ส่งมาจาก Controller (หรือค่า Default) --}}
            <h2 class="reservation-title">จองโต๊ะสาขา {{ $branchName ?? 'Select Branch' }}</h2>
            <p class="subtitle">เลือกวัน เวลา และโต๊ะที่ต้องการจอง</p>
        </div>

        {{-- 2. ฟอร์มสำหรับเลือก/กรอง วันที่และเวลา (POST ไปที่ ReservationController@checkTableAvailability) --}}
        <form id="time-selection-form" action="{{ route('reservation.check') }}" method="POST">
            @csrf
            
            <div class="datetime-selection-box"> 
                
                {{-- 2.1 Dropdown สาขา --}}
                <div class="input-group-booking">
                    <label for="branch_id">Branch*</label>
                    {{-- Branch Dropdown (ต้องมี onchange เพื่อให้ฟอร์มทำงานซ้ำเมื่อเปลี่ยนสาขา) --}}
                    <select id="branch_id_select" name="branch_id" required onchange="this.form.submit()">
                        <option value="">-- เลือกสาขา --</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->branch_id }}" 
                                {{ ($branch->branch_id == ($branchId ?? 0)) ? 'selected' : '' }}>
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- 2.2 Date Picker --}}
                <div class="input-group-booking">
                    <label for="date">Date*</label>
                    {{-- ใช้ค่า old() หรือค่าจาก Controller --}}
              <input type="date" id="date" name="date" required 
                  value="{{ old('date', $date ?? date('Y-m-d', strtotime($startTime ?? 'now'))) }}">
                </div>
                
                {{-- 2.3 Start Time Selector --}}
                <div class="input-group-booking">
                    <label for="start_time">Start time*</label>
                    <select id="start_time_select" name="start_time" required>
                        @for ($h = 9; $h < 24; $h++)
                            @foreach ([0, 30] as $m)
                                @php $time = sprintf('%02d:%02d', $h, $m); @endphp
                                <option value="{{ $time }}" 
                                    {{ old('start_time', date('H:i')) == $time ? 'selected' : '' }}>
                                    {{ $time }}
                                </option>
                            @endforeach
                        @endfor
                    </select>
                </div>

                {{-- 2.4 Duration Selector --}}
                <div class="input-group-booking">
                    <label for="duration">Duration (mins)*</label>
                    <select id="duration_select" name="duration" required>
                        @foreach ([30, 60, 90, 120, 150, 180] as $durationOption)
                            <option value="{{ $durationOption }}" 
                                {{ old('duration', $duration ?? 60) == $durationOption ? 'selected' : '' }}>
                                {{ $durationOption }} minutes
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- ปุ่มค้นหา/กรอง --}}
                <button type="submit" class="filter-btn">Check Availability</button>
            </div>
        </form>

        {{-- 3. ส่วนแสดงผลโต๊ะ (จะแสดงเมื่อมีการค้นหาและ $tables ไม่ว่าง) --}}
        @if (count($tables) > 0)
            <div class="table-selection-area">
                <h3 class="status-legend">Table Status: 
                    <span class="available-dot"></span> Available | 
                    <span class="reserved-dot"></span> Reserved | 
                    <span class="unavailable-dot"></span> Unavailable
                </h3>
                
                <div class="table-grid-buttons">
                    @foreach ($tables as $table)
                        
                            {{-- (1. เราจะสร้างตัวแปร Logic ที่นี่) --}}
                            @php
                                $isAvailable = $table->is_available; // (ตัวแปรใหม่จาก Controller)
                                
                                // (สร้างตัวแปรสีและข้อความ)
                                $colorClass = $isAvailable ? 'bg-green-500 hover:bg-green-400' : 'bg-red-600 cursor-not-allowed';
                                $statusText = $isAvailable ? 'ว่าง' : 'จองแล้ว';
                                $isDisabled = !$isAvailable; // (ถ้าไม่ว่าง = true)
                            @endphp

                            {{-- (2. นี่คือ <button> ดีไซน์เดิมของคุณ) --}}
                            <button 
                                type="button" 
                            
                                {{-- (3. ใช้ตัวแปรใหม่ของเรา) --}}
                                class="table-btn {{ $colorClass }} {{ $isAvailable ? 'table-selectable' : '' }}" 

                                data-table-id="{{ $table->table_id }}"
                                onclick="toggleTableSelection(this)" 
                            
                                {{-- (4. ใช้ตัวแปรใหม่ของเรา) --}}
                                {{ $isDisabled ? 'disabled' : '' }}>
                                
                                <span class="table-name">{{ $table->table_number }}</span>
                                
                                {{-- (5. ใช้ตัวแปรใหม่ของเรา) --}}
                                <span class="status-text">{{ $statusText }}</span>
                            </button>
                        @endforeach
                </div>
            </div>
        @endif
        
        {{-- 4. ฟอร์มยืนยันการจอง และสรุปราคา (Hidden by Default) --}}
        <form id="final-booking-form" action="{{ route('reservation.confirm') }}" method="POST">
            @csrf
            
            {{-- **Hidden Inputs สำคัญ** --}}
            <input type="hidden" name="selected_tables" id="selected-table-ids" required>
            <input type="hidden" name="branch_id" value="{{ $branchId }}">
            <input type="hidden" name="start_time" id="hidden-start-time" value="{{ isset($date) && isset($startTime) ? ($date . ' ' . $startTime . ':00') : date('Y-m-d H:i:s', strtotime($startTime ?? '')) }}">
            <input type="hidden" name="end_time" id="hidden-end-time" value="{{ date('Y-m-d H:i:s', strtotime($endTime ?? '')) }}">
            <input type="hidden" name="duration" id="hidden-duration" value="{{ $duration ?? 60 }}">

            @auth
                <input type="text" name="reserve_name" value="{{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}" required class="reserve-name-input" readonly>
            @else
                <input type="text" name="reserve_name" placeholder="Reserve in name" required class="reserve-name-input"> {{-- ชื่อผู้จอง --}}
            @endauth

            <div id="price-summary" class="price-summary hidden">
                <p>Total Tables: <span id="selected-table-count">0</span></p>
                <p>Duration: <span id="display-duration">{{ $duration ?? 60 }}</span> mins</p>
                <p>Start: <span id="display-start-time">--</span></p>
                <p>End: <span id="display-end-time">--</span></p>
                <p>Total Price: <span id="final-price">0.00</span> THB</p>
                @auth
                    <button type="submit" class="confirm-booking-btn" disabled>Confirm Booking</button>
                @else
                    <a href="{{ route('login') }}" class="confirm-booking-btn">Login to Reserve</a>
                @endauth
            </div>
        </form>

    </div>
</main>
<script>
    // --- 1. โค้ดเดิมของคุณ (สำหรับการคลิกและคำนวณราคา) ---
    const selectedTables = new Set();
    const pricePerHalfHour = 50;
    
    
    function calculatePrice(numTables) {
        // (แก้ไข) เราต้องหา dropdown นี้ "ข้างใน" ฟังก์ชัน
        // เพราะมันอาจจะยังโหลดไม่เสร็จตอนเริ่ม
        const durationSelect = document.getElementById('duration_select');
        const durationInMinutes = durationSelect ? parseInt(durationSelect.value, 10) : NaN;

        if (isNaN(durationInMinutes)) {
            const numHalfHours = (60 / 30); // 60 นาที default
            return numTables * pricePerHalfHour * numHalfHours;
        }

        const numHalfHours = durationInMinutes / 30;
        return numTables * pricePerHalfHour * numHalfHours;
    }

    function updateSummary() {
        const numTables = selectedTables.size;
        const totalPrice = calculatePrice(numTables);
        
        document.getElementById('selected-table-ids').value = Array.from(selectedTables).join(',');
        document.getElementById('selected-table-count').textContent = numTables;
        document.getElementById('final-price').textContent = totalPrice.toFixed(2);
        
        // Update start / end display + hidden inputs
        const dateInput = document.getElementById('date');
        const timeSelect = document.getElementById('start_time_select');
        const durationSelect = document.getElementById('duration_select');

        const displayStartEl = document.getElementById('display-start-time');
        const displayEndEl = document.getElementById('display-end-time');
        const displayDurationEl = document.getElementById('display-duration');

        const hiddenStart = document.getElementById('hidden-start-time');
        const hiddenEnd = document.getElementById('hidden-end-time');
        const hiddenDuration = document.getElementById('hidden-duration');

        let displayStart = '--';
        let displayEnd = '--';
        let durationMinutes = durationSelect ? parseInt(durationSelect.value, 10) : (hiddenDuration ? parseInt(hiddenDuration.value, 10) : 60);

        if (displayDurationEl) displayDurationEl.textContent = durationMinutes;

        if (dateInput && timeSelect && dateInput.value && timeSelect.value) {
            // Build local date-time from date input and time select
            const startString = `${dateInput.value}T${timeSelect.value}:00`;
            const startDate = new Date(startString);
            if (!isNaN(startDate)) {
                const endDate = new Date(startDate.getTime() + durationMinutes * 60000);

                // Clamp end to same calendar day as start (set to 23:59 if overflow)
                if (endDate.getDate() !== startDate.getDate()) {
                    endDate.setHours(23, 59, 0, 0);
                }

                const pad = (n) => String(n).padStart(2, '0');
                const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;

                displayStart = fmt(startDate);
                displayEnd = fmt(endDate);

                if (hiddenStart) hiddenStart.value = `${displayStart}:00`;
                if (hiddenEnd) hiddenEnd.value = `${displayEnd}:00`;
                if (hiddenDuration) hiddenDuration.value = durationMinutes;
            }
        }

        if (displayStartEl) displayStartEl.textContent = displayStart;
        if (displayEndEl) displayEndEl.textContent = displayEnd;

        const summary = document.getElementById('price-summary');
        const confirmBtn = document.querySelector('.confirm-booking-btn');

        if (numTables > 0) {
            summary.classList.remove('hidden');
            if (confirmBtn) confirmBtn.disabled = false;
        } else {
            summary.classList.add('hidden');
            if (confirmBtn) confirmBtn.disabled = true;
        }
    }

    function toggleTableSelection(button) {
        const tableId = button.getAttribute('data-table-id');
        
        if (button.classList.contains('selected')) {
            button.classList.remove('selected');
            selectedTables.delete(tableId);
        } else {
            button.classList.add('selected');
            selectedTables.add(tableId);
        }
        
        updateSummary();
    }
    
    
    // --- 2. โค้ดใหม่ (สำหรับสร้างตัวเลือกเวลา และ เปลี่ยนหน้าสาขา) ---

    // 2a. แปลงข้อมูล PHP (วางไว้ข้างนอกได้)
    const branchHoursMap = @json($branches->mapWithKeys(function ($branch) {
        return [$branch->branch_id => [
            'open' => $branch->time_open,
            'close' => $branch->time_close
        ]];
    }));

    // 2c. ฟังก์ชันสร้างตัวเลือกเวลา
    function populateTimeOptions(selectedBranchId, timeSelectElement) {
        
        timeSelectElement.innerHTML = ''; // ล้างตัวเลือกเก่า

        if (!selectedBranchId || !branchHoursMap[selectedBranchId]) {
            timeSelectElement.add(new Option('-- โปรดเลือกสาขาก่อน --', ''));
            return;
        }

        const hours = branchHoursMap[selectedBranchId];
        
        const openTime = new Date(`1970-01-01T${hours.open}`);
        const closeTime = new Date(`1970-01-01T${hours.close}`);
        
        if (closeTime < openTime) { // จัดการกรณีปิดข้ามวัน
            closeTime.setDate(closeTime.getDate() + 1);
        }

        let currentTime = new Date(openTime);

        while (currentTime < closeTime) {
            const hour = String(currentTime.getHours()).padStart(2, '0');
            const minute = String(currentTime.getMinutes()).padStart(2, '0');
            const timeString = `${hour}:${minute}`;

            const option = new Option(timeString, timeString);
            timeSelectElement.add(option);
            
            currentTime.setMinutes(currentTime.getMinutes() + 30);
        }

        // ตั้งค่าเวลาที่ผู้ใช้เลือกไว้ (ถ้ามี)
        const previouslySelectedTime = '{{ $startTime ?? '' }}';
        if (previouslySelectedTime) {
            timeSelectElement.value = previouslySelectedTime;
        }
    }

    // 2d. ฟังก์ชันย้ายหน้า
    function handleBranchChange() {
        const newBranchId = this.value; // 'this' คือ branchSelect
        if (newBranchId) {
            let urlTemplate = "{{ route('booking.table', ['branchId' => 'BRANCH_ID_PLACEHOLDER']) }}";
            let newUrl = urlTemplate.replace('BRANCH_ID_PLACEHOLDER', newBranchId);
            window.location.href = newUrl;
        }
    }

    // 3. (แก้ไข) Event Listener หลัก
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- (ย้าย Const Declarations มาไว้ "ข้างใน" DOMContentLoaded) ---
        const branchSelect = document.getElementById('branch_id_select');
        const timeSelect = document.getElementById('start_time_select');
        const durationSelect = document.getElementById('duration_select'); 
        // ----------------------------------------------------------------

        // 3a. สั่งให้ "สร้างตัวเลือกเวลา" ทันทีที่โหลดหน้า
        if (branchSelect && timeSelect) {
            populateTimeOptions(branchSelect.value, timeSelect);
        }

        // 3b. ดักฟังการ "เปลี่ยนสาขา"
        if (branchSelect) {
            branchSelect.addEventListener('change', handleBranchChange);
        }

        // 3c. ดักฟังการ "เปลี่ยนระยะเวลา" (เพื่อคำนวณราคาใหม่)
        if (durationSelect) {
            durationSelect.addEventListener('change', updateSummary);
        }
        
        // 3d. อัปเดตสรุปราคาครั้งแรก (เพื่อให้มัน "ซ่อน" แถบราคา)
        updateSummary(); 
    });

</script>
@endsection