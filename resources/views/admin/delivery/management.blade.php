@extends('layouts.admin')

@php
    $pageTitle = 'متابعة الدليفري';
    $pageSubtitle = 'متابعة أداء جميع أفراد الدليفري وحالة الطلبات المسندة لهم';
@endphp

@section('content')
<div class="admin-card p-4">
    <h3 class="section-title mb-3">متابعة الدليفري</h3>

    @forelse($deliveryUsers as $entry)
        <div class="border rounded-3 p-3 mb-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">{{ $entry['user']->name }}</h5>
                    <small class="text-muted">{{ $entry['user']->email }}</small>
                </div>
                <a href="{{ route('admin.staff.edit', $entry['user']->id) }}" class="btn-admin-soft btn-sm">تعديل الموظف</a>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3"><div class="border rounded-3 p-2">إجمالي مسند: <strong>{{ $entry['assigned_total'] }}</strong></div></div>
                <div class="col-6 col-md-3"><div class="border rounded-3 p-2">جارية: <strong>{{ $entry['active_total'] }}</strong></div></div>
                <div class="col-6 col-md-3"><div class="border rounded-3 p-2">تم التسليم: <strong>{{ $entry['delivered_total'] }}</strong></div></div>
                <div class="col-6 col-md-3"><div class="border rounded-3 p-2">ملغية: <strong>{{ $entry['cancelled_total'] }}</strong></div></div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>الحالة</th>
                            <th>الإجمالي</th>
                            <th>تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entry['latest_orders'] as $order)
                            <tr>
                                <td>{{ $order->order_number ?? ('ORD-' . $order->id) }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->status }}</td>
                                <td>{{ number_format($order->total, 2) }} ج.م</td>
                                <td><a href="{{ route('admin.orders.show', $order->id) }}" class="btn-admin-soft btn-sm">عرض</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">لا توجد طلبات لهذا الدليفري.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-info mb-0">لا يوجد موظفون بدور دليفري حالياً.</div>
    @endforelse
</div>
@endsection
