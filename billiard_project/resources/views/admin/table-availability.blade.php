@extends('layouts.admin')
@section('title', 'Admin - Table Availability Check')

@section('content')
<div class="admin-content-wrapper">
    <h1>ตรวจสอบสถานะโต๊ะตามเวลา</h1>
    <p>ดูสถานะการจองโต๊ะของแต่ละสาขาในช่วงเวลาที่กำหนด (เช็กการจอง 1 ชั่วโมง นับจากเวลาเริ่มต้น)</p>
    
    <div class="widget" style="margin-bottom: 20px; padding: 20px;">
        <h3 style="font-size: 1.1rem; margin-bottom: 15px;">เลือกสาขา, วันที่ และเวลา</h3>
        
        <form action="{{ route('admin.tables.check') }}" method="POST" style="display: flex; gap: 15px; align-items: flex-end;">
            @csrf
            <div style="flex-grow: 1;">
                <label for="branch_id" style="display: block; font-weight: bold; margin-bottom: 5px;">สาขา:</label>
                <select name="branch_id" id="branch_id" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%;" required>
                    <option value="">-- เลือกสาขา --</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->branch_id }}" {{ (string)$selectedBranchId === (string)$branch->branch_id ? 'selected' : '' }}>
                            {{ $branch->branch_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="flex-grow: 1;">
                <label for="date" style="display: block; font-weight: bold; margin-bottom: 5px;">วันที่:</label>
                <input type="date" name="date" id="date" value="{{ $selectedDate }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%;" required>
            </div>

            <div style="flex-grow: 1;">
                <label for="time" style="display: block; font-weight: bold; margin-bottom: 5px;">เวลาเริ่มต้น (H:i):</label>
                <input type="time" name="time" id="time" value="{{ $selectedTime }}" step="3600" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%;" required>
            </div>

            <button type="submit" style="background-color: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; height: 38px;">
                ตรวจสอบ
            </button>
        </form>
    </div>

    @if ($selectedBranchId && $availableTables->isNotEmpty())
        <div class="widget">
            <h2 style="font-size: 1.5rem; margin-bottom: 20px;">
                สถานะโต๊ะที่สาขา: {{ $selectedBranch->branch_name }} (วันที่ {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }} เวลา {{ $selectedTime }} น.)
            </h2>
            
            <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                @foreach ($availableTables->sortBy('table_number') as $table)
                    @php
                        $status = $table->is_booked ? 'ไม่ว่าง (ติดจอง)' : 'ว่าง (Available)';
                        $statusColor = $table->is_booked ? '#f8d7da' : '#d4edda';
                        $statusTextColor = $table->is_booked ? '#721c24' : '#155724';
                    @endphp

                    <div style="width: 280px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; {{ 'background-color: ' . $statusColor . '; color: ' . $statusTextColor . '; box-shadow: 0 2px 4px rgba(0,0,0,0.05);' }}">
                        <strong style="font-size: 1.2rem;">โต๊ะ #{{ $table->table_number }}</strong>
                        <p style="font-weight: bold; margin: 5px 0 10px 0;">สถานะ: {{ $status }}</p>
                        
                        @if ($table->is_booked)
                            <small>
                                จองโดย: {{ $table->details->user_name }}<br>
                                เวลาจอง: {{ $table->details->start_time }} - {{ $table->details->end_time }}<br>
                                Reservation ID: {{ $table->details->reserve_id }}
                            </small>
                        @else
                            <small>พร้อมให้บริการตลอดช่วงเวลาที่ตรวจสอบ</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @elseif ($selectedBranchId)
        <div class="alert alert-info" style="background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px;">
            ไม่พบข้อมูลการจองในช่วงเวลาที่เลือก
        </div>
    @endif

</div>
@endsection