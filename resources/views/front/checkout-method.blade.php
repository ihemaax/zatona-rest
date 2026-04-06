@extends('layouts.app')

@section('content')
@php
    $cartCount = count(session('cart', []));
@endphp

<style>
    .methoda-wrap{max-width:1180px;margin:0 auto;padding-bottom:86px}
    .methoda-banner{position:relative;overflow:hidden;background:linear-gradient(135deg,#0f3a2f,#1f5f4f 55%,#2f7967);border-radius:30px;padding:26px;color:#fff;border:1px solid rgba(255,255,255,.2);box-shadow:0 20px 44px rgba(15,58,47,.28)}
    .methoda-banner::after{content:"";position:absolute;inset:auto -70px -90px auto;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 62%)}
    .methoda-kicker{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.28);font-size:.74rem;font-weight:900}
    .methoda-title{margin:11px 0 8px;font-size:1.95rem;font-weight:900;letter-spacing:-.03em}
    .methoda-sub{margin:0;color:rgba(255,255,255,.92);font-weight:700;line-height:1.9;max-width:760px}
    .methoda-links{margin-top:14px;display:flex;gap:10px;flex-wrap:wrap}
    .methoda-link{min-height:44px;padding:10px 14px;border-radius:12px;text-decoration:none;font-weight:900;display:inline-flex;align-items:center;justify-content:center}
    .methoda-link.soft{background:rgba(255,255,255,.16);color:#fff;border:1px solid rgba(255,255,255,.28)}
    .methoda-link.main{background:#f4dfbf;color:#173028}

    .methoda-layout{display:grid;grid-template-columns:280px minmax(0,1fr);gap:16px;margin-top:18px;align-items:start}
    .methoda-steps{background:var(--fb-card);border:1px solid var(--fb-border);border-radius:22px;padding:14px;box-shadow:var(--fb-shadow);display:grid;gap:10px}
    .methoda-step{padding:10px 12px;border-radius:12px;background:#f8f2e7;border:1px solid #eadfcd;color:#5f5a52;font-size:.82rem;font-weight:800;line-height:1.7}
    .methoda-step strong{display:block;color:#1a2a25;font-size:.84rem;font-weight:900}

    .methoda-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .methoda-card{background:var(--fb-card);border:1px solid #e7dbc8;border-radius:26px;padding:20px;box-shadow:var(--fb-shadow);display:flex;flex-direction:column;gap:12px;min-height:360px;position:relative;overflow:hidden}
    .methoda-card::before{content:"";position:absolute;inset:0 auto auto 0;width:100%;height:5px;background:linear-gradient(135deg,#123f34,#2f7261)}
    .methoda-card.delivery::before{background:linear-gradient(135deg,#7d633d,#c29a64)}
    .methoda-icon{width:76px;height:76px;border-radius:24px;display:flex;align-items:center;justify-content:center;font-size:2rem;background:#eaf3ef;border:1px solid #d2e5dd}
    .methoda-card.delivery .methoda-icon{background:#f7efe2;border-color:#e7d6bc}
    .methoda-card h3{margin:0;font-size:1.18rem;font-weight:900;color:#1b2a25}
    .methoda-card p{margin:0;color:#696358;font-weight:700;line-height:1.85;font-size:.9rem}
    .methoda-points{display:grid;gap:8px;margin-top:4px;margin-bottom:auto}
    .methoda-point{display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:11px;background:#faf5ec;border:1px solid #efe2cf;color:#605a4f;font-size:.81rem;font-weight:800}
    .methoda-point i{font-size:.9rem;color:#1f6656}
    .methoda-card.delivery .methoda-point i{color:#916b3f}
    .methoda-cta{display:block;text-align:center;text-decoration:none;padding:12px;border-radius:14px;font-weight:900;color:#fff;background:linear-gradient(135deg,#103f33,#2d705f);box-shadow:0 10px 18px rgba(16,58,47,.24)}
    .methoda-card.delivery .methoda-cta{background:linear-gradient(135deg,#896a3f,#bf955f)}

    @media (max-width:991.98px){.methoda-layout{grid-template-columns:1fr}.methoda-grid{grid-template-columns:1fr}.methoda-card{min-height:auto}}
    @media (max-width:767.98px){.methoda-wrap{padding-bottom:70px}.methoda-banner{padding:16px;border-radius:22px}.methoda-title{font-size:1.28rem}.methoda-sub{font-size:.84rem}.methoda-links{display:grid;grid-template-columns:1fr 1fr}.methoda-link{width:100%}.methoda-card{padding:16px;border-radius:20px}.methoda-icon{width:66px;height:66px;border-radius:20px;font-size:1.6rem}}
    @media (max-width:390px){.methoda-links{grid-template-columns:1fr}}
</style>

<div class="methoda-wrap">
    <section class="methoda-banner">
        <span class="methoda-kicker"><span style="width:7px;height:7px;border-radius:50%;background:#f8ddb2;display:inline-block"></span>{{ __('checkout_method.receiving_method') }}</span>
        <h1 class="methoda-title">{{ __('checkout_method.choose_order_receiving_method') }}</h1>
        <p class="methoda-sub">{{ __('checkout_method.choose_pickup_or_delivery', ['count' => $cartCount]) }}</p>

        <div class="methoda-links">
            <a href="{{ route('cart.index') }}" class="methoda-link soft">{{ __('checkout_method.back_to_cart') }}</a>
            <a href="{{ url('/') }}" class="methoda-link main">{{ __('checkout_method.continue_browsing') }}</a>
        </div>
    </section>

    <div class="methoda-layout">
        <aside class="methoda-steps">
            <div class="methoda-step">
                <strong>1) {{ __('cart.shopping_cart') }}</strong>
                {{ __('cart.review_order_before_continue') }}
            </div>
            <div class="methoda-step">
                <strong>2) {{ __('checkout_method.receiving_method') }}</strong>
                {{ __('checkout_method.choose_order_receiving_method') }}
            </div>
            <div class="methoda-step">
                <strong>3) {{ __('cart.complete_order') }}</strong>
                {{ __('checkout_method.continue_pickup') }} / {{ __('checkout_method.continue_delivery') }}
            </div>
        </aside>

        <section class="methoda-grid">
            <article class="methoda-card">
                <div class="methoda-icon">🏪</div>
                <h3>{{ __('checkout_method.pickup_from_restaurant') }}</h3>
                <p>{{ __('checkout_method.pickup_description') }}</p>

                <div class="methoda-points">
                    <div class="methoda-point"><i class="bi bi-geo-alt-fill"></i>{{ __('checkout_method.pickup_feature_branch') }}</div>
                    <div class="methoda-point"><i class="bi bi-lightning-charge-fill"></i>{{ __('checkout_method.pickup_feature_fast') }}</div>
                    <div class="methoda-point"><i class="bi bi-shield-check"></i>{{ __('checkout_method.pickup_feature_no_address') }}</div>
                </div>

                <a href="{{ route('checkout.index', ['order_type' => 'pickup']) }}" class="methoda-cta">{{ __('checkout_method.continue_pickup') }}</a>
            </article>

            <article class="methoda-card delivery">
                <div class="methoda-icon">🚚</div>
                <h3>{{ __('checkout_method.delivery_to_address') }}</h3>
                <p>{{ __('checkout_method.delivery_description') }}</p>

                <div class="methoda-points">
                    <div class="methoda-point"><i class="bi bi-pin-map-fill"></i>{{ __('checkout_method.delivery_feature_address') }}</div>
                    <div class="methoda-point"><i class="bi bi-house-heart-fill"></i>{{ __('checkout_method.delivery_feature_home') }}</div>
                    <div class="methoda-point"><i class="bi bi-check2-circle"></i>{{ __('checkout_method.delivery_feature_easy') }}</div>
                </div>

                <a href="{{ route('checkout.index', ['order_type' => 'delivery']) }}" class="methoda-cta">{{ __('checkout_method.continue_delivery') }}</a>
            </article>
        </section>
    </div>
</div>
@endsection
