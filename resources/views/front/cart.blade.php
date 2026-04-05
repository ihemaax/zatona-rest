@extends('layouts.app')

@section('content')
@php
    $restaurantName = $setting->restaurant_name ?? __('site.brand');
    $cartCount = count($cart ?? []);
    $subtotal = collect($cart ?? [])->sum(function ($item) {
        return $item['total'] ?? 0;
    });
@endphp

<style>
    .checkout-cart-page{
        max-width: 980px;
        margin: 0 auto;
        padding-bottom: 120px;
    }

    .checkout-cart-head{
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        border-radius: 24px;
        box-shadow: var(--fb-shadow);
        padding: 22px;
        margin-bottom: 18px;
    }

    .checkout-cart-kicker{
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #f4f0e8;
        color: #746d63;
        font-size: .75rem;
        font-weight: 900;
        margin-bottom: 10px;
        letter-spacing: .02em;
    }

    .checkout-cart-kicker .dot{
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }

    .checkout-cart-title{
        margin: 0 0 8px;
        font-size: 1.7rem;
        line-height: 1.2;
        font-weight: 900;
        color: var(--fb-text);
        letter-spacing: -.02em;
    }

    .checkout-cart-subtitle{
        margin: 0;
        color: var(--fb-muted);
        font-size: .94rem;
        line-height: 1.8;
        font-weight: 700;
    }

    .checkout-cart-actions{
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .checkout-cart-btn-soft,
    .checkout-cart-btn-main{
        min-height: 46px;
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        white-space: nowrap;
        transition: .2s ease;
        border: none;
    }

    .checkout-cart-btn-soft{
        background: #f5f0e8;
        color: var(--fb-text);
        border: 1px solid var(--fb-border);
    }

    .checkout-cart-btn-soft:hover{
        background: #ece4d8;
        color: var(--fb-text);
    }

    .checkout-cart-btn-main{
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
        box-shadow: 0 14px 28px rgba(111,127,95,.18);
    }

    .checkout-cart-btn-main:hover{
        color: #fff;
        opacity: .98;
    }

    .checkout-cart-card{
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        border-radius: 24px;
        box-shadow: var(--fb-shadow);
        padding: 16px;
    }

    .checkout-cart-empty{
        text-align: center;
        padding: 34px 18px;
    }

    .checkout-cart-empty-icon{
        width: 82px;
        height: 82px;
        margin: 0 auto 14px;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #fff4ea 0%, #f4e3d3 100%);
        font-size: 1.9rem;
    }

    .checkout-cart-empty-title{
        margin: 0 0 8px;
        font-size: 1.16rem;
        font-weight: 900;
        color: var(--fb-text);
    }

    .checkout-cart-empty-text{
        margin: 0 0 16px;
        color: var(--fb-muted);
        font-size: .9rem;
        line-height: 1.8;
        font-weight: 700;
    }

    .checkout-cart-list{
        display: grid;
        gap: 12px;
    }

    .checkout-cart-item{
        background: #fff;
        border: 1px solid #ece4db;
        border-radius: 20px;
        padding: 14px;
    }

    .checkout-cart-item-top{
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
    }

    .checkout-cart-item-name{
        margin: 0 0 6px;
        font-size: 1rem;
        font-weight: 900;
        color: var(--fb-text);
        line-height: 1.5;
    }

    .checkout-cart-item-price{
        color: var(--fb-primary);
        font-weight: 900;
        font-size: .92rem;
        white-space: nowrap;
    }

    .checkout-cart-options{
        background: #faf6f1;
        border: 1px solid #efe5d9;
        border-radius: 14px;
        padding: 10px 12px;
        margin-bottom: 12px;
    }

    .checkout-cart-option{
        color: var(--fb-muted);
        font-size: .82rem;
        line-height: 1.7;
        font-weight: 700;
    }

    .checkout-cart-option + .checkout-cart-option{
        margin-top: 4px;
    }

    .checkout-cart-item-bottom{
        display: grid;
        gap: 12px;
    }

    .checkout-cart-qty-form{
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .checkout-cart-qty-input{
        width: 96px;
        border: 1px solid #ded3c7;
        background: #fcfaf7;
        border-radius: 12px;
        padding: 10px 12px;
        outline: none;
        color: var(--fb-text);
        font-weight: 800;
        text-align: center;
    }

    .checkout-cart-qty-input:focus{
        border-color: #c6d2ba;
        box-shadow: 0 0 0 4px rgba(111,127,95,.08);
        background: #fff;
    }

    .checkout-cart-update-btn{
        border: none;
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 900;
        font-size: .82rem;
        background: #ece4db;
        color: var(--fb-text);
    }

    .checkout-cart-update-btn:hover{
        background: #e4d8ca;
    }

    .checkout-cart-item-meta{
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .checkout-cart-total{
        font-size: .95rem;
        font-weight: 900;
        color: var(--fb-text);
    }

    .checkout-cart-total span{
        color: var(--fb-primary);
    }

    .checkout-cart-delete-form{
        margin: 0;
    }

    .checkout-cart-delete-btn{
        border: none;
        border-radius: 12px;
        padding: 10px 14px;
        background: #fff1f1;
        color: #b42318;
        font-weight: 900;
        font-size: .82rem;
    }

    .checkout-cart-delete-btn:hover{
        background: #f9dede;
        color: #991b1b;
    }

    .checkout-cart-summary{
        position: sticky;
        bottom: 14px;
        z-index: 20;
        margin-top: 16px;
    }

    .checkout-cart-summary-inner{
        background: rgba(255,253,249,.98);
        border: 1px solid var(--fb-border);
        box-shadow: 0 18px 40px rgba(60,52,40,.14);
        border-radius: 18px;
        padding: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .checkout-cart-summary-label{
        font-size: .76rem;
        color: var(--fb-muted);
        font-weight: 800;
        margin-bottom: 2px;
    }

    .checkout-cart-summary-value{
        font-size: 1rem;
        font-weight: 900;
        color: var(--fb-text);
    }

    .checkout-cart-summary-value span{
        color: var(--fb-primary);
    }

    .checkout-cart-summary-btn{
        border: none;
        border-radius: 14px;
        padding: 12px 16px;
        font-weight: 900;
        font-size: .86rem;
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 12px 24px rgba(111,127,95,.18);
    }

    .checkout-cart-summary-btn:hover{
        color: #fff;
        opacity: .98;
    }

    @media (min-width: 768px){
        .checkout-cart-item-bottom{
            grid-template-columns: 1fr auto;
            align-items: end;
        }

        .checkout-cart-summary-inner{
            padding: 14px 16px;
        }
    }

    @media (max-width: 767.98px){
        .checkout-cart-page{
            padding-bottom: 100px;
        }

        .checkout-cart-head{
            padding: 16px;
            border-radius: 20px;
        }

        .checkout-cart-title{
            font-size: 1.2rem;
            line-height: 1.3;
        }

        .checkout-cart-subtitle{
            font-size: .84rem;
            line-height: 1.75;
        }

        .checkout-cart-kicker{
            font-size: .68rem;
            padding: 6px 10px;
            margin-bottom: 8px;
        }

        .checkout-cart-actions{
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .checkout-cart-btn-soft,
        .checkout-cart-btn-main{
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            font-size: .8rem;
        }

        .checkout-cart-card{
            border-radius: 20px;
            padding: 12px;
        }

        .checkout-cart-item{
            border-radius: 16px;
            padding: 12px;
        }

        .checkout-cart-item-name{
            font-size: .9rem;
        }

        .checkout-cart-item-price{
            font-size: .84rem;
        }

        .checkout-cart-option{
            font-size: .76rem;
        }

        .checkout-cart-update-btn,
        .checkout-cart-delete-btn{
            font-size: .76rem;
        }

        .checkout-cart-summary{
            bottom: 10px;
        }

        .checkout-cart-summary-inner{
            border-radius: 16px;
        }

        .checkout-cart-summary-value{
            font-size: .88rem;
        }

        .checkout-cart-summary-btn{
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }

    @media (max-width: 390px){
        .checkout-cart-actions{
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="checkout-cart-page">
    <div class="checkout-cart-head">
        <span class="checkout-cart-kicker">
            <span class="dot"></span>
            {{ __('cart.shopping_cart') }}
        </span>

        <h1 class="checkout-cart-title">{{ __('cart.review_order_before_continue') }}</h1>
        <p class="checkout-cart-subtitle">
            {{ $restaurantName }} • {{ $cartCount }}
            {{ $cartCount == 1 ? __('cart.product_singular') : __('cart.product_plural') }}
            {{ __('cart.inside_cart') }}
        </p>

        <div class="checkout-cart-actions">
            <a href="{{ url()->previous() }}" class="checkout-cart-btn-soft">{{ __('cart.continue_browsing') }}</a>
            <a href="{{ route('checkout.method') }}" class="checkout-cart-btn-main">{{ __('cart.complete_order') }}</a>
        </div>
    </div>

    @if(empty($cart))
        <div class="checkout-cart-card checkout-cart-empty">
            <div class="checkout-cart-empty-icon">🍽️</div>
            <h2 class="checkout-cart-empty-title">{{ __('cart.cart_is_empty_now') }}</h2>
            <p class="checkout-cart-empty-text">
                {{ __('cart.empty_cart_message') }}
            </p>

            <a href="{{ url('/') }}" class="checkout-cart-btn-main" style="display:inline-flex; width:auto;">
                {{ __('cart.browse_menu') }}
            </a>
        </div>
    @else
        <div class="checkout-cart-card">
            <div class="checkout-cart-list">
                @foreach($cart as $item)
                    <article class="checkout-cart-item">
                        <div class="checkout-cart-item-top">
                            <div>
                                <h3 class="checkout-cart-item-name">{{ $item['name'] }}</h3>

                                @if(!empty($item['selected_options']))
                                    <div class="checkout-cart-options">
                                        @foreach($item['selected_options'] as $option)
                                            <div class="checkout-cart-option">
                                                {{ $option['group_name'] }}:
                                                {{ $option['item_name'] }}
                                                @if(($option['price'] ?? 0) > 0)
                                                    (+{{ number_format($option['price'], 2) }} {{ __('cart.currency_egp') }})
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="checkout-cart-item-price">
                                {{ number_format($item['price'], 2) }} {{ __('cart.currency_egp') }}
                            </div>
                        </div>

                        <div class="checkout-cart-item-bottom">
                            <form action="{{ route('cart.update', $item['cart_key']) }}" method="POST" class="checkout-cart-qty-form">
                                @csrf
                                <input
                                    type="number"
                                    name="quantity"
                                    value="{{ $item['quantity'] }}"
                                    min="1"
                                    class="checkout-cart-qty-input"
                                >
                                <button type="submit" class="checkout-cart-update-btn">{{ __('cart.update_quantity') }}</button>
                            </form>

                            <div class="checkout-cart-item-meta">
                                <div class="checkout-cart-total">
                                    {{ __('cart.total') }}: <span>{{ number_format($item['total'], 2) }} {{ __('cart.currency_egp') }}</span>
                                </div>

                                <form action="{{ route('cart.remove', $item['cart_key']) }}" method="POST" class="checkout-cart-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="checkout-cart-delete-btn">{{ __('cart.delete') }}</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="checkout-cart-summary">
            <div class="checkout-cart-summary-inner">
                <div>
                    <div class="checkout-cart-summary-label">{{ __('cart.current_order_total') }}</div>
                    <div class="checkout-cart-summary-value">
                        {{ $cartCount }} {{ $cartCount == 1 ? __('cart.product_singular') : __('cart.product_plural') }} •
                        <span>{{ number_format($subtotal, 2) }} {{ __('cart.currency_egp') }}</span>
                    </div>
                </div>

                <a href="{{ route('checkout.method') }}" class="checkout-cart-summary-btn">
                    {{ __('cart.complete_order') }}
                </a>
            </div>
        </div>
    @endif
</div>
@endsection