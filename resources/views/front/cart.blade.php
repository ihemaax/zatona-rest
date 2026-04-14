@extends('layouts.app')

@section('content')
@php
    $restaurantName = $setting->restaurant_name ?? __('site.brand');
    $cartCount = count($cart ?? []);
    $subtotal = collect($cart ?? [])->sum(fn ($item) => $item['total'] ?? 0);
@endphp

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
                @featureEnabled('checkout')
                <a href="{{ route('checkout.method') }}" class="cartx-btn cartx-btn-main">{{ __('cart.complete_order') }}</a>
                @else
                <button type="button" class="cartx-btn cartx-btn-main" disabled title="{{ config('subscription.blocked_message') }}">{{ __('cart.complete_order') }}</button>
                @endfeatureEnabled
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
                            @if(!empty($item['notes']))
                                <div class="cartx-options">
                                    <span><strong>ملاحظات:</strong> {{ $item['notes'] }}</span>
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
                @featureEnabled('checkout')
                <a href="{{ route('checkout.method') }}" class="cartx-cta">{{ __('cart.complete_order') }}</a>
                @else
                <button type="button" class="cartx-cta" disabled title="{{ config('subscription.blocked_message') }}">{{ __('cart.complete_order') }}</button>
                @endfeatureEnabled
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
