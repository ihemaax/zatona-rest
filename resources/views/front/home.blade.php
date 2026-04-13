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
{!! file_get_contents(resource_path('css/pages/front-home.css')) !!}
</style>

<div
    class="menu-home"
    style="--home-logo-url: url('{{ $logoImageUrl ?: 'https://via.placeholder.com/300x300?text=Logo' }}'); --home-cover-url: url('{{ $coverImageUrl ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1800&auto=format&fit=crop' }}');"
>
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

@include('front.partials.quick-add-modal', ['cspNonce' => $cspNonce])

@if($popupCampaign)
<div class="offer-popup-overlay" id="offerPopupOverlay"><div class="offer-popup-card">@if($popupCampaign->image)<img src="{{ $popupImageUrl }}" alt="{{ $popupCampaign->title }}" class="offer-popup-image">@endif<div class="offer-popup-body">@if($popupCampaign->title)<h3 class="offer-popup-title">{{ $popupCampaign->title }}</h3>@endif @if($popupCampaign->description)<div class="offer-popup-desc">{{ $popupCampaign->description }}</div>@endif <button type="button" class="offer-popup-close" id="offerPopupCloseBtn">{{ __('home.close') }}</button></div></div></div>
@endif

<script nonce="{{ $cspNonce }}">
window.frontHomeConfig = {
    cartCount: {{ $cartCount }},
    cartTotal: {{ (float) $cartTotal }},
    currency: @json(__('home.currency_egp')),
    productLabel: @json(__('home.product')),
    addingText: @json(__('home.adding')),
    popup: @json($popupCampaign ? [
        'id' => $popupCampaign->id,
        'showOnce' => (bool) ($popupCampaign->show_once_per_user ?? $popupCampaign->show_once ?? false),
    ] : null),
};
</script>
<script nonce="{{ $cspNonce }}">
{!! file_get_contents(resource_path('js/pages/front-home.js')) !!}
</script>
@endsection
