@extends('layouts.app')

@section('content')
@php
    $title = __('site.brand');
    $metaDescription = 'Online ordering experience for faster checkout and clear delivery tracking.';

    $restaurantName = $setting->restaurant_name ?? __('site.brand');
    $cartCount = count($cart ?? []);
    $subtotal = collect($cart ?? [])->sum(fn ($item) => $item['total'] ?? 0);
@endphp

<style>
    .aura-cart{max-width:1220px;margin:0 auto;padding-bottom:110px}
    .aura-top{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;margin-bottom:16px}
    .aura-banner{background:linear-gradient(140deg,#0f3a2f 0%,#1f5f4f 52%,#2d7b67 100%);border:1px solid rgba(255,255,255,.2);border-radius:30px;padding:24px;color:#fff;box-shadow:0 20px 42px rgba(15,58,47,.28)}
    .aura-banner small{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);font-size:.73rem;font-weight:900}
    .aura-banner h1{margin:12px 0 8px;font-size:1.95rem;font-weight:900;letter-spacing:-.03em}
    .aura-banner p{margin:0;color:rgba(255,255,255,.92);font-weight:700;line-height:1.8}
    .aura-banner-actions{margin-top:14px;display:flex;gap:10px;flex-wrap:wrap}
    .aura-btn{min-height:44px;padding:10px 15px;border-radius:13px;font-size:.83rem;font-weight:900;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
    .aura-btn-light{background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.32);color:#fff}
    .aura-btn-main{background:#f5dfba;color:#19342c;box-shadow:0 10px 20px rgba(9,28,22,.2)}

    .aura-panel{background:var(--fb-card);border:1px solid var(--fb-border);border-radius:26px;padding:18px;box-shadow:var(--fb-shadow)}
    .aura-panel h3{margin:0 0 10px;font-size:1rem;font-weight:900;color:#1a2a25}
    .aura-kpis{display:grid;gap:10px}
    .aura-kpi{display:flex;justify-content:space-between;align-items:center;background:#f7f2e8;border:1px solid #ebdfcd;border-radius:13px;padding:10px 12px;font-size:.84rem;font-weight:800;color:#5d584f}
    .aura-kpi strong{color:#153328;font-size:.94rem}

    .aura-layout{display:grid;grid-template-columns:minmax(0,1fr) 345px;gap:16px;align-items:start}
    .aura-list{background:var(--fb-card);border:1px solid var(--fb-border);border-radius:26px;padding:14px;box-shadow:var(--fb-shadow);display:grid;gap:12px}
    .aura-item{background:linear-gradient(180deg,#fffdf9,#f8f2e8);border:1px solid #e8ddcb;border-radius:20px;padding:14px;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:14px}
    .aura-item h4{margin:0 0 6px;font-size:1.05rem;font-weight:900;color:#182722}
    .aura-price{color:#1f6757;font-size:.84rem;font-weight:900}
    .aura-options{margin-top:10px;background:#f5eee2;border:1px solid #e7dbc8;border-radius:12px;padding:9px;display:grid;gap:6px}
    .aura-options span{font-size:.79rem;font-weight:800;color:#615b52;line-height:1.7}
    .aura-actions{margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;align-items:center}
    .aura-actions input{width:90px;border:1px solid #d9ccba;border-radius:11px;background:#fff;padding:8px 10px;text-align:center;font-weight:900}
    .aura-update,.aura-delete{border:none;border-radius:11px;padding:8px 12px;font-size:.76rem;font-weight:900}
    .aura-update{background:#ede2cf;color:#22332e}
    .aura-delete{background:#fff0f0;color:#992222}
    .aura-line-total{display:grid;gap:7px;justify-items:end;align-content:start;text-align:end}
    .aura-line-total small{font-size:.74rem;color:#6f695f;font-weight:800}
    .aura-line-total strong{display:inline-flex;padding:8px 12px;border-radius:10px;background:#edf5f2;border:1px solid #d4e6df;color:#1f6253;font-size:.9rem;font-weight:900}

    .aura-checkout{position:sticky;top:86px;background:var(--fb-card);border:1px solid var(--fb-border);border-radius:24px;padding:16px;box-shadow:var(--fb-shadow)}
    .aura-checkout h3{margin:0 0 10px;font-size:1.02rem;font-weight:900;color:#1a2a25}
    .aura-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px dashed #e9dfd2;font-size:.86rem;font-weight:800;color:#615c52}
    .aura-row strong{font-size:1rem;color:#11251f}
    .aura-go{margin-top:12px;display:block;text-align:center;text-decoration:none;padding:12px;border-radius:13px;font-weight:900;background:linear-gradient(135deg,#103f33,#2c705f);color:#fff;box-shadow:0 10px 18px rgba(16,58,47,.24)}

    .aura-empty{background:var(--fb-card);border:1px solid var(--fb-border);border-radius:28px;padding:36px 18px;text-align:center;box-shadow:var(--fb-shadow)}
    .aura-empty h2{margin:10px 0 6px;font-size:1.2rem;font-weight:900;color:#182722}
    .aura-empty p{margin:0 0 14px;color:#676157;font-weight:700}

    @media (max-width:991.98px){.aura-top{grid-template-columns:1fr}.aura-layout{grid-template-columns:1fr}.aura-checkout{position:static}}
    @media (max-width:767.98px){.aura-cart{padding-bottom:95px}.aura-banner{padding:16px;border-radius:22px}.aura-banner h1{font-size:1.26rem}.aura-banner-actions{display:grid;grid-template-columns:1fr 1fr}.aura-btn{width:100%}.aura-item{grid-template-columns:1fr;padding:12px}.aura-line-total{justify-items:start;text-align:start}}
    @media (max-width:390px){.aura-banner-actions{grid-template-columns:1fr}}
</style>

<div class="aura-cart">
    @if(empty($cart))
        <div class="aura-empty">
            <div style="font-size:2.2rem">🛒</div>
            <h2>{{ __('cart.cart_is_empty_now') }}</h2>
            <p>{{ __('cart.empty_cart_message') }}</p>
            <a href="{{ url('/') }}" class="aura-go" style="max-width:260px;margin:0 auto">{{ __('cart.browse_menu') }}</a>
        </div>
    @else
        <section class="aura-top">
            <div class="aura-banner">
                <small><span style="width:7px;height:7px;border-radius:50%;background:#f8ddb2;display:inline-block"></span>{{ __('cart.shopping_cart') }}</small>
                <h1>{{ __('cart.review_order_before_continue') }}</h1>
                <p>{{ $restaurantName }} • {{ $cartCount }} {{ $cartCount == 1 ? __('cart.product_singular') : __('cart.product_plural') }} {{ __('cart.inside_cart') }}</p>
                <div class="aura-banner-actions">
                    <a href="{{ url()->previous() }}" class="aura-btn aura-btn-light">{{ __('cart.continue_browsing') }}</a>
                    <a href="{{ route('checkout.method') }}" class="aura-btn aura-btn-main">{{ __('cart.complete_order') }}</a>
                </div>
            </div>

            <div class="aura-panel">
                <h3>{{ __('cart.current_order_total') }}</h3>
                <div class="aura-kpis">
                    <div class="aura-kpi"><span>{{ __('cart.product_plural') }}</span><strong>{{ $cartCount }}</strong></div>
                    <div class="aura-kpi"><span>{{ __('cart.total') }}</span><strong>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</strong></div>
                    <div class="aura-kpi"><span>{{ __('cart.complete_order') }}</span><strong>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</strong></div>
                </div>
            </div>
        </section>

        <div class="aura-layout">
            <div class="aura-list">
                @foreach($cart as $item)
                    <article class="aura-item">
                        <div>
                            <h4>{{ $item['name'] }}</h4>
                            <div class="aura-price">{{ number_format($item['price'], 2) }} {{ __('cart.currency_egp') }}</div>

                            @if(!empty($item['selected_options']))
                                <div class="aura-options">
                                    @foreach($item['selected_options'] as $option)
                                        <span>
                                            {{ $option['group_name'] }}: {{ $option['item_name'] }}
                                            @if(($option['price'] ?? 0) > 0)
                                                (+{{ number_format($option['price'], 2) }} {{ __('cart.currency_egp') }})
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="aura-actions">
                                <form action="{{ route('cart.update', $item['cart_key']) }}" method="POST" style="display:flex;gap:8px;flex-wrap:wrap">
                                    @csrf
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1">
                                    <button type="submit" class="aura-update">{{ __('cart.update_quantity') }}</button>
                                </form>

                                <form action="{{ route('cart.remove', $item['cart_key']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="aura-delete">{{ __('cart.delete') }}</button>
                                </form>
                            </div>
                        </div>

                        <div class="aura-line-total">
                            <small>{{ __('cart.total') }}</small>
                            <strong>{{ number_format($item['total'], 2) }} {{ __('cart.currency_egp') }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="aura-checkout">
                <h3>{{ __('cart.complete_order') }}</h3>
                <div class="aura-row"><span>{{ __('cart.product_plural') }}</span><span>{{ $cartCount }}</span></div>
                <div class="aura-row"><span>{{ __('cart.total') }}</span><span>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</span></div>
                <div class="aura-row"><strong>{{ __('cart.complete_order') }}</strong><strong>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</strong></div>
                <a href="{{ route('checkout.method') }}" class="aura-go">{{ __('cart.complete_order') }}</a>
            </aside>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
    event: 'view_cart',
    cart_count: {{ (int) $cartCount }},
    value: {{ (float) $subtotal }},
    currency: @json(__('cart.currency_egp')),
});
</script>
@endpush
