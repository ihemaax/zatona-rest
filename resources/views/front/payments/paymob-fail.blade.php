@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="card-shell p-5">
    <h2 class="mb-3 text-danger">تعذر إتمام الدفع</h2>
    <p class="text-muted mb-4">يمكنك إعادة المحاولة الآن أو اختيار الدفع النقدي في طلب جديد.</p>
    @if($order)
        @php($params = ['order' => $order->id])
        @if(!$order->user_id) @php($params['token'] = $order->guest_token) @endif
        <a class="btn btn-primary" href="{{ route('checkout.paymob.start', $params) }}">إعادة محاولة الدفع</a>
    @endif
    </div>
</div>
@endsection
