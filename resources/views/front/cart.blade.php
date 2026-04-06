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
    .cartnova-wrap{max-width:1240px;margin:0 auto;padding-bottom:110px}
    .cartnova-shell{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:20px;align-items:start}
    .cartnova-main{background:#0f172a;border-radius:30px;padding:24px;border:1px solid #1e293b;box-shadow:0 24px 60px rgba(15,23,42,.35);color:#e2e8f0}
    .cartnova-head{display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;padding-bottom:18px;border-bottom:1px solid rgba(148,163,184,.28);margin-bottom:18px}
    .cartnova-title h1{margin:0;font-size:2rem;font-weight:900;letter-spacing:-.02em;color:#fff}
    .cartnova-title p{margin:8px 0 0;font-size:.92rem;color:#cbd5e1;font-weight:700}
    .cartnova-pills{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start}
    .cartnova-pill{background:#111827;border:1px solid #334155;border-radius:999px;padding:8px 12px;font-size:.75rem;font-weight:800;color:#cbd5e1}
    .cartnova-list{display:grid;gap:12px}
    .cartnova-item{background:linear-gradient(180deg,#0b1220,#0a101d);border:1px solid #24324a;border-radius:22px;padding:16px;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px}
    .cartnova-name{margin:0 0 6px;font-size:1.08rem;font-weight:900;color:#f8fafc}
    .cartnova-base{font-size:.82rem;font-weight:800;color:#93c5fd}
    .cartnova-options{margin-top:12px;background:#10192d;border:1px solid #2d3d5b;border-radius:12px;padding:10px;display:grid;gap:6px}
    .cartnova-option{font-size:.8rem;color:#dbeafe;font-weight:700}
    .cartnova-controls{margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .cartnova-controls input{width:88px;border-radius:11px;border:1px solid #334155;background:#0b1324;color:#f8fafc;padding:8px 10px;text-align:center;font-weight:900}
    .cartnova-btn{border:none;border-radius:11px;padding:9px 12px;font-size:.78rem;font-weight:900}
    .cartnova-update{background:#dbeafe;color:#102040}
    .cartnova-delete{background:#fee2e2;color:#7f1d1d}
    .cartnova-prices{display:grid;gap:8px;align-content:start;justify-items:end;text-align:end}
    .cartnova-price{font-size:.82rem;font-weight:800;color:#bfdbfe}
    .cartnova-total{font-size:.95rem;font-weight:900;color:#f8fafc;background:#111b2f;border:1px solid #324566;border-radius:10px;padding:8px 12px}

    .cartnova-side{position:sticky;top:90px;background:#f8fafc;border:1px solid #dbe5f1;border-radius:24px;padding:18px;box-shadow:0 12px 30px rgba(15,23,42,.12)}
    .cartnova-side h3{margin:0;font-size:1.05rem;font-weight:900;color:#0f172a}
    .cartnova-rows{margin-top:14px;display:grid;gap:10px}
    .cartnova-row{display:flex;justify-content:space-between;gap:10px;font-size:.86rem;font-weight:800;color:#334155;padding-bottom:8px;border-bottom:1px dashed #cbd5e1}
    .cartnova-row strong{font-size:1rem;color:#0f172a}
    .cartnova-action{margin-top:12px;display:block;text-decoration:none;text-align:center;padding:12px;border-radius:13px;font-weight:900;background:#2563eb;color:#fff}
    .cartnova-secondary{margin-top:8px;display:block;text-decoration:none;text-align:center;padding:11px;border-radius:13px;font-weight:900;background:#eef2ff;color:#312e81;border:1px solid #c7d2fe}

    .cartnova-empty{background:#fff;border:1px solid #e2e8f0;border-radius:26px;padding:40px 20px;text-align:center;box-shadow:0 14px 32px rgba(15,23,42,.08)}
    .cartnova-empty h2{margin:10px 0 8px;font-size:1.2rem;font-weight:900;color:#0f172a}
    .cartnova-empty p{margin:0 0 14px;color:#64748b;font-weight:700}

    @media (max-width:991.98px){.cartnova-shell{grid-template-columns:1fr}.cartnova-side{position:static}}
    @media (max-width:767.98px){.cartnova-wrap{padding-bottom:95px}.cartnova-main{padding:16px;border-radius:22px}.cartnova-title h1{font-size:1.3rem}.cartnova-item{grid-template-columns:1fr;padding:12px}.cartnova-prices{justify-items:start;text-align:start}}
</style>

<div class="cartnova-wrap">
    @if(empty($cart))
        <div class="cartnova-empty">
            <div style="font-size:2.4rem">🛍️</div>
            <h2>{{ __('cart.cart_is_empty_now') }}</h2>
            <p>{{ __('cart.empty_cart_message') }}</p>
            <a href="{{ url('/') }}" class="cartnova-action" style="max-width:260px;margin:0 auto">{{ __('cart.browse_menu') }}</a>
        </div>
    @else
        <div class="cartnova-shell">
            <section class="cartnova-main">
                <header class="cartnova-head">
                    <div class="cartnova-title">
                        <h1>{{ __('cart.shopping_cart') }}</h1>
                        <p>{{ $restaurantName }} · {{ $cartCount }} {{ $cartCount == 1 ? __('cart.product_singular') : __('cart.product_plural') }}</p>
                    </div>
                    <div class="cartnova-pills">
                        <span class="cartnova-pill">{{ __('cart.review_order_before_continue') }}</span>
                        <span class="cartnova-pill">{{ __('cart.total') }}: {{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</span>
                    </div>
                </header>

                <div class="cartnova-list">
                    @foreach($cart as $item)
                        <article class="cartnova-item">
                            <div>
                                <h3 class="cartnova-name">{{ $item['name'] }}</h3>
                                <div class="cartnova-base">{{ number_format($item['price'], 2) }} {{ __('cart.currency_egp') }} / {{ __('cart.product_singular') }}</div>

                                @if(!empty($item['selected_options']))
                                    <div class="cartnova-options">
                                        @foreach($item['selected_options'] as $option)
                                            <div class="cartnova-option">
                                                • {{ $option['group_name'] }}: {{ $option['item_name'] }}
                                                @if(($option['price'] ?? 0) > 0)
                                                    (+{{ number_format($option['price'], 2) }} {{ __('cart.currency_egp') }})
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="cartnova-controls">
                                    <form action="{{ route('cart.update', $item['cart_key']) }}" method="POST" style="display:flex;gap:8px;flex-wrap:wrap">
                                        @csrf
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1">
                                        <button type="submit" class="cartnova-btn cartnova-update">{{ __('cart.update_quantity') }}</button>
                                    </form>

                                    <form action="{{ route('cart.remove', $item['cart_key']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cartnova-btn cartnova-delete">{{ __('cart.delete') }}</button>
                                    </form>
                                </div>
                            </div>

                            <div class="cartnova-prices">
                                <div class="cartnova-price">{{ __('cart.total') }}</div>
                                <div class="cartnova-total">{{ number_format($item['total'], 2) }} {{ __('cart.currency_egp') }}</div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <aside class="cartnova-side">
                <h3>{{ __('cart.current_order_total') }}</h3>
                <div class="cartnova-rows">
                    <div class="cartnova-row"><span>{{ __('cart.product_plural') }}</span><span>{{ $cartCount }}</span></div>
                    <div class="cartnova-row"><span>{{ __('cart.total') }}</span><span>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</span></div>
                    <div class="cartnova-row"><strong>{{ __('cart.complete_order') }}</strong><strong>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</strong></div>
                </div>
                <a href="{{ route('checkout.method') }}" class="cartnova-action">{{ __('cart.complete_order') }}</a>
                <a href="{{ url()->previous() }}" class="cartnova-secondary">{{ __('cart.continue_browsing') }}</a>
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
