@extends('layouts.app')

@section('content')
@php
    $cartCount = count(session('cart', []));
@endphp

<style>
    .methodx-page{max-width:1120px;margin:0 auto;padding-bottom:80px}
    .methodx-head{background:linear-gradient(135deg,#0c3329,#1a5a4b);border-radius:28px;padding:24px;border:1px solid rgba(255,255,255,.16);box-shadow:0 20px 40px rgba(12,32,27,.22);color:#fff;margin-bottom:18px}
    .methodx-kicker{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);padding:7px 12px;border-radius:999px;border:1px solid rgba(255,255,255,.24);font-size:.74rem;font-weight:800}
    .methodx-kicker .dot{width:7px;height:7px;border-radius:50%;background:#ffd8a0}
    .methodx-title{margin:10px 0 8px;font-size:1.95rem;font-weight:900;letter-spacing:-.03em}
    .methodx-sub{margin:0;color:rgba(255,255,255,.92);font-weight:600;line-height:1.9}
    .methodx-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
    .methodx-btn{min-height:44px;padding:10px 16px;border-radius:13px;text-decoration:none;font-weight:900;display:inline-flex;align-items:center;justify-content:center}
    .methodx-btn-soft{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.24);color:#fff}
    .methodx-btn-main{background:#f4dfbf;color:#173028}

    .methodx-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .methodx-card{position:relative;overflow:hidden;background:var(--fb-card);border:1px solid #e5d8c5;border-radius:26px;padding:24px;box-shadow:var(--fb-shadow);display:flex;flex-direction:column;min-height:420px}
    .methodx-card::before{content:"";position:absolute;inset:0 auto auto 0;width:100%;height:5px;background:linear-gradient(135deg,#0f3a2f,#2f6f5f)}
    .methodx-card.delivery::before{background:linear-gradient(135deg,#7f643c,#c49a62)}
    .methodx-badge{width:74px;height:74px;border-radius:22px;display:flex;align-items:center;justify-content:center;font-size:1.9rem;margin-bottom:14px;background:linear-gradient(135deg,#ecf5f1,#d9eae3)}
    .methodx-card.delivery .methodx-badge{background:linear-gradient(135deg,#fff2de,#f3dfbd)}
    .methodx-card h3{margin:0 0 9px;font-size:1.16rem;font-weight:900;color:#192621}
    .methodx-card p{margin:0 0 14px;color:#666156;line-height:1.85;font-weight:700;font-size:.9rem}
    .methodx-features{display:grid;gap:9px;margin-bottom:auto}
    .methodx-feature{display:flex;align-items:center;gap:8px;background:#fbf6ed;border:1px solid #ede0cb;border-radius:12px;padding:10px 12px;color:#5f584d;font-size:.82rem;font-weight:800}
    .methodx-feature .dot{width:8px;height:8px;border-radius:50%;background:#1f6656;flex-shrink:0}
    .methodx-card.delivery .methodx-feature .dot{background:#9f7746}
    .methodx-submit{margin-top:16px;display:block;width:100%;text-align:center;border:none;border-radius:15px;padding:12px 14px;text-decoration:none;font-weight:900;color:#fff;background:linear-gradient(135deg,#0f3a2f,#2f6f5f);box-shadow:0 10px 18px rgba(16,58,47,.22)}
    .methodx-card.delivery .methodx-submit{background:linear-gradient(135deg,#87683d,#bf9660)}
    @media (max-width:991.98px){.methodx-grid{grid-template-columns:1fr}.methodx-card{min-height:auto}}
    @media (max-width:767.98px){.methodx-page{padding-bottom:70px}.methodx-head{padding:16px;border-radius:20px}.methodx-title{font-size:1.24rem}.methodx-sub{font-size:.84rem}.methodx-actions{display:grid;grid-template-columns:1fr 1fr}.methodx-btn{width:100%;min-height:42px;font-size:.8rem}.methodx-card{padding:16px;border-radius:20px}.methodx-badge{width:64px;height:64px;border-radius:18px;font-size:1.5rem}}
    @media (max-width:390px){.methodx-actions{grid-template-columns:1fr}}
</style>

<div class="methodx-page">
    <section class="methodx-head">
        <span class="methodx-kicker"><span class="dot"></span>{{ __('checkout_method.receiving_method') }}</span>
        <h1 class="methodx-title">{{ __('checkout_method.choose_order_receiving_method') }}</h1>
        <p class="methodx-sub">{{ __('checkout_method.choose_pickup_or_delivery', ['count' => $cartCount]) }}</p>
        <div class="methodx-actions">
            <a href="{{ route('cart.index') }}" class="methodx-btn methodx-btn-soft">{{ __('checkout_method.back_to_cart') }}</a>
            <a href="{{ url('/') }}" class="methodx-btn methodx-btn-main">{{ __('checkout_method.continue_browsing') }}</a>
        </div>
    </section>

    <section class="methodx-grid">
        <article class="methodx-card">
            <div class="methodx-badge">🏪</div>
            <h3>{{ __('checkout_method.pickup_from_restaurant') }}</h3>
            <p>{{ __('checkout_method.pickup_description') }}</p>

            <div class="methodx-features">
                <div class="methodx-feature"><span class="dot"></span>{{ __('checkout_method.pickup_feature_branch') }}</div>
                <div class="methodx-feature"><span class="dot"></span>{{ __('checkout_method.pickup_feature_fast') }}</div>
                <div class="methodx-feature"><span class="dot"></span>{{ __('checkout_method.pickup_feature_no_address') }}</div>
            </div>

            <a href="{{ route('checkout.index', ['order_type' => 'pickup']) }}" class="methodx-submit">{{ __('checkout_method.continue_pickup') }}</a>
        </article>

        <article class="methodx-card delivery">
            <div class="methodx-badge">🚚</div>
            <h3>{{ __('checkout_method.delivery_to_address') }}</h3>
            <p>{{ __('checkout_method.delivery_description') }}</p>

            <div class="methodx-features">
                <div class="methodx-feature"><span class="dot"></span>{{ __('checkout_method.delivery_feature_address') }}</div>
                <div class="methodx-feature"><span class="dot"></span>{{ __('checkout_method.delivery_feature_home') }}</div>
                <div class="methodx-feature"><span class="dot"></span>{{ __('checkout_method.delivery_feature_easy') }}</div>
            </div>

            <a href="{{ route('checkout.index', ['order_type' => 'delivery']) }}" class="methodx-submit">{{ __('checkout_method.continue_delivery') }}</a>
        </article>
    </section>
</div>
@endsection
