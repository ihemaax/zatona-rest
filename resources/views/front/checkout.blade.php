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
    .checkout-pro {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 12px 110px;
    }

    .checkout-pro-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 380px;
        gap: 22px;
        align-items: start;
    }

    .checkout-pro-main {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 24px 50px rgba(15, 23, 42, .09);
    }

    .checkout-pro-hero {
        background: linear-gradient(140deg, #0f172a 0%, #111827 45%, #1d4ed8 120%);
        color: #e2e8f0;
        padding: 26px;
        display: grid;
        gap: 14px;
    }

    .checkout-pro-eyebrow {
        display: inline-flex;
        width: fit-content;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        border: 1px solid rgba(203, 213, 225, .4);
        background: rgba(15, 23, 42, .45);
        padding: 8px 12px;
        font-size: .78rem;
        font-weight: 800;
    }

    .checkout-pro-hero h1 {
        margin: 0;
        font-size: 2rem;
        color: #fff;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .checkout-pro-hero p {
        margin: 0;
        font-size: .92rem;
        color: #cbd5e1;
        font-weight: 600;
    }

    .checkout-pro-switch {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .checkout-pro-switch a {
        text-decoration: none;
        border: 1px solid #334155;
        background: #0b1220;
        color: #cbd5e1;
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 800;
        font-size: .85rem;
    }

    .checkout-pro-switch a.active {
        border-color: #60a5fa;
        color: #fff;
        box-shadow: 0 0 0 2px rgba(96, 165, 250, .22);
    }

    .checkout-pro-form {
        padding: 20px;
        display: grid;
        gap: 16px;
    }

    .checkout-pro-block {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: #f8fafc;
        padding: 16px;
    }

    .checkout-pro-block h3 {
        margin: 0;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 900;
    }

    .checkout-pro-block-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .checkout-pro-hint {
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
    }

    .checkout-pro-grid-2,
    .checkout-pro-grid-3 {
        display: grid;
        gap: 12px;
    }

    .checkout-pro-grid-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .checkout-pro-grid-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .checkout-pro-label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: .82rem;
        font-weight: 800;
    }

    .checkout-pro-map {
        height: 330px;
        border-radius: 14px;
        border: 1px solid #dbe5f1;
        overflow: hidden;
    }

    .delivery-fields,
    .pickup-fields {
        display: none;
    }

    .checkout-pro-summary {
        position: sticky;
        top: 90px;
        border: 1px solid #24324a;
        background: #0b1220;
        border-radius: 24px;
        padding: 18px;
        color: #dbeafe;
        box-shadow: 0 18px 40px rgba(2, 6, 23, .35);
    }

    .checkout-pro-summary h3 {
        margin: 0;
        color: #fff;
        font-size: 1.06rem;
        font-weight: 900;
    }

    .checkout-pro-items {
        margin-top: 12px;
        display: grid;
        gap: 8px;
        max-height: 270px;
        overflow: auto;
    }

    .checkout-pro-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: .83rem;
        font-weight: 800;
        color: #cbd5e1;
        padding-bottom: 6px;
        border-bottom: 1px dashed rgba(100, 116, 139, .4);
    }

    .checkout-pro-calc {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid rgba(100, 116, 139, .45);
        display: grid;
        gap: 9px;
    }

    .checkout-pro-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        font-size: .86rem;
        font-weight: 800;
    }

    .checkout-pro-row.total {
        font-size: 1.03rem;
        color: #fff;
    }

    .checkout-pro-change {
        margin-top: 12px;
        display: block;
        text-align: center;
        text-decoration: none;
        border-radius: 12px;
        padding: 10px;
        background: #1e293b;
        border: 1px solid #334155;
        color: #dbeafe;
        font-weight: 900;
    }

    @media (max-width: 991.98px) {
        .checkout-pro-grid {
            grid-template-columns: 1fr;
        }

        .checkout-pro-summary {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .checkout-pro-main {
            border-radius: 20px;
        }

        .checkout-pro-hero {
            padding: 18px;
        }

        .checkout-pro-hero h1 {
            font-size: 1.35rem;
        }

        .checkout-pro-form {
            padding: 14px;
        }

        .checkout-pro-grid-2,
        .checkout-pro-grid-3 {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="checkout-pro">
    <div class="checkout-pro-grid">
        <section class="checkout-pro-main">
            <header class="checkout-pro-hero">
                <span class="checkout-pro-eyebrow">
                    @if($activeOrderType === 'pickup')
                        🏬 {{ __('checkout.pickup_from_restaurant') }}
                    @else
                        🚚 {{ __('checkout.delivery_to_address') }}
                    @endif
                </span>

                <h1>{{ __('checkout.complete_order') }}</h1>
                <p>{{ __('checkout.receiving_method_label') }} — {{ __('checkout.payment_method') }} {{ __('checkout.cash_on_delivery') }}</p>

                <div class="checkout-pro-switch">
                    <a href="{{ route('checkout.index', ['order_type' => 'delivery']) }}" class="{{ $activeOrderType === 'delivery' ? 'active' : '' }}">🚚 {{ __('checkout.delivery_to_address') }}</a>
                    <a href="{{ route('checkout.index', ['order_type' => 'pickup']) }}" class="{{ $activeOrderType === 'pickup' ? 'active' : '' }}">🏬 {{ __('checkout.pickup_from_restaurant') }}</a>
                </div>
            </header>

            <form action="{{ route('checkout.store') }}" method="POST" class="checkout-pro-form">
                @csrf
                <input type="hidden" name="order_type" id="orderTypeSelect" value="{{ $activeOrderType }}">

                <div class="checkout-pro-block">
                    <div class="checkout-pro-block-head">
                        <h3>{{ __('checkout.customer_name') }} & {{ __('checkout.phone_number') }}</h3>
                        <span class="checkout-pro-hint">{{ __('checkout.confirm_order') }}</span>
                    </div>

                    <div class="checkout-pro-grid-2">
                        <div>
                            <label class="checkout-pro-label">{{ __('checkout.customer_name') }}</label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->check() ? auth()->user()->name : '') }}" required>
                        </div>
                        <div>
                            <label class="checkout-pro-label">{{ __('checkout.phone_number') }}</label>
                            <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}" required>
                        </div>
                    </div>
                </div>

                <div class="checkout-pro-block pickup-fields" id="pickupFields">
                    <div class="checkout-pro-block-head">
                        <h3>{{ __('checkout.choose_branch') }}</h3>
                        <span class="checkout-pro-hint">{{ __('checkout.pickup_from_restaurant') }}</span>
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

                <div class="checkout-pro-block delivery-fields" id="deliveryFields">
                    <div class="checkout-pro-block-head">
                        <h3>{{ __('checkout.delivery_to_address') }}</h3>
                        <span class="checkout-pro-hint">{{ __('checkout.network_issue_hint') }}</span>
                    </div>

                    @if($savedAddresses->count())
                        <label class="checkout-pro-label">{{ __('checkout.choose_saved_address') }}</label>
                        <select id="savedAddressSelect" class="form-select mb-3">
                            <option value="">{{ __('checkout.choose_saved_address_placeholder') }}</option>
                            @foreach($savedAddresses as $address)
                                <option
                                    value="{{ $address->id }}"
                                    data-address="{{ $address->address_line }}"
                                    data-area="{{ $address->area }}"
                                    data-lat="{{ $address->latitude }}"
                                    data-lng="{{ $address->longitude }}"
                                >
                                    {{ $address->label }} - {{ $address->address_line }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <div class="checkout-pro-grid-2">
                        <div>
                            <label class="checkout-pro-label">{{ __('checkout.detailed_address') }}</label>
                            <input type="text" name="address_line" id="address_line" class="form-control" value="{{ old('address_line') }}">
                        </div>
                        <div>
                            <label class="checkout-pro-label">{{ __('checkout.area') }}</label>
                            <input type="text" name="area" id="area" class="form-control" value="{{ old('area') }}">
                        </div>
                    </div>

                    <div class="checkout-pro-grid-2 mt-3">
                        <button type="button" id="useCurrentLocationBtn" class="btn btn-brand w-100">{{ __('checkout.use_current_location') }}</button>
                        <div class="checkout-pro-hint" id="locationStatus"></div>
                    </div>

                    <div class="mt-3">
                        <label class="checkout-pro-label">{{ __('checkout.select_location_on_map') }}</label>
                        <div id="map" class="checkout-pro-map"></div>
                    </div>

                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

                    <div class="checkout-pro-grid-3 mt-3">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="save_address" name="save_address">
                            <label class="form-check-label" for="save_address">{{ __('checkout.save_this_address') }}</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="make_default" name="make_default">
                            <label class="form-check-label" for="make_default">{{ __('checkout.make_default_address') }}</label>
                        </div>
                        <div>
                            <label class="checkout-pro-label">{{ __('checkout.address_name') }}</label>
                            <input type="text" name="address_label" class="form-control" placeholder="{{ __('checkout.address_name_placeholder') }}">
                        </div>
                    </div>
                </div>

                <div class="checkout-pro-block">
                    <h3 class="mb-2">{{ __('checkout.notes') }}</h3>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="checkout-pro-block">
                    <h3 class="mb-2">{{ __('checkout.coupon_code') }}</h3>
                    <div class="input-group">
                        <input type="text" name="coupon_code" id="couponCodeInput" class="form-control" value="{{ $couponCode }}" placeholder="{{ __('checkout.coupon_code_placeholder') }}">
                        @if(Route::has('checkout.apply-coupon'))
                            <button type="submit" formaction="{{ route('checkout.apply-coupon') }}" class="btn btn-outline-secondary">{{ __('checkout.apply_coupon') }}</button>
                        @else
                            <button type="button" class="btn btn-outline-secondary" disabled title="Coupon endpoint unavailable">{{ __('checkout.apply_coupon') }}</button>
                        @endif
                    </div>
                    <div class="checkout-pro-hint mt-2">{{ __('checkout.coupon_hint') }}</div>
                </div>

                <button class="btn btn-brand btn-lg w-100">{{ __('checkout.confirm_order') }}</button>
            </form>
        </section>

        <aside class="checkout-pro-summary">
            <h3>{{ __('checkout.order_summary') }}</h3>
            <div class="checkout-pro-items">
                @foreach($cart as $item)
                    <div class="checkout-pro-item">
                        <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                        <span>{{ number_format($item['total'], 2) }} {{ __('checkout.currency_egp') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="checkout-pro-calc">
                <div class="checkout-pro-row"><span>{{ __('checkout.subtotal') }}</span><span>{{ number_format($subtotal, 2) }} {{ __('checkout.currency_egp') }}</span></div>
                <div class="checkout-pro-row"><span>{{ __('checkout.delivery_fee') }}</span><span id="deliveryFeeText">{{ number_format($delivery, 2) }} {{ __('checkout.currency_egp') }}</span></div>
                <div class="checkout-pro-row" id="discountRow" style="{{ $discountValue > 0 ? '' : 'display:none;' }}"><span>{{ __('checkout.discount') }}</span><span id="discountText">-{{ number_format($discountValue, 2) }} {{ __('checkout.currency_egp') }}</span></div>
                <div class="checkout-pro-row total"><span>{{ __('checkout.final_total') }}</span><span id="finalTotalText">{{ number_format($subtotal + $delivery - $discountValue, 2) }} {{ __('checkout.currency_egp') }}</span></div>
            </div>

            <a href="{{ route('checkout.method') }}" class="checkout-pro-change">{{ __('checkout.receiving_method_label') }}</a>
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
        locationStatus.className = 'checkout-pro-hint';
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
</script>
@endsection
