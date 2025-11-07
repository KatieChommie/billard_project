@extends('layouts.admin')
@section('title', 'Admin - Manage Menus')

@section('content')
<div class="admin-content-wrapper">
    <h1>จัดการเมนู</h1>
    
    <div class="widget">
        <h3 style="font-size: 1.2rem; margin-bottom: 15px;">รายการอาหารและเครื่องดื่ม</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f1f1f1;">
                    @php
                    function renderSortableHeader($columnName, $displayName, $currentSort, $currentDir) {
                        $isCurrent = $currentSort == $columnName;
                        $newDirection = ($isCurrent && $currentDir == 'asc') ? 'desc' : 'asc';
                        $arrow = $isCurrent ? ($currentDir == 'asc' ? '&uarr;' : '&darr;') : '';
                        $url = route('admin.menus', ['sort' => $columnName, 'direction' => $newDirection]);
                        echo "<th style=\"padding: 10px; border: 1px solid #ddd; text-align: left;\">
                                <a href=\"{$url}\" class=\"sortable-link\">
                                    {$displayName} <span class=\"arrow\">{$arrow}</span>
                                </a>
                              </th>";
                    }
                    @endphp

                    {!! renderSortableHeader('menus.menu_id', 'ID', $sortColumn ?? 'menus.branch_id', $sortDirection ?? 'asc') !!}
                    {!! renderSortableHeader('menus.menu_name', 'ชื่อเมนู', $sortColumn ?? 'menus.branch_id', $sortDirection ?? 'asc') !!}
                    
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">สาขา</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ประเภท</th>

                    {!! renderSortableHeader('menus.price', 'ราคา (บาท)', $sortColumn ?? 'menus.branch_id', $sortDirection ?? 'asc') !!}
                    
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">จำนวนคงเหลือ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menus as $menu)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $menu->menu_id }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $menu->menu_name }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $menu->branch_name }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $menu->menu_type }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ number_format($menu->price, 2) }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ number_format($menu->stock_qty ?? 0) }}</td>
                        
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding: 20px; text-align: center;">ไม่พบข้อมูลเมนู</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection