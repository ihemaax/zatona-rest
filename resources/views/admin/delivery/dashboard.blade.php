@extends('layouts.admin')

@php
    $pageTitle = 'طلباتي (الدليفري)';
    $pageSubtitle = 'متابعة الطلبات الحالية والمنتهية بشكل واضح ومنظم';
@endphp

@section('content')
<div class="admin-card p-4 mb-4">
    <h3 class="section-title mb-3">ملخص سريع</h3>
    <div class="row g-3">
        <div class="col-12 col-md-3">
            <div class="border rounded-3 p-3 bg-light">
                <div class="text-muted">إجمالي الطلبات المسندة</div>
                <div class="h4 mb-0">{{ $stats['assigned_total'] }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="border rounded-3 p-3 bg-light">
                <div class="text-muted">طلبات جارية</div>
                <div class="h4 mb-0">{{ $stats['active_total'] }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="border rounded-3 p-3 bg-light">
                <div class="text-muted">طلبات تم تسليمها</div>
                <div class="h4 mb-0">{{ $stats['completed_total'] }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="border rounded-3 p-3 bg-light">
                <div class="text-muted">طلبات ملغية</div>
                <div class="h4 mb-0">{{ $stats['cancelled_total'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="admin-card p-4 mb-4">
    <h3 class="section-title mb-3">الطلبات الجارية</h3>
    <div class="admin-table-wrap">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الحالة</th>
                    <th>الإجمالي</th>
                    <th>العنوان</th>
                    <th>تفاصيل</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeOrders as $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->order_number ?? ('ORD-' . $order->id) }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ number_format($order->total, 2) }} ج.م</td>
                        <td>{{ $order->address_line ?? '-' }}</td>
                        <td><a href="{{ route('admin.orders.show', $order->id) }}" class="btn-admin-soft btn-sm">عرض</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">لا توجد طلبات جارية حالياً.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $activeOrders->links() }}</div>
</div>

<div class="admin-card p-4">
    <h3 class="section-title mb-3">الطلبات المنتهية</h3>
    <div class="admin-table-wrap">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الحالة</th>
                    <th>الإجمالي</th>
                    <th>العنوان</th>
                    <th>تفاصيل</th>
                </tr>
            </thead>
            <tbody>
                @forelse($completedOrders as $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->order_number ?? ('ORD-' . $order->id) }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ number_format($order->total, 2) }} ج.م</td>
                        <td>{{ $order->address_line ?? '-' }}</td>
                        <td><a href="{{ route('admin.orders.show', $order->id) }}" class="btn-admin-soft btn-sm">عرض</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">لا توجد طلبات منتهية حالياً.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $completedOrders->links() }}</div>
</div>
@endsection
