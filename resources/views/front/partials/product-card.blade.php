{{-- 
  Horizontal Product Card Component
  Row-based layout with image on left, content on right
  Features: consistent sizing, RTL support, responsive design
--}}
<article class="menu-item product-card-item" data-name="{{ strtolower($name . ' ' . ($description ?? '')) }}">
    <div class="product-image-wrap">
        <img
            src="{{ $image ?? 'https://via.placeholder.com/600x400?text=Food' }}"
            alt="{{ $name }}"
            class="product-image"
            loading="lazy"
        >
        @if($badge)
            <span class="menu-badge">{{ $badge }}</span>
        @endif
    </div>

    <div class="product-content">
        <h4 class="product-name">{{ $name }}</h4>
        @if($description)
            <p class="product-desc">{{ $description }}</p>
        @endif

        <div class="product-footer">
            <span class="product-price">{!! $price !!}</span>

            @if(isset($productPayload))
                {{-- Regular product with add to cart --}}
                @featureEnabled('cart')
                <button type="button" class="menu-cta-btn add-to-cart-btn open-product-modal" data-bs-toggle="modal" data-bs-target="#productQuickAddModal" data-product='@json($productPayload)'>
                    {{ $buttonText ?? __('home.add_to_cart') }}
                </button>
                @else
                <button type="button" class="menu-cta-btn add-to-cart-btn" disabled title="{{ config('subscription.blocked_message') }}">
                </button>
                @endfeatureEnabled
            @else
                {{-- Offer with link button --}}
                {!! $button ?? '' !!}
            @endif
        </div>
    </div>
</article>
