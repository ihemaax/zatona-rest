@extends('layouts.app')

@section('content')
@php
    $manifestPath = public_path('build/manifest.json');
    $hasFrontProductAssets = false;
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
        $hasFrontProductAssets = isset($manifest['resources/css/pages/front-product-show.css'])
            && isset($manifest['resources/js/pages/front-product-show.js']);
    }
@endphp

@if($hasFrontProductAssets)
    @vite(['resources/css/pages/front-product-show.css', 'resources/js/pages/front-product-show.js'])
@else
    <style>
    {!! file_get_contents(resource_path('css/pages/front-product-show.css')) !!}
    </style>
@endif

<div class="elite-product-page">
    <div class="elite-product-wrap">
        <div class="elite-product-media-card">
            <img
                src="{{ $product->image ? \App\Support\MediaUrl::fromPath( $product->image) : 'https://via.placeholder.com/700x500?text=Food' }}"
                class="elite-product-image"
                alt="{{ $product->name }}"
            >
        </div>

        <div class="elite-product-info-card">
            @if($product->category?->name)
                <span class="elite-product-category">{{ $product->category?->name }}</span>
            @endif

            <h1 class="elite-product-title">{{ $product->name }}</h1>

            @if($product->description)
                <p class="elite-product-desc">{{ $product->description }}</p>
            @endif

            <div class="elite-price-box">
                <div>
                    <div class="elite-price-box-label">{{ __('product.product_price') }}</div>
                    <div class="elite-price-box-value">{{ number_format($product->price, 2) }} {{ __('product.currency_egp') }}</div>
                </div>
            </div>

            <form action="{{ route('cart.add', $product->id) }}" method="POST" id="productOptionsForm">
                @csrf

                <div class="elite-options-stack">
                    @foreach($product->optionGroups as $group)
                        <div class="elite-option-card">
                            <div class="elite-option-head">
                                <h5 class="elite-option-title">{{ $group->name }}</h5>
                                <span class="elite-option-note">
                                    {{ $group->type === 'single' ? __('product.single_choice') : __('product.multiple_choices') }}
                                    {{ $group->is_required ? '• ' . __('product.required') : '' }}
                                </span>
                            </div>

                            <div class="elite-option-list">
                                @if($group->type === 'single')
                                    @foreach($group->items as $item)
                                        <div class="elite-option-item">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input option-input"
                                                    type="radio"
                                                    name="group_{{ $group->id }}"
                                                    id="item_{{ $item->id }}"
                                                    value="{{ $item->id }}"
                                                    data-price="{{ $item->price }}"
                                                    {{ $item->is_default ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="item_{{ $item->id }}">
                                                    {{ $item->name }}
                                                    @if($item->price > 0)
                                                        <span class="elite-option-price">(+{{ number_format($item->price, 2) }} {{ __('product.currency_egp') }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    @foreach($group->items as $item)
                                        <div class="elite-option-item">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input option-input group-multiple-{{ $group->id }}"
                                                    type="checkbox"
                                                    name="group_{{ $group->id }}[]"
                                                    id="item_{{ $item->id }}"
                                                    value="{{ $item->id }}"
                                                    data-price="{{ $item->price }}"
                                                    data-max="{{ $group->max_selection }}"
                                                >
                                                <label class="form-check-label" for="item_{{ $item->id }}">
                                                    {{ $item->name }}
                                                    @if($item->price > 0)
                                                        <span class="elite-option-price">(+{{ number_format($item->price, 2) }} {{ __('product.currency_egp') }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="elite-qty-wrap mt-3">
                    <label class="elite-qty-label">{{ __('product.quantity') }}</label>
                    <input type="number" name="quantity" id="quantityInput" class="form-control elite-qty-input" value="1" min="1" required>
                </div>

                <div class="elite-summary-card">
                    <div class="elite-summary-row">
                        <span class="elite-summary-label">{{ __('product.final_price') }}</span>
                        <strong class="elite-summary-value" id="finalPrice">{{ number_format($product->price, 2) }} {{ __('product.currency_egp') }}</strong>
                    </div>
                </div>

                <button class="elite-submit-btn">{{ __('product.add_to_cart') }}</button>

                @if(session('item_added_to_cart'))
                    <div class="elite-success-card">
                        <div class="elite-success-title">{{ __('product.product_added_successfully') }}</div>

                        <div class="elite-success-actions">
                            <a href="{{ route('home') }}" class="elite-secondary-link">
                                {{ __('product.add_more_products') }}
                            </a>

                            <a href="{{ route('cart.index') }}" class="elite-primary-link">
                                {{ __('product.go_to_cart') }}
                            </a>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<script nonce="{{ $cspNonce }}">
window.frontProductConfig = {
    basePrice: {{ (float) $product->price }},
    currencyText: @json(__('product.currency_egp')),
    maxSelectionText: @json(__('product.max_selection_reached')),
};
</script>
@if(!$hasFrontProductAssets)
    <script nonce="{{ $cspNonce }}">
    {!! file_get_contents(resource_path('js/pages/front-product-show.js')) !!}
    </script>
@endif
@endsection
