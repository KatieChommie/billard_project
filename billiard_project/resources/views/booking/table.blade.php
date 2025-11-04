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
            <span>กลับไปยังหน้าสาขา</span>
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
                    <select id="branch_id" name="branch_id" required onchange="this.form.submit()">
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
                           value="{{ old('date', date('Y-m-d', strtotime($startTime ?? 'now'))) }}">
                </div>
                
                {{-- 2.3 Start Time Selector --}}
                <div class="input-group-booking">
                    <label for="start_time">Start time*</label>
                    <select id="start_time" name="start_time" required>
                        {{-- 💡 สร้าง Options ทีละ 30 นาที --}}
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
                    <select id="duration" name="duration" required>
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
                        <button type="button" 
                                class="table-btn status-{{ strtolower($table->status_color) }}" 
                                data-table-id="{{ $table->table_id }}"
                                data-table-number="{{ $table->table_number }}"
                                data-price="{{ 50 * ($duration / 30) }}" {{-- ราคาต่อโต๊ะ --}}
                                onclick="toggleTableSelection(this, {{ $duration }})" 
                                {{ $table->status_for_user !== 'Available' ? 'disabled' : '' }}>
                            table {{ $table->table_number }}
                            @if ($table->status_for_user !== 'Available')
                                <span class="status-text">{{ strtolower($table->status_for_user) }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
        
        {{-- 4. ฟอร์มยืนยันการจอง และสรุปราคา (Hidden by Default) --}}
        <form id="final-booking-form" action="{{ route('reservation.confirm') }}" method="POST">
            @csrf
            
            {{-- **Hidden Inputs สำคัญ** --}}
            <input type="hidden" name="selected_table_ids" id="selected-table-ids" required> 
            <input type="hidden" name="branch_id" value="{{ $branchId }}">
            <input type="hidden" name="start_time" value="{{ date('Y-m-d H:i:s', strtotime($startTime ?? '')) }}">
            <input type="hidden" name="end_time" value="{{ date('Y-m-d H:i:s', strtotime($endTime ?? '')) }}">
            <input type="hidden" name="duration" value="{{ $duration ?? 60 }}">
            
            <input type="text" name="reserve_name" placeholder="Reserve in name" required class="reserve-name-input"> {{-- ชื่อผู้จอง --}}

            <div id="price-summary" class="price-summary hidden">
                <p>Total Tables: <span id="selected-table-count">0</span></p>
                <p>Duration: {{ $duration ?? 60 }} mins</p>
                <p>Total Price: <span id="final-price">0.00</span> THB</p>
                <button type="submit" class="confirm-booking-btn" disabled>Confirm Booking</button>
            </div>
        </form>

    </div>
</main>
<script>
    let selectedTables = new Set(); 
    const pricePer30Mins = 50;
    const durationMins = {{ $duration ?? 60 }};

    // Logic คำนวณราคา: 50 บาท ต่อ 30 นาที ต่อโต๊ะ
    function calculatePrice(numTables) {
        const blocks = durationMins / 30;
        return (blocks * pricePer30Mins) * numTables; 
    }

    // อัปเดตส่วนสรุปราคาและปุ่มยืนยัน
    function updateSummary() {
        const numTables = selectedTables.size;
        const totalPrice = calculatePrice(numTables);
        
        // อัปเดต Hidden Field สำหรับ Controller
        document.getElementById('selected-table-ids').value = Array.from(selectedTables).join(',');

        // อัปเดต UI
        document.getElementById('selected-table-count').textContent = numTables;
        document.getElementById('final-price').textContent = totalPrice.toFixed(2);
        
        const summary = document.getElementById('price-summary');
        const confirmBtn = document.querySelector('.confirm-booking-btn');

        if (numTables > 0) {
            summary.classList.remove('hidden');
            confirmBtn.disabled = false;
        } else {
            summary.classList.add('hidden');
            confirmBtn.disabled = true;
        }
    }

    // ฟังก์ชันที่รันเมื่อผู้ใช้คลิกโต๊ะ
    function toggleTableSelection(button) {
        const tableId = button.getAttribute('data-table-id');
        
        // 1. จัดการคลาส 'selected'
        if (button.classList.contains('selected')) {
            button.classList.remove('selected');
            selectedTables.delete(tableId);
        } else {
            button.classList.add('selected');
            selectedTables.add(tableId);
        }
        
        // 2. อัปเดตสรุปราคา
        updateSummary();
    }
    
    // ตั้งค่าเริ่มต้นเมื่อโหลดหน้า
    document.addEventListener('DOMContentLoaded', updateSummary); 
</script>
@endsection