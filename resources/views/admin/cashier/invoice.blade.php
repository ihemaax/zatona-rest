@extends('layouts.admin')

@section('content')
<div class="container py-4" id="printArea">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">فاتورة كاشير #{{ $order->order_number }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.cashier.pos', $branch) }}" class="btn btn-outline-secondary">رجوع للكاشير</a>
            <button class="btn btn-primary" onclick="window.print()">طباعة الفاتورة</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div><strong>الفرع:</strong> {{ $branch->name }}</div>
                    <div><strong>العميل:</strong> {{ $order->customer_name }}</div>
                    <div><strong>الهاتف:</strong> {{ $order->customer_phone }}</div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div><strong>التاريخ:</strong> {{ $order->created_at?->format('Y-m-d h:i A') }}</div>
                    <div><strong>طريقة الدفع:</strong> كاش</div>
                    <div><strong>الحالة:</strong> {{ $order->status }}</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            <th>السعر</th>
                            <th>الكمية</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ number_format($item->price, 2) }} ج.م</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->total, 2) }} ج.م</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">الإجمالي النهائي</th>
                            <th>{{ number_format($order->total, 2) }} ج.م</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($order->notes)
                <div class="mt-3"><strong>ملاحظات:</strong> {{ $order->notes }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
