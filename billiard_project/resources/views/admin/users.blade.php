@extends('layouts.admin')
@section('title', 'Admin - Manage Users')

@section('content')
<div class="admin-content-wrapper">
    <h1>จัดการผู้ใช้งาน</h1>
    
    <div class="widget">
        <h3 style="font-size: 1.2rem; margin-bottom: 15px;">รายชื่อผู้ใช้ทั้งหมด (Customers)</h3>

        {{-- (แสดง Success/Error Messages ถ้ามี) --}}
        @if (session('success'))
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
        @endif
        
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #f1f1f1;">
                    @php
                function renderSortableHeader($columnName, $displayName, $currentSort, $currentDir) {
                    $isCurrent = $currentSort == $columnName;
                    // (Logic สลับทิศทาง)
                    $newDirection = ($isCurrent && $currentDir == 'asc') ? 'desc' : 'asc';
                    // (Logic แสดงลูกศร)
                    $arrow = $isCurrent ? ($currentDir == 'asc' ? '&uarr;' : '&darr;') : '';

                    // (สร้าง URL ใหม่พร้อมพารามิเตอร์ sort/direction)
                    $url = route('admin.users', ['sort' => $columnName, 'direction' => $newDirection]);
                    
                    echo "<th style=\"padding: 10px; border: 1px solid #ddd; text-align: left;\">
                            <a href=\"{$url}\" class=\"sortable-link\">
                                {$displayName} <span class=\"arrow\">{$arrow}</span>
                            </a>
                          </th>";
                }
                @endphp

                {{-- (เรียกใช้ Helper เพื่อสร้างหัวตาราง) --}}
                {!! renderSortableHeader('user_id', 'ID', $sortColumn, $sortDirection) !!}
                {!! renderSortableHeader('first_name', 'ชื่อ - นามสกุล', $sortColumn, $sortDirection) !!}
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Email</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">เบอร์โทร</th>
                {!! renderSortableHeader('loyalty_points', 'แต้มสะสม', $sortColumn, $sortDirection) !!}
                {!! renderSortableHeader('created_at', 'วันที่สมัคร', $sortColumn, $sortDirection) !!}
                
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Action</th>

                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $user->user_id }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $user->first_name }} {{ $user->last_name }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $user->email }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $user->phone_number }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $user->loyalty_points }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            {{-- **อัปเดต: แทนที่ลิงก์แก้ไขด้วยปุ่มลบ** --}}
                            <form action="{{ route('admin.users.delete', ['user_id' => $user->user_id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('คุณต้องการลบผู้ใช้งาน #{{ $user->user_id }} ใช่หรือไม่? การกระทำนี้ไม่สามารถยกเลิกได้');">
                                @csrf
                                <button type="submit" style="color: #dc3545; text-decoration: underline; background: none; border: none; padding: 0; cursor: pointer;">ลบผู้ใช้</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 20px; text-align: center; color: #888; border: 1px solid #ddd;">
                            ไม่พบข้อมูลผู้ใช้งาน
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>
@endsection