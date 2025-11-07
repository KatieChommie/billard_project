@extends('layouts.admin')
@section('title', 'Admin - Manage Tables')

@section('content')
<div class="admin-content-wrapper">
    <h1>จัดการสถานะโต๊ะ</h1>
    
    <div class="widget">
        <h3 style="font-size: 1.2rem; margin-bottom: 15px;">สถานะโต๊ะบิลเลียดทั้งหมด</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f1f1f1;">
                    @php
                    function renderSortableHeader($columnName, $displayName, $currentSort, $currentDir) {
                        $isCurrent = $currentSort == $columnName;
                        $newDirection = ($isCurrent && $currentDir == 'asc') ? 'desc' : 'asc';
                        $arrow = $isCurrent ? ($currentDir == 'asc' ? '&uarr;' : '&darr;') : '';
                        $url = route('admin.tables', ['sort' => $columnName, 'direction' => $newDirection]);
                        echo "<th style=\"padding: 10px; border: 1px solid #ddd; text-align: left;\">
                                <a href=\"{$url}\" class=\"sortable-link\">
                                    {$displayName} <span class=\"arrow\">{$arrow}</span>
                                </a>
                              </th>";
                    }
                    @endphp
                    
                    {!! renderSortableHeader('table_id', 'Table ID', $sortColumn, $sortDirection) !!}
                    {!! renderSortableHeader('tables.branch_id', 'สาขา', $sortColumn, $sortDirection) !!}
                    {!! renderSortableHeader('table_number', 'หมายเลขโต๊ะ', $sortColumn, $sortDirection) !!}
                    {!! renderSortableHeader('table_status', 'สถานะปัจจุบัน', $sortColumn, $sortDirection) !!}
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tables as $table)
                    @php
                        $statusText = ucfirst($table->table_status);
                        $statusColor = 'background-color: #fff; color: #333;';

                        if ($table->table_status === 'available') {
                            $statusColor = 'background-color: #d4edda; color: #155724; font-weight: bold;'; // Green
                        } elseif ($table->table_status === 'unavailable') {
                            $statusColor = 'background-color: #f8d7da; color: #721c24;';
                        }
                    @endphp
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $table->table_id }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $table->branch_name }} (ID: {{ $table->branch_id }})</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $table->table_number }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <span style="display: inline-block; padding: 5px 10px; border-radius: 5px; {{ $statusColor }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <a href="#" style="color: #007bff;">สลับสถานะ</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding: 20px; text-align: center;">ไม่พบข้อมูลโต๊ะบิลเลียด</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection