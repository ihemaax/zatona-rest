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
    .elite-home{max-width:1320px;margin:0 auto;padding:0 14px 120px;color:#14211c}
    .elite-hero{border-radius:0 0 28px 28px;overflow:hidden;border:1px solid #dcccc0;background:#f8f4ea;box-shadow:0 20px 38px rgba(19,37,31,.14)}
    .elite-cover{min-height:280px;background:linear-gradient(120deg,rgba(11,44,36,.72),rgba(26,80,66,.45)),url('{{ $coverImageUrl ?: "https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=1600&auto=format&fit=crop" }}') center/cover no-repeat}
    .elite-hero-body{margin-top:-46px;padding:0 20px 20px}
    .elite-hero-card{border:1px solid #e5d8c3;border-radius:24px;background:#fffcf8;padding:18px;box-shadow:0 16px 30px rgba(14,35,29,.1)}
    .elite-top{display:flex;gap:14px;align-items:center;justify-content:space-between;flex-wrap:wrap}
    .elite-brand{display:flex;gap:14px;align-items:center}
    .elite-logo{width:84px;height:84px;border-radius:50%;border:4px solid #fff;background:url('{{ $logoImageUrl ?: "https://via.placeholder.com/500x500?text=Logo" }}') center/cover no-repeat;box-shadow:0 10px 22px rgba(17,46,38,.2)}
    .elite-title{margin:0;font-size:1.7rem;font-weight:900}
    .elite-sub{margin:6px 0 0;color:#647069;font-weight:700}
    .elite-actions{display:flex;gap:10px;flex-wrap:wrap}
    .elite-btn{min-height:44px;padding:10px 16px;border-radius:12px;font-weight:900;text-decoration:none;display:inline-flex;align-items:center}
    .elite-btn.main{background:linear-gradient(135deg,#103f33,#2f7562);color:#fff}
    .elite-btn.alt{background:#efe3d0;border:1px solid #ddccb3;color:#263a34}
    .elite-pills{margin-top:14px;display:flex;gap:8px;flex-wrap:wrap}
    .elite-pill{padding:8px 12px;border-radius:999px;border:1px solid #e6d8c4;background:#f7efe2;font-size:.78rem;font-weight:900;color:#655f54}

    .elite-main{margin-top:18px;display:grid;gap:14px}
    .elite-box{background:#fffdf8;border:1px solid #e6d8c4;border-radius:22px;box-shadow:0 10px 24px rgba(22,36,31,.08)}
    .elite-box-body{padding:16px}
    .elite-search{position:relative}
    .elite-search input{width:100%;border:1px solid #dccab0;background:#fff;border-radius:12px;padding:13px 14px 13px 42px;font-weight:700}
    .elite-search svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:17px;height:17px;stroke:#8f7f67}
    .elite-alert{display:none;margin-top:10px}.elite-alert-box{padding:10px 12px;border-radius:12px;font-weight:800}.elite-alert-box.success{background:#e8f4ee;color:#1b5a49}.elite-alert-box.error{background:#fff1f1;color:#9f3434}

    .elite-categories{display:flex;gap:10px;overflow:auto;padding-bottom:2px;scrollbar-width:none}
    .elite-categories::-webkit-scrollbar{display:none}
    .elite-cat{border:1px solid #e5d6c1;background:#fff8ee;border-radius:999px;padding:8px 14px;font-weight:800;color:#5d564a;white-space:nowrap}
    .elite-cat.active{background:linear-gradient(135deg,#103f33,#2c705f);color:#fff;border-color:#1f5f4f}

    .elite-section{background:#fffdf8;border:1px solid #e6d8c4;border-radius:22px;box-shadow:0 10px 24px rgba(22,36,31,.08);padding:14px}
    .elite-section-head{display:flex;justify-content:space-between;align-items:center;padding:4px 2px 12px;border-bottom:1px solid #efe3d2;margin-bottom:12px}
    .elite-section-title{margin:0;font-size:1.08rem;font-weight:900}
    .elite-products{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .elite-product{border:1px solid #e8dccb;border-radius:18px;background:#fff;overflow:hidden;display:grid;grid-template-columns:120px minmax(0,1fr);gap:12px;padding:10px;min-height:152px}
    .elite-product-image{width:100%;height:120px;object-fit:cover;border-radius:12px}
    .elite-product-badge{display:none}
    .elite-product-body{display:flex;flex-direction:column;gap:8px}
    .elite-product-name{margin:0;font-size:1rem;font-weight:900}
    .elite-product-category{display:inline-flex;padding:4px 9px;background:#e8f1ed;color:#255f50;border-radius:999px;font-size:.7rem;font-weight:900}
    .elite-product-desc{margin:0;color:#6f675a;font-size:.82rem;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .elite-product-bottom{display:flex;justify-content:space-between;gap:8px;align-items:end;margin-top:auto}
    .elite-price-label{font-size:.72rem;color:#8a7c66;font-weight:800}
    .elite-price-value{font-size:.92rem;font-weight:900;color:#1f6757}
    .elite-add-btn{border:none;border-radius:12px;padding:9px 13px;font-size:.8rem;font-weight:900;color:#fff;background:linear-gradient(135deg,#103f33,#2f7562);white-space:nowrap}

    .elite-floating-cart{position:fixed;inset-inline:14px;bottom:14px;z-index:1050;display:flex;justify-content:center;pointer-events:none}
    .elite-floating-cart-inner{max-width:520px;width:100%;pointer-events:auto;border:1px solid #356a59;background:linear-gradient(120deg,rgba(11,44,36,.95),rgba(47,111,95,.94));border-radius:16px;padding:10px;box-shadow:0 14px 28px rgba(14,27,23,.3);display:flex;justify-content:space-between;align-items:center;gap:12px}
    .elite-floating-cart-value{font-size:.9rem;font-weight:900;color:#fff}
    .elite-floating-cart-btn{color:#18302a !important;text-decoration:none;border-radius:12px;padding:10px 14px;background:#f6e8d0 !important;border:1px solid #e4cfab;font-weight:900}

    .quick-modal .modal-dialog{max-width:820px}.quick-modal .modal-content{border-radius:18px;border:1px solid #e3d6c3}.quick-product-media img{width:100%;height:240px;object-fit:cover;border-radius:14px}.quick-product-name{font-size:1.2rem;font-weight:900}.quick-product-price{font-size:1rem;font-weight:900;color:#1f6554;margin:6px 0}.quick-product-desc{font-size:.88rem;color:#6f675a;line-height:1.7;margin-bottom:10px}.offer-popup-btn{display:block;width:100%;border:none;text-decoration:none;border-radius:14px;padding:12px 15px;color:#fff !important;background:linear-gradient(135deg,#0b2c24,#2f6f5f);font-weight:900;margin-bottom:10px}
    .offer-popup-overlay{position:fixed;inset:0;background:rgba(8,12,10,.64);display:none;align-items:center;justify-content:center;padding:16px;z-index:999999}
    .offer-popup-overlay.show{display:flex}
    .offer-popup-card{max-width:440px;width:100%;background:#fff;border:1px solid #e5d7c4;border-radius:22px;overflow:hidden;box-shadow:0 28px 64px rgba(0,0,0,.3)}
    .offer-popup-image{width:100%;height:300px;object-fit:cover;background:#efe6d7}
    .offer-popup-body{padding:18px;text-align:center}
    .offer-popup-title{margin:0 0 8px;font-size:1.18rem;font-weight:900;color:#1a2723}
    .offer-popup-desc{margin:0 0 14px;color:#666056;line-height:1.8;font-size:.9rem;font-weight:700}
    .offer-popup-close{width:100%;border:none;border-radius:12px;padding:11px 14px;background:#f2f3f5;font-weight:800}

    @media (max-width:991.98px){.elite-products{grid-template-columns:1fr}.elite-cover{min-height:220px}}
    @media (max-width:767.98px){
        .elite-home{padding:0 10px 106px}
        .elite-hero-body{margin-top:-26px;padding:0 10px 12px}
        .elite-hero-card{padding:14px;border-radius:18px}
        .elite-logo{width:70px;height:70px}
        .elite-title{font-size:1.22rem}
        .elite-sub{font-size:.82rem;line-height:1.7}
        .elite-actions{width:100%;display:grid;grid-template-columns:1fr 1fr}
        .elite-btn{justify-content:center}
        .elite-box-body{padding:12px}
        .elite-product{grid-template-columns:84px minmax(0,1fr);padding:8px;min-height:unset}
        .elite-product-image{height:84px}
        .elite-product-body{gap:6px;justify-content:space-between}
        .elite-product-bottom{flex-direction:column;align-items:stretch;gap:6px}
        .elite-price{display:flex;align-items:center;justify-content:space-between}
        .elite-price-label{font-size:.66rem}
        .elite-price-value{font-size:.84rem}
        .elite-add-btn{width:100%;padding:8px 10px;font-size:.72rem;text-align:center;justify-content:center}
        .elite-floating-cart{bottom:calc(var(--mobile-bar-h) + env(safe-area-inset-bottom, 0px) + 8px)}
        .quick-modal .modal-dialog{margin:0;min-height:100dvh;align-items:flex-end;display:flex}
        .quick-modal .modal-content{border-radius:20px 20px 0 0;max-height:86dvh;overflow:hidden}
        .quick-modal .modal-body{overflow:auto;padding-bottom:calc(16px + env(safe-area-inset-bottom, 0px))}
        .quick-product-media img{height:160px;border-radius:12px}
    }
</style>

<div class="elite-home">
    <section class="elite-hero">
        <div class="elite-cover"></div>
        <div class="elite-hero-body">
            <div class="elite-hero-card">
                <div class="elite-top">
                    <div class="elite-brand">
                        <div class="elite-logo"></div>
                        <div>
                            <h1 class="elite-title">{{ $restaurantName }}</h1>
                            <p class="elite-sub">منيو مرتب وسريع عشان تختار بسهولة وتكمّل الطلب في دقائق.</p>
                        </div>
                    </div>
                    <div class="elite-actions">
                        <a href="#menu-area" class="elite-btn main">{{ __('home.browse_menu') }}</a>
                        <a href="{{ route('cart.index') }}" class="elite-btn alt">{{ __('home.cart') }} <span id="heroCartCount">{{ $cartCount > 0 ? '(' . $cartCount . ')' : '' }}</span></a>
                    </div>
                </div>
                <div class="elite-pills">
                    <span class="elite-pill">{{ $isOpen ? __('home.orders_available_now') : __('home.orders_unavailable_now') }}</span>
                    <span class="elite-pill">{{ __('home.delivery_fee') }} {{ number_format($deliveryFee, 2) }} {{ __('home.currency_egp') }}</span>
                    <span class="elite-pill">{{ $products->count() }} {{ __('home.items_available') }}</span>
                </div>
            </div>
        </div>
    </section>

    <main id="menu-area" class="elite-main">
        <div class="elite-box">
            <div class="elite-box-body">
                <div class="elite-search">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="7" stroke="currentColor"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-linecap="round"/></svg>
                    <input type="text" id="menuSearchInput" placeholder="{{ __('home.search_placeholder') }}">
                </div>
                <div id="cartAjaxAlert" class="elite-alert"><div class="elite-alert-box success" id="cartAjaxAlertBox">{{ __('home.product_added_successfully') }}</div></div>
            </div>
        </div>

        <div class="elite-box">
            <div class="elite-box-body">
                <div class="elite-categories" id="categoryBubbles">
                    <button class="elite-cat active" type="button" data-category="all">{{ __('home.all') }}</button>
                    @foreach($groupedProducts as $categoryName => $categoryProducts)
                        <button class="elite-cat" type="button" data-category="{{ \Illuminate\Support\Str::slug($categoryName) }}">{{ $categoryName }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        @if($products->count())
            @foreach($groupedProducts as $categoryName => $categoryProducts)
                <section class="elite-section product-section" data-category="{{ \Illuminate\Support\Str::slug($categoryName) }}">
                    <div class="elite-section-head">
                        <h3 class="elite-section-title">{{ $categoryName }}</h3>
                        <span>{{ $categoryProducts->count() }} {{ __('home.item') }}</span>
                    </div>
                    <div class="elite-products">
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
                                                    ? $group->items->map(function ($item) {
                                                        return ['id' => $item->id,'name' => $item->name,'price' => $item->price ?? 0,];
                                                    })->values()->toArray()
                                                    : [],
                                            ];
                                        })->values()->toArray()
                                        : [],
                                ];
                            @endphp
                            <article class="elite-product product-card-item" data-name="{{ strtolower($product->name . ' ' . ($product->description ?? '')) }}">
                                <img src="{{ $product->image ? \App\Support\MediaUrl::fromPath($product->image) : 'https://via.placeholder.com/600x400?text=Food' }}" alt="{{ $product->name }}" class="elite-product-image">
                                <div class="elite-product-body">
                                    <div><h4 class="elite-product-name">{{ $product->name }}</h4><span class="elite-product-category">{{ $product->category->name ?? __('home.menu') }}</span></div>
                                    <p class="elite-product-desc">{{ $product->description ?: __('home.default_product_description') }}</p>
                                    <div class="elite-product-bottom">
                                        <div class="elite-price"><div class="elite-price-label">{{ __('home.price') }}</div><div class="elite-price-value">{{ number_format($product->price, 2) }} {{ __('home.currency_egp') }}</div></div>
                                        <button type="button" class="btn elite-add-btn open-product-modal" data-bs-toggle="modal" data-bs-target="#productQuickAddModal" data-product='@json($productPayload)'>{{ __('home.add_to_cart') }}</button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @else
            <div class="elite-section">{{ __('home.no_items_available_now') }}</div>
        @endif
    </main>
</div>

<div class="elite-floating-cart" id="floatingCheckout" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">
    <div class="elite-floating-cart-inner">
        <div class="elite-floating-cart-value" id="floatingCheckoutValue">{{ $cartCount }} {{ __('home.product') }} • {{ number_format($cartTotal, 2) }} {{ __('home.currency_egp') }}</div>
        <a href="{{ route('cart.index') }}" class="elite-floating-cart-btn">{{ __('home.continue_order') }}</a>
    </div>
</div>

@include('front.partials.quick-add-modal', ['cspNonce' => $cspNonce])

@if($popupCampaign)
<div class="offer-popup-overlay" id="offerPopupOverlay"><div class="offer-popup-card">@if($popupCampaign->image)<img src="{{ $popupImageUrl }}" alt="{{ $popupCampaign->title }}" class="offer-popup-image">@endif<div class="offer-popup-body">@if($popupCampaign->title)<h3 class="offer-popup-title">{{ $popupCampaign->title }}</h3>@endif @if($popupCampaign->description)<div class="offer-popup-desc">{{ $popupCampaign->description }}</div>@endif <button type="button" class="offer-popup-close" id="offerPopupCloseBtn">{{ __('home.close') }}</button></div></div></div>
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

    modalElement?.addEventListener('show.bs.modal', () => {
        document.body.classList.add('mobile-modal-open');
    });

    modalElement?.addEventListener('hidden.bs.modal', () => {
        document.body.classList.remove('mobile-modal-open');
    });
    let currentCartCount = {{ $cartCount }};
    let currentCartTotal = {{ (float) $cartTotal }};

    function formatMoney(value){return Number(value||0).toFixed(2)+' {{ __("home.currency_egp") }}';}
    function updateCartUI(cartCount, cartTotal){currentCartCount=Number(cartCount||0);currentCartTotal=Number(cartTotal||0);if(heroCartCount){heroCartCount.textContent=currentCartCount>0?`(${currentCartCount})`:'';}if(floatingCheckout&&floatingCheckoutValue){if(currentCartCount>0){floatingCheckout.style.display='';floatingCheckoutValue.textContent=`${currentCartCount} {{ __("home.product") }} • ${formatMoney(currentCartTotal)}`;}else{floatingCheckout.style.display='none';}}}
    function filterMenu(){const searchValue=(searchInput?.value||'').toLowerCase().trim();const activePill=document.querySelector('.elite-cat.active');const activeCategory=activePill?activePill.dataset.category:'all';sections.forEach(section=>{const sectionCategory=section.dataset.category;const cards=section.querySelectorAll('.product-card-item');let visibleCards=0;cards.forEach(card=>{const name=card.dataset.name||'';const matchesSearch=!searchValue||name.includes(searchValue);const matchesCategory=activeCategory==='all'||activeCategory===sectionCategory;if(matchesSearch&&matchesCategory){card.style.display='';visibleCards++;}else{card.style.display='none';}});section.style.display=visibleCards>0?'':'none';});}
    function showAjaxMessage(message,isError=false){if(!cartAjaxAlert||!cartAjaxAlertBox)return;cartAjaxAlertBox.textContent=message;cartAjaxAlertBox.classList.remove('success','error');cartAjaxAlertBox.classList.add(isError?'error':'success');cartAjaxAlert.style.display='block';clearTimeout(window.__cartAlertTimer);window.__cartAlertTimer=setTimeout(()=>{cartAjaxAlert.style.display='none';},2500);}

    pills.forEach(pill=>pill.addEventListener('click',function(){pills.forEach(btn=>btn.classList.remove('active'));this.classList.add('active');filterMenu();}));
    if(searchInput){searchInput.addEventListener('input',filterMenu);}

    document.querySelectorAll('.open-product-modal').forEach(button=>{button.addEventListener('click',function(){let product={};try{product=JSON.parse(this.dataset.product||'{}');}catch(e){product={};}
        productName.textContent=product.name||'';productPrice.textContent=formatMoney(product.price||0);productDescription.textContent=product.description||'';productImage.src=product.image||'https://via.placeholder.com/600x400?text=Food';form.action=`/cart/add/${product.id}`;quantityInput.value=1;optionsWrap.innerHTML='';
        if(Array.isArray(product.options)&&product.options.length){product.options.forEach(group=>{const groupBox=document.createElement('div');groupBox.className='mb-3';let itemsHtml='';if(group.type==='multiple'){group.items.forEach(item=>{itemsHtml+=`<div class="form-check"><input class="form-check-input" type="checkbox" name="options[${group.id}][]" value="${item.id}" id="opt_${group.id}_${item.id}"><label class="form-check-label" for="opt_${group.id}_${item.id}">${item.name} ${parseFloat(item.price||0)>0?`( +${parseFloat(item.price).toFixed(2)} {{ __("home.currency_egp") }} )`:''}</label></div>`;});}else{group.items.forEach(item=>{itemsHtml+=`<div class="form-check"><input class="form-check-input" type="radio" name="options[${group.id}]" value="${item.id}" id="opt_${group.id}_${item.id}" ${group.is_required?'required':''}><label class="form-check-label" for="opt_${group.id}_${item.id}">${item.name} ${parseFloat(item.price||0)>0?`( +${parseFloat(item.price).toFixed(2)} {{ __("home.currency_egp") }} )`:''}</label></div>`;});}
            groupBox.innerHTML=`<label class="form-label fw-bold d-block mb-2">${group.name}${group.is_required?'<span class="text-danger">*</span>':''}</label><div class="quick-option-box">${itemsHtml}</div>`;optionsWrap.appendChild(groupBox);});}
    });});

    form.addEventListener('submit',async function(e){e.preventDefault();const submitBtn=form.querySelector('button[type="submit"]');const originalBtnText=submitBtn.textContent;submitBtn.disabled=true;submitBtn.textContent='{{ __("home.adding") }}';
        try{const formData=new FormData(form);const response=await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('input[name="_token"]').value,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:formData});const data=await response.json();if(!response.ok){let errorMessage='{{ __("home.product_added_currently_unavailable") }}';if(data.message){errorMessage=data.message;}else if(data.errors){const firstKey=Object.keys(data.errors)[0];if(firstKey&&data.errors[firstKey][0]){errorMessage=data.errors[firstKey][0];}}showAjaxMessage(errorMessage,true);return;}productModal.hide();form.reset();optionsWrap.innerHTML='';quantityInput.value=1;const newCartCount=typeof data.cart_count!=='undefined'?data.cart_count:(currentCartCount+parseInt(formData.get('quantity')||1,10));const newCartTotal=typeof data.cart_total!=='undefined'?data.cart_total:currentCartTotal;updateCartUI(newCartCount,newCartTotal);showAjaxMessage(data.message||'{{ __("home.product_added_successfully") }}');}catch(error){showAjaxMessage('{{ __("home.connection_error_try_again") }}',true);}finally{submitBtn.disabled=false;submitBtn.textContent=originalBtnText;}});

    @if($popupCampaign)
    const popup=document.getElementById('offerPopupOverlay');const closeBtn=document.getElementById('offerPopupCloseBtn');
    if(popup){const popupId='popup_campaign_{{ $popupCampaign->id }}';const showOnce={{ $popupCampaign->show_once_per_user ? 'true' : 'false' }};let canShow=true;if(showOnce){const alreadySeen=localStorage.getItem(popupId);if(alreadySeen==='1'){canShow=false;}}
        if(canShow){setTimeout(()=>popup.classList.add('show'),500);}closeBtn?.addEventListener('click',()=>{popup.classList.remove('show');if(showOnce){localStorage.setItem(popupId,'1');}});popup.addEventListener('click',e=>{if(e.target===popup){popup.classList.remove('show');if(showOnce){localStorage.setItem(popupId,'1');}}});}
    @endif
});
</script>
@endsection
