@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="card-shell p-5">
    <h2 class="mb-3 text-success">تم استلام الدفع بنجاح</h2>
    <p class="text-muted mb-4">شكرًا لك. تم تسجيل العملية وسيتم تجهيز طلبك.</p>
    @if($order)
        @if($order->user_id)
            <a class="btn btn-success" href="{{ route('my.orders.show', $order) }}">عرض الطلب</a>
        @else
            <a class="btn btn-success" href="{{ route('order.success', [$order, $order->guest_token]) }}">عرض تفاصيل الطلب</a>
        @endif
    @endif
    </div>
</div>
@endsection
