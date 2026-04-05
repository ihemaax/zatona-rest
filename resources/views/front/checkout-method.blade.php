@extends('layouts.app')

@section('content')
@php
    $cartCount = count(session('cart', []));
@endphp

<style>
    .checkout-method-page{
        max-width: 980px;
        margin: 0 auto;
        padding-bottom: 80px;
    }

    .checkout-method-head{
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        border-radius: 24px;
        box-shadow: var(--fb-shadow);
        padding: 22px;
        margin-bottom: 18px;
    }

    .checkout-method-kicker{
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

    .checkout-method-kicker .dot{
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }

    .checkout-method-title{
        margin: 0 0 8px;
        font-size: 1.7rem;
        line-height: 1.2;
        font-weight: 900;
        color: var(--fb-text);
        letter-spacing: -.02em;
    }

    .checkout-method-subtitle{
        margin: 0;
        color: var(--fb-muted);
        font-size: .94rem;
        line-height: 1.8;
        font-weight: 700;
    }

    .checkout-method-actions{
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .checkout-btn-soft,
    .checkout-btn-main{
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
    }

    .checkout-btn-soft{
        background: #f5f0e8;
        color: var(--fb-text);
        border: 1px solid var(--fb-border);
    }

    .checkout-btn-soft:hover{
        background: #ece4d8;
        color: var(--fb-text);
    }

    .checkout-btn-main{
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
        border: none;
        box-shadow: 0 14px 28px rgba(111,127,95,.18);
    }

    .checkout-btn-main:hover{
        color: #fff;
        opacity: .98;
    }

    .checkout-method-grid{
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 18px;
    }

    .checkout-method-card{
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        border-radius: 24px;
        box-shadow: var(--fb-shadow);
        padding: 22px;
        position: relative;
        overflow: hidden;
        transition: .2s ease;
    }

    .checkout-method-card:hover{
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(60,52,40,.10);
    }

    .checkout-method-card::before{
        content:"";
        position:absolute;
        inset-inline:0;
        top:0;
        height:4px;
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
    }

    .checkout-method-card.delivery::before{
        background: linear-gradient(135deg, #7d8d6d 0%, #9caf8d 100%);
    }

    .checkout-method-icon{
        width: 72px;
        height: 72px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.9rem;
        margin-bottom: 16px;
        background: linear-gradient(135deg, #fff4ea 0%, #f2e0cf 100%);
    }

    .checkout-method-card.delivery .checkout-method-icon{
        background: linear-gradient(135deg, #eef6ec 0%, #dfe9d7 100%);
    }

    .checkout-method-card-title{
        margin: 0 0 10px;
        font-size: 1.12rem;
        font-weight: 900;
        color: var(--fb-text);
        letter-spacing: -.01em;
    }

    .checkout-method-card-text{
        margin: 0 0 16px;
        color: var(--fb-muted);
        font-size: .92rem;
        line-height: 1.9;
        font-weight: 700;
    }

    .checkout-method-features{
        display: grid;
        gap: 9px;
        margin-bottom: 18px;
    }

    .checkout-method-feature{
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 13px;
        background: #faf6f1;
        border: 1px solid #efe5d9;
        color: #5f5449;
        font-size: .83rem;
        font-weight: 800;
    }

    .checkout-method-feature .dot{
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--fb-primary);
        flex-shrink: 0;
    }

    .checkout-method-card.delivery .checkout-method-feature .dot{
        background: #7d8d6d;
    }

    .checkout-method-submit{
        display: block;
        width: 100%;
        border: none;
        border-radius: 16px;
        padding: 13px 16px;
        text-align: center;
        text-decoration: none;
        font-size: .9rem;
        font-weight: 900;
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
        box-shadow: 0 14px 26px rgba(111,127,95,.16);
    }

    .checkout-method-submit:hover{
        color: #fff;
        opacity: .98;
    }

    .checkout-method-card.delivery .checkout-method-submit{
        background: linear-gradient(135deg, #7d8d6d 0%, #9caf8d 100%);
    }

    @media (max-width: 991.98px){
        .checkout-method-grid{
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px){
        .checkout-method-page{
            padding-bottom: 70px;
        }

        .checkout-method-head{
            padding: 16px;
            border-radius: 20px;
        }

        .checkout-method-title{
            font-size: 1.2rem;
            line-height: 1.3;
        }

        .checkout-method-subtitle{
            font-size: .84rem;
            line-height: 1.75;
        }

        .checkout-method-kicker{
            font-size: .68rem;
            padding: 6px 10px;
            margin-bottom: 8px;
        }

        .checkout-method-actions{
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .checkout-btn-soft,
        .checkout-btn-main{
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            font-size: .8rem;
        }

        .checkout-method-card{
            padding: 16px;
            border-radius: 20px;
        }

        .checkout-method-icon{
            width: 64px;
            height: 64px;
            border-radius: 18px;
            font-size: 1.6rem;
            margin-bottom: 14px;
        }

        .checkout-method-card-title{
            font-size: 1rem;
        }

        .checkout-method-card-text{
            font-size: .84rem;
            line-height: 1.8;
        }

        .checkout-method-feature{
            font-size: .77rem;
            line-height: 1.6;
        }

        .checkout-method-submit{
            font-size: .82rem;
            padding: 12px 14px;
        }
    }

    @media (max-width: 390px){
        .checkout-method-actions{
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="checkout-method-page">
    <div class="checkout-method-head">
        <span class="checkout-method-kicker">
            <span class="dot"></span>
            {{ __('checkout_method.receiving_method') }}
        </span>

        <h1 class="checkout-method-title">{{ __('checkout_method.choose_order_receiving_method') }}</h1>
        <p class="checkout-method-subtitle">
            {{ __('checkout_method.choose_pickup_or_delivery', ['count' => $cartCount]) }}
        </p>

        <div class="checkout-method-actions">
            <a href="{{ route('cart.index') }}" class="checkout-btn-soft">{{ __('checkout_method.back_to_cart') }}</a>
            <a href="{{ url('/') }}" class="checkout-btn-main">{{ __('checkout_method.continue_browsing') }}</a>
        </div>
    </div>

    <div class="checkout-method-grid">
        <div class="checkout-method-card">
            <div class="checkout-method-icon">🏪</div>

            <h3 class="checkout-method-card-title">{{ __('checkout_method.pickup_from_restaurant') }}</h3>
            <p class="checkout-method-card-text">
                {{ __('checkout_method.pickup_description') }}
            </p>

            <div class="checkout-method-features">
                <div class="checkout-method-feature"><span class="dot"></span>{{ __('checkout_method.pickup_feature_branch') }}</div>
                <div class="checkout-method-feature"><span class="dot"></span>{{ __('checkout_method.pickup_feature_fast') }}</div>
                <div class="checkout-method-feature"><span class="dot"></span>{{ __('checkout_method.pickup_feature_no_address') }}</div>
            </div>

            <a href="{{ route('checkout.index', ['order_type' => 'pickup']) }}" class="checkout-method-submit">
                {{ __('checkout_method.continue_pickup') }}
            </a>
        </div>

        <div class="checkout-method-card delivery">
            <div class="checkout-method-icon">🚚</div>

            <h3 class="checkout-method-card-title">{{ __('checkout_method.delivery_to_address') }}</h3>
            <p class="checkout-method-card-text">
                {{ __('checkout_method.delivery_description') }}
            </p>

            <div class="checkout-method-features">
                <div class="checkout-method-feature"><span class="dot"></span>{{ __('checkout_method.delivery_feature_address') }}</div>
                <div class="checkout-method-feature"><span class="dot"></span>{{ __('checkout_method.delivery_feature_home') }}</div>
                <div class="checkout-method-feature"><span class="dot"></span>{{ __('checkout_method.delivery_feature_easy') }}</div>
            </div>

            <a href="{{ route('checkout.index', ['order_type' => 'delivery']) }}" class="checkout-method-submit">
                {{ __('checkout_method.continue_delivery') }}
            </a>
        </div>
    </div>
</div>
@endsection