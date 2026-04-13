@extends('layouts.app')

@section('content')
@php
    $isPickupOrder = $order->order_type === 'pickup';

    $trackingSteps = $isPickupOrder
        ? [
            ['status' => 'pending', 'label' => 'تم الاستلام'],
            ['status' => 'confirmed', 'label' => 'تم التأكيد'],
            ['status' => 'preparing', 'label' => 'جاري التحضير'],
            ['status' => 'ready_for_pickup', 'label' => 'جاهز للاستلام'],
            ['status' => 'delivered', 'label' => 'تم الاستلام'],
        ]
        : [
            ['status' => 'pending', 'label' => 'تم الاستلام'],
            ['status' => 'confirmed', 'label' => 'تم التأكيد'],
            ['status' => 'preparing', 'label' => 'جاري التحضير'],
            ['status' => 'out_for_delivery', 'label' => 'خرج للتوصيل'],
            ['status' => 'delivered', 'label' => 'تم التوصيل'],
        ];

    $stepStatusMap = [];
    foreach ($trackingSteps as $index => $step) {
        $stepStatusMap[$step['status']] = $index + 1;
    }

    $currentStep = $stepStatusMap[$order->status] ?? 0;

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

<style>
    .order-tracker {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 30px;
        overflow-x: auto;
        padding-bottom: 6px;
    }

    .tracker-step {
        flex: 1 0 120px;
        text-align: center;
    }

    .tracker-step .circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #9fb6e4;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: 700;
    }

    .tracker-step.done .circle {
        background: #111827;
        color: #fff;
    }

    .tracker-step .label {
        font-size: 14px;
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        .order-tracker {
            gap: 8px;
            margin-bottom: 18px;
        }

        .tracker-step {
            flex-basis: 100px;
            min-width: 100px;
        }

        .tracker-step .circle {
            width: 38px;
            height: 38px;
            margin-bottom: 8px;
        }

        .tracker-step .label {
            font-size: 12px;
            line-height: 1.35;
        }
    }
</style>

<h2 class="section-title mb-4">
    تفاصيل الطلب {{ $order->order_number ?? ('#' . $order->id) }}
</h2>

<div class="card-shell p-4 mb-4">
    <h5 class="fw-bold mb-4">تتبع الطلب</h5>

    <div class="order-tracker">
        @foreach($trackingSteps as $index => $step)
            <div class="tracker-step {{ $currentStep >= ($index + 1) ? 'done' : '' }}">
                <div class="circle">{{ $index + 1 }}</div>
                <div class="label">{{ $step['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="d-flex flex-wrap gap-3">
        <div>
            <strong>الحالة الحالية:</strong>
            <span class="{{ orderStatusClass($order->status) }}">
                {{ orderStatusLabel($order->status) }}
            </span>
        </div>

        @if(!$isPickupOrder && $order->estimated_delivery_at)
            <div>
                <strong>وقت التوصيل المتوقع:</strong>
                {{ $order->estimated_delivery_at->format('Y-m-d h:i A') }}
            </div>
        @endif
    </div>

    @if($order->status_note)
        <div class="mt-3">
            <strong>ملاحظة الحالة:</strong>
            <div class="text-muted mt-1">{{ $order->status_note }}</div>
        </div>
    @endif
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-shell p-4">
            <h5 class="fw-bold mb-3">الأصناف</h5>

            <div class="table-responsive">
                <table class="table align-middle">
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
<td>
    <div class="fw-bold">{{ $item->product_name }}</div>

    @if(!empty($item->selected_options))
        <div class="small text-muted mt-1">
            @foreach($item->selected_options as $option)
                <div>
                    {{ $option['group_name'] }}:
                    {{ $option['item_name'] }}
                    @if(($option['price'] ?? 0) > 0)
                        (+{{ number_format($option['price'], 2) }} ج.م)
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</td>                                <td>{{ number_format($item->price, 2) }} ج.م</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->total, 2) }} ج.م</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr>

            <div class="d-flex justify-content-between mb-2">
                <strong>الإجمالي الفرعي:</strong>
                <span>{{ number_format($order->subtotal, 2) }} ج.م</span>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <strong>التوصيل:</strong>
                <span>{{ number_format($order->delivery_fee, 2) }} ج.م</span>
            </div>

            <div class="d-flex justify-content-between">
                <strong>الإجمالي النهائي:</strong>
                <span>{{ number_format($order->total, 2) }} ج.م</span>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-shell p-4">
            <h5 class="fw-bold mb-3">بيانات الطلب</h5>

            <p><strong>رقم الطلب:</strong> {{ $order->order_number ?? ('#' . $order->id) }}</p>
            <p><strong>الاسم:</strong> {{ $order->customer_name }}</p>
            <p><strong>الهاتف:</strong> {{ $order->customer_phone }}</p>
            <p><strong>العنوان:</strong> {{ $order->address_line }}</p>
            <p><strong>المنطقة:</strong> {{ $order->area ?? '-' }}</p>
            <p><strong>الدفع:</strong> {{ $order->payment_method }}</p>

            @if($order->latitude && $order->longitude)
                <p>
                    <strong>الموقع:</strong>
                    <a href="https://www.google.com/maps?q={{ $order->latitude }},{{ $order->longitude }}" target="_blank">
                        عرض على الخريطة
                    </a>
                </p>
            @endif

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if($order->canBeCancelledByCustomer())
                    <form action="{{ route('my.orders.cancel', $order->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-danger">إلغاء الطلب</button>
                    </form>
                @endif

                <form action="{{ route('my.orders.reorder', $order->id) }}" method="POST">
                    @csrf
                    <button class="btn btn-dark">إعادة الطلب</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
