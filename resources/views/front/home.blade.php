@extends('layouts.app')

@section('content')
@php
    if (!($setting instanceof \App\Models\Setting)) {
        $setting = null;
    }

    $restaurantName = $setting->restaurant_name ?? __('site.brand');
    $deliveryFee = (float) ($setting->delivery_fee ?? 0);
    $isOpen = (bool) ($setting && $setting->is_open);

    $cartCount = count(session('cart', []));
    $cartTotal = collect(session('cart', []))->sum(function ($item) {
        return ($item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1)));
    });

    $groupedProducts = $products->groupBy(function ($product) {
        return $product->category->name ?? __('home.menu');
    });

    $allProducts = $products->values();
    $mostOrderedProducts = $allProducts->take(6);
    $featuredProducts = $allProducts->filter(fn ($product) => !empty($product->image))->take(8);

    if ($featuredProducts->isEmpty()) {
        $featuredProducts = $allProducts->take(8);
    }

    $coverImage = $setting->banner ?? $setting->cover_image ?? null;
    $logoImage = $setting->logo ?? null;
    $coverImageUrl = \App\Support\MediaUrl::fromPath($coverImage);
    $logoImageUrl = \App\Support\MediaUrl::fromPath($logoImage);
    $popupImageUrl = \App\Support\MediaUrl::fromPath($popupCampaign?->image);

    $storyItems = collect([
        ['key' => 'most-ordered', 'label' => 'الأكثر طلبًا', 'icon' => 'bi-fire'],
        ['key' => 'featured', 'label' => 'مميزة', 'icon' => 'bi-stars'],
    ])->merge(
        $groupedProducts->keys()->map(function ($name) {
            return [
                'key' => \Illuminate\Support\Str::slug($name),
                'label' => $name,
                'icon' => 'bi-cup-hot',
            ];
        })
    )->unique('key')->values();
@endphp

<style>
    body { overflow-x: hidden; }
    #mainNavbar,
    .site-footer,
    .mobile-bottom-bar { display: none !important; }
    .page-container.container {
        max-width: 100%;
        width: 100%;
        padding: 0 !important;
        margin: 0 auto;
    }

    .feed-home,
    .feed-home * { box-sizing: border-box; min-width: 0; }

    .feed-home {
        --shell-max: 760px;
        --safe-bottom: calc(74px + env(safe-area-inset-bottom, 0px));
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        padding: 0 0 var(--safe-bottom);
        background: radial-gradient(circle at top, #fff8ee 0%, #f8f5f0 55%, #f5f1eb 100%);
        color: #1f2421;
    }

    .feed-shell {
        width: 100%;
        max-width: min(var(--shell-max), 100%);
        margin: 0 auto;
        padding: 0 12px;
    }

    .feed-top {
        position: sticky;
        top: 0;
        z-index: 30;
        backdrop-filter: blur(12px);
        background: rgba(248, 245, 240, 0.88);
        border-bottom: 1px solid #eadfce;
    }

    .feed-top-inner {
        padding: 10px 0 12px;
        display: grid;
        gap: 10px;
    }

    .feed-top-brand {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .feed-brand-info { display: flex; align-items: center; gap: 10px; }
    .feed-logo {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        flex: 0 0 auto;
        background: url('{{ $logoImageUrl ?: "https://via.placeholder.com/300x300?text=Logo" }}') center/cover no-repeat;
        box-shadow: 0 10px 22px rgba(17, 35, 29, 0.22);
    }

    .feed-name { font-size: 1rem; font-weight: 900; margin: 0; }
    .feed-open-pill {
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        padding: 5px 10px;
        border: 1px solid #d8ccb8;
        background: #fffdf7;
        color: #1f6b56;
    }

    .feed-open-pill.closed {
        color: #8b2e2e;
        background: #fff1f1;
        border-color: #f3d4d4;
    }

    .feed-top-actions { display: flex; gap: 8px; }
    .feed-icon-btn {
        border: 1px solid #dfd3c0;
        background: #fff;
        border-radius: 12px;
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #33413b;
        text-decoration: none;
        position: relative;
    }

    .feed-icon-btn .badge-dot {
        position: absolute;
        top: -5px;
        inset-inline-end: -5px;
        min-width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #de4a3e;
        color: #fff;
        font-size: 0.68rem;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
    }

    .feed-search {
        position: relative;
        width: 100%;
    }

    .feed-search i {
        position: absolute;
        inset-inline-start: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #7f7668;
        font-size: 0.94rem;
    }

    .feed-search input {
        width: 100%;
        max-width: 100%;
        border-radius: 14px;
        border: 1px solid #deceb5;
        background: #fff;
        padding: 11px 12px 11px 36px;
        font-weight: 700;
    }

    .feed-main { padding-top: 12px; display: grid; gap: 14px; }

    .story-row {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
        padding: 2px 2px 6px;
        scrollbar-width: none;
    }

    .story-row::-webkit-scrollbar { display: none; }
    .story-btn {
        border: 0;
        background: transparent;
        padding: 0;
        width: 74px;
        flex: 0 0 auto;
        display: grid;
        justify-items: center;
        gap: 6px;
        text-decoration: none;
        color: inherit;
    }

    .story-avatar {
        width: 64px;
        height: 64px;
        border-radius: 22px;
        border: 2px solid transparent;
        background: linear-gradient(#fff, #fff) padding-box,
                    linear-gradient(160deg, #ff9f43, #cf3f69, #5d43d6) border-box;
        display: grid;
        place-items: center;
        font-size: 1.18rem;
        color: #1d2d26;
        box-shadow: 0 8px 20px rgba(36, 39, 58, 0.16);
    }

    .story-btn.active .story-avatar {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(29, 45, 38, 0.25);
    }

    .story-label {
        width: 100%;
        font-size: 0.72rem;
        font-weight: 800;
        color: #544d45;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
    }

    .feed-card {
        background: #fff;
        border: 1px solid #e8ddcd;
        border-radius: 22px;
        box-shadow: 0 12px 30px rgba(28, 37, 31, 0.08);
        overflow: hidden;
    }

    .hero-card {
        position: relative;
        background: linear-gradient(125deg, rgba(17, 57, 45, 0.96), rgba(39, 118, 97, 0.88));
        color: #fff;
    }

    .hero-cover {
        position: absolute;
        inset: 0;
        background: linear-gradient(125deg, rgba(17, 57, 45, 0.72), rgba(39, 118, 97, 0.6)),
                    url('{{ $coverImageUrl ?: "https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1400&auto=format&fit=crop" }}') center/cover no-repeat;
        opacity: 0.5;
    }

    .hero-body { position: relative; z-index: 1; padding: 16px; display: grid; gap: 10px; }
    .hero-title { margin: 0; font-size: 1.16rem; font-weight: 900; }
    .hero-sub { margin: 0; color: #e8f3ee; font-size: 0.84rem; line-height: 1.8; }
    .hero-stats { display: flex; flex-wrap: wrap; gap: 8px; }
    .hero-stat { border-radius: 999px; padding: 5px 10px; background: rgba(255,255,255,0.16); font-size: 0.72rem; font-weight: 800; }

    .section-card { padding: 14px; display: grid; gap: 12px; }
    .section-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .section-title { margin: 0; font-size: 1.05rem; font-weight: 900; }
    .section-sub { margin: 3px 0 0; color: #7a7063; font-size: 0.78rem; font-weight: 700; }

    .products-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        width: 100%;
        max-width: 100%;
    }

    .product-card {
        width: 100%;
        max-width: 100%;
        border: 1px solid #efe3d5;
        border-radius: 18px;
        background: #fffdfa;
        overflow: hidden;
        display: grid;
        grid-template-columns: 98px minmax(0, 1fr);
        gap: 10px;
        padding: 9px;
    }

    .product-thumb {
        width: 100%;
        height: 98px;
        border-radius: 14px;
        object-fit: cover;
        background: #f0e6d7;
    }

    .product-content { display: grid; gap: 7px; align-content: start; }
    .product-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
    .product-name { margin: 0; font-size: 0.95rem; font-weight: 900; line-height: 1.45; }
    .product-desc {
        margin: 0;
        color: #6d655a;
        font-size: 0.76rem;
        line-height: 1.7;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .badge-soft {
        border-radius: 999px;
        padding: 3px 8px;
        background: #fff0cb;
        color: #7d4a03;
        border: 1px solid #f0dca9;
        font-size: 0.65rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .product-bottom { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .product-price { font-size: 0.88rem; font-weight: 900; color: #1f6756; }

    .add-btn {
        border: 0;
        border-radius: 11px;
        padding: 8px 12px;
        background: linear-gradient(135deg, #113f33, #2f7763);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .mini-slider {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(220px, 74%);
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }

    .mini-slider::-webkit-scrollbar { display: none; }

    .floating-checkout {
        position: fixed;
        inset-inline: 12px;
        bottom: calc(74px + env(safe-area-inset-bottom, 0px));
        z-index: 25;
        display: flex;
        justify-content: center;
        pointer-events: none;
    }

    .floating-checkout-inner {
        pointer-events: auto;
        width: 100%;
        max-width: min(var(--shell-max), calc(100% - 4px));
        border-radius: 16px;
        padding: 10px 12px;
        background: linear-gradient(120deg, rgba(16, 63, 51, 0.98), rgba(45, 113, 94, 0.96));
        border: 1px solid #2f715f;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #fff;
    }

    .floating-checkout-btn {
        text-decoration: none;
        color: #17322a;
        background: #f8ebd5;
        border-radius: 11px;
        padding: 8px 12px;
        font-size: 0.75rem;
        font-weight: 900;
    }

    .home-bottom-nav {
        position: fixed;
        inset-inline: 0;
        bottom: 0;
        z-index: 26;
        padding: 8px 10px calc(8px + env(safe-area-inset-bottom, 0px));
        background: rgba(247, 243, 236, 0.95);
        border-top: 1px solid #e8ddcd;
        backdrop-filter: blur(10px);
    }

    .home-bottom-nav-inner {
        width: 100%;
        max-width: min(var(--shell-max), 100%);
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 4px;
    }

    .home-nav-item {
        color: #6a645b;
        text-decoration: none;
        display: grid;
        justify-items: center;
        gap: 2px;
        padding: 4px;
        font-size: 0.64rem;
        font-weight: 800;
    }

    .home-nav-item i { font-size: 1rem; }
    .home-nav-item.active { color: #134a3b; }

    .quick-modal .modal-dialog{max-width:820px}
    .quick-modal .modal-content{border-radius:18px;border:1px solid #e3d6c3}
    .quick-product-media img{width:100%;height:240px;object-fit:cover;border-radius:14px}
    .quick-product-name{font-size:1.2rem;font-weight:900}
    .quick-product-price{font-size:1rem;font-weight:900;color:#1f6554;margin:6px 0}
    .quick-product-desc{font-size:.88rem;color:#6f675a;line-height:1.7;margin-bottom:10px}
    .offer-popup-overlay{position:fixed;inset:0;background:rgba(8,12,10,.64);display:none;align-items:center;justify-content:center;padding:16px;z-index:999999}
    .offer-popup-overlay.show{display:flex}
    .offer-popup-card{max-width:440px;width:100%;background:#fff;border:1px solid #e5d7c4;border-radius:22px;overflow:hidden;box-shadow:0 28px 64px rgba(0,0,0,.3)}
    .offer-popup-image{width:100%;height:300px;object-fit:cover;background:#efe6d7}
    .offer-popup-body{padding:18px;text-align:center}
    .offer-popup-title{margin:0 0 8px;font-size:1.18rem;font-weight:900;color:#1a2723}
    .offer-popup-desc{margin:0 0 14px;color:#666056;line-height:1.8;font-size:.9rem;font-weight:700}
    .offer-popup-close{width:100%;border:none;border-radius:12px;padding:11px 14px;background:#f2f3f5;font-weight:800}

    @media (min-width: 768px) {
        .feed-shell { padding: 0 16px; }
        .feed-top-inner { padding: 14px 0 14px; }
        .feed-name { font-size: 1.1rem; }
        .hero-body { padding: 20px; }
        .products-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .mini-slider { grid-auto-columns: minmax(240px, 36%); }
        .product-card { grid-template-columns: 112px minmax(0, 1fr); }
        .product-thumb { height: 112px; }
    }

    @media (min-width: 1024px) {
        .feed-home { --shell-max: 980px; }
        .feed-shell { padding: 0 20px; }
        .products-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .mini-slider { grid-auto-columns: minmax(260px, 32%); }
        .home-bottom-nav { max-width: 560px; inset-inline: 0; margin: 0 auto; border-radius: 16px 16px 0 0; }
    }
</style>

<div class="feed-home">
    <header class="feed-top">
        <div class="feed-shell feed-top-inner">
            <div class="feed-top-brand">
                <div class="feed-brand-info">
                    <div class="feed-logo"></div>
                    <div>
                        <h1 class="feed-name">{{ $restaurantName }}</h1>
                        <span class="feed-open-pill {{ $isOpen ? '' : 'closed' }}">{{ $isOpen ? 'مفتوح الآن' : 'مغلق الآن' }}</span>
                    </div>
                </div>
                <div class="feed-top-actions">
                    @if(Route::has('my.orders'))
                        <a class="feed-icon-btn" href="{{ route('my.orders') }}" aria-label="طلباتي"><i class="bi bi-receipt"></i></a>
                    @else
                        <a class="feed-icon-btn" href="{{ route('pages.contact') }}" aria-label="الحساب"><i class="bi bi-person"></i></a>
                    @endif
                    <a class="feed-icon-btn" href="{{ route('cart.index') }}" aria-label="السلة" id="headerCartButton">
                        <i class="bi bi-bag"></i>
                        @if($cartCount > 0)
                            <span class="badge-dot" id="headerCartCount">{{ $cartCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
            <label class="feed-search" for="menuSearchInput">
                <i class="bi bi-search"></i>
                <input type="text" id="menuSearchInput" placeholder="{{ __('home.search_placeholder') }}">
            </label>
        </div>
    </header>

    <main class="feed-shell feed-main" id="menu-area">
        <section class="story-row" id="storiesRow">
            @foreach($storyItems as $story)
                <button class="story-btn {{ $loop->first ? 'active' : '' }}" type="button" data-story-target="{{ $story['key'] }}">
                    <span class="story-avatar"><i class="bi {{ $story['icon'] }}"></i></span>
                    <span class="story-label">{{ $story['label'] }}</span>
                </button>
            @endforeach
        </section>

        <section class="feed-card hero-card">
            <div class="hero-cover"></div>
            <div class="hero-body">
                <h2 class="hero-title">تجربة طلب حديثة وسريعة بطابع مطعم بريميوم</h2>
                <p class="hero-sub">اختر من الأقسام عبر Stories، أضف منتجاتك للسلة في خطوة واحدة، وكمّل طلبك بسهولة بدون أي ازدحام بصري.</p>
                <div class="hero-stats">
                    <span class="hero-stat">{{ number_format($deliveryFee, 2) }} {{ __('home.currency_egp') }} توصيل</span>
                    <span class="hero-stat">{{ $products->count() }} عنصر متاح</span>
                    <span class="hero-stat">{{ $isOpen ? 'الطلبات متاحة' : 'الطلبات متوقفة' }}</span>
                </div>
            </div>
        </section>

        @if($products->count())
            <section class="feed-card section-card product-section" data-category="most-ordered" id="section-most-ordered">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">الأكثر طلبًا</h3>
                        <p class="section-sub">اختيارات العملاء المفضلة اليوم.</p>
                    </div>
                </div>
                <div class="mini-slider">
                    @foreach($mostOrderedProducts as $product)
                        @php
                            $productPayload = [
                                'id' => $product->id,
                                'name' => $product->name,
                                'price' => $product->price,
                                'description' => $product->description,
                                'image' => $product->image ? \App\Support\MediaUrl::fromPath($product->image) : null,
                                'options' => $product->relationLoaded('optionGroups')
                                    ? $product->optionGroups->map(function ($group) {
                                        return [
                                            'id' => $group->id,
                                            'name' => $group->name,
                                            'type' => $group->type ?? 'single',
                                            'is_required' => (bool) ($group->is_required ?? false),
                                            'items' => $group->relationLoaded('items')
                                                ? $group->items->map(fn ($item) => ['id' => $item->id, 'name' => $item->name, 'price' => $item->price ?? 0])->values()->toArray()
                                                : [],
                                        ];
                                    })->values()->toArray()
                                    : [],
                            ];
                        @endphp
                        <article class="product-card product-card-item" data-name="{{ strtolower($product->name . ' ' . ($product->description ?? '')) }}">
                            <img src="{{ $product->image ? \App\Support\MediaUrl::fromPath($product->image) : 'https://via.placeholder.com/600x400?text=Food' }}" alt="{{ $product->name }}" class="product-thumb">
                            <div class="product-content">
                                <div class="product-top">
                                    <h4 class="product-name">{{ $product->name }}</h4>
                                    <span class="badge-soft">الأكثر طلبًا</span>
                                </div>
                                <p class="product-desc">{{ $product->description ?: __('home.default_product_description') }}</p>
                                <div class="product-bottom">
                                    <span class="product-price">{{ number_format($product->price, 2) }} {{ __('home.currency_egp') }}</span>
                                    <button type="button" class="add-btn open-product-modal" data-bs-toggle="modal" data-bs-target="#productQuickAddModal" data-product='@json($productPayload)'>{{ __('home.add_to_cart') }}</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="feed-card section-card product-section" data-category="featured" id="section-featured">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">منتجات مميزة</h3>
                        <p class="section-sub">منتقاة لك بعناية من المنيو.</p>
                    </div>
                </div>
                <div class="products-grid">
                    @foreach($featuredProducts as $product)
                        @php
                            $productPayload = [
                                'id' => $product->id,
                                'name' => $product->name,
                                'price' => $product->price,
                                'description' => $product->description,
                                'image' => $product->image ? \App\Support\MediaUrl::fromPath($product->image) : null,
                                'options' => $product->relationLoaded('optionGroups')
                                    ? $product->optionGroups->map(function ($group) {
                                        return [
                                            'id' => $group->id,
                                            'name' => $group->name,
                                            'type' => $group->type ?? 'single',
                                            'is_required' => (bool) ($group->is_required ?? false),
                                            'items' => $group->relationLoaded('items')
                                                ? $group->items->map(fn ($item) => ['id' => $item->id, 'name' => $item->name, 'price' => $item->price ?? 0])->values()->toArray()
                                                : [],
                                        ];
                                    })->values()->toArray()
                                    : [],
                            ];
                        @endphp
                        <article class="product-card product-card-item" data-name="{{ strtolower($product->name . ' ' . ($product->description ?? '')) }}">
                            <img src="{{ $product->image ? \App\Support\MediaUrl::fromPath($product->image) : 'https://via.placeholder.com/600x400?text=Food' }}" alt="{{ $product->name }}" class="product-thumb">
                            <div class="product-content">
                                <div class="product-top">
                                    <h4 class="product-name">{{ $product->name }}</h4>
                                    <span class="badge-soft">مميز</span>
                                </div>
                                <p class="product-desc">{{ $product->description ?: __('home.default_product_description') }}</p>
                                <div class="product-bottom">
                                    <span class="product-price">{{ number_format($product->price, 2) }} {{ __('home.currency_egp') }}</span>
                                    <button type="button" class="add-btn open-product-modal" data-bs-toggle="modal" data-bs-target="#productQuickAddModal" data-product='@json($productPayload)'>{{ __('home.add_to_cart') }}</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            @foreach($groupedProducts as $categoryName => $categoryProducts)
                <section class="feed-card section-card product-section" data-category="{{ \Illuminate\Support\Str::slug($categoryName) }}" id="section-{{ \Illuminate\Support\Str::slug($categoryName) }}">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">{{ $categoryName }}</h3>
                            <p class="section-sub">{{ $categoryProducts->count() }} {{ __('home.item') }} متاح الآن.</p>
                        </div>
                    </div>
                    <div class="products-grid">
                        @foreach($categoryProducts as $product)
                            @php
                                $productPayload = [
                                    'id' => $product->id,
                                    'name' => $product->name,
                                    'price' => $product->price,
                                    'description' => $product->description,
                                    'image' => $product->image ? \App\Support\MediaUrl::fromPath($product->image) : null,
                                    'options' => $product->relationLoaded('optionGroups')
                                        ? $product->optionGroups->map(function ($group) {
                                            return [
                                                'id' => $group->id,
                                                'name' => $group->name,
                                                'type' => $group->type ?? 'single',
                                                'is_required' => (bool) ($group->is_required ?? false),
                                                'items' => $group->relationLoaded('items')
                                                    ? $group->items->map(fn ($item) => ['id' => $item->id, 'name' => $item->name, 'price' => $item->price ?? 0])->values()->toArray()
                                                    : [],
                                            ];
                                        })->values()->toArray()
                                        : [],
                                ];
                            @endphp
                            <article class="product-card product-card-item" data-name="{{ strtolower($product->name . ' ' . ($product->description ?? '')) }}">
                                <img src="{{ $product->image ? \App\Support\MediaUrl::fromPath($product->image) : 'https://via.placeholder.com/600x400?text=Food' }}" alt="{{ $product->name }}" class="product-thumb">
                                <div class="product-content">
                                    <div class="product-top">
                                        <h4 class="product-name">{{ $product->name }}</h4>
                                        @if($loop->first)
                                            <span class="badge-soft">عرض</span>
                                        @endif
                                    </div>
                                    <p class="product-desc">{{ $product->description ?: __('home.default_product_description') }}</p>
                                    <div class="product-bottom">
                                        <span class="product-price">{{ number_format($product->price, 2) }} {{ __('home.currency_egp') }}</span>
                                        <button type="button" class="add-btn open-product-modal" data-bs-toggle="modal" data-bs-target="#productQuickAddModal" data-product='@json($productPayload)'>{{ __('home.add_to_cart') }}</button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @else
            <section class="feed-card section-card">{{ __('home.no_items_available_now') }}</section>
        @endif
    </main>
</div>

<div class="floating-checkout" id="floatingCheckout" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">
    <div class="floating-checkout-inner">
        <div id="floatingCheckoutValue">{{ $cartCount }} {{ __('home.product') }} • {{ number_format($cartTotal, 2) }} {{ __('home.currency_egp') }}</div>
        <a href="{{ route('cart.index') }}" class="floating-checkout-btn">{{ __('home.continue_order') }}</a>
    </div>
</div>

<nav class="home-bottom-nav" aria-label="التنقل السفلي">
    <div class="home-bottom-nav-inner">
        <a href="{{ route('home') }}" class="home-nav-item active"><i class="bi bi-house-door"></i><span>الرئيسية</span></a>
        <a href="#section-featured" class="home-nav-item" data-story-target="featured"><i class="bi bi-grid"></i><span>المنتجات</span></a>
        <a href="{{ Route::has('my.orders') ? route('my.orders') : route('pages.contact') }}" class="home-nav-item"><i class="bi bi-receipt"></i><span>الطلبات</span></a>
        <a href="{{ Route::has('pages.about') ? route('pages.about') : route('pages.contact') }}" class="home-nav-item"><i class="bi bi-person"></i><span>الحساب</span></a>
        <a href="{{ route('cart.index') }}" class="home-nav-item"><i class="bi bi-bag"></i><span>السلة</span></a>
    </div>
</nav>

@include('front.partials.quick-add-modal', ['cspNonce' => $cspNonce])

@if($popupCampaign)
<div class="offer-popup-overlay" id="offerPopupOverlay"><div class="offer-popup-card">@if($popupCampaign->image)<img src="{{ $popupImageUrl }}" alt="{{ $popupCampaign->title }}" class="offer-popup-image">@endif<div class="offer-popup-body">@if($popupCampaign->title)<h3 class="offer-popup-title">{{ $popupCampaign->title }}</h3>@endif @if($popupCampaign->description)<div class="offer-popup-desc">{{ $popupCampaign->description }}</div>@endif <button type="button" class="offer-popup-close" id="offerPopupCloseBtn">{{ __('home.close') }}</button></div></div></div>
@endif

<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('menuSearchInput');
    const sections = document.querySelectorAll('.product-section');
    const stories = document.querySelectorAll('[data-story-target]');
    const form = document.getElementById('quickAddToCartForm');
    const productName = document.getElementById('quickProductName');
    const productPrice = document.getElementById('quickProductPrice');
    const productDescription = document.getElementById('quickProductDescription');
    const productImage = document.getElementById('quickProductImage');
    const optionsWrap = document.getElementById('quickProductOptions');
    const quantityInput = form.querySelector('input[name="quantity"]');
    const floatingCheckout = document.getElementById('floatingCheckout');
    const floatingCheckoutValue = document.getElementById('floatingCheckoutValue');
    const headerCartCount = document.getElementById('headerCartCount');
    const headerCartButton = document.getElementById('headerCartButton');
    const modalElement = document.getElementById('productQuickAddModal');
    const productModal = bootstrap.Modal.getOrCreateInstance(modalElement);

    let currentCartCount = {{ $cartCount }};
    let currentCartTotal = {{ (float) $cartTotal }};

    function formatMoney(value){return Number(value||0).toFixed(2)+' {{ __('home.currency_egp') }}';}

    function ensureHeaderBadge(count){
        const badge = headerCartCount || (() => {
            if (!headerCartButton) { return null; }
            const span = document.createElement('span');
            span.id = 'headerCartCount';
            span.className = 'badge-dot';
            headerCartButton.appendChild(span);
            return span;
        })();
        if (!badge) { return; }
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    }

    function updateCartUI(cartCount, cartTotal){
        currentCartCount = Number(cartCount || 0);
        currentCartTotal = Number(cartTotal || 0);
        ensureHeaderBadge(currentCartCount);

        if(floatingCheckout && floatingCheckoutValue){
            if(currentCartCount > 0){
                floatingCheckout.style.display = '';
                floatingCheckoutValue.textContent = `${currentCartCount} {{ __('home.product') }} • ${formatMoney(currentCartTotal)}`;
            } else {
                floatingCheckout.style.display = 'none';
            }
        }
    }

    function filterBySearchAndCategory(category = 'all') {
        const searchValue = (searchInput?.value || '').toLowerCase().trim();

        sections.forEach(section => {
            const sectionCategory = section.dataset.category;
            const cards = section.querySelectorAll('.product-card-item');
            let visibleCards = 0;

            cards.forEach(card => {
                const searchableText = card.dataset.name || '';
                const matchesSearch = !searchValue || searchableText.includes(searchValue);
                const matchesCategory = category === 'all' || category === sectionCategory;

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

    function activateStory(target) {
        stories.forEach(story => {
            story.classList.toggle('active', story.dataset.storyTarget === target);
        });
    }

    stories.forEach(story => {
        story.addEventListener('click', function () {
            const target = this.dataset.storyTarget || 'all';
            activateStory(target);
            filterBySearchAndCategory(target === 'most-ordered' || target === 'featured' ? target : target);

            const destination = document.getElementById(`section-${target}`);
            if (destination) {
                destination.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const activeStory = document.querySelector('.story-btn.active')?.dataset.storyTarget || 'all';
            filterBySearchAndCategory(activeStory);
        });
    }

    modalElement?.addEventListener('show.bs.modal', () => document.body.classList.add('mobile-modal-open'));
    modalElement?.addEventListener('hidden.bs.modal', () => document.body.classList.remove('mobile-modal-open'));

    document.querySelectorAll('.open-product-modal').forEach(button => {
        button.addEventListener('click', function () {
            let product = {};
            try { product = JSON.parse(this.dataset.product || '{}'); } catch (e) { product = {}; }

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
                            itemsHtml += `<div class="form-check"><input class="form-check-input" type="checkbox" name="options[${group.id}][]" value="${item.id}" id="opt_${group.id}_${item.id}"><label class="form-check-label" for="opt_${group.id}_${item.id}">${item.name} ${parseFloat(item.price||0) > 0 ? `( +${parseFloat(item.price).toFixed(2)} {{ __('home.currency_egp') }} )` : ''}</label></div>`;
                        });
                    } else {
                        group.items.forEach(item => {
                            itemsHtml += `<div class="form-check"><input class="form-check-input" type="radio" name="options[${group.id}]" value="${item.id}" id="opt_${group.id}_${item.id}" ${group.is_required?'required':''}><label class="form-check-label" for="opt_${group.id}_${item.id}">${item.name} ${parseFloat(item.price||0) > 0 ? `( +${parseFloat(item.price).toFixed(2)} {{ __('home.currency_egp') }} )` : ''}</label></div>`;
                        });
                    }

                    groupBox.innerHTML = `<label class="form-label fw-bold d-block mb-2">${group.name}${group.is_required ? '<span class="text-danger">*</span>' : ''}</label><div class="quick-option-box">${itemsHtml}</div>`;
                    optionsWrap.appendChild(groupBox);
                });
            }
        });
    });

    form.addEventListener('submit', async function(e){
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '{{ __('home.adding') }}';

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
                return;
            }

            productModal.hide();
            form.reset();
            optionsWrap.innerHTML = '';
            quantityInput.value = 1;
            const newCartCount = typeof data.cart_count !== 'undefined' ? data.cart_count : (currentCartCount + parseInt(formData.get('quantity') || 1, 10));
            const newCartTotal = typeof data.cart_total !== 'undefined' ? data.cart_total : currentCartTotal;
            updateCartUI(newCartCount, newCartTotal);
        } catch (error) {
            // intentionally silent to keep feed interactions smooth
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

        if(showOnce && localStorage.getItem(popupId) === '1') {
            canShow = false;
        }

        if (canShow) {
            setTimeout(() => popup.classList.add('show'), 500);
        }

        closeBtn?.addEventListener('click', () => {
            popup.classList.remove('show');
            if (showOnce) localStorage.setItem(popupId, '1');
        });

        popup.addEventListener('click', e => {
            if (e.target === popup) {
                popup.classList.remove('show');
                if (showOnce) localStorage.setItem(popupId, '1');
            }
        });
    }
    @endif
});
</script>
@endsection
