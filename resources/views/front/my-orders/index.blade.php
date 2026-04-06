@extends('layouts.app')

@section('content')
@php
    function orderStatusClass($status) {
        return match($status) {
            'pending' => 'status-pill status-pending',
            'confirmed' => 'status-pill status-confirmed',
            'preparing' => 'status-pill status-preparing',
            'out_for_delivery' => 'status-pill status-delivery',
            'ready_for_pickup' => 'status-pill status-confirmed',
            'delivered' => 'status-pill status-delivered',
            default => 'status-pill status-cancelled',
        };
    }

    function orderStatusLabel($status) {
        return match($status) {
            'pending' => 'قيد الانتظار',
            'confirmed' => 'تم التأكيد',
            'preparing' => 'جاري التحضير',
            'out_for_delivery' => 'خرج للتوصيل',
            'ready_for_pickup' => 'جاهز للاستلام',
            'delivered' => 'تم التوصيل',
            default => 'ملغي',
        };
    }
@endphp

<h2 class="section-title">{{ __('site.my_orders') }}</h2>

<div class="card-shell p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ number_format($order->total, 2) }} ج.م</td>
                        <td><span class="{{ orderStatusClass($order->status) }}">{{ orderStatusLabel($order->status) }}</span></td>
                        <td>{{ $order->created_at->format('Y-m-d h:i A') }}</td>
                        <td>
                            <a href="{{ route('my.orders.show', $order->id) }}" class="btn btn-soft">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">لا توجد طلبات حتى الآن</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</div>
@endsection