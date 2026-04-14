@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="card-shell p-5">
    <h2 class="mb-3">جارٍ تأكيد العملية</h2>
    <p class="text-muted mb-4">الدفع قيد المراجعة من مزود الدفع. برجاء الانتظار قليلًا ثم تحديث الصفحة.</p>
    @if($order)
        @if($order->user_id)
            <a class="btn btn-outline-primary" href="{{ route('my.orders.show', $order) }}">متابعة حالة الطلب</a>
        @else
            <a class="btn btn-outline-primary" href="{{ route('order.success', [$order, $order->guest_token]) }}">متابعة حالة الطلب</a>
        @endif
    @endif
    </div>
</div>
@endsection
