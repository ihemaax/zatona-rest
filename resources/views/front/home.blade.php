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

    $coverImageUrl = \App\Support\MediaUrl::fromPath($setting->banner ?? $setting->cover_image ?? null);
    $logoImageUrl = \App\Support\MediaUrl::fromPath($setting->logo ?? null);
    $popupImageUrl = \App\Support\MediaUrl::fromPath($popupCampaign?->image);
    $storyPlaceholder = asset('images/placeholders/image-placeholder.svg');
    $firstCategoryKey = \Illuminate\Support\Str::slug((string) $groupedProducts->keys()->first());
    $productsSectionTarget = $firstCategoryKey ? "#section-{$firstCategoryKey}" : (($offers ?? collect())->isNotEmpty() ? '#section-offers' : '#menu-area');

    $storyItems = $groupedProducts->map(function ($categoryProducts, $name) use ($storyPlaceholder) {
        $firstProduct = $categoryProducts->first();
        $storyImage = $firstProduct?->image ? \App\Support\MediaUrl::fromPath($firstProduct->image) : null;

        return [
            'key' => \Illuminate\Support\Str::slug($name),
            'label' => \Illuminate\Support\Str::limit($name, 16, ''),
            'image' => $storyImage ?: $storyPlaceholder,
        ];
    })->values();
@endphp

@php
    $manifestPath = public_path('build/manifest.json');
    $hasFrontHomeAssets = false;
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
        $hasFrontHomeAssets = isset($manifest['resources/css/pages/front-home.css'])
            && isset($manifest['resources/js/pages/front-home.js']);
    }
@endphp

@if($hasFrontHomeAssets)
    @vite(['resources/css/pages/front-home.css', 'resources/js/pages/front-home.js'])
@else
    <style>
    {!! file_get_contents(resource_path('css/pages/front-home.css')) !!}
    </style>
@endif

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
                    @featureEnabled('cart')
                    <a href="{{ route('cart.index') }}" class="cart-icon" aria-label="السلة" id="headerCartButton">
                        <i class="bi bi-bag"></i>
                        @if($cartCount > 0)
                            <span class="cart-count" id="headerCartCount">{{ $cartCount }}</span>
                        @endif
                    </a>
                    @endfeatureEnabled
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
                @featureEnabled('cart')
                <a href="{{ route('cart.index') }}" class="profile-cta">السلة ({{ $cartCount }})</a>
                @endfeatureEnabled
            </div>

            <nav class="desktop-tabs" aria-label="روابط الصفحة">
                <a href="{{ route('home') }}" class="active">الرئيسية</a>
                <a href="{{ $productsSectionTarget }}">المنتجات</a>
                @featureEnabled('order_tracking')
                <a href="{{ Route::has('my.orders') ? route('my.orders') : route('pages.contact') }}">الطلبات</a>
                @endfeatureEnabled
                <a href="{{ auth()->check() ? route('account.index') : route('login') }}">الحساب</a>
                @featureEnabled('cart')
                <a href="{{ route('cart.index') }}">السلة</a>
                @endfeatureEnabled
            </nav>

            <label class="search-box mt-3" for="menuSearchInputDesktop">
                <i class="bi bi-search"></i>
                <input id="menuSearchInputDesktop" type="text" placeholder="{{ __('home.search_placeholder') }}">
            </label>
        </section>

        <section class="stories-wrap">
            <div class="stories-row" id="storiesRow">
                <button type="button" class="story-chip active" data-story-target="all">
                    <span class="story-thumb">
                        <img src="{{ $storyPlaceholder }}" alt="الكل" loading="lazy">
                    </span>
                    <span class="story-label">الكل</span>
                </button>
                @if(($offers ?? collect())->isNotEmpty())
                    @php
                        $offersStoryImage = ($offers->first()?->image) ? \App\Support\MediaUrl::fromPath($offers->first()->image) : $storyPlaceholder;
                    @endphp
                    <button type="button" class="story-chip" data-story-target="offers">
                        <span class="story-thumb">
                            <img src="{{ $offersStoryImage }}" alt="العروض" loading="lazy">
                        </span>
                        <span class="story-label">العروض</span>
                    </button>
                @endif
                @foreach($storyItems as $story)
                    <button type="button" class="story-chip" data-story-target="{{ $story['key'] }}">
                        <span class="story-thumb">
                            <img src="{{ $story['image'] }}" alt="{{ $story['label'] }}" loading="lazy">
                        </span>
                        <span class="story-label">{{ $story['label'] }}</span>
                    </button>
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

                    <div class="offers-grid">
                        @foreach($offers as $offer)
                            @php
                                $priceHtml = '';
                                if (!is_null($offer->old_price)) {
                                    $priceHtml = '<small class="product-old-price">' . number_format((float) $offer->old_price, 2) . ' ' . __('home.currency_egp') . '</small>';
                                }
                                $priceHtml .= '<span class="product-price">' . number_format((float) $offer->new_price, 2) . ' ' . __('home.currency_egp') . '</span>';
                            @endphp
                            @include('front.partials.product-card', [
                                'name' => $offer->name,
                                'description' => $offer->short_description ?: 'استفد من العرض الحالي قبل انتهاء الفترة المحددة.',
                                'price' => $priceHtml,
                                'image' => $offer->image ? \App\Support\MediaUrl::fromPath($offer->image) : 'https://via.placeholder.com/600x400?text=Offer',
                                'badge' => 'عرض',
                                'button' => '<a href="' . $productsSectionTarget . '" class="product-btn product-btn-link">شاهد المنيو</a>',
                            ])
                        @endforeach
                    </div>
                </section>
                @endif

                @foreach($groupedProducts as $categoryName => $categoryProducts)
                    <section class="section-card product-section" data-category="{{ \Illuminate\Support\Str::slug($categoryName) }}" id="section-{{ \Illuminate\Support\Str::slug($categoryName) }}">
                        <div class="section-head">
                            <div>
                                <h3 class="section-title">{{ $categoryName }}</h3>
                                <p class="section-sub">{{ $categoryProducts->count() }} {{ __('home.item') }} متاح.</p>
                            </div>
                        </div>

                        <div class="products-grid">
                            @foreach($categoryProducts as $product)
                                @php
                                    $badgeLabel = null;
                                    if ($loop->iteration <= 2) {
                                        $badgeLabel = 'الأكثر طلبًا';
                                    } elseif ($product->created_at && $product->created_at->gt(now()->subDays(10))) {
                                        $badgeLabel = 'جديد';
                                    }
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
                                @include('front.partials.product-card', [
                                    'name' => $product->name,
                                    'description' => $product->description ?: __('home.default_product_description'),
                                    'price' => number_format($product->price, 2) . ' ' . __('home.currency_egp'),
                                    'image' => $product->image ? \App\Support\MediaUrl::fromPath($product->image) : null,
                                    'badge' => $badgeLabel,
                                    'productPayload' => $productPayload,
                                    'buttonText' => __('home.add_to_cart'),
                                ])
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

@featureEnabled('cart')
<div class="floating-cart" id="floatingCheckout" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">
    <div class="floating-cart-inner">
        <a href="{{ route('cart.index') }}" class="floating-cart-icon-link" aria-label="{{ __('site.cart') }}">
            <i class="bi bi-bag-check-fill"></i>
            <span class="floating-cart-badge" id="floatingCheckoutCount">{{ $cartCount }}</span>
        </a>
    </div>
</div>
@endfeatureEnabled

@include('front.partials.quick-add-modal', ['cspNonce' => $cspNonce])

@if($popupCampaign)
<div class="offer-popup-overlay" id="offerPopupOverlay"><div class="offer-popup-card">@if($popupCampaign->image)<img src="{{ $popupImageUrl }}" alt="{{ $popupCampaign->title }}" class="offer-popup-image">@endif<div class="offer-popup-body">@if($popupCampaign->title)<h3 class="offer-popup-title">{{ $popupCampaign->title }}</h3>@endif @if($popupCampaign->description)<div class="offer-popup-desc">{{ $popupCampaign->description }}</div>@endif <button type="button" class="offer-popup-close" id="offerPopupCloseBtn">{{ __('home.close') }}</button></div></div></div>
@endif

<script nonce="{{ $cspNonce }}">
window.frontHomeConfig = {
    cartCount: {{ $cartCount }},
    cartTotal: {{ (float) $cartTotal }},
    cartAddBase: @json(url('/cart/add')),
    currency: @json(__('home.currency_egp')),
    productLabel: @json(__('home.product')),
    addingText: @json(__('home.adding')),
    productFallbackImage: @json('https://via.placeholder.com/600x400?text=Food'),
    popup: @json($popupCampaign ? [
        'id' => $popupCampaign->id,
        'showOnce' => (bool) ($popupCampaign->show_once_per_user ?? $popupCampaign->show_once ?? false),
    ] : null),
};
</script>
@if(!$hasFrontHomeAssets)
    <script nonce="{{ $cspNonce }}">
    {!! file_get_contents(resource_path('js/pages/front-home.js')) !!}
    </script>
@endif
@endsection
