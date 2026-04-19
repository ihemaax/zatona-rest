{{-- 
  Unified Product Card Component
  Reusable vertical card for both regular products and offers
  Features: consistent height, fixed image ratio, bottom-pinned CTA
  
  @props includes:
  - $name: Product/Offer name
  - $description: Short description text
  - $price: Formatted price (can be HTML with old-price)
  - $image: Image URL
  - $badge: Optional badge text
  - $productPayload: Optional product data for modal
  - $buttonText: Optional button text (default: Add to Cart)
--}}
<article class="product-card product-card-item" data-name="{{ strtolower($name . ' ' . ($description ?? '')) }}">
    @if($badge)
        <span class="product-badge">{{ $badge }}</span>
    @endif
    
    <div class="product-image-wrap">
        <img 
            src="{{ $image ?? 'https://via.placeholder.com/600x400?text=Food' }}" 
            alt="{{ $name }}"
            class="product-image"
            loading="lazy"
        >
    </div>

    <div class="product-content">
        <div class="product-header">
            <h4 class="product-title">{{ $name }}</h4>
        </div>

        @if($description)
            <p class="product-description">{{ $description }}</p>
        @endif

        <div class="product-footer">
            <span class="product-price">{!! $price !!}</span>
            
            @if(isset($productPayload))
                {{-- Regular product with add to cart --}}
                @featureEnabled('cart')
                <button type="button" class="product-btn open-product-modal" data-bs-toggle="modal" data-bs-target="#productQuickAddModal" data-product='@json($productPayload)'>
                    {{ $buttonText ?? __('home.add_to_cart') }}
                </button>
                @else
                <button type="button" class="product-btn" disabled title="{{ config('subscription.blocked_message') }}">
                    {{ $buttonText ?? __('home.add_to_cart') }}
                </button>
                @endfeatureEnabled
            @else
                {{-- Offer with link button --}}
                {!! $button ?? '' !!}
            @endif
        </div>
    </div>
</article>
