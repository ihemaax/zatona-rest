@extends('layouts.admin')

@php
    $pageTitle = 'الطلبات الجاهزة للاستلام';
    $pageSubtitle = 'طلبات خرجت من المطبخ وجاهزة للتسليم أو إسناد الدليفري';
@endphp

@section('content')
<div class="admin-card p-4 mb-4">
    <div class="section-title mb-1">دورة التشغيل</div>
    <div class="section-subtitle mb-0">Pending → Confirmed → Preparing → Ready (Ready for Pickup) → Delivery Assignment/Customer Pickup</div>
</div>

<div class="admin-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-title mb-0">طلبات التوصيل الجاهزة</div>
        <span class="filter-pill active">{{ $deliveryOrders->count() }} طلب</span>
    </div>

    <div class="admin-table-wrap">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الفرع</th>
                    <th>إسناد للدليفري</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveryOrders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->branch?->name ?? '-' }}</td>
                        <td>
                            <form action="{{ route('admin.orders.assign-delivery', $order->id) }}" method="POST" class="d-flex gap-2 flex-wrap">
                                @csrf
                                @method('PATCH')
                                <select name="delivery_user_id" class="form-select" style="max-width:240px;" required>
                                    <option value="">اختار موظف دليفري</option>
                                    @foreach($deliveryUsers as $deliveryUser)
                                        <option value="{{ $deliveryUser->id }}">{{ $deliveryUser->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-outline-primary">إسناد</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">لا توجد طلبات توصيل جاهزة حالياً.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-title mb-0">طلبات الاستلام من الفرع الجاهزة</div>
        <span class="filter-pill active">{{ $pickupOrders->count() }} طلب</span>
    </div>

    <div class="admin-table-wrap">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الفرع</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pickupOrders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->branch?->name ?? '-' }}</td>
                        <td>
                            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="d-flex gap-2 flex-wrap">
                                @csrf
                                <input type="hidden" name="order_type" value="pickup">
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="btn btn-outline-success">تم التسليم للعميل</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">لا توجد طلبات استلام جاهزة حالياً.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
