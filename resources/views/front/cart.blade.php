@extends('layouts.app')

@section('content')
@php
    $restaurantName = $setting->restaurant_name ?? __('site.brand');
    $cartCount = count($cart ?? []);
    $subtotal = collect($cart ?? [])->sum(fn ($item) => $item['total'] ?? 0);
@endphp

<style>
    .cartx{max-width:1240px;margin:0 auto;padding:14px 14px 120px}
    .cartx-head{background:linear-gradient(135deg,#103f33,#2e725f);color:#fff;border-radius:24px;padding:20px;border:1px solid rgba(255,255,255,.2);box-shadow:0 18px 34px rgba(17,57,47,.24)}
    .cartx-head h1{margin:10px 0 6px;font-size:1.6rem;font-weight:900}
    .cartx-head p{margin:0;color:#e8f2ee;font-weight:700}
    .cartx-head-actions{margin-top:14px;display:flex;gap:10px;flex-wrap:wrap}
    .cartx-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:12px;min-height:44px;padding:10px 14px;font-weight:900}
    .cartx-btn-alt{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.35)}
    .cartx-btn-main{background:#f4e2c5;color:#18322b}

    .cartx-layout{margin-top:16px;display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:16px;align-items:start}
    .cartx-list{display:grid;gap:12px}
    .cartx-item{background:#fffdf8;border:1px solid #e5d8c4;border-radius:18px;padding:14px;box-shadow:0 8px 18px rgba(18,39,32,.08);display:grid;grid-template-columns:minmax(0,1fr) auto;gap:14px}
    .cartx-item h4{margin:0 0 4px;font-size:1.02rem;font-weight:900;color:#172922}
    .cartx-price{font-weight:900;color:#1f6554;font-size:.86rem}
    .cartx-options{margin-top:8px;background:#f8f1e5;border:1px solid #ebdfcc;border-radius:12px;padding:9px;display:grid;gap:4px}
    .cartx-options span{font-size:.79rem;color:#645c50;font-weight:700}
    .cartx-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:10px}
    .cartx-actions input{width:84px;border:1px solid #dccab0;border-radius:10px;padding:8px 10px;font-weight:900;text-align:center}
    .cartx-update,.cartx-delete{border:none;border-radius:10px;padding:8px 12px;font-size:.76rem;font-weight:900}
    .cartx-update{background:#efe4d2;color:#22332e}
    .cartx-delete{background:#fff1f1;color:#a03535}
    .cartx-total{text-align:end;display:grid;gap:6px;align-content:start}
    .cartx-total small{font-size:.74rem;color:#7d7467;font-weight:800}
    .cartx-total strong{font-size:.92rem;font-weight:900;color:#163c31}

    .cartx-summary{position:sticky;top:88px;background:#fffdf8;border:1px solid #e5d8c4;border-radius:18px;padding:14px;box-shadow:0 8px 18px rgba(18,39,32,.08)}
    .cartx-summary h3{margin:0 0 8px;font-size:1rem;font-weight:900}
    .cartx-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px dashed #eadfce;font-size:.86rem;font-weight:800;color:#615a50}
    .cartx-row strong{color:#162b24}
    .cartx-cta{display:block;text-decoration:none;text-align:center;margin-top:12px;border-radius:12px;padding:12px;background:linear-gradient(135deg,#103f33,#2e725f);color:#fff;font-weight:900}

    .cartx-empty{max-width:720px;margin:24px auto;background:#fffdf8;border:1px solid #e5d8c4;border-radius:24px;padding:34px 20px;text-align:center;box-shadow:0 8px 18px rgba(18,39,32,.08)}

    @media (max-width:991.98px){.cartx-layout{grid-template-columns:1fr}.cartx-summary{position:static}}
    @media (max-width:767.98px){.cartx{padding:10px 10px calc(var(--mobile-bar-h) + env(safe-area-inset-bottom, 0px) + 28px)}.cartx-head{border-radius:18px;padding:15px}.cartx-head h1{font-size:1.25rem}.cartx-head-actions{display:grid;grid-template-columns:1fr 1fr}.cartx-item{grid-template-columns:1fr}.cartx-total{text-align:start}}
</style>

<div class="cartx">
    @if(empty($cart))
        <div class="cartx-empty">
            <div style="font-size:2rem">🛒</div>
            <h2>{{ __('cart.cart_is_empty_now') }}</h2>
            <p>{{ __('cart.empty_cart_message') }}</p>
            <a href="{{ url('/') }}" class="cartx-cta" style="max-width:260px;margin:0 auto">{{ __('cart.browse_menu') }}</a>
        </div>
    @else
        <section class="cartx-head">
            <small>{{ __('cart.shopping_cart') }}</small>
            <h1>{{ __('cart.review_order_before_continue') }}</h1>
            <p>{{ $restaurantName }} • {{ $cartCount }} {{ $cartCount == 1 ? __('cart.product_singular') : __('cart.product_plural') }}</p>
            <div class="cartx-head-actions">
                <a href="{{ url()->previous() }}" class="cartx-btn cartx-btn-alt">{{ __('cart.continue_browsing') }}</a>
                <a href="{{ route('checkout.method') }}" class="cartx-btn cartx-btn-main">{{ __('cart.complete_order') }}</a>
            </div>
        </section>

        <section class="cartx-layout">
            <div class="cartx-list">
                @foreach($cart as $item)
                    <article class="cartx-item">
                        <div>
                            <h4>{{ $item['name'] }}</h4>
                            <div class="cartx-price">{{ number_format($item['price'], 2) }} {{ __('cart.currency_egp') }}</div>
                            @if(!empty($item['selected_options']))
                                <div class="cartx-options">
                                    @foreach($item['selected_options'] as $option)
                                        <span>{{ $option['group_name'] }}: {{ $option['item_name'] }} @if(($option['price'] ?? 0) > 0) (+{{ number_format($option['price'], 2) }} {{ __('cart.currency_egp') }}) @endif</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="cartx-actions">
                                <form action="{{ route('cart.update', $item['cart_key']) }}" method="POST" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
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
                        <div class="cartx-total"><small>{{ __('cart.total') }}</small><strong>{{ number_format($item['total'], 2) }} {{ __('cart.currency_egp') }}</strong></div>
                    </article>
                @endforeach
            </div>

            <aside class="cartx-summary">
                <h3>{{ __('cart.complete_order') }}</h3>
                <div class="cartx-row"><span>{{ __('cart.product_plural') }}</span><span>{{ $cartCount }}</span></div>
                <div class="cartx-row"><span>{{ __('cart.total') }}</span><span>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</span></div>
                <div class="cartx-row"><strong>{{ __('cart.complete_order') }}</strong><strong>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</strong></div>
                <a href="{{ route('checkout.method') }}" class="cartx-cta">{{ __('cart.complete_order') }}</a>
            </aside>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({event:'view_cart',cart_count:{{ (int) $cartCount }},value:{{ (float) $subtotal }},currency:@json(__('cart.currency_egp'))});
</script>
@endpush
