@extends('layouts.app')

@section('content')
@php
    $restaurantName = $setting->restaurant_name ?? __('site.brand');
    $restaurantPhone = $setting->restaurant_phone ?? null;
    $deliveryFee = $setting->delivery_fee ?? 0;
    $isOpen = ($setting && $setting->is_open);

    $cartCount = count(session('cart', []));
    $cartTotal = collect(session('cart', []))->sum(function ($item) {
        return ($item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1)));
    });

    $groupedProducts = $products->groupBy(function ($product) {
        return $product->category->name ?? __('home.menu');
    });

    $coverImage = $setting->cover_image ?? null;
    $logoImage = $setting->logo ?? null;

    $popupCampaign = \App\Models\PopupCampaign::query()
        ->where('is_active', true)
        ->latest()
        ->first();
@endphp

<style>
    .elite-home{
        max-width: 1240px;
        margin: 0 auto;
        padding-bottom: 120px;
    }

    .elite-hero-shell{
        margin-bottom: 20px;
    }

    .elite-hero-card{
        position: relative;
        overflow: hidden;
        border-radius: 0 0 30px 30px;
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        box-shadow: var(--fb-shadow-lg);
    }

    .elite-cover{
        position: relative;
        min-height: 360px;
        background:
            linear-gradient(180deg, rgba(20,20,18,.10), rgba(20,20,18,.30)),
            url('{{ $coverImage ? asset("storage/" . $coverImage) : "https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=1600&auto=format&fit=crop" }}') center/cover no-repeat;
    }

    .elite-cover::before{
        content:"";
        position:absolute;
        inset:0;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 26%),
            linear-gradient(180deg, rgba(20,20,18,.02), rgba(20,20,18,.18));
    }

    .elite-cover::after{
        content:"";
        position:absolute;
        inset:auto 0 0 0;
        height:140px;
        background:linear-gradient(to top, rgba(12,12,10,.24), transparent);
    }

    .elite-hero-content{
        position: relative;
        margin-top: -74px;
        padding: 0 24px 22px;
        z-index: 3;
    }

    .elite-identity-card{
        background: rgba(255,253,249,.96);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(233, 227, 216, .95);
        border-radius: 28px;
        box-shadow: 0 24px 60px rgba(60, 52, 40, .14);
        padding: 18px;
    }

    .elite-identity-top{
        display: grid;
        grid-template-columns: auto minmax(0,1fr) auto;
        gap: 18px;
        align-items: center;
    }

    .elite-logo-frame{
        width: 126px;
        height: 126px;
        border-radius: 50%;
        padding: 5px;
        background: linear-gradient(135deg, #ffffff 0%, #efe9de 45%, #d8d1c4 100%);
        box-shadow:
            0 16px 34px rgba(0,0,0,.10),
            0 0 0 1px rgba(255,255,255,.9) inset;
        position: relative;
        flex: 0 0 auto;
    }

    .elite-logo-frame::after{
        content:"";
        position:absolute;
        inset: 8px;
        border-radius:50%;
        border:1px solid rgba(255,255,255,.85);
        pointer-events:none;
    }

    .elite-logo{
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background:
            url('{{ $logoImage ? asset("storage/" . $logoImage) : "https://via.placeholder.com/500x500?text=Logo" }}') center/cover no-repeat,
            #fff;
        border: 4px solid #fff;
    }

    .elite-brand-copy{
        min-width: 0;
    }

    .elite-brand-kicker{
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

    .elite-brand-kicker .dot{
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }

    .elite-title{
        margin: 0 0 6px;
        font-size: 2rem;
        line-height: 1.15;
        font-weight: 900;
        color: var(--fb-text);
        letter-spacing: -.02em;
    }

    .elite-subtitle{
        margin: 0;
        color: var(--fb-muted);
        font-size: .95rem;
        line-height: 1.8;
        font-weight: 800;
    }

    .elite-actions{
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .elite-btn-primary,
    .elite-btn-secondary{
        min-height: 46px;
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: .2s ease;
        text-decoration: none;
    }

    .elite-btn-primary{
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
        box-shadow: 0 14px 28px rgba(111, 127, 95, .20);
        border: none;
    }

    .elite-btn-primary:hover{
        color: #fff;
        opacity: .98;
    }

    .elite-btn-secondary{
        background: #f5f0e8;
        color: var(--fb-text);
        border: 1px solid var(--fb-border);
    }

    .elite-btn-secondary:hover{
        background: #ece4d8;
        color: var(--fb-text);
    }

    .elite-meta-row{
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding-top: 16px;
        margin-top: 16px;
        border-top: 1px solid #ece5da;
    }

    .elite-pill{
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 14px;
        border-radius: 999px;
        background: #f8f5ef;
        border: 1px solid #e7e0d4;
        color: #5f5a52;
        font-size: .84rem;
        font-weight: 900;
    }

    .elite-pill.success{
        background: #edf8ef;
        color: #1f7a40;
        border-color: #d7ebdc;
    }

    .elite-pill .dot{
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }

    .elite-layout{
        display: grid;
        grid-template-columns: 300px minmax(0,1fr);
        gap: 18px;
        align-items: start;
    }

    .elite-sidebar,
    .elite-main{
        display: grid;
        gap: 18px;
    }

    .elite-card{
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        border-radius: 24px;
        box-shadow: var(--fb-shadow);
        overflow: hidden;
    }

    .elite-card-body{
        padding: 18px;
    }

    .elite-card-title{
        margin: 0 0 14px;
        font-size: 1.02rem;
        font-weight: 900;
        color: var(--fb-text);
    }

    .elite-message{
        position: relative;
        border-radius: 20px;
        padding: 18px;
        background: linear-gradient(135deg, #7e8b70 0%, #a6b595 100%);
        color: #fff;
        box-shadow: 0 16px 34px rgba(111, 127, 95, .18);
        overflow: hidden;
    }

    .elite-message::after{
        content:"";
        position:absolute;
        top:-40px;
        right:-20px;
        width:120px;
        height:120px;
        border-radius:50%;
        background: rgba(255,255,255,.10);
    }

    .elite-message strong{
        position: relative;
        display:block;
        font-size: 1rem;
        line-height: 1.9;
        z-index: 2;
    }

    .elite-info-list{
        display: grid;
        gap: 10px;
    }

    .elite-info-item{
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #4d4a45;
        font-size: .92rem;
        line-height: 1.75;
        font-weight: 700;
    }

    .elite-info-icon{
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: #f3efe8;
        color: #6f7f5f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 1rem;
    }

    .elite-search-wrap{
        padding: 14px;
    }

    .elite-search{
        position: relative;
    }

    .elite-search svg{
        position: absolute;
        top: 50%;
        inset-inline-start: 14px;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        stroke: #98a2b3;
        pointer-events: none;
    }

    .elite-search input{
        width: 100%;
        border: 1px solid #ddd7cc;
        background: #faf7f2;
        border-radius: 999px;
        padding: 14px 16px 14px 46px;
        color: var(--fb-text);
        outline: none;
        font-size: .95rem;
        font-weight: 800;
    }

    .elite-search input:focus{
        background: #fff;
        border-color: #c6d2ba;
        box-shadow: 0 0 0 4px rgba(111, 127, 95, .10);
    }

    .elite-alert{
        display: none;
        margin-top: 12px;
    }

    .elite-alert-box{
        border-radius: 14px;
        padding: 12px 14px;
        font-size: .9rem;
        font-weight: 900;
        border: 1px solid transparent;
    }

    .elite-alert-box.success{
        background: #edf8ef;
        color: #166534;
        border-color: #cce8d3;
    }

    .elite-alert-box.error{
        background: #fff1f1;
        color: #991b1b;
        border-color: #f3cdcd;
    }

    .elite-categories{
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }

    .elite-categories::-webkit-scrollbar{
        display:none;
    }

    .elite-cat{
        border: none;
        background: transparent;
        padding: 0;
        min-width: 92px;
        width: 92px;
        text-align: center;
        cursor: pointer;
    }

    .elite-cat-ring{
        width: 92px;
        height: 92px;
        border-radius: 50%;
        padding: 3px;
        background: linear-gradient(135deg, #ddd8cf 0%, #f3efe8 100%);
        margin: 0 auto 8px;
        transition: .2s ease;
        box-shadow: 0 10px 20px rgba(60, 52, 40, .05);
    }

    .elite-cat-inner{
        width: 100%;
        height: 100%;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #fff;
        background: #f2eee7;
    }

    .elite-cat-inner img{
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .elite-cat-label{
        color: #615d55;
        font-size: .8rem;
        font-weight: 900;
        line-height: 1.35;
    }

    .elite-cat.active .elite-cat-ring{
        background: linear-gradient(135deg, #7d8d6d 0%, #b6c4a6 100%);
        transform: translateY(-2px);
        box-shadow: 0 14px 24px rgba(111,127,95,.16);
    }

    .elite-cat.active .elite-cat-label{
        color: var(--fb-primary);
    }

    .elite-feed{
        display: grid;
        gap: 18px;
    }

    .elite-section{
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        border-radius: 24px;
        box-shadow: var(--fb-shadow);
        overflow: hidden;
    }

    .elite-section-head{
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding: 18px 18px 10px;
    }

    .elite-section-title{
        margin: 0;
        font-size: 1.06rem;
        font-weight: 900;
        color: var(--fb-text);
    }

    .elite-section-count{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 999px;
        background: #f3efe8;
        color: #7a7469;
        font-size: .76rem;
        font-weight: 900;
    }

    .elite-products{
        display: grid;
        gap: 14px;
        padding: 0 18px 18px;
    }

    .elite-product{
        position: relative;
        display: grid;
        grid-template-columns: 180px minmax(0,1fr);
        gap: 14px;
        align-items: stretch;
        background: linear-gradient(180deg, #fffdfa 0%, #fcf9f4 100%);
        border: 1px solid #ece5da;
        border-radius: 22px;
        overflow: hidden;
        transition: .22s ease;
        box-shadow: 0 10px 24px rgba(60, 52, 40, .05);
    }

    .elite-product:hover{
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(60, 52, 40, .10);
    }

    .elite-product-media{
        position: relative;
        height: 100%;
    }

    .elite-product-image{
        width: 100%;
        height: 100%;
        min-height: 188px;
        object-fit: cover;
        display: block;
        background: #f1ece4;
    }

    .elite-product-badge{
        position: absolute;
        top: 12px;
        inset-inline-start: 12px;
        background: rgba(25,25,22,.72);
        color: #fff;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: .68rem;
        font-weight: 900;
        backdrop-filter: blur(10px);
    }

    .elite-product-body{
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 16px 16px 16px 0;
    }

    .elite-product-top{
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .elite-product-name{
        margin: 0;
        font-size: 1.02rem;
        font-weight: 900;
        color: var(--fb-text);
        line-height: 1.5;
        letter-spacing: -.01em;
    }

    .elite-product-category{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #edf2e7;
        color: #6f7f5f;
        font-size: .68rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .elite-product-desc{
        margin: 0;
        color: #6f6a61;
        font-size: .84rem;
        line-height: 1.8;
        font-weight: 700;
    }

    .elite-product-bottom{
        margin-top: auto;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .elite-price{
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .elite-price-label{
        font-size: .72rem;
        color: #8a847a;
        font-weight: 800;
    }

    .elite-price-value{
        font-size: 1.08rem;
        font-weight: 900;
        color: var(--fb-text);
    }

    .elite-add-btn{
        min-width: 132px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
        padding: 11px 16px;
        font-size: .82rem;
        font-weight: 900;
        box-shadow: 0 10px 22px rgba(111, 127, 95, .16);
    }

    .elite-add-btn:hover{
        color: #fff;
        opacity: .98;
    }

    .elite-empty{
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        border-radius: 22px;
        box-shadow: var(--fb-shadow);
        padding: 28px 18px;
        text-align: center;
        color: #6f6a61;
        font-weight: 800;
    }

    .elite-floating-cart{
        position: fixed;
        inset-inline: 14px;
        bottom: 14px;
        z-index: 1050;
        display: flex;
        justify-content: center;
        pointer-events: none;
    }

    .elite-floating-cart-inner{
        width: 100%;
        max-width: 440px;
        background: rgba(255,253,249,.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(233,227,216,.95);
        box-shadow: var(--fb-shadow-lg);
        border-radius: 18px;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        pointer-events: auto;
    }

    .elite-floating-cart-label{
        color: #7a7469;
        font-size: .74rem;
        font-weight: 800;
        margin-bottom: 3px;
    }

    .elite-floating-cart-value{
        color: var(--fb-text);
        font-size: .9rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .elite-floating-cart-btn{
        border: none;
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
        border-radius: 14px;
        padding: 12px 16px;
        font-weight: 900;
        font-size: .84rem;
        white-space: nowrap;
        text-decoration: none;
    }

    .elite-floating-cart-btn:hover{
        color: #fff;
        opacity: .98;
    }

    .offer-popup-overlay{
        position: fixed;
        inset: 0;
        background: rgba(15, 18, 12, .58);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        z-index: 999999;
    }

    .offer-popup-overlay.show{
        display: flex;
    }

    .offer-popup-card{
        width: 100%;
        max-width: 440px;
        background: #fff;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(0,0,0,.28);
        border: 1px solid rgba(233,227,216,.95);
    }

    .offer-popup-image{
        width: 100%;
        height: 320px;
        object-fit: cover;
        display: block;
        background: #f2eee8;
    }

    .offer-popup-body{
        padding: 20px;
        text-align: center;
    }

    .offer-popup-title{
        margin: 0 0 8px;
        font-size: 1.28rem;
        font-weight: 900;
        color: #1f1a16;
        line-height: 1.35;
    }

    .offer-popup-desc{
        color: #6f6a61;
        font-size: .95rem;
        line-height: 1.9;
        margin-bottom: 16px;
        font-weight: 700;
    }

    .offer-popup-btn{
        display: block;
        width: 100%;
        text-decoration: none;
        border: none;
        border-radius: 14px;
        padding: 13px 16px;
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
        font-weight: 900;
        margin-bottom: 10px;
    }

    .offer-popup-close{
        display: block;
        width: 100%;
        border: none;
        border-radius: 14px;
        padding: 12px 16px;
        background: #f3f4f6;
        color: #111827;
        font-weight: 800;
    }

    .quick-modal .modal-content{
        border: none;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(15,23,42,.18);
    }

    .quick-modal .modal-header{
        padding: 18px 18px 0;
    }

    .quick-modal .modal-body{
        padding: 18px;
    }

    .quick-modal .modal-footer{
        padding: 0 18px 18px;
    }

    .quick-product-media{
        border: 1px solid #e7ede1;
        border-radius: 18px;
        padding: 8px;
        background: #f8faf6;
    }

    .quick-product-media img{
        width: 100%;
        max-height: 280px;
        object-fit: cover;
        border-radius: 14px;
        display: block;
    }

    .quick-product-name{
        font-size: 1.18rem;
        font-weight: 900;
        color: var(--fb-text);
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .quick-product-price{
        font-size: 1rem;
        font-weight: 900;
        color: var(--fb-primary);
        margin-bottom: 10px;
    }

    .quick-product-desc{
        color: #6f6a61;
        line-height: 1.8;
        margin-bottom: 14px;
        font-size: .9rem;
        font-weight: 700;
    }

    .quick-option-box{
        border: 1px solid #e7ede1;
        background: #f8faf6;
        border-radius: 14px;
        padding: 12px;
    }

    .quick-option-box .form-check{
        margin-bottom: 10px;
    }

    .quick-option-box .form-check:last-child{
        margin-bottom: 0;
    }

    .quick-option-box .form-check-label{
        color: #344054;
        font-weight: 700;
        font-size: .9rem;
    }

    @media (max-width: 991.98px){
        .elite-layout{
            grid-template-columns: 1fr;
        }

        .elite-sidebar{
            order: 2;
        }

        .elite-main{
            order: 1;
        }

        .elite-identity-top{
            grid-template-columns: auto minmax(0,1fr);
        }

        .elite-actions{
            grid-column: 1 / -1;
            justify-content: flex-start;
            padding-top: 4px;
        }
    }

    @media (max-width: 767.98px){
        .elite-home{
            padding-bottom: 110px;
        }

        .elite-hero-card{
            border-radius: 0 0 22px 22px;
        }

        .elite-cover{
            min-height: 220px;
        }

        .elite-hero-content{
            margin-top: -34px;
            padding: 0 12px 14px;
        }

        .elite-identity-card{
            border-radius: 20px;
            padding: 14px;
        }

        .elite-identity-top{
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .elite-brand-main{
            gap: 12px;
            display: flex;
            align-items: center;
        }

        .elite-logo-frame{
            width: 86px;
            height: 86px;
            padding: 4px;
        }

        .elite-title{
            font-size: 1.12rem;
            line-height: 1.3;
        }

        .elite-subtitle{
            font-size: .82rem;
            line-height: 1.7;
        }

        .elite-brand-kicker{
            font-size: .68rem;
            padding: 6px 10px;
            margin-bottom: 8px;
        }

        .elite-actions{
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .elite-btn-primary,
        .elite-btn-secondary{
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            font-size: .8rem;
        }

        .elite-meta-row{
            gap: 8px;
            padding-top: 14px;
            margin-top: 14px;
        }

        .elite-pill{
            font-size: .72rem;
            padding: 8px 10px;
        }

        .elite-card-body{
            padding: 14px;
        }

        .elite-search-wrap{
            padding: 12px;
        }

        .elite-cat,
        .elite-cat-ring{
            width: 74px;
            min-width: 74px;
            height: 74px;
        }

        .elite-cat-label{
            font-size: .74rem;
        }

        .elite-section-head{
            padding: 14px 14px 8px;
        }

        .elite-products{
            padding: 0 14px 14px;
            gap: 12px;
        }

        .elite-product{
            grid-template-columns: 82px minmax(0,1fr);
            gap: 10px;
            border-radius: 16px;
            padding: 8px;
        }

        .elite-product-media{
            border-radius: 12px;
            overflow: hidden;
        }

        .elite-product-image{
            min-height: auto;
            height: 82px;
            border-radius: 12px;
        }

        .elite-product-badge{
            top: auto;
            bottom: 6px;
            inset-inline-start: 6px;
            padding: 5px 8px;
            font-size: .58rem;
        }

        .elite-product-body{
            gap: 7px;
            padding: 0;
            justify-content: center;
        }

        .elite-product-name{
            font-size: .87rem;
            line-height: 1.45;
        }

        .elite-product-category{
            font-size: .60rem;
            padding: 5px 7px;
        }

        .elite-product-desc{
            font-size: .74rem;
            line-height: 1.65;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .elite-product-bottom{
            gap: 8px;
            align-items: center;
        }

        .elite-price-label{
            font-size: .66rem;
        }

        .elite-price-value{
            font-size: .88rem;
        }

        .elite-add-btn{
            min-width: auto;
            padding: 9px 12px;
            font-size: .72rem;
            border-radius: 12px;
        }

        .elite-floating-cart{
            inset-inline: 10px;
            bottom: 10px;
        }

        .elite-floating-cart-inner{
            max-width: none;
            border-radius: 16px;
        }

        .elite-floating-cart-value{
            font-size: .82rem;
            white-space: normal;
        }

        .offer-popup-card{
            border-radius: 22px;
        }

        .offer-popup-image{
            height: 250px;
        }

        .offer-popup-title{
            font-size: 1.1rem;
        }

        .offer-popup-desc{
            font-size: .88rem;
        }

        .quick-product-name{
            font-size: 1.02rem;
        }

        .quick-product-desc{
            font-size: .85rem;
        }

        .modal-dialog{
            margin: .75rem;
        }
    }

    @media (max-width: 390px){
        .elite-brand-main{
            align-items: flex-start;
        }

        .elite-logo-frame{
            width: 78px;
            height: 78px;
        }

        .elite-title{
            font-size: 1rem;
        }

        .elite-actions{
            grid-template-columns: 1fr;
        }

        .elite-product{
            grid-template-columns: 74px minmax(0,1fr);
        }

        .elite-product-image{
            height: 74px;
        }


        
    }
</style>

<div class="elite-home">
    <section class="elite-hero-shell">
        <div class="elite-hero-card">
            <div class="elite-cover"></div>

            <div class="elite-hero-content">
                <div class="elite-identity-card">
                    <div class="elite-identity-top">
                        <div class="elite-logo-frame">
                            <div class="elite-logo"></div>
                        </div>

                        <div class="elite-brand-copy">
                            <div class="elite-brand-kicker">
                                <span class="dot"></span>
                                {{ __('home.official_order_destination') }}
                            </div>

                            <h1 class="elite-title">{{ $restaurantName }}</h1>

                            <p class="elite-subtitle">
                                @if($restaurantPhone)
                                    {{ $restaurantPhone }}
                                @else
                                    {{ __('home.fast_ordering_experience') }}
                                @endif
                            </p>
                        </div>

                        <div class="elite-actions">
                            <a href="#menu-area" class="elite-btn-primary">{{ __('home.browse_menu') }}</a>
                            <a href="{{ route('cart.index') }}" class="elite-btn-secondary">
                                {{ __('home.cart') }} <span id="heroCartCount">{{ $cartCount > 0 ? '(' . $cartCount . ')' : '' }}</span>
                            </a>
                        </div>
                    </div>

                    <div class="elite-meta-row">
                        <div class="elite-pill success">
                            <span class="dot"></span>
                            {{ $isOpen ? __('home.orders_available_now') : __('home.orders_unavailable_now') }}
                        </div>

                        <div class="elite-pill">
                            {{ __('home.delivery_fee') }} {{ number_format($deliveryFee, 2) }} {{ __('home.currency_egp') }}
                        </div>

                        <div class="elite-pill">
                            {{ __('home.available_payment_cash') }}
                        </div>

                        <div class="elite-pill">
                            {{ $products->count() }} {{ __('home.items_available') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="elite-layout" id="menu-area">
        <aside class="elite-sidebar">
            <div class="elite-card">
                <div class="elite-card-body">
                    <div class="elite-message">
                        <strong>{{ __('home.choose_items_and_complete_order') }}</strong>
                    </div>
                </div>
            </div>

            <div class="elite-card">
                <div class="elite-card-body">
                    <h3 class="elite-card-title">{{ __('home.restaurant_info') }}</h3>

                    <div class="elite-info-list">
                        <div class="elite-info-item">
                            <span class="elite-info-icon">🍽️</span>
                            <div>{{ __('home.organized_menu_display') }}</div>
                        </div>

                        <div class="elite-info-item">
                            <span class="elite-info-icon">🛵</span>
                            <div>{{ __('home.order_options_depend_on_settings') }}</div>
                        </div>

                        @if($restaurantPhone)
                            <div class="elite-info-item">
                                <span class="elite-info-icon">📞</span>
                                <div>
                                    <a href="tel:{{ $restaurantPhone }}" style="color:inherit;text-decoration:none;">
                                        {{ $restaurantPhone }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div class="elite-info-item">
                            <span class="elite-info-icon">💵</span>
                            <div>{{ __('home.current_payment_method_cash_on_delivery') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="elite-main">
            <div class="elite-card">
                <div class="elite-search-wrap">
                    <div class="elite-search">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <circle cx="11" cy="11" r="7" stroke="currentColor"/>
                            <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-linecap="round"/>
                        </svg>
                        <input type="text" id="menuSearchInput" placeholder="{{ __('home.search_placeholder') }}">
                    </div>

                    <div id="cartAjaxAlert" class="elite-alert">
                        <div class="elite-alert-box success" id="cartAjaxAlertBox">
                            {{ __('home.product_added_successfully') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="elite-card">
                <div class="elite-card-body">
                    <h3 class="elite-card-title">{{ __('home.categories') }}</h3>

                    <div class="elite-categories" id="categoryBubbles">
                        <button class="elite-cat active" type="button" data-category="all">
                            <div class="elite-cat-ring">
                                <div class="elite-cat-inner">https://via.placeholder.com/300x300?text=Menu
                                    <img src="{{ $coverImage ? asset('storage/' . $coverImage) : '' }}" alt="{{ __('home.all') }}">
                                </div>
                            </div>
                            <div class="elite-cat-label">{{ __('home.all') }}</div>
                        </button>

                        @foreach($groupedProducts as $categoryName => $categoryProducts)
                            @php
                                $firstProduct = $categoryProducts->first();
                                $categoryImage = $firstProduct && $firstProduct->image
                                    ? asset('storage/' . $firstProduct->image)
                                    : 'https://via.placeholder.com/300x300?text=Food';
                            @endphp

                            <button class="elite-cat" type="button" data-category="{{ \Illuminate\Support\Str::slug($categoryName) }}">
                                <div class="elite-cat-ring">
                                    <div class="elite-cat-inner">
                                        <img src="{{ $categoryImage }}" alt="{{ $categoryName }}">
                                    </div>
                                </div>
                                <div class="elite-cat-label">{{ $categoryName }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="elite-feed">
                @if($products->count())
                    @foreach($groupedProducts as $categoryName => $categoryProducts)
                        <section class="elite-section product-section" data-category="{{ \Illuminate\Support\Str::slug($categoryName) }}">
                            <div class="elite-section-head">
                                <h3 class="elite-section-title">{{ $categoryName }}</h3>
                                <span class="elite-section-count">{{ $categoryProducts->count() }} {{ __('home.item') }}</span>
                            </div>

                            <div class="elite-products">
                                @foreach($categoryProducts as $product)
                                    @php
                                        $productPayload = [
                                            'id' => $product->id,
                                            'name' => $product->name,
                                            'price' => $product->price,
                                            'description' => $product->description,
                                            'image' => $product->image ? asset('storage/' . $product->image) : null,
                                            'options' => $product->relationLoaded('optionGroups')
                                                ? $product->optionGroups->map(function ($group) {
                                                    return [
                                                        'id' => $group->id,
                                                        'name' => $group->name,
                                                        'type' => $group->type ?? 'single',
                                                        'is_required' => (bool) ($group->is_required ?? false),
                                                        'items' => $group->relationLoaded('items')
                                                            ? $group->items->map(function ($item) {
                                                                return [
                                                                    'id' => $item->id,
                                                                    'name' => $item->name,
                                                                    'price' => $item->price ?? 0,
                                                                ];
                                                            })->values()->toArray()
                                                            : [],
                                                    ];
                                                })->values()->toArray()
                                                : [],
                                        ];
                                    @endphp

                                    <article class="elite-product product-card-item" data-name="{{ strtolower($product->name . ' ' . ($product->description ?? '')) }}">
                                        <div class="elite-product-media">
                                            <img
                                                src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/600x400?text=Food' }}"
                                                alt="{{ $product->name }}"
                                                class="elite-product-image"
                                            >
                                            <span class="elite-product-badge">{{ __('home.available') }}</span>
                                        </div>

                                        <div class="elite-product-body">
                                            <div class="elite-product-top">
                                                <h4 class="elite-product-name">{{ $product->name }}</h4>
                                                <span class="elite-product-category">{{ $product->category->name ?? __('home.menu') }}</span>
                                            </div>

                                            <p class="elite-product-desc">
                                                {{ $product->description ?: __('home.default_product_description') }}
                                            </p>

                                            <div class="elite-product-bottom">
                                                <div class="elite-price">
                                                    <div class="elite-price-label">{{ __('home.price') }}</div>
                                                    <div class="elite-price-value">{{ number_format($product->price, 2) }} {{ __('home.currency_egp') }}</div>
                                                </div>

                                                <button
                                                    type="button"
                                                    class="btn elite-add-btn open-product-modal"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#productQuickAddModal"
                                                    data-product='@json($productPayload)'
                                                >
                                                    {{ __('home.add_to_cart') }}
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                @else
                    <div class="elite-empty">
                        {{ __('home.no_items_available_now') }}
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>

<div class="elite-floating-cart" id="floatingCheckout" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">
    <div class="elite-floating-cart-inner">
        <div class="elite-floating-cart-meta">
            <div class="elite-floating-cart-label">{{ __('home.current_cart') }}</div>
            <div class="elite-floating-cart-value" id="floatingCheckoutValue">
                {{ $cartCount }} {{ __('home.product') }} • {{ number_format($cartTotal, 2) }} {{ __('home.currency_egp') }}
            </div>
        </div>

        <a href="{{ route('cart.index') }}" class="elite-floating-cart-btn">
            {{ __('home.continue_order') }}
        </a>
    </div>
</div>

<div class="modal fade quick-modal" id="productQuickAddModal" tabindex="-1" aria-labelledby="productQuickAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="quickAddToCartForm" method="POST">
                @csrf

                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="productQuickAddModalLabel">{{ __('home.add_to_cart') }}</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-12 col-md-5">
                            <div class="quick-product-media">
                                <img id="quickProductImage" src="" alt="">
                            </div>
                        </div>

                        <div class="col-12 col-md-7">
                            <div id="quickProductName" class="quick-product-name"></div>
                            <div id="quickProductPrice" class="quick-product-price"></div>
                            <div id="quickProductDescription" class="quick-product-desc"></div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('home.quantity') }}</label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                            </div>

                            <div id="quickProductOptions"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">{{ __('home.cancel') }}</button>
                    <button type="submit" class="btn btn-success px-4">{{ __('home.confirm_addition') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($popupCampaign)
<div class="offer-popup-overlay" id="offerPopupOverlay">
    <div class="offer-popup-card">
        @if($popupCampaign->image)
            <img src="{{ asset('storage/' . $popupCampaign->image) }}" alt="{{ $popupCampaign->title }}" class="offer-popup-image">
        @endif

        <div class="offer-popup-body">
            @if($popupCampaign->title)
                <h3 class="offer-popup-title">{{ $popupCampaign->title }}</h3>
            @endif

            @if($popupCampaign->description)
                <div class="offer-popup-desc">{{ $popupCampaign->description }}</div>
            @endif

            @if($popupCampaign->button_text && $popupCampaign->button_url)
                <a href="{{ $popupCampaign->button_url }}" class="offer-popup-btn">
                    {{ $popupCampaign->button_text }}
                </a>
            @endif

            <button type="button" class="offer-popup-close" id="offerPopupCloseBtn">
                {{ __('home.close') }}
            </button>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('menuSearchInput');
    const sections = document.querySelectorAll('.product-section');
    const pills = document.querySelectorAll('.elite-cat');

    const form = document.getElementById('quickAddToCartForm');
    const productName = document.getElementById('quickProductName');
    const productPrice = document.getElementById('quickProductPrice');
    const productDescription = document.getElementById('quickProductDescription');
    const productImage = document.getElementById('quickProductImage');
    const optionsWrap = document.getElementById('quickProductOptions');
    const quantityInput = form.querySelector('input[name="quantity"]');
    const cartAjaxAlert = document.getElementById('cartAjaxAlert');
    const cartAjaxAlertBox = document.getElementById('cartAjaxAlertBox');

    const floatingCheckout = document.getElementById('floatingCheckout');
    const floatingCheckoutValue = document.getElementById('floatingCheckoutValue');
    const heroCartCount = document.getElementById('heroCartCount');

    const modalElement = document.getElementById('productQuickAddModal');
    const productModal = bootstrap.Modal.getOrCreateInstance(modalElement);

    let currentCartCount = {{ $cartCount }};
    let currentCartTotal = {{ (float) $cartTotal }};

    function formatMoney(value) {
        return Number(value || 0).toFixed(2) + ' {{ __("home.currency_egp") }}';
    }

    function updateCartUI(cartCount, cartTotal) {
        currentCartCount = Number(cartCount || 0);
        currentCartTotal = Number(cartTotal || 0);

        if (heroCartCount) {
            heroCartCount.textContent = currentCartCount > 0 ? `(${currentCartCount})` : '';
        }

        if (floatingCheckout && floatingCheckoutValue) {
            if (currentCartCount > 0) {
                floatingCheckout.style.display = '';
                floatingCheckoutValue.textContent = `${currentCartCount} {{ __("home.product") }} • ${formatMoney(currentCartTotal)}`;
            } else {
                floatingCheckout.style.display = 'none';
            }
        }
    }

    function filterMenu() {
        const searchValue = (searchInput?.value || '').toLowerCase().trim();
        const activePill = document.querySelector('.elite-cat.active');
        const activeCategory = activePill ? activePill.dataset.category : 'all';

        sections.forEach(section => {
            const sectionCategory = section.dataset.category;
            const cards = section.querySelectorAll('.product-card-item');
            let visibleCards = 0;

            cards.forEach(card => {
                const name = card.dataset.name || '';
                const matchesSearch = !searchValue || name.includes(searchValue);
                const matchesCategory = activeCategory === 'all' || activeCategory === sectionCategory;

                if (matchesSearch && matchesCategory) {
                    card.style.display = '';
                    visibleCards++;
                } else {
                    card.style.display = 'none';
                }
            });

            section.style.display = visibleCards > 0 ? '' : 'none';
        });
    }

    function showAjaxMessage(message, isError = false) {
        if (!cartAjaxAlert || !cartAjaxAlertBox) return;

        cartAjaxAlertBox.textContent = message;
        cartAjaxAlertBox.classList.remove('success', 'error');
        cartAjaxAlertBox.classList.add(isError ? 'error' : 'success');
        cartAjaxAlert.style.display = 'block';

        clearTimeout(window.__cartAlertTimer);
        window.__cartAlertTimer = setTimeout(() => {
            cartAjaxAlert.style.display = 'none';
        }, 2500);
    }

    pills.forEach(pill => {
        pill.addEventListener('click', function () {
            pills.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterMenu();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', filterMenu);
    }

    document.querySelectorAll('.open-product-modal').forEach(button => {
        button.addEventListener('click', function () {
            let product = {};

            try {
                product = JSON.parse(this.dataset.product || '{}');
            } catch (e) {
                product = {};
            }

            productName.textContent = product.name || '';
            productPrice.textContent = formatMoney(product.price || 0);
            productDescription.textContent = product.description || '';
            productImage.src = product.image || 'https://via.placeholder.com/600x400?text=Food';
            form.action = `/cart/add/${product.id}`;
            quantityInput.value = 1;
            optionsWrap.innerHTML = '';

            if (Array.isArray(product.options) && product.options.length) {
                product.options.forEach(group => {
                    const groupBox = document.createElement('div');
                    groupBox.className = 'mb-3';

                    let itemsHtml = '';

                    if (group.type === 'multiple') {
                        group.items.forEach(item => {
                            itemsHtml += `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="options[${group.id}][]" value="${item.id}" id="opt_${group.id}_${item.id}">
                                    <label class="form-check-label" for="opt_${group.id}_${item.id}">
                                        ${item.name} ${parseFloat(item.price || 0) > 0 ? `( +${parseFloat(item.price).toFixed(2)} {{ __("home.currency_egp") }} )` : ''}
                                    </label>
                                </div>
                            `;
                        });
                    } else {
                        group.items.forEach(item => {
                            itemsHtml += `
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="options[${group.id}]" value="${item.id}" id="opt_${group.id}_${item.id}" ${group.is_required ? 'required' : ''}>
                                    <label class="form-check-label" for="opt_${group.id}_${item.id}">
                                        ${item.name} ${parseFloat(item.price || 0) > 0 ? `( +${parseFloat(item.price).toFixed(2)} {{ __("home.currency_egp") }} )` : ''}
                                    </label>
                                </div>
                            `;
                        });
                    }

                    groupBox.innerHTML = `
                        <label class="form-label fw-bold d-block mb-2">
                            ${group.name}
                            ${group.is_required ? '<span class="text-danger">*</span>' : ''}
                        </label>
                        <div class="quick-option-box">
                            ${itemsHtml}
                        </div>
                    `;

                    optionsWrap.appendChild(groupBox);
                });
            }
        });
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '{{ __("home.adding") }}';

        try {
            const formData = new FormData(form);

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                let errorMessage = '{{ __("home.product_added_currently_unavailable") }}';

                if (data.message) {
                    errorMessage = data.message;
                } else if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    if (firstKey && data.errors[firstKey][0]) {
                        errorMessage = data.errors[firstKey][0];
                    }
                }

                showAjaxMessage(errorMessage, true);
                return;
            }

            productModal.hide();
            form.reset();
            optionsWrap.innerHTML = '';
            quantityInput.value = 1;

            const newCartCount = typeof data.cart_count !== 'undefined'
                ? data.cart_count
                : (currentCartCount + parseInt(formData.get('quantity') || 1, 10));

            const newCartTotal = typeof data.cart_total !== 'undefined'
                ? data.cart_total
                : currentCartTotal;

            updateCartUI(newCartCount, newCartTotal);
            showAjaxMessage(data.message || '{{ __("home.product_added_successfully") }}');

        } catch (error) {
            showAjaxMessage('{{ __("home.connection_error_try_again") }}', true);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });

    @if($popupCampaign)
    const popup = document.getElementById('offerPopupOverlay');
    const closeBtn = document.getElementById('offerPopupCloseBtn');

    if (popup) {
        const popupId = 'popup_campaign_{{ $popupCampaign->id }}';
        const showOnce = {{ $popupCampaign->show_once_per_user ? 'true' : 'false' }};
        let canShow = true;

        if (showOnce) {
            const alreadySeen = localStorage.getItem(popupId);
            if (alreadySeen === '1') {
                canShow = false;
            }
        }

        if (canShow) {
            setTimeout(function () {
                popup.classList.add('show');
            }, 500);
        }

        function closePopup() {
            popup.classList.remove('show');

            if (showOnce) {
                localStorage.setItem(popupId, '1');
            }
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closePopup);
        }

        popup.addEventListener('click', function (e) {
            if (e.target === popup) {
                closePopup();
            }
        });
    }
    @endif
});
</script>
@endsection