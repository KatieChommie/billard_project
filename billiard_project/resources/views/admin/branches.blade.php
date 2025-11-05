@extends('layouts.admin')
@section('title', 'Admin - Manage Branches')

@section('content')
<div class="admin-content-wrapper">
    <h1>จัดการสาขา</h1>
    
    
    <div class="widget">
        <h3 style="font-size: 1.2rem; margin-bottom: 15px;">รายชื่อสาขาทั้งหมด</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                @php
                function renderSortableHeader($columnName, $displayName, $currentSort, $currentDir) {
                    $isCurrent = $currentSort == $columnName;
                    // (Logic สลับทิศทาง)
                    $newDirection = ($isCurrent && $currentDir == 'asc') ? 'desc' : 'asc';
                    // (Logic แสดงลูกศร)
                    $arrow = $isCurrent ? ($currentDir == 'asc' ? '&uarr;' : '&darr;') : '';

                    $url = route('admin.branches', ['sort' => $columnName, 'direction' => $newDirection]);
                    
                    echo "<th style=\"padding: 10px; border: 1px solid #ddd; text-align: left;\">
                            <a href=\"{$url}\" class=\"sortable-link\">
                                {$displayName} <span class=\"arrow\">{$arrow}</span>
                            </a>
                          </th>";
                }
                @endphp

                {!! renderSortableHeader('branch_id', 'ID', $sortColumn, $sortDirection) !!}
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ชื่อสาขา</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ที่อยู่</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">เบอร์โทร</th>
                {!! renderSortableHeader('time_open', 'เวลาเปิด - ปิด', $sortColumn, $sortDirection) !!}
                
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Action</th>
            </tr>
            </thead>
            <tbody>
                @forelse ($branches as $branch)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $branch->branch_id }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $branch->branch_name }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $branch->branch_info }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $branch->branch_address }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $branch->branch_phone }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $branch->time_open }} - {{ $branch->time_close }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><a href="#" style="color: #007bff;">แก้ไข</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="padding: 20px; text-align: center;">ไม่พบข้อมูลสาขา</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection