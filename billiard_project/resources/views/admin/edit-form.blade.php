@extends('layouts.admin')
@section('title', 'Admin - Edit ' . $type)

@section('content')
<div class="admin-content-wrapper">
    <h1>แก้ไขข้อมูล {{ $type }} (ID: {{ $id }})</h1>
    
    <div class="widget">
        @if (session('error'))
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            // กำหนด Route สำหรับการ Submit ฟอร์ม
            $submitRouteName = '';
            $routeParams = ['id' => $id];
            
            if ($type === 'Branch') {
                $submitRouteName = 'admin.branches.update';
                $routeParams = ['branch_id' => $id];
            } elseif ($type === 'Menu') {
                $submitRouteName = 'admin.menus.update';
                // สำหรับ Menu ต้องใช้ branch_id และ menu_id (ซึ่งเราตั้งชื่อให้ $id คือ menu_id)
                $routeParams = ['branch_id' => $branch_id, 'menu_id' => $id]; 
            }
        @endphp

        <form action="{{ route($submitRouteName, $routeParams) }}" method="POST" style="max-width: 600px;">
            @csrf

            @if ($type === 'Menu')
                <p><strong>Branch ID:</strong> {{ $branch_id }}</p>
            @endif

            @foreach ($fields as $field)
                @php
                    $fieldName = str_replace(['_', '-'], ' ', $field); // เปลี่ยน underscore เป็น space
                    $fieldValue = $data->$field ?? old($field);
                    $inputType = 'text';

                    if (str_contains($field, 'time')) {
                        $inputType = 'time';
                        $fieldValue = \Carbon\Carbon::parse($fieldValue)->format('H:i'); // ฟอร์แมตเวลาให้เหมาะกับ input type time
                    } elseif (str_contains($field, 'email')) {
                        $inputType = 'email';
                    } elseif (str_contains($field, 'points') || str_contains($field, 'price') || str_contains($field, 'stock')) {
                        $inputType = 'number';
                    }
                @endphp
                
                <div style="margin-bottom: 15px;">
                    <label for="{{ $field }}" style="display: block; margin-bottom: 5px; font-weight: bold;">{{ ucwords($fieldName) }}:</label>
                    @if ($field === 'menu_type')
                        <select id="{{ $field }}" name="{{ $field }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                            <option value="meal" {{ ($fieldValue === 'meal') ? 'selected' : '' }}>Meal</option>
                            <option value="snack" {{ ($fieldValue === 'snack') ? 'selected' : '' }}>Snack</option>
                            <option value="drink" {{ ($fieldValue === 'drink') ? 'selected' : '' }}>Drink</option>
                        </select>
                    @else
                        <input type="{{ $inputType }}" id="{{ $field }}" name="{{ $field }}" 
                               value="{{ $fieldValue }}" 
                               style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"
                               @if ($inputType === 'number') step="any" @endif
                               required>
                    @endif
                    @error($field)
                        <span style="color: red; font-size: 0.8rem;">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach

            <button type="submit" style="background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">
                บันทึกการแก้ไข
            </button>
            <a href="{{ url()->previous() }}" style="background-color: #6c757d; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; margin-left: 10px;">
                ยกเลิก
            </a>
        </form>
    </div>
</div>
@endsection