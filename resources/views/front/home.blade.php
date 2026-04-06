@extends('layouts.app')

@section('content')
@php
    if (!($setting instanceof \App\Models\Setting)) {
        $setting = null;
    }

    $restaurantName = $setting->restaurant_name ?? __('site.brand');
    $deliveryFee = $setting->delivery_fee ?? 0;
    $isOpen = ($setting && $setting->is_open);

    $cartCount = count(session('cart', []));
    $cartTotal = collect(session('cart', []))->sum(function ($item) {
        return ($item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1)));
    });

    $groupedProducts = $products->groupBy(function ($product) {
        return $product->category->name ?? __('home.menu');
    });

    $coverImage = $setting->banner ?? $setting->cover_image ?? null;
    $logoImage = $setting->logo ?? null;
@endphp

<style>
    .elite-home{
        --zaatar:#6f7f4f;
        --zaatar-dark:#4f5d37;
        --olive:#8ea06a;
        --olive-soft:#eef3e4;
        --cream:#fffdf8;
        --sand:#f3ede2;
        --text:#27231d;
        --muted:#6e665a;
        max-width: 1240px;
        margin: 0 auto;
        padding-bottom: 124px;
        color: var(--text);
        position: relative;
    }

    .elite-home::before{
        content:"";
        position: fixed;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at 12% 0%, rgba(145, 164, 101, .12), transparent 30%),
            radial-gradient(circle at 88% 14%, rgba(231, 211, 170, .18), transparent 34%);
        z-index: -1;
    }

    .elite-hero-shell{ margin-bottom: 24px; }

    .elite-hero-card{
        border-radius: 0 0 36px 36px;
        overflow: hidden;
        border: 1px solid #d8cdb9;
        background: var(--cream);
        box-shadow: 0 30px 60px rgba(44,38,30,.18);
    }

    .elite-cover{
        min-height: 370px;
        background:
            linear-gradient(115deg, rgba(55,67,35,.58), rgba(35,45,24,.36)),
            url('{{ $coverImage ? asset("storage/" . $coverImage) : "https://images.unsplash.com/photo-1498837167922-ddd27525d352?q=80&w=1600&auto=format&fit=crop" }}') center/cover no-repeat;
        position: relative;
    }

    .elite-cover::after{
        content:"";
        position:absolute;
        inset:auto 0 0;
        height:160px;
        background: linear-gradient(to top, rgba(10,13,8,.45), transparent);
    }

    .elite-hero-content{ margin-top: -74px; padding: 0 24px 24px; position: relative; z-index: 2; }

    .elite-identity-card{
        border-radius: 28px;
        border: 1px solid rgba(225,217,206,.95);
        background: rgba(255,253,248,.94);
        backdrop-filter: blur(10px);
        box-shadow: 0 20px 38px rgba(44,38,30,.13);
        padding: 20px;
    }

    .elite-identity-top{ display:grid; grid-template-columns: auto minmax(0,1fr) auto; gap:18px; align-items:center; }

    .elite-logo-frame{
        width: 120px; height:120px; border-radius: 50%; padding: 5px;
        background: conic-gradient(from 180deg at 50% 50%, #f6f0e4, #d8cbad, #f6f0e4);
        box-shadow: 0 16px 28px rgba(46,39,31,.2);
    }

    .elite-logo{ width:100%; height:100%; border-radius:50%; border:4px solid #fff; background:url('{{ $logoImage ? asset("storage/" . $logoImage) : "https://via.placeholder.com/500x500?text=Logo" }}') center/cover no-repeat,#fff; }

    .elite-brand-kicker{ display:inline-flex; align-items:center; gap:8px; background:#f2eddf; color:#7a705f; border-radius:999px; padding:7px 12px; font-size:.74rem; font-weight:900; margin-bottom:10px; }
    .elite-brand-kicker .dot{ width:7px; height:7px; border-radius:50%; background:var(--zaatar); display:inline-block; }
    .elite-title{ margin:0 0 8px; font-size:2.05rem; font-weight:900; letter-spacing:-.02em; }
    .elite-subtitle{ margin:0; color:var(--muted); font-weight:800; line-height:1.8; }

    .elite-actions{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
    .elite-btn-primary,.elite-btn-secondary{ min-height:46px; padding:11px 18px; border-radius:14px; font-weight:900; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
    .elite-btn-primary{ color:#fff; background:linear-gradient(135deg,var(--zaatar-dark),var(--olive)); box-shadow:0 14px 24px rgba(79,93,55,.26); }
    .elite-btn-secondary{ color:var(--text); background:#f5efe3; border:1px solid #e2d8c8; }
    .elite-btn-primary:hover,.offer-popup-btn:hover{ color:#fff; opacity:.97; }
    .elite-floating-cart-btn:hover{ color:#1f2616 !important; background:#fff0d8 !important; }
    .elite-btn-secondary:hover{ color:var(--text); background:#eee4d2; }

    .elite-meta-row{ margin-top:16px; padding-top:14px; border-top:1px solid #ece4d7; display:flex; flex-wrap:wrap; gap:10px; }
    .elite-pill{ border-radius:999px; padding:9px 13px; background:#f8f4eb; border:1px solid #e9e0d2; color:#635b4f; font-size:.82rem; font-weight:900; display:inline-flex; align-items:center; gap:7px; }
    .elite-pill.success{ background:#ebf4df; border-color:#dbe8c7; color:#40612e; }
    .elite-pill .dot{ width:8px; height:8px; border-radius:50%; background:currentColor; }

    .elite-layout{ display:grid; grid-template-columns: 310px minmax(0,1fr); gap:18px; align-items:start; }
    .elite-sidebar,.elite-main{ display:grid; gap:18px; }
    .elite-card,.elite-section,.elite-empty{ background:var(--cream); border:1px solid #e4dacb; border-radius:24px; box-shadow:0 10px 26px rgba(44,38,30,.08); overflow:hidden; }
    .elite-card-body{ padding:18px; }
    .elite-card-title,.elite-section-title{ margin:0; font-size:1.02rem; font-weight:900; }

    .elite-message{ border-radius:20px; padding:18px; color:#fff; background:linear-gradient(132deg,#41502f,#6f874a); box-shadow:0 18px 30px rgba(65,80,47,.32); border:1px solid rgba(255,255,255,.14); }
    .elite-message strong{ line-height:1.85; font-size:.95rem; display:block; }

    .elite-info-list{ display:grid; gap:12px; margin-top:14px; }
    .elite-info-item{ display:flex; gap:10px; align-items:flex-start; font-size:.88rem; color:#4f483e; font-weight:800; border:1px solid #ece2d4; border-radius:14px; padding:10px; background:#fffaf2; }
    .elite-info-icon{ width:34px; height:34px; border-radius:11px; background:linear-gradient(145deg,#eff5e4,#dde9c8); color:#465b2f; display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; box-shadow:0 6px 12px rgba(70,91,47,.18); }
    .elite-info-icon i{ font-size:.92rem; line-height:1; }

    .elite-search-wrap{ padding:14px; }
    .elite-search{ position:relative; }
    .elite-search svg{ position:absolute; inset-inline-start:14px; top:50%; transform:translateY(-50%); width:18px; height:18px; stroke:#9b9386; }
    .elite-search input{ width:100%; border:1px solid #e2d8c9; background:#faf6ee; border-radius:999px; padding:14px 16px 14px 46px; font-size:.93rem; font-weight:800; }
    .elite-search input:focus{ outline:none; border-color:#9caf7d; box-shadow:0 0 0 4px rgba(126,146,94,.15); background:#fff; }
    .elite-alert{ display:none; margin-top:12px; }
    .elite-alert-box{ border-radius:14px; padding:11px 13px; font-size:.88rem; font-weight:900; }
    .elite-alert-box.success{ background:#edf8ef; color:#166534; border:1px solid #cce8d3; }
    .elite-alert-box.error{ background:#fff1f1; color:#991b1b; border:1px solid #f3cdcd; }

    .elite-categories{ display:flex; gap:12px; overflow:auto; padding-bottom:4px; scrollbar-width:none; }
    .elite-categories::-webkit-scrollbar{ display:none; }
    .elite-cat{ border:none; background:transparent; width:94px; min-width:94px; text-align:center; }
    .elite-cat-ring{ width:94px; height:94px; border-radius:50%; padding:3px; background:linear-gradient(140deg,#dcd1bc,#f4eee1); margin:0 auto 8px; transition:.2s; }
    .elite-cat-inner{ width:100%; height:100%; border-radius:50%; overflow:hidden; border:3px solid #fff; background:#f0e9db; }
    .elite-cat-inner img{ width:100%; height:100%; object-fit:cover; }
    .elite-cat-label{ font-size:.78rem; font-weight:900; color:#5d554a; line-height:1.3; }
    .elite-cat.active .elite-cat-ring{ transform:translateY(-2px); background:linear-gradient(140deg,#5f6f40,#9aac7a); box-shadow:0 14px 24px rgba(85,105,56,.28); }
    .elite-cat.active .elite-cat-label{ color:var(--zaatar-dark); }

    .elite-feed{ display:grid; gap:18px; }
    .elite-section-head{ display:flex; justify-content:space-between; align-items:center; gap:10px; padding:18px 18px 10px; background:linear-gradient(180deg,#fffaf0, #fffdf9); border-bottom:1px solid #ede3d5; }
    .elite-section-count{ background:#f2ecdf; color:#746b5e; border-radius:999px; padding:7px 11px; font-size:.74rem; font-weight:900; }
    .elite-products{ display:grid; gap:14px; padding:0 18px 18px; }

    .elite-product{
        display:grid; grid-template-columns:190px minmax(0,1fr); gap:14px;
        border:1px solid #e8dece; border-radius:20px; overflow:hidden;
        background: linear-gradient(180deg,#fffefb,#faf4e9);
        box-shadow: 0 12px 22px rgba(47,41,32,.08); transition:.2s;
    }
    .elite-product:hover{ transform:translateY(-2px); box-shadow:0 20px 30px rgba(47,41,32,.14); }
    .elite-product-image{ width:100%; height:100%; min-height:190px; object-fit:cover; background:#f3ebdd; }
    .elite-product-badge{ position:absolute; top:12px; inset-inline-start:12px; background:rgba(30,39,20,.74); color:#fff; border-radius:999px; padding:6px 11px; font-size:.66rem; font-weight:900; }
    .elite-product-body{ padding:16px 16px 16px 0; display:flex; flex-direction:column; gap:10px; }
    .elite-product-top{ display:flex; justify-content:space-between; align-items:flex-start; gap:8px; flex-wrap:wrap; }
    .elite-product-name{ margin:0; font-size:1.02rem; font-weight:900; }
    .elite-product-category{ background:#eaf2dc; color:#4f6a37; border-radius:999px; padding:6px 10px; font-size:.66rem; font-weight:900; }
    .elite-product-desc{ margin:0; color:#6f675a; font-size:.83rem; line-height:1.75; font-weight:700; }
    .elite-product-bottom{ margin-top:auto; display:flex; justify-content:space-between; align-items:end; gap:12px; flex-wrap:wrap; }
    .elite-price-label{ font-size:.7rem; color:#8f8577; font-weight:800; }
    .elite-price-value{ font-size:1.04rem; font-weight:900; }
    .elite-add-btn{ border:none; min-width:136px; border-radius:14px; padding:11px 15px; font-size:.82rem; font-weight:900; color:#fff; background:linear-gradient(135deg,#435130,#80975b); box-shadow:0 12px 22px rgba(67,81,48,.35); }

    .elite-empty{ text-align:center; padding:30px 18px; color:#6c6458; font-weight:800; }

    .elite-floating-cart{ position:fixed; inset-inline:14px; bottom:14px; z-index:1050; display:flex; justify-content:center; pointer-events:none; }
    .elite-floating-cart-inner{ max-width:460px; width:100%; pointer-events:auto; border:1px solid #7d9360; background:linear-gradient(120deg, rgba(75,90,52,.94), rgba(105,124,75,.94)); backdrop-filter:blur(10px); border-radius:18px; padding:10px; box-shadow:0 16px 32px rgba(48,41,33,.22); display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .elite-floating-cart-label{ color:rgba(255,255,255,.84); font-size:.74rem; font-weight:800; }
    .elite-floating-cart-value{ font-size:.9rem; font-weight:900; color:#fff; }
    .elite-floating-cart-btn{ color:#2f3822 !important; text-decoration:none; border-radius:14px; padding:11px 15px; background:#fff6e8 !important; border:1px solid #f0dfc2; font-weight:900; font-size:.82rem; white-space:nowrap; box-shadow:0 8px 16px rgba(0,0,0,.18); }

    .offer-popup-overlay{ position:fixed; inset:0; background:rgba(14,17,10,.62); display:none; align-items:center; justify-content:center; padding:18px; z-index:999999; }
    .offer-popup-overlay.show{ display:flex; }
    .offer-popup-card{ max-width:440px; width:100%; background:#fff; border-radius:24px; overflow:hidden; border:1px solid #e6dccf; box-shadow:0 30px 70px rgba(0,0,0,.28); }
    .offer-popup-image{ width:100%; height:320px; object-fit:cover; background:#f2ebe0; }
    .offer-popup-body{ padding:20px; text-align:center; }
    .offer-popup-title{ margin:0 0 8px; font-size:1.22rem; font-weight:900; }
    .offer-popup-desc{ margin-bottom:16px; color:#6f6659; font-size:.92rem; line-height:1.8; font-weight:700; }
    .offer-popup-btn{ display:block; width:100%; border:none; text-decoration:none; border-radius:14px; padding:12px 15px; color:#fff !important; background:linear-gradient(135deg,#435130,#80975b); font-weight:900; margin-bottom:10px; box-shadow:0 10px 20px rgba(67,81,48,.26); }
    .offer-popup-close{ width:100%; border:none; border-radius:14px; padding:12px 15px; background:#f3f4f6; font-weight:800; }

    .quick-modal .modal-content{ border:none; border-radius:22px; overflow:hidden; box-shadow:0 30px 70px rgba(15,23,42,.18); }
    .quick-modal .modal-header{ padding:18px 18px 0; }
    .quick-modal .modal-body{ padding:18px; }
    .quick-modal .modal-footer{ padding:0 18px 18px; }
    .quick-product-media{ border:1px solid #e4ddcf; border-radius:16px; padding:8px; background:#faf6ee; }
    .quick-product-media img{ width:100%; max-height:280px; object-fit:cover; border-radius:12px; }
    .quick-product-name{ font-size:1.16rem; font-weight:900; margin-bottom:8px; }
    .quick-product-price{ font-size:.98rem; font-weight:900; color:#4f5d37; margin-bottom:10px; }
    .quick-product-desc{ font-size:.9rem; font-weight:700; line-height:1.8; color:#675f53; margin-bottom:14px; }
    .quick-option-box{ border:1px solid #e4ddcf; background:#f9f6ef; border-radius:14px; padding:12px; }

    @media (max-width: 991.98px){
        .elite-layout{ grid-template-columns:1fr; }
        .elite-sidebar{ order:2; }
        .elite-main{ order:1; }
        .elite-identity-top{ grid-template-columns:auto minmax(0,1fr); }
        .elite-actions{ grid-column:1/-1; justify-content:flex-start; }
    }

    @media (max-width: 767.98px){
        .elite-home{ padding-bottom:112px; }
        .elite-hero-card{ border-radius:0 0 24px 24px; }
        .elite-cover{ min-height:220px; }
        .elite-hero-content{ margin-top:-36px; padding:0 12px 14px; }
        .elite-identity-card{ padding:14px; border-radius:20px; }
        .elite-identity-top{ grid-template-columns:1fr; gap:14px; }
        .elite-brand-main{ display:flex; align-items:center; gap:12px; }
        .elite-logo-frame{ width:84px; height:84px; }
        .elite-title{ font-size:1.12rem; }
        .elite-subtitle{ font-size:.8rem; }
        .elite-actions{ display:grid; grid-template-columns:1fr 1fr; width:100%; gap:8px; }
        .elite-btn-primary,.elite-btn-secondary{ min-height:42px; font-size:.79rem; padding:10px 12px; width:100%; }
        .elite-card-body{ padding:14px; }
        .elite-search-wrap{ padding:12px; }
        .elite-cat,.elite-cat-ring{ width:76px; min-width:76px; height:76px; }
        .elite-cat-label{ font-size:.72rem; }
        .elite-section-head{ padding:14px 14px 8px; }
        .elite-products{ padding:0 14px 14px; gap:12px; }
        .elite-product{ grid-template-columns:84px minmax(0,1fr); gap:10px; padding:8px; border-radius:16px; }
        .elite-product-media,.elite-product-image{ border-radius:11px; overflow:hidden; }
        .elite-product-image{ min-height:auto; height:84px; }
        .elite-product-badge{ display:none; }
        .elite-product-body{ padding:0; gap:7px; }
        .elite-product-name{ font-size:.87rem; }
        .elite-product-category{ font-size:.58rem; padding:4px 6px; }
        .elite-product-desc{ font-size:.73rem; line-height:1.65; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .elite-price-value{ font-size:.87rem; }
        .elite-add-btn{ min-width:auto; font-size:.71rem; padding:9px 12px; }
        .elite-floating-cart{ inset-inline:10px; bottom:10px; }
        .elite-floating-cart-inner{ border-radius:15px; }
        .elite-floating-cart-value{ font-size:.8rem; }
        .offer-popup-card{ border-radius:20px; }
        .offer-popup-image{ height:250px; }
        .quick-product-name{ font-size:1rem; }
    }

    @media (max-width: 390px){
        .elite-actions{ grid-template-columns:1fr; }
        .elite-logo-frame{ width:78px; height:78px; }
        .elite-title{ font-size:1rem; }
        .elite-product{ grid-template-columns:74px minmax(0,1fr); }
        .elite-product-image{ height:74px; }
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

                            <p class="elite-subtitle">تجربة طلب احترافية بتصميم واضح وسريع تساعدك تختار وجبتك وتكمل طلبك بثقة في أقل وقت.</p>
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
                        <strong>اختر من الأصناف المتاحة وأكمل طلبك بسهولة من خلال منيو منظم وتجربة استخدام واضحة وسريعة.</strong>
                    </div>
                </div>
            </div>

            <div class="elite-card">
                <div class="elite-card-body">
                    <h3 class="elite-card-title">دليل الطلب السريع</h3>

                    <div class="elite-info-list">
                        <div class="elite-info-item">
                            <span class="elite-info-icon"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                            <div>منيو مصمم بطريقة منظمة لسهولة الوصول للأصناف والتفاصيل والإضافة للسلة.</div>
                        </div>

                        <div class="elite-info-item">
                            <span class="elite-info-icon"><i class="bi bi-truck"></i></span>
                            <div>خيارات الطلب مرنة حسب إعدادات الفرع المتاحة للتوصيل أو الاستلام.</div>
                        </div>

                        <div class="elite-info-item">
                            <span class="elite-info-icon"><i class="bi bi-shield-check"></i></span>
                            <div>تحديث حالة الطلب بشكل مستمر مع تجربة متابعة سلسة حتى إتمام الاستلام.</div>
                        </div>

                        <div class="elite-info-item">
                            <span class="elite-info-icon"><i class="bi bi-cash-coin"></i></span>
                            <div>وسيلة الدفع الحالية: الدفع النقدي عند الاستلام.</div>
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
                                <div class="elite-cat-inner">
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
