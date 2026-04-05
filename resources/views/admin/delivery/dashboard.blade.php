@extends('layouts.admin')

@php
    $pageTitle = 'لوحة الدليفري';
    $pageSubtitle = 'الطلبات المسندة إليك تلقائياً من الكاشير أو مسؤول الطلبات';
@endphp

@section('content')
<div class="admin-card p-4">
    <h3 class="section-title mb-3">طلبات الدليفري المسندة لي</h3>

    <div class="admin-table-wrap">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الحالة</th>
                    <th>الإجمالي</th>
                    <th>الفرع</th>
                    <th>العنوان</th>
                    <th>تفاصيل</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->order_number ?? ('ORD-' . $order->id) }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ number_format($order->total, 2) }} ج.م</td>
                        <td>{{ $order->branch->name ?? '-' }}</td>
                        <td>{{ $order->address_line ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-admin-soft btn-sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">لا توجد طلبات دليفري مسندة لك حالياً</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $orders->links() }}
    </div>
</div>
@endsection
