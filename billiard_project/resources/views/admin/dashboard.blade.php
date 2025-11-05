@extends('layouts.admin') 

@section('title', 'Admin Dashboard')

@section('content')
    <h1>ภาพรวม (Dashboard)</h1>
    
    <div class="widget-grid">
        <div class="widget">
            <h3>Total Users</h3>
            <p>{{ $userCount }}</p>
        </div>
        <div class="widget">
            <h3>Today Bookings</h3>
            <p>{{ $todayBookings }}</p>
        </div>
        <div class="widget">
            <h3>Total Reviews</h3>
            <p>{{ $reviewCount }}</p>
        </div>
    </div>
    
    {{-- (แสดงข้อความ Success/Error เมื่อกดปุ่ม) --}}
    @if (session('success'))
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif


    <h2 class="admin-section-title" style="margin-top: 2rem;">รายการจองที่รอเสร็จสิ้น (Confirmed)</h2>

    <div class="widget">
        
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #f1f1f1;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Order ID</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ชื่อลูกค้า</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">นามสกุลลูกค้า</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">เวลาเริ่ม</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ยอดเงิน</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">วิธีจ่าย</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">สถานะ</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ordersToComplete as $order)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; border: 1px solid #ddd;">#{{ $order->order_id }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $order->first_name }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $order->last_name }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($order->start_time)->format('d/m/Y H:i') }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ number_format($order->final_amount, 2) }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $order->pay_method }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd; color: green; font-weight: bold;">
                            {{ $order->order_status }}
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            
                            {{-- (นี่คือปุ่มที่เรียกใช้ Logic 'markAsCompleted') --}}
                            <form action="{{ route('admin.order.complete') }}" method="POST" 
                                  onsubmit="return confirm('ยืนยันว่าลูกค้าเล่นเสร็จสิ้นแล้วใช่หรือไม่? (Order #{{ $order->order_id }})');">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order->order_id }}">
                                <button type="submit" 
                                        style="background-color: #007bff; color: white; padding: 8px 12px; border-radius: 5px; border: none; cursor: pointer;">
                                    Mark Completed
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 20px; text-align: center; color: #888; border: 1px solid #ddd;">
                            ไม่พบรายการจองที่รอการเสร็จสิ้น (Confirmed)
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
@endsection
