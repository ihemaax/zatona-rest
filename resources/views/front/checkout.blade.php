@extends('layouts.app')

@section('content')
@php
    $title = __('site.brand');
    $metaDescription = 'Online ordering experience for faster checkout and clear delivery tracking.';

    $subtotal = collect($cart)->sum('total');
    $delivery = $setting->delivery_fee ?? 25;
    $couponCode = old('coupon_code', $couponPreview['coupon']?->code ?? session('checkout_coupon_code'));
    $discountValue = (float) ($couponPreview['discount'] ?? 0);
    $activeOrderType = old('order_type', $selectedOrderType ?? 'delivery');
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .elite-checkout {
        --green-900:#0b2b23;
        --green-800:#0f3a2f;
        --green-700:#1f5f4f;
        --green-100:#e6f1ec;
        --cream-100:#fbf7ef;
        --cream-200:#f5eddc;
        --cream-300:#eadcc2;
        --ink:#16241f;
        --muted:#5f655d;
        max-width: 1240px;margin:0 auto;padding:8px 14px 120px;position:relative;color:var(--ink);
    }

    .elite-checkout::before {
        content:"";position:fixed;inset:0;z-index:-1;pointer-events:none;
        background:
            radial-gradient(circle at 6% 3%, rgba(16,89,73,.16), transparent 32%),
            radial-gradient(circle at 95% 12%, rgba(181,139,82,.18), transparent 34%);
    }

    .elite-checkout-grid{display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:20px;align-items:start}

    .elite-checkout-main,
    .elite-checkout-summary {
        background:linear-gradient(180deg,rgba(255,255,255,.8),rgba(255,255,255,.66));
        border:1px solid rgba(24,74,62,.14);
        border-radius:28px;
        box-shadow:0 20px 42px rgba(18,44,37,.12);
        overflow:hidden;
        backdrop-filter:blur(6px);
    }

    .elite-checkout-hero {
        background:linear-gradient(125deg,var(--green-900),var(--green-800) 48%,var(--green-700));
        color:#fff;padding:26px;border-bottom:1px solid rgba(255,255,255,.18);position:relative;
    }

    .elite-checkout-hero::after{
        content:"";position:absolute;inset:auto -40px -90px auto;width:240px;height:240px;border-radius:50%;
        background:radial-gradient(circle,rgba(255,255,255,.2),transparent 62%);
    }

    .elite-checkout-badge {
        display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:8px 13px;background:rgba(251,247,239,.14);
        border:1px solid rgba(251,247,239,.38);font-size:.78rem;font-weight:900;margin-bottom:12px;
    }

    .elite-checkout-title {
        margin:0;font-size:2rem;font-weight:900;letter-spacing:-.03em;
    }

    .elite-checkout-sub {
        margin:9px 0 0;color:#dbebe5;font-weight:700;font-size:.9rem;
    }

    .elite-checkout-switch {
        margin-top:16px;display:flex;flex-wrap:wrap;gap:8px;
    }

    .elite-checkout-switch a {
        text-decoration:none;border-radius:12px;padding:10px 14px;border:1px solid rgba(251,247,239,.38);color:#f7f3ea;
        background:rgba(9,25,21,.38);font-size:.83rem;font-weight:900;transition:all .2s ease;
    }

    .elite-checkout-switch a.active {
        background:#f7e6c8;color:#17342c;border-color:#ecd0a0;
    }

    .elite-checkout-form {
        padding:18px;display:grid;gap:14px;
    }

    .elite-checkout-card {
        border:1px solid #e6d8c1;border-radius:20px;background:linear-gradient(180deg,#fffefb,#fdf8ef);
        box-shadow:0 8px 18px rgba(19,47,38,.06), inset 0 1px 0 rgba(255,255,255,.9);padding:16px;
    }

    .elite-checkout-card h3 {
        margin:0;font-size:1.02rem;font-weight:900;color:#17322a;
    }

    .elite-checkout-head {
        margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:10px;
    }

    .elite-checkout-note {
        font-size:.77rem;font-weight:800;color:#6f675a;
    }

    .elite-checkout-label {
        display:block;margin-bottom:7px;font-size:.82rem;font-weight:800;color:#496058;
    }

    .elite-checkout-2,
    .elite-checkout-3 {
        display:grid;gap:10px;
    }

    .elite-checkout-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .elite-checkout-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }

    .elite-checkout .form-control,
    .elite-checkout .form-select {
        border:1px solid #dfceb6;background:#fffdf8;border-radius:13px;min-height:46px;font-weight:700;
        box-shadow:inset 0 1px 2px rgba(32,64,53,.04);
    }

    .elite-checkout .form-control:focus,
    .elite-checkout .form-select:focus{
        border-color:#8cb8aa;box-shadow:0 0 0 .18rem rgba(47,111,95,.14);
    }

    .elite-checkout .input-group-text{
        background:#f7efe0;border:1px solid #dfceb6;border-radius:13px 0 0 13px;font-weight:900;color:#3a5249;
    }

    .elite-checkout .btn-brand {
        background:linear-gradient(135deg,var(--green-900),var(--green-700));border:1px solid #275446;border-radius:13px;
        font-weight:900;color:#fff;min-height:46px;
    }

    .elite-checkout .btn-outline-secondary {
        border-radius:13px;border-color:#dcc8a9;color:#4e473d;font-weight:900;background:#fff8eb;
    }

    .elite-checkout-map {
        height:325px;border-radius:16px;border:1px solid #e4d7c2;overflow:hidden;
    }

    .delivery-fields,
    .pickup-fields { display: none; }

    .elite-checkout-summary {
        position:sticky;top:90px;padding:18px;
    }

    .elite-checkout-summary h3 {
        margin:0;font-size:1.03rem;font-weight:900;color:#17342c;
    }

    .elite-checkout-items {
        margin-top:12px;display:grid;gap:8px;max-height:260px;overflow:auto;padding-inline-end:4px;
    }

    .elite-checkout-item {
        display:flex;justify-content:space-between;gap:8px;border-bottom:1px dashed #dccdb5;padding-bottom:7px;
        font-size:.84rem;font-weight:800;color:#3f4d47;
    }

    .elite-checkout-calc {
        margin-top:12px;border-top:1px solid #dfccb2;padding-top:12px;display:grid;gap:8px;
    }

    .elite-checkout-row {
        display:flex;justify-content:space-between;font-size:.87rem;font-weight:800;color:#33423d;
    }

    .elite-checkout-row.total {
        margin-top:3px;font-size:1.04rem;color:#13211c;
    }

    .elite-checkout-change {
        margin-top:14px;text-decoration:none;display:block;text-align:center;border-radius:13px;padding:10px;font-weight:900;
        color:#21463b;background:#edf5f1;border:1px solid #c8dfd5;
    }

    .elite-checkout-submit{
        border:0;border-radius:14px;padding:14px 18px;background:linear-gradient(135deg,var(--green-900),var(--green-700));
        color:#fff;font-size:1.02rem;font-weight:900;box-shadow:0 16px 28px rgba(13,58,46,.23);
    }

    .elite-mini-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}

    .elite-section-divider{
        margin:4px 0 2px;padding-top:2px;font-size:.74rem;font-weight:900;color:#6e685d;letter-spacing:.02em;
    }

    @media (max-width: 991.98px) {
        .elite-checkout-grid { grid-template-columns: 1fr; gap:14px; }
        .elite-checkout-summary { position: static; }
    }

    @media (max-width: 767.98px) {
        .elite-checkout{padding:0 6px 94px}
        .elite-checkout-main,.elite-checkout-summary{border-radius:20px}
        .elite-checkout-hero{padding:18px}
        .elite-checkout-title { font-size: 1.38rem; }
        .elite-checkout-sub{font-size:.82rem}
        .elite-checkout-2,
        .elite-checkout-3 { grid-template-columns: 1fr; }
        .elite-checkout-form{padding:12px}
        .elite-mini-grid{grid-template-columns:1fr}
    }
</style>

<div class="elite-checkout">
    <div class="elite-checkout-grid">
        <section class="elite-checkout-main">
            <header class="elite-checkout-hero">
                <span class="elite-checkout-badge">
                    @if($activeOrderType === 'pickup') 🏬 {{ __('checkout.pickup_from_restaurant') }}
                    @else 🚚 {{ __('checkout.delivery_to_address') }} @endif
                </span>
                <h1 class="elite-checkout-title">{{ __('checkout.complete_order') }}</h1>
                <p class="elite-checkout-sub">{{ __('checkout.receiving_method_label') }} • {{ __('checkout.payment_method') }} {{ __('checkout.cash_on_delivery') }}</p>

                <div class="elite-checkout-switch">
                    <a href="{{ route('checkout.index', ['order_type' => 'delivery']) }}" class="{{ $activeOrderType === 'delivery' ? 'active' : '' }}">🚚 {{ __('checkout.delivery_to_address') }}</a>
                    <a href="{{ route('checkout.index', ['order_type' => 'pickup']) }}" class="{{ $activeOrderType === 'pickup' ? 'active' : '' }}">🏬 {{ __('checkout.pickup_from_restaurant') }}</a>
                </div>
            </header>

            <form id="checkoutForm" action="{{ route('checkout.store') }}" method="POST" class="elite-checkout-form">
                @csrf
                <input type="hidden" name="order_type" id="orderTypeSelect" value="{{ $activeOrderType }}">

                <div class="elite-checkout-card">
                    <div class="elite-checkout-head">
                        <h3>{{ __('checkout.customer_name') }} & {{ __('checkout.phone_number') }}</h3>
                        <span class="elite-checkout-note">{{ __('checkout.confirm_order') }}</span>
                    </div>
                    <div class="elite-checkout-2">
                        <div>
                            <label class="elite-checkout-label">{{ __('checkout.customer_name') }}</label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->check() ? auth()->user()->name : '') }}" required>
                        </div>
                        <div>
                            <label class="elite-checkout-label">{{ __('checkout.phone_number') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" dir="ltr">+20</span>
                                <input type="text" name="customer_phone" id="customerPhoneInput" class="form-control" value="{{ old('customer_phone') }}" maxlength="10" inputmode="numeric" pattern="[0-9]*" placeholder="1206628718" required>
                            </div>
                            <div class="elite-checkout-note mt-1">اكتب 10 أرقام فقط بدون +20</div>
                        </div>
                    </div>
                </div>

                <div class="elite-checkout-card pickup-fields" id="pickupFields">
                    <div class="elite-checkout-head">
                        <h3>{{ __('checkout.choose_branch') }}</h3>
                        <span class="elite-checkout-note">{{ __('checkout.pickup_from_restaurant') }}</span>
                    </div>
                    <select name="branch_id" class="form-select">
                        <option value="">{{ __('checkout.choose_branch_placeholder') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                {{ $branch->name }} - {{ $branch->address }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="elite-checkout-card delivery-fields" id="deliveryFields">
                    <div class="elite-checkout-head">
                        <h3>{{ __('checkout.delivery_to_address') }}</h3>
                        <span class="elite-checkout-note">{{ __('checkout.network_issue_hint') }}</span>
                    </div>

                    @if($savedAddresses->count())
                        <label class="elite-checkout-label">{{ __('checkout.choose_saved_address') }}</label>
                        <select id="savedAddressSelect" class="form-select mb-3">
                            <option value="">{{ __('checkout.choose_saved_address_placeholder') }}</option>
                            @foreach($savedAddresses as $address)
                                <option value="{{ $address->id }}" data-address="{{ $address->address_line }}" data-area="{{ $address->area }}" data-lat="{{ $address->latitude }}" data-lng="{{ $address->longitude }}">
                                    {{ $address->label }} - {{ $address->address_line }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <div class="elite-checkout-2">
                        <div>
                            <label class="elite-checkout-label">{{ __('checkout.detailed_address') }}</label>
                            <input type="text" name="address_line" id="address_line" class="form-control" value="{{ old('address_line') }}">
                        </div>
                        <div>
                            <label class="elite-checkout-label">{{ __('checkout.area') }}</label>
                            <input type="text" name="area" id="area" class="form-control" value="{{ old('area') }}">
                        </div>
                    </div>

                    <div class="elite-mini-grid mt-3">
                        <button type="button" id="useCurrentLocationBtn" class="btn btn-brand w-100">{{ __('checkout.use_current_location') }}</button>
                        <div class="elite-checkout-note d-flex align-items-center" id="locationStatus"></div>
                    </div>

                    <div class="mt-3">
                        <label class="elite-checkout-label">{{ __('checkout.select_location_on_map') }}</label>
                        <div id="map" class="elite-checkout-map"></div>
                    </div>

                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

                    <div class="elite-checkout-3 mt-3">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="save_address" name="save_address">
                            <label class="form-check-label" for="save_address">{{ __('checkout.save_this_address') }}</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="make_default" name="make_default">
                            <label class="form-check-label" for="make_default">{{ __('checkout.make_default_address') }}</label>
                        </div>
                        <div>
                            <label class="elite-checkout-label">{{ __('checkout.address_name') }}</label>
                            <input type="text" name="address_label" class="form-control" placeholder="{{ __('checkout.address_name_placeholder') }}">
                        </div>
                    </div>
                </div>

                <div class="elite-checkout-card">
                    <h3 class="mb-2">{{ __('checkout.notes') }}</h3>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="elite-checkout-card">
                    <h3 class="mb-2">{{ __('checkout.coupon_code') }}</h3>
                    <div class="input-group">
                        <input type="text" name="coupon_code" id="couponCodeInput" class="form-control" value="{{ $couponCode }}" placeholder="{{ __('checkout.coupon_code_placeholder') }}">
                        @if(Route::has('checkout.apply-coupon'))
                            <button type="submit" formaction="{{ route('checkout.apply-coupon') }}" class="btn btn-outline-secondary">{{ __('checkout.apply_coupon') }}</button>
                        @else
                            <button type="button" class="btn btn-outline-secondary" disabled title="Coupon endpoint unavailable">{{ __('checkout.apply_coupon') }}</button>
                        @endif
                    </div>
                    <div class="elite-checkout-note mt-2">{{ __('checkout.coupon_hint') }}</div>
                </div>

                <button id="confirmOrderBtn" class="elite-checkout-submit w-100">{{ __('checkout.confirm_order') }}</button>
                <div class="elite-checkout-note mt-2">بعد تأكيد الطلب هنحوّلك مباشرة لصفحة التحقق على واتساب.</div>
            </form>
        </section>

        <aside class="elite-checkout-summary">
            <h3>{{ __('checkout.order_summary') }}</h3>
            <div class="elite-checkout-items">
                @foreach($cart as $item)
                    <div class="elite-checkout-item">
                        <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                        <span>{{ number_format($item['total'], 2) }} {{ __('checkout.currency_egp') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="elite-checkout-calc">
                <div class="elite-checkout-row"><span>{{ __('checkout.subtotal') }}</span><span>{{ number_format($subtotal, 2) }} {{ __('checkout.currency_egp') }}</span></div>
                <div class="elite-checkout-row"><span>{{ __('checkout.delivery_fee') }}</span><span id="deliveryFeeText">{{ number_format($delivery, 2) }} {{ __('checkout.currency_egp') }}</span></div>
                <div class="elite-checkout-row" id="discountRow" style="{{ $discountValue > 0 ? '' : 'display:none;' }}"><span>{{ __('checkout.discount') }}</span><span id="discountText">-{{ number_format($discountValue, 2) }} {{ __('checkout.currency_egp') }}</span></div>
                <div class="elite-checkout-row total"><span>{{ __('checkout.final_total') }}</span><span id="finalTotalText">{{ number_format($subtotal + $delivery - $discountValue, 2) }} {{ __('checkout.currency_egp') }}</span></div>
            </div>

            <a href="{{ route('checkout.method') }}" class="elite-checkout-change">{{ __('checkout.receiving_method_label') }}</a>
        </aside>
    </div>
</div>

<script nonce="{{ $cspNonce }}" src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script nonce="{{ $cspNonce }}">
    const defaultLat = 31.2001;
    const defaultLng = 29.9187;
    const deliveryFee = {{ (float)($setting->delivery_fee ?? 0) }};
    const subtotal = {{ (float)$subtotal }};
    const discountAmount = {{ $discountValue }};
    const currencyText = @json(__('checkout.currency_egp'));
    const textZeroDelivery = `0.00 ${currencyText}`;
    const textDetecting = @json(__('checkout.detecting_location'));
    const textLocationUnavailable = @json(__('checkout.unable_to_detect_location'));
    const textLocationPermissionDenied = @json(__('checkout.location_permission_denied'));
    const textLocationTimeout = @json(__('checkout.location_timeout'));
    const textLocationServiceUnavailable = @json(__('checkout.location_unavailable'));
    const textLocationDetected = @json(__('checkout.location_detected_successfully'));
    const textAddressAutoFilled = @json(__('checkout.address_auto_filled'));
    const textAddressAutoFailed = @json(__('checkout.unable_to_fetch_address'));

    const map = L.map('map').setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('address_line');
    const areaInput = document.getElementById('area');
    const locationStatus = document.getElementById('locationStatus');
    const useCurrentLocationBtn = document.getElementById('useCurrentLocationBtn');
    const savedAddressSelect = document.getElementById('savedAddressSelect');
    const orderTypeSelect = document.getElementById('orderTypeSelect');
    const deliveryFields = document.getElementById('deliveryFields');
    const pickupFields = document.getElementById('pickupFields');
    const deliveryFeeText = document.getElementById('deliveryFeeText');
    const finalTotalText = document.getElementById('finalTotalText');
    const discountText = document.getElementById('discountText');
    const discountRow = document.getElementById('discountRow');
    const customerPhoneInput = document.getElementById('customerPhoneInput');

    latInput.value = defaultLat;
    lngInput.value = defaultLng;

    function toggleOrderTypeFields() {
        if (orderTypeSelect.value === 'pickup') {
            deliveryFields.style.display = 'none';
            pickupFields.style.display = 'block';
            deliveryFeeText.textContent = textZeroDelivery;
            if (discountAmount > 0 && discountText && discountRow) {
                discountRow.style.display = '';
                discountText.textContent = '-' + discountAmount.toFixed(2) + ' ' + currencyText;
            }
            finalTotalText.textContent = Math.max(0, subtotal - discountAmount).toFixed(2) + ' ' + currencyText;
        } else {
            deliveryFields.style.display = 'block';
            pickupFields.style.display = 'none';
            deliveryFeeText.textContent = deliveryFee.toFixed(2) + ' ' + currencyText;
            if (discountAmount > 0 && discountText && discountRow) {
                discountRow.style.display = '';
                discountText.textContent = '-' + discountAmount.toFixed(2) + ' ' + currencyText;
            }
            finalTotalText.textContent = Math.max(0, subtotal + deliveryFee - discountAmount).toFixed(2) + ' ' + currencyText;
        }
    }

    toggleOrderTypeFields();

    function setStatus(message, type = 'muted') {
        locationStatus.className = 'elite-checkout-note';
        if (type === 'success') locationStatus.classList.add('text-success');
        else if (type === 'error') locationStatus.classList.add('text-danger');
        else locationStatus.classList.add('text-muted');

        locationStatus.textContent = message;
    }

    function updateLatLng(lat, lng) {
        latInput.value = lat;
        lngInput.value = lng;
    }

    async function reverseGeocode(lat, lng) {
        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=ar`;
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data && data.display_name) {
                addressInput.value = data.display_name;

                if (data.address) {
                    areaInput.value =
                        data.address.suburb ||
                        data.address.neighbourhood ||
                        data.address.city_district ||
                        data.address.city ||
                        data.address.town ||
                        data.address.village ||
                        '';
                }

                setStatus(textAddressAutoFilled, 'success');
            }
        } catch (error) {
            setStatus(textAddressAutoFailed, 'error');
        }
    }

    async function moveMarkerAndFill(lat, lng) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 16);
        updateLatLng(lat, lng);
        await reverseGeocode(lat, lng);
    }

    map.on('click', async function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        await moveMarkerAndFill(lat, lng);
    });

    marker.on('dragend', async function() {
        const position = marker.getLatLng();
        await moveMarkerAndFill(position.lat, position.lng);
    });

    if (useCurrentLocationBtn) {
        useCurrentLocationBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                setStatus(textLocationUnavailable, 'error');
                return;
            }

            setStatus(textDetecting);

            navigator.geolocation.getCurrentPosition(
                async function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    await moveMarkerAndFill(lat, lng);
                    setStatus(textLocationDetected, 'success');
                },
                function(error) {
                    if (error && error.code === error.PERMISSION_DENIED) {
                        setStatus(textLocationPermissionDenied, 'error');
                    } else if (error && error.code === error.TIMEOUT) {
                        setStatus(textLocationTimeout, 'error');
                    } else if (error && error.code === error.POSITION_UNAVAILABLE) {
                        setStatus(textLocationServiceUnavailable, 'error');
                    } else {
                        setStatus(textLocationUnavailable, 'error');
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        event: 'begin_checkout',
        order_type: orderTypeSelect?.value || 'delivery',
        value: Number((subtotal + deliveryFee).toFixed(2)),
        currency: currencyText
    });

    if (savedAddressSelect) {
        savedAddressSelect.addEventListener('change', async function () {
            const selected = this.options[this.selectedIndex];
            if (!selected.value) return;

            const address = selected.getAttribute('data-address') || '';
            const area = selected.getAttribute('data-area') || '';
            const lat = parseFloat(selected.getAttribute('data-lat'));
            const lng = parseFloat(selected.getAttribute('data-lng'));

            addressInput.value = address;
            areaInput.value = area;

            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);
                updateLatLng(lat, lng);
            }
        });
    }

    function sanitizeLocalEgyptianPhone(value) {
        const digits = (value || '').replace(/\D/g, '');
        return digits.slice(0, 10);
    }

    if (customerPhoneInput) {
        customerPhoneInput.value = sanitizeLocalEgyptianPhone(customerPhoneInput.value);
        customerPhoneInput.addEventListener('input', function () {
            customerPhoneInput.value = sanitizeLocalEgyptianPhone(customerPhoneInput.value);
        });
    }
</script>
@endsection
