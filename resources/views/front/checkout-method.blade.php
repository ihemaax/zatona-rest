@extends('layouts.app')

@section('content')
@php
    $cartCount = count(session('cart', []));
@endphp

<style>
    .methodx-wrap{max-width:980px;margin:0 auto;padding:28px 0 86px}
    .methodx-shell{background:linear-gradient(180deg,rgba(255,255,255,.78),rgba(255,255,255,.64));border:1px solid rgba(21,72,60,.12);border-radius:30px;box-shadow:0 28px 60px rgba(14,54,45,.12);backdrop-filter:blur(6px);padding:34px}
    .methodx-cart-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;border:1px solid rgba(21,72,60,.2);background:#f6f1e6;color:#21493e;font-size:.82rem;font-weight:900}
    .methodx-cart-pill .dot{width:8px;height:8px;border-radius:50%;background:#2f7a67}
    .methodx-title{margin:16px 0 8px;font-size:2rem;font-weight:900;letter-spacing:-.02em;color:#17352d;text-align:center}
    .methodx-sub{margin:0 auto 28px;max-width:620px;text-align:center;color:#5e6056;font-weight:700;line-height:1.9}

    .methodx-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .methodx-card{position:relative;border-radius:24px;padding:22px;background:linear-gradient(160deg,#fff,#f9f4ea);border:1px solid #e6dccb;box-shadow:0 16px 34px rgba(20,57,47,.09);transition:all .22s ease;display:flex;flex-direction:column;gap:12px}
    .methodx-card:hover{transform:translateY(-5px);box-shadow:0 22px 36px rgba(20,57,47,.16)}
    .methodx-card.active{border-color:rgba(32,96,79,.55);box-shadow:0 0 0 3px rgba(32,96,79,.16),0 20px 38px rgba(20,57,47,.18)}
    .methodx-badge{position:absolute;inset-inline-end:16px;top:14px;font-size:.74rem;font-weight:900;padding:5px 10px;border-radius:999px;background:#eaf4ef;color:#1a5a4a;border:1px solid #cfe4dc}
    .methodx-icon{width:64px;height:64px;border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:linear-gradient(145deg,#ebf4ef,#dfece5);color:#1f6554;border:1px solid #d2e3db}
    .methodx-card.delivery .methodx-icon{background:linear-gradient(145deg,#f9f1e5,#f2e6d4);border-color:#e4d5bf;color:#8d683a}
    .methodx-card h3{margin:0;font-size:1.38rem;font-weight:900;color:#16392f}
    .methodx-card p{margin:0;color:#686257;font-weight:700;line-height:1.8}
    .methodx-features{display:grid;gap:8px;margin:4px 0 2px;padding:0;list-style:none}
    .methodx-features li{display:flex;align-items:center;gap:8px;background:#fbf7ef;border:1px solid #ece0cd;border-radius:12px;padding:9px 11px;font-size:.84rem;font-weight:800;color:#5f5a50}
    .methodx-features i{color:#1f6656}
    .methodx-card.delivery .methodx-features i{color:#906737}
    .methodx-select{margin-top:auto;border:0;border-radius:13px;padding:12px 14px;font-weight:900;background:linear-gradient(135deg,#103e33,#2f7462);color:#fff;box-shadow:0 10px 18px rgba(16,58,47,.22)}
    .methodx-card.delivery .methodx-select{background:linear-gradient(135deg,#7f5f39,#b58a55)}

    .methodx-footer{margin-top:22px;padding-top:18px;border-top:1px solid rgba(21,72,60,.12);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px}
    .methodx-selected{font-size:.9rem;font-weight:800;color:#36554a}
    .methodx-actions{display:flex;gap:10px;flex-wrap:wrap}
    .methodx-link{min-height:46px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:12px;padding:10px 16px;font-weight:900}
    .methodx-link.back{background:#f4ede0;border:1px solid #e5d8c2;color:#4d4a43}
    .methodx-link.proceed{background:linear-gradient(135deg,#103e33,#2f7462);color:#fff;box-shadow:0 10px 18px rgba(16,58,47,.2)}

    @media (max-width:991.98px){.methodx-grid{grid-template-columns:1fr}}
    @media (max-width:767.98px){
        .methodx-wrap{padding-top:14px;padding-bottom:70px}
        .methodx-shell{padding:18px;border-radius:22px}
        .methodx-title{font-size:1.5rem}
        .methodx-sub{font-size:.88rem;margin-bottom:18px}
        .methodx-card{padding:16px}
        .methodx-footer{display:grid}
        .methodx-actions{display:grid;grid-template-columns:1fr;gap:8px}
        .methodx-link{width:100%}
    }
</style>

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
