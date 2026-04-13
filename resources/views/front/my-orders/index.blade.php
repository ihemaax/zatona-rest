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

<style>
    .orders-mobile-list {
        display: none;
    }

    .order-mobile-card {
        border: 1px solid #efe7da;
        border-radius: 16px;
        padding: 14px;
        background: #fffdfa;
        box-shadow: 0 8px 20px rgba(47, 36, 20, 0.05);
    }

    .order-mobile-card__head {
        display: flex;
        align-items: start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .order-mobile-card__meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px 12px;
        margin-bottom: 12px;
    }

    .order-mobile-card__meta-label {
        color: #7a7063;
        font-size: .74rem;
        font-weight: 800;
        margin-bottom: 2px;
    }

    .order-mobile-card__meta-value {
        color: #2b241a;
        font-size: .84rem;
        font-weight: 900;
        word-break: break-word;
    }

    @media (max-width: 767.98px) {
        .orders-desktop-table {
            display: none;
        }

        .orders-mobile-list {
            display: grid;
            gap: 10px;
        }
    }
</style>

<div class="card-shell p-4">
    <div class="table-responsive orders-desktop-table">
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

    <div class="orders-mobile-list">
        @forelse($orders as $order)
            <article class="order-mobile-card">
                <div class="order-mobile-card__head">
                    <div class="fw-bold">#{{ $order->id }}</div>
                    <span class="{{ orderStatusClass($order->status) }}">{{ orderStatusLabel($order->status) }}</span>
                </div>

                <div class="order-mobile-card__meta">
                    <div>
                        <div class="order-mobile-card__meta-label">الإجمالي</div>
                        <div class="order-mobile-card__meta-value">{{ number_format($order->total, 2) }} ج.م</div>
                    </div>
                    <div>
                        <div class="order-mobile-card__meta-label">نوع الطلب</div>
                        <div class="order-mobile-card__meta-value">{{ $order->order_type === 'pickup' ? 'استلام من الفرع' : 'توصيل' }}</div>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <div class="order-mobile-card__meta-label">التاريخ</div>
                        <div class="order-mobile-card__meta-value">{{ $order->created_at->format('Y-m-d h:i A') }}</div>
                    </div>
                </div>

                <a href="{{ route('my.orders.show', $order->id) }}" class="btn btn-soft w-100">عرض التفاصيل</a>
            </article>
        @empty
            <div class="text-center text-muted">لا توجد طلبات حتى الآن</div>
        @endforelse
    </div>

    {{ $orders->links() }}
</div>
@endsection
