@extends('layouts.app')
@section('title', 'จองโต๊ะ')

@section('content')
<main class="booking-page-container">
    <div class="booking-card"> 
    
        <div class="booking-header">
            <a href="{{ route('booking.branches') }}" class="back-link" aria-label="ย้อนกลับ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
            
            <h2 class="reservation-title">จองโต๊ะสาขา {{ $branchName ?? 'Select Branch' }}</h2>
            <p class="subtitle">เลือกวัน เวลา และโต๊ะที่ต้องการจอง</p>
        </div>

        <form id="time-selection-form" action="{{ route('reservation.check') }}" method="POST">
            @csrf
            
            <div class="datetime-selection-box"> 
                
                <div class="input-group-booking">
                    <label for="branch_id">Branch*</label>
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
                
                <div class="input-group-booking">
                    <label for="date">Date*</label>
                    <input type="date" id="date" name="date" required 
                        value="{{ old('date', $date ?? date('Y-m-d', strtotime($startTime ?? 'now'))) }}"
                        min="{{ date('Y-m-d') }}">
                </div>
                
                <div class="input-group-booking">
                    <label for="start_time">Start time*</label>
                    <select id="start_time_select" name="start_time" required>
                    </select>
                </div>

                @if (old('start_time'))
                    <input type="hidden" id="old_start_time" value="{{ old('start_time') }}">
                @elseif (isset($startTime))
                    <input type="hidden" id="old_start_time" value="{{ $startTime }}">
                @else
                    <input type="hidden" id="old_start_time" value="">
                @endif

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
                
                <button type="submit" class="filter-btn">Check Availability</button>
            </div>
        </form>

        @if (count($tables) > 0)
            <div class="table-selection-area">
                <h3 class="status-legend">Table Status: 
                    <span class="available-dot"></span> Available | 
                    <span class="reserved-dot"></span> Reserved | 
                    <span class="unavailable-dot"></span> Unavailable
                </h3>
                
                <div class="table-grid-buttons">
                    @foreach ($tables as $table)
                    
                            @php
                                $isAvailable = $table->is_available; 
                                $colorClass = $isAvailable ? 'bg-green-500 hover:bg-green-400' : 'bg-red-600 cursor-not-allowed';
                                $statusText = $isAvailable ? 'ว่าง' : 'จองแล้ว';
                                $isDisabled = !$isAvailable;
                            @endphp

                            <button 
                                type="button" 
                            
                                class="table-btn {{ $colorClass }} {{ $isAvailable ? 'table-selectable' : '' }}" 

                                data-table-id="{{ $table->table_id }}"
                                onclick="toggleTableSelection(this)" 
                                {{ $isDisabled ? 'disabled' : '' }}>
                                <span class="table-name">{{ $table->table_number }}</span>
                                
                                <span class="status-text">{{ $statusText }}</span>
                            </button>
                        @endforeach
                </div>
            </div>
        @endif
        
        <form id="final-booking-form" action="{{ route('reservation.confirm') }}" method="POST">
            @csrf
            <input type="hidden" name="selected_tables" id="selected-table-ids" required>
            <input type="hidden" name="branch_id" value="{{ $branchId }}">
            <input type="hidden" name="start_time" id="hidden-start-time" value="{{ isset($date) && isset($startTime) ? ($date . ' ' . $startTime . ':00') : date('Y-m-d H:i:s', strtotime($startTime ?? '')) }}">
            <input type="hidden" name="end_time" id="hidden-end-time" value="{{ date('Y-m-d H:i:s', strtotime($endTime ?? '')) }}">
            <input type="hidden" name="duration" id="hidden-duration" value="{{ $duration ?? 60 }}">
            @auth
                <input type="text" name="reserve_name" value="{{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}" required class="reserve-name-input" readonly>
            @else
                <input type="text" name="reserve_name" placeholder="Reserve in name" required class="reserve-name-input">
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
    const selectedTables = new Set();
    const pricePerHalfHour = 50;
    
    function calculatePrice(numTables) {
        const durationSelect = document.getElementById('duration_select');
        const durationInMinutes = durationSelect ? parseInt(durationSelect.value, 10) : NaN;

        if (isNaN(durationInMinutes)) {
            const numHalfHours = (60 / 30);
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
    
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('start_time_select');
    const durationSelect = document.getElementById('duration_select');
    const branchSelect = document.getElementById('branch_id_select');

    const displayStartEl = document.getElementById('display-start-time');
    const displayEndEl = document.getElementById('display-end-time');
    const displayDurationEl = document.getElementById('display-duration');

    const hiddenStart = document.getElementById('hidden-start-time');
    const hiddenEnd = document.getElementById('hidden-end-time');
    const hiddenDuration = document.getElementById('hidden-duration');

    let displayStart = '--';
    let displayEnd = '--';
    let durationMinutes = durationSelect ? parseInt(durationSelect.value, 10) : (hiddenDuration ? parseInt(hiddenDuration.value, 10) : 60);

    const selectedBranchId = branchSelect ? branchSelect.value : null;
    let isOverClosingTime = false;
    
    if (displayDurationEl) displayDurationEl.textContent = durationMinutes;

    if (dateInput && timeSelect && dateInput.value && timeSelect.value) {
        
        
        const startString = `${dateInput.value}T${timeSelect.value}:00`;
        const startDate = new Date(startString);
        
        if (!isNaN(startDate)) {
            const endDate = new Date(startDate.getTime() + durationMinutes * 60000);
            
            if (selectedBranchId && branchHoursMap[selectedBranchId]) {
                const branchHours = branchHoursMap[selectedBranchId];
                const closeTimeStr = branchHours.close; // เช่น "23:00" หรือ "02:00"
                const openTimeStr = branchHours.open; 
                
                const closeTimeHours = parseInt(closeTimeStr.substring(0, 2));
                const openTimeHours = parseInt(openTimeStr.substring(0, 2));

                let closeDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate(), 
                                           closeTimeHours, 
                                           parseInt(closeTimeStr.substring(3, 5)), 0, 0);

                if (closeTimeHours < openTimeHours) {
                    if (startDate.getHours() < openTimeHours) {
                    } else {
                        closeDate.setDate(closeDate.getDate() + 1);
                    }
                } else {
                }
                
                if (endDate.getTime() > closeDate.getTime()) {
                     isOverClosingTime = true;
                }
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
    
    const summary = document.getElementById('price-summary');
    const confirmBtn = document.querySelector('.confirm-booking-btn');

    if (isOverClosingTime) {
        displayEnd = 'EXCEEDS CLOSING TIME';
        summary.classList.remove('hidden'); 
        if (confirmBtn) confirmBtn.disabled = true;
        summary.classList.add('text-red-500'); 
    } else {
        summary.classList.remove('text-red-500'); 
        if (numTables > 0) {
            summary.classList.remove('hidden');
            if (confirmBtn) confirmBtn.disabled = false;
        } else {
            summary.classList.add('hidden');
            if (confirmBtn) confirmBtn.disabled = true;
        }
    }
    
    if (displayEndEl) displayEndEl.textContent = displayEnd;
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
    
    const branchHoursMap = @json($branches->mapWithKeys(function ($branch) {
        return [$branch->branch_id => [
            'open' => $branch->time_open,
            'close' => $branch->time_close
        ]];
    }));

    function populateTimeOptions(selectedBranchId, timeSelectElement) {
        
        timeSelectElement.innerHTML = '';

        if (!selectedBranchId || !branchHoursMap[selectedBranchId]) {
            timeSelectElement.add(new Option('-- โปรดเลือกสาขาก่อน --', ''));
            return;
        }

        const hours = branchHoursMap[selectedBranchId];
        
        const openTime = new Date(`1970-01-01T${hours.open}`);
        const closeTime = new Date(`1970-01-01T${hours.close}`);
        
        if (closeTime < openTime) { 
            closeTime.setDate(closeTime.getDate() + 1);
        }

        let currentTime = new Date(openTime);

        const selectedDateInput = document.getElementById('date');
        const selectedDate = selectedDateInput ? selectedDateInput.value : '';
        const todayDate = '{{ date('Y-m-d') }}';
        const isToday = selectedDate === todayDate;
        const now = new Date();

        while (currentTime < closeTime) {
            const hour = String(currentTime.getHours()).padStart(2, '0');
            const minute = String(currentTime.getMinutes()).padStart(2, '0');
            const timeString = `${hour}:${minute}`;

            if (isToday) {
            const slotTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), currentTime.getHours(), currentTime.getMinutes(), 0, 0);

            if (slotTime.getTime() < (now.getTime() + 30 * 60000)) { 
                currentTime.setMinutes(currentTime.getMinutes() + 30);
                continue; 
            }
        }

            const option = new Option(timeString, timeString);
            timeSelectElement.add(option);
            
            currentTime.setMinutes(currentTime.getMinutes() + 30);
        }

        const oldStartTimeInput = document.getElementById('old_start_time');
        let previouslySelectedTime = oldStartTimeInput ? oldStartTimeInput.value : '';

        if (!previouslySelectedTime) {
            previouslySelectedTime = '{{ $startTime ?? '' }}'; 
        }

        if (previouslySelectedTime) {
            timeSelectElement.value = previouslySelectedTime;
        } else {
            timeSelectElement.value = "{{ date('H:00') }}";
        }
    }

    function handleBranchChange() {
        const newBranchId = this.value;
        if (newBranchId) {
            let urlTemplate = "{{ route('booking.table', ['branchId' => 'BRANCH_ID_PLACEHOLDER']) }}";
            let newUrl = urlTemplate.replace('BRANCH_ID_PLACEHOLDER', newBranchId);
            window.location.href = newUrl;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        
        const branchSelect = document.getElementById('branch_id_select');
        const timeSelect = document.getElementById('start_time_select');
        const durationSelect = document.getElementById('duration_select'); 

        if (branchSelect && timeSelect) {
            populateTimeOptions(branchSelect.value, timeSelect);
        }

        if (branchSelect) {
            branchSelect.addEventListener('change', handleBranchChange);
        }

        const dateInput = document.getElementById('date');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                populateTimeOptions(branchSelect.value, timeSelect); 

                document.getElementById('time-selection-form').submit();
            });
        }

        if (durationSelect) {
            durationSelect.addEventListener('change', updateSummary);
        }
        
        updateSummary(); 
    });

</script>
@endsection