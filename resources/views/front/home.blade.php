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
    $coverImageUrl = \App\Support\MediaUrl::fromPath($coverImage);
    $logoImageUrl = \App\Support\MediaUrl::fromPath($logoImage);
    $popupImageUrl = \App\Support\MediaUrl::fromPath($popupCampaign?->image);
@endphp

<style>
    .elite-home{
        --zaatar:#0f3a2f;
        --zaatar-dark:#0b2c24;
        --olive:#2f6f5f;
        --olive-soft:#e8f2ee;
        --cream:#f7f3ea;
        --sand:#e6dbc8;
        --text:#14211c;
        --muted:#5c6a64;
        max-width: 1240px;
        margin: 0 auto;
        padding-bottom: 124px;
        color: var(--text);
        position: relative;
        font-family: 'Instrument Sans','Cairo',sans-serif;
    }
    .elite-home::before{content:"";position:fixed;inset:0;pointer-events:none;background:radial-gradient(circle at 8% 0%,rgba(16,89,73,.16),transparent 28%),radial-gradient(circle at 92% 10%,rgba(195,154,99,.16),transparent 34%);z-index:-1}
    .elite-hero-shell{ margin-bottom: 24px; }
    .elite-hero-card{border-radius:0 0 40px 40px;overflow:hidden;border:1px solid #d8cab2;background:var(--cream);box-shadow:0 24px 56px rgba(17,33,28,.2)}
    .elite-cover{min-height:390px;background:linear-gradient(112deg,rgba(6,34,28,.76),rgba(8,42,35,.44)),url('{{ $coverImageUrl ?: "https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=1600&auto=format&fit=crop" }}') center/cover no-repeat;position:relative}
    .elite-cover::after{content:"";position:absolute;inset:auto 0 0;height:190px;background:linear-gradient(to top,rgba(10,16,13,.8),transparent)}
    .elite-hero-content{ margin-top: -92px; padding: 0 24px 24px; position: relative; z-index: 2; }
    .elite-identity-card{border-radius:30px;border:1px solid rgba(255,255,255,.5);background:rgba(247,243,234,.96);backdrop-filter:blur(10px);box-shadow:0 18px 36px rgba(16,35,30,.14);padding:22px}
    .elite-identity-top{ display:grid; grid-template-columns: auto minmax(0,1fr) auto; gap:18px; align-items:center; }
    .elite-logo-frame{width:120px;height:120px;border-radius:50%;padding:5px;background:conic-gradient(from 220deg at 50% 50%,#fefaf2,#d6be95,#fefaf2);box-shadow:0 14px 26px rgba(15,40,33,.22)}
    .elite-logo{ width:100%; height:100%; border-radius:50%; border:4px solid #fff; background:url('{{ $logoImageUrl ?: "https://via.placeholder.com/500x500?text=Logo" }}') center/cover no-repeat,#fff; }
    .elite-brand-kicker{display:inline-flex;align-items:center;gap:8px;background:#e7ddd0;color:#6b5f4f;border-radius:999px;padding:7px 12px;font-size:.74rem;font-weight:900;margin-bottom:10px}
    .elite-brand-kicker .dot{width:7px;height:7px;border-radius:50%;background:var(--olive);display:inline-block}
    .elite-title{ margin:0 0 8px; font-size:2.1rem; font-weight:900; letter-spacing:-.03em; }
    .elite-subtitle{ margin:0; color:var(--muted); font-weight:700; line-height:1.9; max-width:720px; }
    .elite-actions{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
    .elite-btn-primary,.elite-btn-secondary{ min-height:46px; padding:11px 18px; border-radius:14px; font-weight:900; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
    .elite-btn-primary{ color:#fff; background:linear-gradient(135deg,var(--zaatar-dark),var(--olive)); box-shadow:0 14px 24px rgba(19,67,55,.3); }
    .elite-btn-secondary{ color:var(--text); background:#efe3d2; border:1px solid #dac7ab; }
    .elite-meta-row{ margin-top:16px; padding-top:14px; border-top:1px solid #e3d5c0; display:flex; flex-wrap:wrap; gap:10px; }
    .elite-pill{ border-radius:999px; padding:9px 13px; background:#f4ead9; border:1px solid #e7d8bf; color:#635b4f; font-size:.82rem; font-weight:900; display:inline-flex; align-items:center; gap:7px; }
    .elite-pill.success{ background:#dff1e9; border-color:#c5dfd5; color:#165547; }
    .elite-pill .dot{ width:8px; height:8px; border-radius:50%; background:currentColor; }
    .elite-layout{ display:grid; grid-template-columns: 310px minmax(0,1fr); gap:18px; align-items:start; }
    .elite-sidebar,.elite-main{ display:grid; gap:18px; }
    .elite-card,.elite-section,.elite-empty{ background:var(--cream); border:1px solid #deceb4; border-radius:24px; box-shadow:0 10px 26px rgba(20,33,28,.09); overflow:hidden; }
    .elite-card-body{ padding:18px; }
    .elite-card-title,.elite-section-title{ margin:0; font-size:1.02rem; font-weight:900; }
    .elite-message{ border-radius:20px; padding:18px; color:#fff; background:linear-gradient(132deg,#0f3a2f,#2f6f5f); box-shadow:0 16px 28px rgba(15,58,47,.3); border:1px solid rgba(255,255,255,.15); }
    .elite-message strong{ line-height:1.85; font-size:.95rem; display:block; }
    .elite-info-list{ display:grid; gap:12px; margin-top:14px; }
    .elite-info-item{ display:flex; gap:10px; align-items:flex-start; font-size:.88rem; color:#3d4943; font-weight:800; border:1px solid #eadcc7; border-radius:14px; padding:10px; background:#fffcf6; }
    .elite-info-icon{ width:34px; height:34px; border-radius:11px; background:linear-gradient(145deg,#ecf5f1,#d4e8e1); color:#285a4d; display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; }
    .elite-search-wrap{ padding:14px; }
    .elite-search input{ width:100%; border:1px solid #dfccb2; background:#fdf7ed; border-radius:999px; padding:14px 16px 14px 46px; font-size:.93rem; font-weight:700; }
    .elite-search svg{ position:absolute; inset-inline-start:14px; top:50%; transform:translateY(-50%); width:18px; height:18px; stroke:#8f7f67; }
    .elite-search{ position:relative; }
    .elite-alert{ display:none; margin-top:12px; }
    .elite-alert-box{ border-radius:14px; padding:11px 13px; font-size:.88rem; font-weight:900; }
    .elite-categories{ display:flex; gap:12px; overflow:auto; padding-bottom:4px; scrollbar-width:none; }
    .elite-categories::-webkit-scrollbar{ display:none; }
    .elite-cat{ border:none; background:transparent; width:94px; min-width:94px; text-align:center; }
    .elite-cat-ring{ width:94px; height:94px; border-radius:50%; padding:3px; background:linear-gradient(140deg,#d4c2a3,#f1e7d4); margin:0 auto 8px; transition:.2s; }
    .elite-cat-inner{ width:100%; height:100%; border-radius:50%; overflow:hidden; border:3px solid #fff; background:#f0e9db; }
    .elite-cat-label{ font-size:.78rem; font-weight:900; color:#5d554a; line-height:1.3; }
    .elite-cat.active .elite-cat-ring{ transform:translateY(-2px); background:linear-gradient(140deg,#0f3a2f,#3f846f); box-shadow:0 14px 24px rgba(28,77,64,.25); }
    .elite-section-head{ display:flex; justify-content:space-between; align-items:center; gap:10px; padding:18px 18px 10px; background:linear-gradient(180deg,#fdf8ef, #fffdf9); border-bottom:1px solid #ede3d5; }
    .elite-products{ display:grid; gap:14px; padding:0 18px 18px; }
    .elite-product{display:grid;grid-template-columns:190px minmax(0,1fr);gap:14px;border:1px solid #e6d9c6;border-radius:20px;overflow:hidden;background:linear-gradient(180deg,#fffefb,#f8f2e7);box-shadow:0 10px 20px rgba(22,36,31,.08);transition:.2s}
    .elite-product:hover{transform:translateY(-2px)}
    .elite-product-image{width:100%;height:100%;min-height:190px;object-fit:cover;background:#f3ebdd}
    .elite-product-badge{position:absolute;top:12px;inset-inline-start:12px;background:rgba(11,44,36,.8);color:#fff;border-radius:999px;padding:6px 11px;font-size:.66rem;font-weight:900}
    .elite-product-body{padding:16px 16px 16px 0;display:flex;flex-direction:column;gap:10px}
    .elite-product-name{margin:0;font-size:1.02rem;font-weight:900}
    .elite-product-category{background:#e4efe9;color:#1f6553;border-radius:999px;padding:6px 10px;font-size:.66rem;font-weight:900}
    .elite-product-desc{margin:0;color:#6f675a;font-size:.83rem;line-height:1.75;font-weight:700}
    .elite-add-btn{border:none;min-width:136px;border-radius:14px;padding:11px 15px;font-size:.82rem;font-weight:900;color:#fff;background:linear-gradient(135deg,#0b2c24,#2f6f5f)}
    .elite-floating-cart{ position:fixed; inset-inline:14px; bottom:14px; z-index:1050; display:flex; justify-content:center; pointer-events:none; }
    .elite-floating-cart-inner{max-width:460px;width:100%;pointer-events:auto;border:1px solid #356a59;background:linear-gradient(120deg, rgba(11,44,36,.95), rgba(47,111,95,.94));border-radius:18px;padding:10px;box-shadow:0 14px 28px rgba(14,27,23,.3);display:flex;justify-content:space-between;align-items:center;gap:12px}
    .elite-floating-cart-value{font-size:.9rem;font-weight:900;color:#fff}
    .elite-floating-cart-btn{color:#18302a !important;text-decoration:none;border-radius:14px;padding:11px 15px;background:#f6e8d0 !important;border:1px solid #e4cfab;font-weight:900}
    .offer-popup-btn{ display:block; width:100%; border:none; text-decoration:none; border-radius:14px; padding:12px 15px; color:#fff !important; background:linear-gradient(135deg,#0b2c24,#2f6f5f); font-weight:900; margin-bottom:10px; }
    .offer-popup-overlay{position:fixed;inset:0;background:rgba(8,12,10,.64);display:none;align-items:center;justify-content:center;padding:16px;z-index:999999}
    .offer-popup-overlay.show{display:flex}
    .offer-popup-card{max-width:440px;width:100%;background:#fff;border:1px solid #e5d7c4;border-radius:22px;overflow:hidden;box-shadow:0 28px 64px rgba(0,0,0,.3)}
    .offer-popup-image{width:100%;height:300px;object-fit:cover;background:#efe6d7}
    .offer-popup-body{padding:18px;text-align:center}
    .offer-popup-title{margin:0 0 8px;font-size:1.18rem;font-weight:900;color:#1a2723}
    .offer-popup-desc{margin:0 0 14px;color:#666056;line-height:1.8;font-size:.9rem;font-weight:700}
    .offer-popup-close{width:100%;border:none;border-radius:12px;padding:11px 14px;background:#f2f3f5;font-weight:800}
    @media (max-width: 991.98px){.elite-layout{grid-template-columns:1fr}.elite-sidebar{order:2}.elite-main{order:1}.elite-identity-top{grid-template-columns:auto minmax(0,1fr)}.elite-actions{grid-column:1/-1;justify-content:flex-start}}
    @media (max-width:767.98px){.elite-home{padding-bottom:112px}.elite-cover{min-height:220px}.elite-hero-content{margin-top:-36px;padding:0 12px 14px}.elite-identity-card{padding:14px;border-radius:20px}.elite-identity-top{grid-template-columns:1fr;gap:14px}.elite-logo-frame{width:84px;height:84px}.elite-title{font-size:1.12rem}.elite-subtitle{font-size:.8rem}.elite-actions{display:grid;grid-template-columns:1fr 1fr;width:100%;gap:8px}.elite-card-body,.elite-search-wrap{padding:12px}.elite-product{grid-template-columns:84px minmax(0,1fr);gap:10px;padding:8px}.elite-product-image{height:84px;min-height:auto}.elite-product-badge{display:none}.elite-product-body{padding:0}.elite-add-btn{min-width:auto;font-size:.71rem;padding:9px 12px}}
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

                            <p class="elite-subtitle">جعان وعايز تختار بسرعة؟ المنيو هنا مترتبة بنَفَس زعتر وزيتونة.. تختار، تضيف، وتكمل من غير لفة.</p>
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
                        <strong>اختار اللي نفسك فيه فورًا.. كل صنف واضح، وكل خطوة محسوبة عشان الطلب يخلص في دقايق.</strong>
                    </div>
                </div>
            </div>

            <div class="elite-card">
                <div class="elite-card-body">
                    <h3 class="elite-card-title">رحلة طلب بسيطة وواضحة</h3>

                    <div class="elite-info-list">
                        <div class="elite-info-item">
                            <span class="elite-info-icon"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                            <div>أقسام واضحة تساعدك توصل للصنف اللي في بالك من أول نظرة.</div>
                        </div>

                        <div class="elite-info-item">
                            <span class="elite-info-icon"><i class="bi bi-truck"></i></span>
                            <div>توصيل أو استلام؟ اختار اللي يناسب يومك في ضغطة واحدة.</div>
                        </div>

                        <div class="elite-info-item">
                            <span class="elite-info-icon"><i class="bi bi-shield-check"></i></span>
                            <div>متابعة حالة الطلب لحظة بلحظة لحد ما يبقى بين إيديك.</div>
                        </div>

                        <div class="elite-info-item">
                            <span class="elite-info-icon"><i class="bi bi-cash-coin"></i></span>
                            <div>الدفع الحالي كاش، بشكل واضح وسريع وقت الاستلام أو التوصيل.</div>
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
                                    <img src="{{ $coverImage ? \App\Support\MediaUrl::fromPath( $coverImage) : '' }}" alt="{{ __('home.all') }}">
                                </div>
                            </div>
                            <div class="elite-cat-label">{{ __('home.all') }}</div>
                        </button>

                        @foreach($groupedProducts as $categoryName => $categoryProducts)
                            @php
                                $firstProduct = $categoryProducts->first();
                                $categoryImage = $firstProduct && $firstProduct->image
                                    ? \App\Support\MediaUrl::fromPath( $firstProduct->image)
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
                                            'image' => $product->image ? \App\Support\MediaUrl::fromPath( $product->image) : null,
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
                                                src="{{ $product->image ? \App\Support\MediaUrl::fromPath( $product->image) : 'https://via.placeholder.com/600x400?text=Food' }}"
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
            <img src="{{ $popupImageUrl }}" alt="{{ $popupCampaign->title }}" class="offer-popup-image">
        @endif

        <div class="offer-popup-body">
            @if($popupCampaign->title)
                <h3 class="offer-popup-title">{{ $popupCampaign->title }}</h3>
            @endif

            @if($popupCampaign->description)
                <div class="offer-popup-desc">{{ $popupCampaign->description }}</div>
            @endif

            <button type="button" class="offer-popup-close" id="offerPopupCloseBtn">
                {{ __('home.close') }}
            </button>
        </div>
    </div>
</div>
@endif

<script nonce="{{ $cspNonce }}">
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
