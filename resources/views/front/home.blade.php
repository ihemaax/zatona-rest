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
    $featuredProducts = $allProducts->filter(fn ($product) => !empty($product->image))->take(5);

    if ($featuredProducts->isEmpty()) {
        $featuredProducts = $allProducts->take(5);
    }

    $coverImageUrl = \App\Support\MediaUrl::fromPath($setting->banner ?? $setting->cover_image ?? null);
    $logoImageUrl = \App\Support\MediaUrl::fromPath($setting->logo ?? null);
    $popupImageUrl = \App\Support\MediaUrl::fromPath($popupCampaign?->image);

    $storyItems = $groupedProducts->keys()->map(function ($name) {
        return [
            'key' => \Illuminate\Support\Str::slug($name),
            'label' => \Illuminate\Support\Str::limit($name, 16, ''),
        ];
    })->values();
@endphp

<style>
    :root {
        --zz-bg: #f6f2ea;
        --zz-surface: #fffdf9;
        --zz-border: #e9dece;
        --zz-text: #20302a;
        --zz-sub: #6f675b;
        --zz-brand: #124638;
        --zz-brand-2: #2f7763;
        --zz-safe-bottom: calc(70px + env(safe-area-inset-bottom, 0px));
    }

    body { overflow-x: hidden; background: var(--zz-bg); }
    #mainNavbar, .site-footer, .mobile-bottom-bar { display: none !important; }
    .page-container.container { max-width: 100%; width: 100%; padding: 0 !important; }

    .menu-home,
    .menu-home * { box-sizing: border-box; min-width: 0; }

    .menu-home {
        width: 100%;
        max-width: 100%;
        color: var(--zz-text);
        padding: 0 0 calc(var(--zz-safe-bottom) + 8px);
    }

    .menu-shell {
        width: 100%;
        max-width: min(980px, 100%);
        margin: 0 auto;
        padding: 0 12px;
    }

    .mobile-top {
        position: sticky;
        top: 0;
        z-index: 40;
        background: color-mix(in srgb, var(--zz-bg) 92%, white);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--zz-border);
    }

    .mobile-top-inner { display: grid; gap: 10px; padding: 10px 0 12px; }

    .mobile-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .identity { display: flex; align-items: center; gap: 10px; }
    .identity-logo {
        width: 44px; height: 44px; border-radius: 13px; flex-shrink: 0;
        background: url('{{ $logoImageUrl ?: "https://via.placeholder.com/300x300?text=Logo" }}') center/cover no-repeat;
        box-shadow: 0 10px 18px rgba(16, 43, 35, 0.18);
    }

    .identity-name { margin: 0; font-size: .98rem; font-weight: 900; line-height: 1.3; }
    .identity-status {
        font-size: .72rem; font-weight: 800; border-radius: 999px; padding: 4px 9px;
        background: #eef8f2; color: #1d7458; border: 1px solid #cfe8da;
    }

    .identity-status.closed { background: #fff2f2; color: #912f2f; border-color: #f3d2d2; }

    .cart-icon {
        width: 40px; height: 40px; border-radius: 12px; text-decoration: none;
        border: 1px solid var(--zz-border); color: #2f3f39; background: white;
        display: inline-flex; align-items: center; justify-content: center; position: relative;
    }

    .cart-count {
        position: absolute; top: -5px; inset-inline-end: -5px;
        min-width: 18px; height: 18px; border-radius: 99px; background: #df4c40;
        color: white; font-size: .66rem; font-weight: 900; display: inline-flex; align-items: center; justify-content: center;
    }

    .search-box { position: relative; }
    .search-box i {
        position: absolute; top: 50%; transform: translateY(-50%);
        inset-inline-start: 12px; color: #8a7f6f;
    }

    .search-box input {
        width: 100%; max-width: 100%;
        border: 1px solid #e2d6c2; border-radius: 14px; background: #fff;
        padding: 11px 12px 11px 36px; font-size: .88rem; font-weight: 700;
    }

    .desktop-profile-wrap { display: none; }

    .stories-wrap { padding-top: 12px; }
    .stories-row {
        display: flex; gap: 8px; overflow-x: auto; padding: 2px 0 4px;
        scrollbar-width: none; overscroll-behavior-inline: contain;
    }

    .stories-row::-webkit-scrollbar { display: none; }
    .story-chip {
        border: 1px solid #dfd2bd; background: #fff; color: #5f584e;
        border-radius: 999px; padding: 7px 12px; font-size: .76rem; font-weight: 800;
        white-space: nowrap; text-decoration: none;
    }

    .story-chip.active { color: #fff; background: linear-gradient(135deg, var(--zz-brand), var(--zz-brand-2)); border-color: #275e4f; }

    .page-feed { display: grid; gap: 12px; padding-top: 10px; }

    .section-card {
        background: var(--zz-surface); border: 1px solid var(--zz-border); border-radius: 18px;
        box-shadow: 0 10px 28px rgba(26, 36, 31, 0.07); padding: 13px;
    }

    .section-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
    .section-title { margin: 0; font-size: 1rem; font-weight: 900; }
    .section-sub { margin: 2px 0 0; font-size: .76rem; color: var(--zz-sub); font-weight: 700; }

    .most-row {
        display: grid; grid-auto-flow: column; grid-auto-columns: minmax(196px, 72%);
        gap: 10px; overflow-x: auto; padding-bottom: 2px; scrollbar-width: none;
    }

    .most-row::-webkit-scrollbar { display: none; }

    .most-card {
        border: 1px solid #eee2d1; border-radius: 16px; background: white; overflow: hidden;
        display: grid; grid-template-rows: auto 1fr; min-width: 0;
    }

    .most-image { width: 100%; aspect-ratio: 4/3; object-fit: cover; background: #efe4d3; }
    .most-body { padding: 10px; display: grid; gap: 7px; }
    .most-title { margin: 0; font-size: .88rem; line-height: 1.5; font-weight: 900; }
    .most-desc {
        margin: 0; font-size: .73rem; line-height: 1.65; color: #6e6659;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }

    .most-footer { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; }
    .price { font-size: .84rem; font-weight: 900; color: #1b6652; }
    .add-btn {
        border: 0; border-radius: 11px; padding: 7px 11px;
        background: linear-gradient(135deg, #0f4032, #2e7762); color: white;
        font-size: .7rem; font-weight: 900;
    }

    .featured-strip { display: grid; gap: 8px; }
    .featured-item {
        border: 1px solid #efe2d0; border-radius: 14px; background: #fff; padding: 10px;
        display: grid; grid-template-columns: 62px minmax(0,1fr) auto; gap: 10px; align-items: center;
    }

    .featured-item img { width: 62px; height: 62px; border-radius: 11px; object-fit: cover; }
    .featured-item h4 { margin: 0; font-size: .84rem; font-weight: 900; line-height: 1.45; }
    .featured-item p { margin: 2px 0 0; font-size: .72rem; color: #786f60; }

    .menu-grid {
        display: grid; grid-template-columns: 1fr; gap: 9px;
    }

    .menu-item {
        border: 1px solid #ecdfcf; border-radius: 15px; background: #fff;
        padding: 9px; display: grid; grid-template-columns: 86px minmax(0, 1fr); gap: 9px;
    }

    .menu-item img { width: 100%; height: 86px; border-radius: 11px; object-fit: cover; }
    .menu-body { display: grid; gap: 6px; align-content: start; }
    .menu-top { display: flex; gap: 8px; justify-content: space-between; align-items: start; }
    .menu-name { margin: 0; font-size: .88rem; font-weight: 900; line-height: 1.45; }
    .menu-desc {
        margin: 0; font-size: .73rem; color: #6e6659; line-height: 1.65;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }

    .menu-footer { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

    .floating-cart {
        position: fixed; z-index: 45; inset-inline: 10px; bottom: calc(64px + env(safe-area-inset-bottom, 0px));
        display: flex; justify-content: center; pointer-events: none;
    }

    .floating-cart-inner {
        width: 100%; max-width: min(720px, 100%);
        background: linear-gradient(120deg, rgba(13, 53, 42, 0.95), rgba(37, 103, 85, 0.92));
        border: 1px solid #2a6654; border-radius: 13px; padding: 8px 10px;
        color: #fff; display: flex; align-items: center; justify-content: space-between; gap: 10px;
        pointer-events: auto;
    }

    .floating-cart-total { font-size: .78rem; font-weight: 900; }
    .floating-cart-inner a {
        text-decoration: none; border-radius: 10px; padding: 7px 10px; background: #f8ebd5;
        color: #18352d; font-size: .72rem; font-weight: 900;
    }

    .home-nav {
        position: fixed; z-index: 46; inset-inline: 0; bottom: 0;
        background: rgba(247, 244, 238, 0.95); border-top: 1px solid var(--zz-border);
        padding: 7px 10px calc(7px + env(safe-area-inset-bottom, 0px)); backdrop-filter: blur(10px);
    }

    .home-nav-inner {
        max-width: min(760px, 100%); margin: 0 auto;
        display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 4px;
    }

    .home-nav a {
        text-decoration: none; color: #6c6459;
        display: grid; justify-items: center; gap: 2px;
        font-size: .63rem; font-weight: 800; padding: 3px;
    }

    .home-nav a i { font-size: .98rem; }
    .home-nav a.active { color: #154f40; }

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

    @media (min-width: 992px) {
        .menu-shell { padding: 0 20px; }

        .mobile-top { display: none; }
        .desktop-profile-wrap { display: block; padding-top: 16px; }

        .cover-card {
            border-radius: 20px; overflow: hidden; border: 1px solid var(--zz-border);
            background: #e6dbc9; box-shadow: 0 14px 30px rgba(26,36,31,.1);
        }

        .cover-banner {
            width: 100%; aspect-ratio: 5/1.55;
            background: linear-gradient(120deg, rgba(14, 53, 42, .55), rgba(39, 104, 87, .32)),
            url('{{ $coverImageUrl ?: "https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1800&auto=format&fit=crop" }}') center/cover no-repeat;
        }

        .profile-card {
            margin: -42px 20px 0;
            border-radius: 18px; border: 1px solid #e8ddce; background: #fff;
            padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px;
        }

        .profile-left { display: flex; align-items: center; gap: 12px; }
        .profile-logo {
            width: 78px; height: 78px; border-radius: 18px; flex-shrink: 0;
            border: 3px solid #fff;
            background: url('{{ $logoImageUrl ?: "https://via.placeholder.com/300x300?text=Logo" }}') center/cover no-repeat;
            box-shadow: 0 12px 20px rgba(19, 37, 31, .18);
        }

        .profile-name { margin: 0; font-size: 1.3rem; font-weight: 900; }
        .profile-sub { margin: 4px 0 0; color: #6f675b; font-size: .82rem; font-weight: 700; }
        .profile-meta { display: flex; gap: 7px; flex-wrap: wrap; margin-top: 8px; }
        .meta-pill {
            border-radius: 999px; border: 1px solid #e5d8c6;
            background: #faf5ec; color: #5f574d;
            font-size: .72rem; font-weight: 800; padding: 5px 10px;
        }

        .profile-cta {
            text-decoration: none; border-radius: 12px;
            background: linear-gradient(135deg, var(--zz-brand), var(--zz-brand-2));
            color: #fff; font-size: .8rem; font-weight: 900; padding: 9px 14px;
            white-space: nowrap;
        }

        .desktop-tabs {
            margin-top: 12px;
            border: 1px solid var(--zz-border); border-radius: 16px; background: #fff;
            padding: 8px; display: flex; gap: 8px; flex-wrap: wrap;
        }

        .desktop-tabs a {
            border: 1px solid #e9ddcc; border-radius: 999px; text-decoration: none;
            color: #5d564c; background: #fbf7f1; font-size: .78rem; font-weight: 800;
            padding: 8px 13px;
        }

        .desktop-tabs a.active {
            color: #fff; border-color: #265e4f;
            background: linear-gradient(135deg, var(--zz-brand), var(--zz-brand-2));
        }

        .stories-wrap { padding-top: 14px; }
        .most-row { grid-auto-columns: minmax(220px, 31%); }
        .menu-grid { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px; }
        .menu-item { grid-template-columns: 102px minmax(0,1fr); }
        .menu-item img { height: 102px; }

        .home-nav { display: none; }
        .floating-cart { bottom: 14px; }
    }
</style>

<div class="menu-home">
    <div class="menu-shell">
        <header class="mobile-top">
            <div class="mobile-top-inner">
                <div class="mobile-head">
                    <div class="identity">
                        <div class="identity-logo"></div>
                        <div>
                            <h1 class="identity-name">{{ $restaurantName }}</h1>
                            <span class="identity-status {{ $isOpen ? '' : 'closed' }}">{{ $isOpen ? 'مفتوح الآن' : 'مغلق الآن' }}</span>
                        </div>
                    </div>
                    <a href="{{ route('cart.index') }}" class="cart-icon" aria-label="السلة" id="headerCartButton">
                        <i class="bi bi-bag"></i>
                        @if($cartCount > 0)
                            <span class="cart-count" id="headerCartCount">{{ $cartCount }}</span>
                        @endif
                    </a>
                </div>

                <label class="search-box" for="menuSearchInput">
                    <i class="bi bi-search"></i>
                    <input id="menuSearchInput" type="text" placeholder="{{ __('home.search_placeholder') }}">
                </label>
            </div>
        </header>

        <section class="desktop-profile-wrap">
            <div class="cover-card">
                <div class="cover-banner"></div>
            </div>

            <div class="profile-card">
                <div class="profile-left">
                    <div class="profile-logo"></div>
                    <div>
                        <h2 class="profile-name">{{ $restaurantName }}</h2>
                        <p class="profile-sub">تجربة طلب راقية وسريعة — منيو واضح، إضافة سلسة، ودفع مريح.</p>
                        <div class="profile-meta">
                            <span class="meta-pill">{{ $isOpen ? 'مفتوح الآن' : 'مغلق الآن' }}</span>
                            <span class="meta-pill">{{ number_format($deliveryFee, 2) }} {{ __('home.currency_egp') }} توصيل</span>
                            <span class="meta-pill">{{ $products->count() }} عنصر</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('cart.index') }}" class="profile-cta">السلة ({{ $cartCount }})</a>
            </div>

            <nav class="desktop-tabs" aria-label="روابط الصفحة">
                <a href="{{ route('home') }}" class="active">الرئيسية</a>
                <a href="#section-featured" data-story-target="featured">المنتجات</a>
                <a href="{{ Route::has('my.orders') ? route('my.orders') : route('pages.contact') }}">الطلبات</a>
                <a href="{{ Route::has('pages.about') ? route('pages.about') : route('pages.contact') }}">الحساب</a>
                <a href="{{ route('cart.index') }}">السلة</a>
            </nav>

            <label class="search-box mt-3" for="menuSearchInputDesktop">
                <i class="bi bi-search"></i>
                <input id="menuSearchInputDesktop" type="text" placeholder="{{ __('home.search_placeholder') }}">
            </label>
        </section>

        <section class="stories-wrap">
            <div class="stories-row" id="storiesRow">
                <button type="button" class="story-chip active" data-story-target="all">الكل</button>
                @if(($offers ?? collect())->isNotEmpty())
                    <button type="button" class="story-chip" data-story-target="offers">العروض</button>
                @endif
                <button type="button" class="story-chip" data-story-target="featured">مميزة</button>
                @foreach($storyItems as $story)
                    <button type="button" class="story-chip" data-story-target="{{ $story['key'] }}">{{ $story['label'] }}</button>
                @endforeach
            </div>
        </section>

        <main class="page-feed" id="menu-area">
            @if($products->count())
                @if(($offers ?? collect())->isNotEmpty())
                <section class="section-card product-section" data-category="offers" id="section-offers">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">العروض</h3>
                            <p class="section-sub">أفضل العروض المتاحة حالياً.</p>
                        </div>
                    </div>

                    <div class="most-row">
                        @foreach($offers as $offer)
                            <article class="most-card product-card-item" data-name="{{ strtolower($offer->name . ' ' . ($offer->short_description ?? '')) }}">
                                <img src="{{ $offer->image ? \App\Support\MediaUrl::fromPath($offer->image) : 'https://via.placeholder.com/600x400?text=Offer' }}" class="most-image" alt="{{ $offer->name }}">
                                <div class="most-body">
                                    <h4 class="most-title">{{ $offer->name }}</h4>
                                    <p class="most-desc">{{ $offer->short_description ?: 'استفد من العرض الحالي قبل انتهاء الفترة المحددة.' }}</p>
                                    <div class="most-footer">
                                        <span class="price">
                                            @if(!is_null($offer->old_price))
                                                <small class="text-muted text-decoration-line-through d-block">{{ number_format((float) $offer->old_price, 2) }} {{ __('home.currency_egp') }}</small>
                                            @endif
                                            {{ number_format((float) $offer->new_price, 2) }} {{ __('home.currency_egp') }}
                                        </span>
                                        <a href="#section-featured" class="add-btn text-decoration-none d-inline-flex align-items-center">شاهد المنيو</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
                @endif

                <section class="section-card product-section" data-category="featured" id="section-featured">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">منتجات مميزة</h3>
                            <p class="section-sub">اختيارات خفيفة وسريعة من المنيو.</p>
                        </div>
                    </div>

                    <div class="featured-strip">
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

                            <article class="featured-item product-card-item" data-name="{{ strtolower($product->name . ' ' . ($product->description ?? '')) }}">
                                <img src="{{ $product->image ? \App\Support\MediaUrl::fromPath($product->image) : 'https://via.placeholder.com/600x400?text=Food' }}" alt="{{ $product->name }}">
                                <div>
                                    <h4>{{ $product->name }}</h4>
                                    <p>{{ $product->description ?: __('home.default_product_description') }}</p>
                                </div>
                                <button type="button" class="add-btn open-product-modal" data-bs-toggle="modal" data-bs-target="#productQuickAddModal" data-product='@json($productPayload)'>{{ __('home.add_to_cart') }}</button>
                            </article>
                        @endforeach
                    </div>
                </section>

                @foreach($groupedProducts as $categoryName => $categoryProducts)
                    <section class="section-card product-section" data-category="{{ \Illuminate\Support\Str::slug($categoryName) }}" id="section-{{ \Illuminate\Support\Str::slug($categoryName) }}">
                        <div class="section-head">
                            <div>
                                <h3 class="section-title">{{ $categoryName }}</h3>
                                <p class="section-sub">{{ $categoryProducts->count() }} {{ __('home.item') }} متاح.</p>
                            </div>
                        </div>

                        <div class="menu-grid">
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
                                <article class="menu-item product-card-item" data-name="{{ strtolower($product->name . ' ' . ($product->description ?? '')) }}">
                                    <img src="{{ $product->image ? \App\Support\MediaUrl::fromPath($product->image) : 'https://via.placeholder.com/600x400?text=Food' }}" alt="{{ $product->name }}">
                                    <div class="menu-body">
                                        <div class="menu-top">
                                            <h4 class="menu-name">{{ $product->name }}</h4>
                                        </div>
                                        <p class="menu-desc">{{ $product->description ?: __('home.default_product_description') }}</p>
                                        <div class="menu-footer">
                                            <span class="price">{{ number_format($product->price, 2) }} {{ __('home.currency_egp') }}</span>
                                            <button type="button" class="add-btn open-product-modal" data-bs-toggle="modal" data-bs-target="#productQuickAddModal" data-product='@json($productPayload)'>{{ __('home.add_to_cart') }}</button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @else
                <section class="section-card">{{ __('home.no_items_available_now') }}</section>
            @endif
        </main>
    </div>
</div>

<div class="floating-cart" id="floatingCheckout" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">
    <div class="floating-cart-inner">
        <div class="floating-cart-total" id="floatingCheckoutValue">{{ $cartCount }} {{ __('home.product') }} • {{ number_format($cartTotal, 2) }} {{ __('home.currency_egp') }}</div>
        <a href="{{ route('cart.index') }}">{{ __('home.continue_order') }}</a>
    </div>
</div>

<nav class="home-nav" aria-label="التنقل السفلي">
    <div class="home-nav-inner">
        <a href="{{ route('home') }}" class="active"><i class="bi bi-house-door"></i><span>الرئيسية</span></a>
        <a href="#section-featured" data-story-target="featured"><i class="bi bi-grid"></i><span>المنتجات</span></a>
        <a href="{{ Route::has('my.orders') ? route('my.orders') : route('pages.contact') }}"><i class="bi bi-receipt"></i><span>الطلبات</span></a>
        <a href="{{ Route::has('pages.about') ? route('pages.about') : route('pages.contact') }}"><i class="bi bi-person"></i><span>الحساب</span></a>
        <a href="{{ route('cart.index') }}"><i class="bi bi-bag"></i><span>السلة</span></a>
    </div>
</nav>

@include('front.partials.quick-add-modal', ['cspNonce' => $cspNonce])

@if($popupCampaign)
<div class="offer-popup-overlay" id="offerPopupOverlay"><div class="offer-popup-card">@if($popupCampaign->image)<img src="{{ $popupImageUrl }}" alt="{{ $popupCampaign->title }}" class="offer-popup-image">@endif<div class="offer-popup-body">@if($popupCampaign->title)<h3 class="offer-popup-title">{{ $popupCampaign->title }}</h3>@endif @if($popupCampaign->description)<div class="offer-popup-desc">{{ $popupCampaign->description }}</div>@endif <button type="button" class="offer-popup-close" id="offerPopupCloseBtn">{{ __('home.close') }}</button></div></div></div>
@endif

<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function () {
    const mobileSearch = document.getElementById('menuSearchInput');
    const desktopSearch = document.getElementById('menuSearchInputDesktop');
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
    const headerCartButton = document.getElementById('headerCartButton');
    let headerCartCount = document.getElementById('headerCartCount');
    const modalElement = document.getElementById('productQuickAddModal');
    const productModal = bootstrap.Modal.getOrCreateInstance(modalElement);

    let currentCartCount = {{ $cartCount }};
    let currentCartTotal = {{ (float) $cartTotal }};
    let activeCategory = 'all';

    function formatMoney(value){return Number(value || 0).toFixed(2) + ' {{ __('home.currency_egp') }}';}

    function ensureHeaderBadge(count){
        if (!headerCartCount && headerCartButton) {
            headerCartCount = document.createElement('span');
            headerCartCount.id = 'headerCartCount';
            headerCartCount.className = 'cart-count';
            headerCartButton.appendChild(headerCartCount);
        }

        if (!headerCartCount) return;
        if (count > 0) {
            headerCartCount.textContent = count;
            headerCartCount.style.display = '';
        } else {
            headerCartCount.style.display = 'none';
        }
    }

    function getSearchValue() {
        return (mobileSearch?.value || desktopSearch?.value || '').toLowerCase().trim();
    }

    function syncSearchInputs(source) {
        const value = source?.value || '';
        if (mobileSearch && source !== mobileSearch) mobileSearch.value = value;
        if (desktopSearch && source !== desktopSearch) desktopSearch.value = value;
    }

    function filterMenu() {
        const searchValue = getSearchValue();
        sections.forEach(section => {
            const sectionCategory = section.dataset.category;
            const cards = section.querySelectorAll('.product-card-item');
            let visibleCards = 0;

            cards.forEach(card => {
                const searchable = card.dataset.name || '';
                const searchMatch = !searchValue || searchable.includes(searchValue);
                const categoryMatch = activeCategory === 'all' || activeCategory === sectionCategory;
                if (searchMatch && categoryMatch) {
                    card.style.display = '';
                    visibleCards += 1;
                } else {
                    card.style.display = 'none';
                }
            });

            section.style.display = visibleCards ? '' : 'none';
        });
    }

    function setActiveStory(target) {
        activeCategory = target || 'all';
        stories.forEach(story => {
            story.classList.toggle('active', story.dataset.storyTarget === activeCategory);
        });
        filterMenu();
    }

    function updateCartUI(cartCount, cartTotal){
        currentCartCount = Number(cartCount || 0);
        currentCartTotal = Number(cartTotal || 0);
        ensureHeaderBadge(currentCartCount);

        if (floatingCheckout && floatingCheckoutValue) {
            if (currentCartCount > 0) {
                floatingCheckout.style.display = '';
                floatingCheckoutValue.textContent = `${currentCartCount} {{ __('home.product') }} • ${formatMoney(currentCartTotal)}`;
            } else {
                floatingCheckout.style.display = 'none';
            }
        }
    }

    stories.forEach(story => {
        story.addEventListener('click', function () {
            const target = this.dataset.storyTarget || 'all';
            setActiveStory(target);
            const destination = document.getElementById(`section-${target}`);
            if (destination) destination.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    mobileSearch?.addEventListener('input', function () { syncSearchInputs(mobileSearch); filterMenu(); });
    desktopSearch?.addEventListener('input', function () { syncSearchInputs(desktopSearch); filterMenu(); });

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
            if (!response.ok) return;

            productModal.hide();
            form.reset();
            optionsWrap.innerHTML = '';
            quantityInput.value = 1;
            const newCartCount = typeof data.cart_count !== 'undefined' ? data.cart_count : (currentCartCount + parseInt(formData.get('quantity') || 1, 10));
            const newCartTotal = typeof data.cart_total !== 'undefined' ? data.cart_total : currentCartTotal;
            updateCartUI(newCartCount, newCartTotal);
        } catch (error) {
            // noop
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

        if (showOnce && localStorage.getItem(popupId) === '1') canShow = false;
        if (canShow) setTimeout(() => popup.classList.add('show'), 500);

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
