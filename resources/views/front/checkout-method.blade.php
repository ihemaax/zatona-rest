@extends('layouts.app')

@section('content')
@php
    $cartCount = count(session('cart', []));
@endphp

<style>
    .flowpick-wrap{max-width:1160px;margin:0 auto;padding-bottom:86px}
    .flowpick-hero{background:#020617;border-radius:30px;padding:26px;border:1px solid #1e293b;color:#e2e8f0;box-shadow:0 22px 50px rgba(2,6,23,.45)}
    .flowpick-hero h1{margin:0 0 8px;font-size:2rem;font-weight:900;color:#f8fafc}
    .flowpick-hero p{margin:0;color:#cbd5e1;font-weight:700;line-height:1.9}
    .flowpick-top-actions{margin-top:15px;display:flex;gap:10px;flex-wrap:wrap}
    .flowpick-link{padding:10px 14px;border-radius:12px;text-decoration:none;font-weight:900;border:1px solid #334155}
    .flowpick-link.soft{background:#0f172a;color:#cbd5e1}
    .flowpick-link.main{background:#e2e8f0;color:#0f172a}

    .flowpick-grid{margin-top:18px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .flowpick-card{position:relative;overflow:hidden;background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:22px;display:grid;gap:14px;box-shadow:0 14px 34px rgba(15,23,42,.08)}
    .flowpick-card::before{content:"";position:absolute;inset:0 auto auto 0;width:100%;height:5px;background:linear-gradient(90deg,#3b82f6,#1d4ed8)}
    .flowpick-card.delivery::before{background:linear-gradient(90deg,#f59e0b,#b45309)}
    .flowpick-icon{width:72px;height:72px;border-radius:22px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;background:#eff6ff}
    .flowpick-card.delivery .flowpick-icon{background:#fff7ed}
    .flowpick-card h3{margin:0;font-size:1.2rem;font-weight:900;color:#0f172a}
    .flowpick-card p{margin:0;color:#64748b;font-size:.9rem;font-weight:700;line-height:1.8}
    .flowpick-list{display:grid;gap:8px}
    .flowpick-item{display:flex;align-items:center;gap:8px;font-size:.84rem;color:#334155;font-weight:800}
    .flowpick-item .dot{width:8px;height:8px;border-radius:50%;background:#2563eb;flex-shrink:0}
    .flowpick-card.delivery .flowpick-item .dot{background:#d97706}
    .flowpick-submit{margin-top:6px;display:block;text-align:center;text-decoration:none;padding:12px;border-radius:13px;font-weight:900;background:#1d4ed8;color:#fff}
    .flowpick-card.delivery .flowpick-submit{background:#b45309}

    @media (max-width:991.98px){.flowpick-grid{grid-template-columns:1fr}}
    @media (max-width:767.98px){.flowpick-wrap{padding-bottom:70px}.flowpick-hero{padding:16px;border-radius:20px}.flowpick-hero h1{font-size:1.28rem}.flowpick-card{padding:16px;border-radius:18px}}
</style>

<div class="flowpick-wrap">
    <section class="flowpick-hero">
        <h1>{{ __('checkout_method.choose_order_receiving_method') }}</h1>
        <p>{{ __('checkout_method.choose_pickup_or_delivery', ['count' => $cartCount]) }}</p>

        <div class="flowpick-top-actions">
            <a href="{{ route('cart.index') }}" class="flowpick-link soft">{{ __('checkout_method.back_to_cart') }}</a>
            <a href="{{ url('/') }}" class="flowpick-link main">{{ __('checkout_method.continue_browsing') }}</a>
        </div>
    </section>

    <section class="flowpick-grid">
        <article class="flowpick-card">
            <div class="flowpick-icon">🏬</div>
            <h3>{{ __('checkout_method.pickup_from_restaurant') }}</h3>
            <p>{{ __('checkout_method.pickup_description') }}</p>
            <div class="flowpick-list">
                <div class="flowpick-item"><span class="dot"></span>{{ __('checkout_method.pickup_feature_branch') }}</div>
                <div class="flowpick-item"><span class="dot"></span>{{ __('checkout_method.pickup_feature_fast') }}</div>
                <div class="flowpick-item"><span class="dot"></span>{{ __('checkout_method.pickup_feature_no_address') }}</div>
            </div>
            <a href="{{ route('checkout.index', ['order_type' => 'pickup']) }}" class="flowpick-submit">{{ __('checkout_method.continue_pickup') }}</a>
        </article>

        <article class="flowpick-card delivery">
            <div class="flowpick-icon">🛵</div>
            <h3>{{ __('checkout_method.delivery_to_address') }}</h3>
            <p>{{ __('checkout_method.delivery_description') }}</p>
            <div class="flowpick-list">
                <div class="flowpick-item"><span class="dot"></span>{{ __('checkout_method.delivery_feature_address') }}</div>
                <div class="flowpick-item"><span class="dot"></span>{{ __('checkout_method.delivery_feature_home') }}</div>
                <div class="flowpick-item"><span class="dot"></span>{{ __('checkout_method.delivery_feature_easy') }}</div>
            </div>
            <a href="{{ route('checkout.index', ['order_type' => 'delivery']) }}" class="flowpick-submit">{{ __('checkout_method.continue_delivery') }}</a>
        </article>
    </section>
</div>
@endsection
