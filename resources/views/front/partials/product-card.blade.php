{{-- 
  Unified Product Card Component
  Reusable vertical card for both regular products and offers
  Features: consistent height, fixed image ratio, bottom-pinned CTA
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
            <span class="product-price">{{ $price }}</span>
            {!! $button !!}
        </div>
    </div>
</article>
