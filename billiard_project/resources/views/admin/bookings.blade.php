@extends('layouts.admin')
@section('title', 'Admin - Manage Bookings')

@section('content')
<div class="admin-content-wrapper">
    <h1>ประวัติการจองทั้งหมด</h1>
    
    <div class="widget">
        <h3 style="font-size: 1.2rem; margin-bottom: 15px;">ประวัติการทำรายการ</h3>
        
            @php
        function renderSortableHeader($columnName, $displayName, $currentSort, $currentDir, $routeName) {
            $isCurrent = $currentSort == $columnName;
            $newDirection = ($isCurrent && $currentDir == 'asc') ? 'desc' : 'asc';
            $arrow = $isCurrent ? ($currentDir == 'asc' ? '&uarr;' : '&darr;') : '';
            $url = route($routeName, array_merge(request()->query(), ['sort' => $columnName, 'direction' => $newDirection]));
            echo "<th style=\"padding: 10px; border: 1px solid #ddd; text-align: left;\">
                    <a href=\"{$url}\" class=\"sortable-link\">{$displayName} <span class=\"arrow\">{$arrow}</span></a>
                  </th>";
        }
        @endphp
        
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f1f1f1;">
                    {!! renderSortableHeader('orders.order_id', 'Order ID', $sortColumn, $sortDirection, 'admin.bookings') !!}
                    {!! renderSortableHeader('first_name', 'ลูกค้า', $sortColumn, $sortDirection, 'admin.bookings') !!}
                    {!! renderSortableHeader('start_time', 'เวลาเริ่ม', $sortColumn, $sortDirection, 'admin.bookings') !!}
                    {!! renderSortableHeader('final_amount', 'ยอดเงิน', $sortColumn, $sortDirection, 'admin.bookings') !!}
                    {!! renderSortableHeader('order_status', 'สถานะ Order', $sortColumn, $sortDirection, 'admin.bookings') !!}
                    {!! renderSortableHeader('pay_status', 'สถานะ Payment', $sortColumn, $sortDirection, 'admin.bookings') !!}
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">
                        จัดการ (Actions)
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; border: 1px solid #ddd;">#{{ $booking->order_id }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $booking->first_name }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($booking->start_time)->format('d/m/Y H:i') }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ number_format($booking->final_amount, 2) }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $booking->order_status }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $booking->pay_status }} ({{ $booking->pay_method ?? 'N/A' }})</td>
                        
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            @if ($booking->order_status == 'confirmed')
                                <form action="{{ route('admin.order.complete', $booking->order_id) }}" method="POST"
                                      onsubmit="return confirm('ยืนยันว่าลูกค้ารายนี้เล่นเสร็จสิ้นแล้ว?');">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $booking->order_id }}">
                                    <button type="submit" class="btn-mark-completed" 
                                            style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 5px; border: none; cursor: pointer;">
                                        Mark as Completed
                                    </button>
                                </form>
                                
                            @elseif ($booking->order_status == 'completed')
                                <span class="status-completed" style="background-color: #28a746bb; color: white; padding: 5px 10px; border-radius: 5px; border: none;">เสร็จสิ้นแล้ว</span>
                            @else
                                <span class="status-other" style="color: #888;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding: 20px; text-align: center;">ไม่พบข้อมูลการจอง</td></tr>
                @endforelse
            </tbody>
        </table>
            
        <div style="margin-top: 20px;">
            {{ $bookings->appends(['sort' => $sortColumn, 'direction' => $sortDirection])->links() }}
        </div>
    </div>
</div>
@endsection