@extends('layouts.admin')

@section('content')
<div class="admin-card p-4">
    <h3 class="section-title mb-3">الطلبات المكتملة</h3>

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
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->order_number ?? ('ORD-' . $order->id) }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ number_format($order->total, 2) }}</td>
                        <td>{{ $order->branch->name ?? '-' }}</td>
                        <td>{{ $order->address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">لا توجد طلبات مكتملة</td>
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