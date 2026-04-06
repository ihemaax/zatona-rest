@extends('layouts.app')

@section('content')
@php
    $title = __('site.brand');
    $metaDescription = 'Online ordering experience for faster checkout and clear delivery tracking.';

    $restaurantName = $setting->restaurant_name ?? __('site.brand');
    $cartCount = count($cart ?? []);
    $subtotal = collect($cart ?? [])->sum(function ($item) {
        return $item['total'] ?? 0;
    });
@endphp

<style>
    .cartx-page{max-width:1160px;margin:0 auto;padding-bottom:110px}
    .cartx-hero{background:linear-gradient(135deg,#0d352b,#1f5f4f);border-radius:30px;padding:24px;border:1px solid rgba(255,255,255,.18);box-shadow:0 20px 40px rgba(8,28,23,.22);color:#fff;margin-bottom:18px}
    .cartx-kicker{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.24);padding:7px 12px;border-radius:999px;font-size:.74rem;font-weight:800}
    .cartx-kicker .dot{width:7px;height:7px;border-radius:50%;background:#f9ddb1}
    .cartx-title{font-size:2rem;font-weight:900;margin:10px 0 8px;letter-spacing:-.03em}
    .cartx-sub{margin:0;color:rgba(255,255,255,.92);font-weight:600;line-height:1.9}
    .cartx-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
    .cartx-btn{min-height:46px;padding:11px 16px;border-radius:14px;text-decoration:none;font-weight:900;display:inline-flex;align-items:center;justify-content:center}
    .cartx-btn-soft{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.28)}
    .cartx-btn-main{background:#f5dfba;color:#173029;box-shadow:0 10px 18px rgba(0,0,0,.15)}
    .cartx-grid{display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:18px;align-items:start}
    .cartx-card{background:var(--fb-card);border:1px solid var(--fb-border);border-radius:24px;box-shadow:var(--fb-shadow)}
    .cartx-list{padding:14px;display:grid;gap:12px}
    .cartx-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;border:1px solid #e8ddcb;background:linear-gradient(180deg,#fffdf9,#f9f4ea);border-radius:18px;padding:14px}
    .cartx-name{margin:0 0 6px;font-size:1.02rem;font-weight:900;color:#1b2723}
    .cartx-price{color:#1e5e50;font-weight:900;font-size:.92rem;white-space:nowrap}
    .cartx-options{background:#f8f2e7;border:1px solid #eadfcd;border-radius:12px;padding:10px;margin-bottom:10px}
    .cartx-option{font-size:.8rem;color:#5f5a52;font-weight:700;line-height:1.7}
    .cartx-meta{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
    .cartx-qty{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .cartx-qty input{width:90px;border:1px solid #d9ccba;background:#fff;border-radius:12px;padding:9px 10px;text-align:center;font-weight:800}
    .cartx-update,.cartx-delete{border:none;border-radius:12px;padding:9px 12px;font-size:.8rem;font-weight:900}
    .cartx-update{background:#efe4d2;color:#26332f}
    .cartx-delete{background:#fff2f2;color:#a12020}
    .cartx-total{font-weight:900;color:#1d2d28}
    .cartx-total span{color:#1e5e50}
    .cartx-summary{position:sticky;top:86px;padding:16px}
    .cartx-summary h3{margin:0 0 12px;font-size:1.05rem;font-weight:900;color:#1b2723}
    .cartx-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px dashed #e9dfd2;font-size:.9rem;color:#5e5a52;font-weight:700}
    .cartx-row strong{color:#162620;font-size:1rem}
    .cartx-checkout{margin-top:14px;display:block;text-align:center;background:linear-gradient(135deg,#0f3a2f,#2f6f5f);color:#fff;padding:13px 14px;border-radius:14px;text-decoration:none;font-weight:900;box-shadow:0 10px 18px rgba(16,58,47,.26)}
    .cartx-empty{padding:36px 18px;text-align:center}
    .cartx-empty i{font-size:2.2rem;color:#1f5f4f}
    .cartx-empty h2{margin:12px 0 6px;font-size:1.16rem;font-weight:900}
    .cartx-empty p{margin:0 0 16px;color:#6a645a;font-weight:700}
    @media (max-width:991.98px){.cartx-grid{grid-template-columns:1fr}.cartx-summary{position:static}}
    @media (max-width:767.98px){.cartx-page{padding-bottom:94px}.cartx-hero{border-radius:22px;padding:16px}.cartx-title{font-size:1.26rem}.cartx-sub{font-size:.84rem}.cartx-actions{display:grid;grid-template-columns:1fr 1fr}.cartx-btn{width:100%;min-height:42px;font-size:.8rem}.cartx-item{grid-template-columns:1fr;padding:12px}}
    @media (max-width:390px){.cartx-actions{grid-template-columns:1fr}}
</style>

<div class="cartx-page">
    <section class="cartx-hero">
        <span class="cartx-kicker"><span class="dot"></span>{{ __('cart.shopping_cart') }}</span>
        <h1 class="cartx-title">{{ __('cart.review_order_before_continue') }}</h1>
        <p class="cartx-sub">{{ $restaurantName }} • {{ $cartCount }} {{ $cartCount == 1 ? __('cart.product_singular') : __('cart.product_plural') }} {{ __('cart.inside_cart') }}</p>
        <div class="cartx-actions">
            <a href="{{ url()->previous() }}" class="cartx-btn cartx-btn-soft">{{ __('cart.continue_browsing') }}</a>
            <a href="{{ route('checkout.method') }}" class="cartx-btn cartx-btn-main">{{ __('cart.complete_order') }}</a>
        </div>
    </section>

    @if(empty($cart))
        <div class="cartx-card cartx-empty">
            <i class="bi bi-bag-x"></i>
            <h2>{{ __('cart.cart_is_empty_now') }}</h2>
            <p>{{ __('cart.empty_cart_message') }}</p>
            <a href="{{ url('/') }}" class="cartx-checkout" style="max-width:260px;margin:0 auto">{{ __('cart.browse_menu') }}</a>
        </div>
    @else
        <div class="cartx-grid">
            <div class="cartx-card">
                <div class="cartx-list">
                    @foreach($cart as $item)
                        <article class="cartx-item">
                            <div>
                                <h3 class="cartx-name">{{ $item['name'] }}</h3>
                                @if(!empty($item['selected_options']))
                                    <div class="cartx-options">
                                        @foreach($item['selected_options'] as $option)
                                            <div class="cartx-option">
                                                {{ $option['group_name'] }}: {{ $option['item_name'] }}
                                                @if(($option['price'] ?? 0) > 0)
                                                    (+{{ number_format($option['price'], 2) }} {{ __('cart.currency_egp') }})
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="cartx-meta">
                                    <form action="{{ route('cart.update', $item['cart_key']) }}" method="POST" class="cartx-qty">
                                        @csrf
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1">
                                        <button type="submit" class="cartx-update">{{ __('cart.update_quantity') }}</button>
                                    </form>

                                    <form action="{{ route('cart.remove', $item['cart_key']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cartx-delete">{{ __('cart.delete') }}</button>
                                    </form>
                                </div>
                            </div>

                            <div>
                                <div class="cartx-price">{{ number_format($item['price'], 2) }} {{ __('cart.currency_egp') }}</div>
                                <div class="cartx-total mt-3">{{ __('cart.total') }}: <span>{{ number_format($item['total'], 2) }} {{ __('cart.currency_egp') }}</span></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="cartx-card cartx-summary">
                <h3>{{ __('cart.current_order_total') }}</h3>
                <div class="cartx-row"><span>{{ __('cart.product_plural') }}</span><span>{{ $cartCount }}</span></div>
                <div class="cartx-row"><span>{{ __('cart.total') }}</span><span>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</span></div>
                <div class="cartx-row"><strong>{{ __('cart.complete_order') }}</strong><strong>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</strong></div>
                <a href="{{ route('checkout.method') }}" class="cartx-checkout">{{ __('cart.complete_order') }}</a>
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
