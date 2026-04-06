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
    .checknova-wrap{max-width:1260px;margin:0 auto;padding-bottom:110px}
    .checknova-grid{display:grid;grid-template-columns:minmax(0,1fr) 370px;gap:22px;align-items:start}

    .checknova-form{background:#ffffff;border:1px solid #e2e8f0;border-radius:30px;overflow:hidden;box-shadow:0 20px 48px rgba(15,23,42,.1)}
    .checknova-hero{padding:24px;background:radial-gradient(circle at top left,#0f172a,#111827 40%,#1e293b);color:#e2e8f0}
    .checknova-hero h1{margin:0;font-size:1.9rem;font-weight:900;color:#fff}
    .checknova-hero p{margin:8px 0 0;font-size:.92rem;font-weight:700;color:#cbd5e1}
    .checknova-method{margin-top:12px;display:inline-flex;align-items:center;gap:8px;border:1px solid #334155;background:#0b1220;padding:8px 12px;border-radius:999px;font-size:.8rem;font-weight:900}
    .checknova-method.delivery{color:#fcd34d}.checknova-method.pickup{color:#93c5fd}

    .checknova-body{padding:20px;display:grid;gap:18px}
    .checknova-block{background:#f8fafc;border:1px solid #e2e8f0;border-radius:20px;padding:16px}
    .checknova-block h3{margin:0 0 12px;font-size:1rem;font-weight:900;color:#0f172a}
    .checknova-two{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .checknova-three{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
    .checknova-label{display:block;margin:0 0 6px;font-size:.8rem;font-weight:800;color:#334155}
    .checknova-note{font-size:.78rem;color:#64748b;font-weight:700;margin-top:6px}

    #map{height:340px;border-radius:14px;border:1px solid #dbe5f1;overflow:hidden}
    .delivery-fields,.pickup-fields{display:none}

    .checknova-summary{position:sticky;top:90px;background:#0b1220;border:1px solid #24324a;border-radius:24px;padding:18px;color:#dbeafe;box-shadow:0 16px 34px rgba(2,6,23,.35)}
    .checknova-summary h3{margin:0;color:#f8fafc;font-size:1.05rem;font-weight:900}
    .checknova-items{margin-top:12px;display:grid;gap:8px;max-height:260px;overflow:auto;padding-right:4px}
    .checknova-item{display:flex;justify-content:space-between;gap:10px;font-size:.82rem;font-weight:800;color:#cbd5e1}
    .checknova-calc{margin-top:14px;padding-top:12px;border-top:1px dashed #334155;display:grid;gap:9px}
    .checknova-row{display:flex;justify-content:space-between;gap:10px;font-size:.84rem;font-weight:800}
    .checknova-row.total{font-size:1rem;color:#fff}
    .checknova-change{margin-top:12px;display:block;text-align:center;text-decoration:none;border-radius:12px;padding:10px;background:#1e293b;border:1px solid #334155;color:#dbeafe;font-weight:900}

    @media (max-width:991.98px){.checknova-grid{grid-template-columns:1fr}.checknova-summary{position:static}}
    @media (max-width:767.98px){.checknova-wrap{padding-bottom:92px}.checknova-form{border-radius:20px}.checknova-hero{padding:16px}.checknova-hero h1{font-size:1.24rem}.checknova-body{padding:14px}.checknova-two,.checknova-three{grid-template-columns:1fr}}
</style>

<div class="checknova-wrap">
    <div class="checknova-grid">
        <section class="checknova-form">
            <header class="checknova-hero">
                <h1>{{ __('checkout.complete_order') }}</h1>
                <p>{{ __('checkout.receiving_method_label') }}</p>
                <div class="checknova-method {{ $activeOrderType }}">
                    @if($activeOrderType === 'pickup')
                        🏬 {{ __('checkout.pickup_from_restaurant') }}
                    @else
                        🚚 {{ __('checkout.delivery_to_address') }}
                    @endif
                </div>
            </header>

            <form action="{{ route('checkout.store') }}" method="POST" class="checknova-body">
                @csrf
                <input type="hidden" name="order_type" id="orderTypeSelect" value="{{ $activeOrderType }}">

                <div class="checknova-block">
                    <h3>{{ __('checkout.customer_name') }} & {{ __('checkout.phone_number') }}</h3>
                    <div class="checknova-two">
                        <div>
                            <label class="checknova-label">{{ __('checkout.customer_name') }}</label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->check() ? auth()->user()->name : '') }}" required>
                        </div>
                        <div>
                            <label class="checknova-label">{{ __('checkout.phone_number') }}</label>
                            <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}" required>
                        </div>
                    </div>
                </div>

                <div class="checknova-block pickup-fields" id="pickupFields">
                    <h3>{{ __('checkout.choose_branch') }}</h3>
                    <select name="branch_id" class="form-select">
                        <option value="">{{ __('checkout.choose_branch_placeholder') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                {{ $branch->name }} - {{ $branch->address }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="checknova-block delivery-fields" id="deliveryFields">
                    <h3>{{ __('checkout.delivery_to_address') }}</h3>

                    @if($savedAddresses->count())
                        <label class="checknova-label">{{ __('checkout.choose_saved_address') }}</label>
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

                    <div class="checknova-two">
                        <div>
                            <label class="checknova-label">{{ __('checkout.detailed_address') }}</label>
                            <input type="text" name="address_line" id="address_line" class="form-control" value="{{ old('address_line') }}">
                        </div>
                        <div>
                            <label class="checknova-label">{{ __('checkout.area') }}</label>
                            <input type="text" name="area" id="area" class="form-control" value="{{ old('area') }}">
                        </div>
                    </div>

                    <div class="checknova-two mt-3">
                        <button type="button" id="useCurrentLocationBtn" class="btn btn-brand w-100">{{ __('checkout.use_current_location') }}</button>
                        <div class="checknova-note" id="locationStatus"></div>
                    </div>
                    <div class="checknova-note">{{ __('checkout.network_issue_hint') }}</div>

                    <div class="mt-3">
                        <label class="checknova-label">{{ __('checkout.select_location_on_map') }}</label>
                        <div id="map"></div>
                    </div>

                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

                    <div class="checknova-three mt-3">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="save_address" name="save_address">
                            <label class="form-check-label" for="save_address">{{ __('checkout.save_this_address') }}</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="make_default" name="make_default">
                            <label class="form-check-label" for="make_default">{{ __('checkout.make_default_address') }}</label>
                        </div>
                        <div>
                            <label class="checknova-label">{{ __('checkout.address_name') }}</label>
                            <input type="text" name="address_label" class="form-control" placeholder="{{ __('checkout.address_name_placeholder') }}">
                        </div>
                    </div>
                </div>

                <div class="checknova-block">
                    <h3>{{ __('checkout.notes') }}</h3>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="checknova-block">
                    <h3>{{ __('checkout.payment_method') }}</h3>
                    <input type="text" class="form-control" value="{{ __('checkout.cash_on_delivery') }}" disabled>
                </div>

                <div class="checknova-block">
                    <h3>{{ __('checkout.coupon_code') }}</h3>
                    <div class="input-group">
                        <input type="text" name="coupon_code" id="couponCodeInput" class="form-control" value="{{ $couponCode }}" placeholder="{{ __('checkout.coupon_code_placeholder') }}">
                        @if(Route::has('checkout.apply-coupon'))
                            <button type="submit" formaction="{{ route('checkout.apply-coupon') }}" class="btn btn-outline-secondary">{{ __('checkout.apply_coupon') }}</button>
                        @else
                            <button type="button" class="btn btn-outline-secondary" disabled title="Coupon endpoint unavailable">{{ __('checkout.apply_coupon') }}</button>
                        @endif
                    </div>
                    <div class="checknova-note">{{ __('checkout.coupon_hint') }}</div>
                </div>

                <button class="btn btn-brand btn-lg w-100">{{ __('checkout.confirm_order') }}</button>
            </form>
        </section>

        <aside class="checknova-summary">
            <h3>{{ __('checkout.order_summary') }}</h3>
            <div class="checknova-items">
                @foreach($cart as $item)
                    <div class="checknova-item">
                        <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                        <span>{{ number_format($item['total'], 2) }} {{ __('checkout.currency_egp') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="checknova-calc">
                <div class="checknova-row"><span>{{ __('checkout.subtotal') }}</span><span>{{ number_format($subtotal, 2) }} {{ __('checkout.currency_egp') }}</span></div>
                <div class="checknova-row"><span>{{ __('checkout.delivery_fee') }}</span><span id="deliveryFeeText">{{ number_format($delivery, 2) }} {{ __('checkout.currency_egp') }}</span></div>
                <div class="checknova-row" id="discountRow" style="{{ $discountValue > 0 ? '' : 'display:none;' }}"><span>{{ __('checkout.discount') }}</span><span id="discountText">-{{ number_format($discountValue, 2) }} {{ __('checkout.currency_egp') }}</span></div>
                <div class="checknova-row total"><span>{{ __('checkout.final_total') }}</span><span id="finalTotalText">{{ number_format($subtotal + $delivery - $discountValue, 2) }} {{ __('checkout.currency_egp') }}</span></div>
            </div>

            <a href="{{ route('checkout.method') }}" class="checknova-change">{{ __('checkout.receiving_method_label') }}</a>
        </aside>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
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
        locationStatus.className = 'checknova-note';
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
