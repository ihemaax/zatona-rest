@extends('layouts.app')

@section('content')
@php
    $title = __('site.brand');
    $metaDescription = 'Online ordering experience for faster checkout and clear delivery tracking.';
@endphp

<div class="card border-0 shadow-sm rounded-4 text-center">
    <div class="card-body p-5">
        <h2 class="fw-bold text-success mb-3">{{ __('order_success.order_sent_successfully') }}</h2>
        <p class="mb-2">{{ __('order_success.order_number') }}: <strong>#{{ $order->id }}</strong></p>
        <p class="mb-2">{{ __('order_success.name') }}: {{ $order->customer_name }}</p>
        <p class="mb-2">{{ __('order_success.total') }}: {{ number_format($order->total, 2) }} {{ __('order_success.currency_egp') }}</p>

        <p class="mb-4">
            {{ __('order_success.order_status') }}:
            @if($order->status == 'pending')
                <span class="badge bg-warning text-dark">{{ __('order_success.pending') }}</span>
            @elseif($order->status == 'confirmed')
                <span class="badge bg-info">{{ __('order_success.confirmed') }}</span>
            @elseif($order->status == 'preparing')
                <span class="badge bg-primary">{{ __('order_success.preparing') }}</span>
            @elseif($order->status == 'out_for_delivery')
                <span class="badge bg-dark">{{ __('order_success.out_for_delivery') }}</span>
            @elseif($order->status == 'ready_for_pickup')
                <span class="badge bg-primary">جاهز للاستلام من الفرع</span>
            @elseif($order->status == 'delivered')
                <span class="badge bg-success">{{ __('order_success.delivered') }}</span>
            @else
                <span class="badge bg-danger">{{ __('order_success.cancelled') }}</span>
            @endif
        </p>

        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ route('my.orders.show', $order->id) }}" class="btn btn-dark">{{ __('order_success.track_order') }}</a>
            <a href="{{ route('home') }}" class="btn btn-main">{{ __('order_success.back_to_menu') }}</a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
    event: 'purchase',
    transaction_id: @json($order->order_number ?? ('order-' . $order->id)),
    value: {{ (float) $order->total }},
    shipping: {{ (float) $order->delivery_fee }},
    currency: @json(__('order_success.currency_egp')),
});
</script>
@endpush
