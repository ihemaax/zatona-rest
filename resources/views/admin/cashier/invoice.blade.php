@extends('layouts.admin')

@section('content')
<style>
    .invoice-wrap { max-width: 900px; margin: auto; }
    .invoice-card { border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, .07); overflow: hidden; }
    .invoice-head { background: linear-gradient(135deg, #0d6efd, #3b82f6); color: #fff; padding: 1rem 1.25rem; }
    .invoice-body { background: #fff; padding: 1rem 1.25rem; }
    .invoice-meta { background: #f8fafc; border: 1px solid #edf2f7; border-radius: 12px; padding: .75rem; }
    .invoice-table th { background: #f8fafc; font-size: .9rem; }
    .invoice-total { background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 12px; }

    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; }
        .invoice-card { box-shadow: none; border: 0; }
        .invoice-wrap { max-width: 100%; }
    }
</style>

<div class="container py-3">
    <div class="invoice-wrap">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h1 class="h5 mb-0">فاتورة كاشير</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.cashier.pos', $branch) }}" class="btn btn-outline-secondary">رجوع للكاشير</a>
                <button class="btn btn-primary" onclick="window.print()">طباعة</button>
            </div>
        </div>

        <div class="invoice-card" id="printArea">
            <div class="invoice-head d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="fw-bold fs-5">{{ config('app.name', 'Restaurant POS') }}</div>
                    <div class="small opacity-75">فاتورة طلب داخلي - كاشير</div>
                </div>
                <div class="text-end">
                    <div class="small">رقم الفاتورة</div>
                    <div class="fw-bold">{{ $order->order_number }}</div>
                </div>
            </div>

            <div class="invoice-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="invoice-meta h-100">
                            <div><strong>الفرع:</strong> {{ $branch->name }}</div>
                            <div><strong>العميل:</strong> {{ $order->customer_name }}</div>
                            <div><strong>الهاتف:</strong> {{ $order->customer_phone }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        @php
                            $statusLabel = [
                                'pending' => 'جديد',
                                'confirmed' => 'مؤكد',
                                'preparing' => 'قيد التحضير',
                                'ready_for_pickup' => 'جاهز',
                                'out_for_delivery' => 'خرج للتوصيل',
                                'delivered' => 'مكتمل',
                                'cancelled' => 'ملغي',
                            ][$order->status] ?? $order->status;
                        @endphp
                        <div class="invoice-meta h-100">
                            <div><strong>التاريخ:</strong> {{ $order->created_at?->format('Y-m-d h:i A') }}</div>
                            <div><strong>طريقة الدفع:</strong> كاش</div>
                            <div><strong>الحالة:</strong> {{ $statusLabel }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table invoice-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>الصنف</th>
                                <th class="text-center">السعر</th>
                                <th class="text-center">الكمية</th>
                                <th class="text-end">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ number_format($item->price, 2) }} ج.م</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($item->total, 2) }} ج.م</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="invoice-total p-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>الإجمالي الفرعي</span>
                        <strong>{{ number_format($order->subtotal, 2) }} ج.م</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>خصم</span>
                        <strong>{{ number_format($order->discount_amount, 2) }} ج.م</strong>
                    </div>
                    <div class="d-flex justify-content-between fs-5">
                        <span>الإجمالي النهائي</span>
                        <strong>{{ number_format($order->total, 2) }} ج.م</strong>
                    </div>
                </div>

                @if($order->notes)
                    <div class="alert alert-info mt-3 mb-0"><strong>ملاحظات:</strong> {{ $order->notes }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
