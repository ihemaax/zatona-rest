@extends('layouts.app')

@section('content')
@php
    $cartCount = count(session('cart', []));
@endphp

<div class="methodx-wrap">
    <section class="methodx-shell">
        <span class="methodx-cart-pill"><span class="dot"></span> السلة تحتوي على {{ $cartCount }} منتج</span>
        <h1 class="methodx-title">{{ __('checkout_method.choose_order_receiving_method') }}</h1>
        <p class="methodx-sub">اختار الطريقة الأنسب ليك: استلام سريع من الفرع أو توصيل مباشر لعنوانك بنفس الجودة والخدمة.</p>

        <section class="methodx-grid">
            <article class="methodx-card active" data-method="pickup" data-target="{{ route('checkout.index', ['order_type' => 'pickup']) }}">
                <span class="methodx-badge">الأسرع</span>
                <div class="methodx-icon"><i class="bi bi-shop-window"></i></div>
                <h3>{{ __('checkout_method.pickup_from_restaurant') }}</h3>
                <p>{{ __('checkout_method.pickup_description') }}</p>
                <ul class="methodx-features">
                    <li><i class="bi bi-lightning-charge-fill"></i>{{ __('checkout_method.pickup_feature_fast') }}</li>
                    <li><i class="bi bi-geo-alt-fill"></i>{{ __('checkout_method.pickup_feature_branch') }}</li>
                    <li><i class="bi bi-shield-check"></i>{{ __('checkout_method.pickup_feature_no_address') }}</li>
                </ul>
                <button type="button" class="methodx-select">{{ __('checkout_method.continue_pickup') }}</button>
            </article>

            <article class="methodx-card delivery" data-method="delivery" data-target="{{ route('checkout.index', ['order_type' => 'delivery']) }}">
                <span class="methodx-badge">الأكثر راحة</span>
                <div class="methodx-icon"><i class="bi bi-truck"></i></div>
                <h3>{{ __('checkout_method.delivery_to_address') }}</h3>
                <p>{{ __('checkout_method.delivery_description') }}</p>
                <ul class="methodx-features">
                    <li><i class="bi bi-pin-map-fill"></i>{{ __('checkout_method.delivery_feature_address') }}</li>
                    <li><i class="bi bi-house-heart-fill"></i>{{ __('checkout_method.delivery_feature_home') }}</li>
                    <li><i class="bi bi-check2-circle"></i>{{ __('checkout_method.delivery_feature_easy') }}</li>
                </ul>
                <button type="button" class="methodx-select">{{ __('checkout_method.continue_delivery') }}</button>
            </article>
        </section>

        <div class="methodx-footer">
            <div id="selectedMethodLabel" class="methodx-selected">الاختيار الحالي: {{ __('checkout_method.pickup_from_restaurant') }}</div>
            <div class="methodx-actions">
                <a href="{{ route('cart.index') }}" class="methodx-link back">{{ __('checkout_method.back_to_cart') }}</a>
                <a id="methodProceedLink" href="{{ route('checkout.index', ['order_type' => 'pickup']) }}" class="methodx-link proceed">متابعة بالطريقة المختارة</a>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
    (() => {
        const cards = Array.from(document.querySelectorAll('.methodx-card'));
        const proceedLink = document.getElementById('methodProceedLink');
        const selectedLabel = document.getElementById('selectedMethodLabel');

        if (!cards.length || !proceedLink || !selectedLabel) return;

        const setActiveCard = (card) => {
            cards.forEach((item) => item.classList.remove('active'));
            card.classList.add('active');

            const target = card.dataset.target;
            const methodName = card.querySelector('h3')?.textContent?.trim();
            if (target) proceedLink.setAttribute('href', target);
            if (methodName) selectedLabel.textContent = `الاختيار الحالي: ${methodName}`;
        };

        cards.forEach((card) => {
            card.addEventListener('click', (event) => {
                const clickedButton = event.target.closest('.methodx-select');
                setActiveCard(card);
                if (clickedButton) {
                    const target = card.dataset.target;
                    if (target) window.location.href = target;
                }
            });
        });
    })();
</script>
@endpush
@endsection
