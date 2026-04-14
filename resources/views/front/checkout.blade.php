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
@php
    $manifestPath = public_path('build/manifest.json');
    $hasFrontCheckoutAssets = false;
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
        $hasFrontCheckoutAssets = isset($manifest['resources/css/pages/front-checkout.css']);
    }
@endphp

@if($hasFrontCheckoutAssets)
    @vite(['resources/css/pages/front-checkout.css'])
@else
    <style>
    {!! file_get_contents(resource_path('css/pages/front-checkout.css')) !!}
    </style>
@endif



<div class="elite-checkout">
    <div class="elite-checkout-grid">
        <section class="elite-checkout-main">
            <header class="elite-checkout-hero">
                <div class="elite-checkout-topline">
                    <div>
                        <span class="elite-checkout-badge">
                            @if($activeOrderType === 'pickup') 🏬 {{ __('checkout.pickup_from_restaurant') }}
                            @else 🚚 {{ __('checkout.delivery_to_address') }} @endif
                        </span>
                        <h1 class="elite-checkout-title">{{ __('checkout.complete_order') }}</h1>
                        <p class="elite-checkout-sub">{{ __('checkout.receiving_method_label') }} • {{ __('checkout.payment_method') }}</p>
                    </div>
                </div>

                <div class="elite-checkout-switch">
                    <a href="{{ route('checkout.index', ['order_type' => 'delivery']) }}" class="{{ $activeOrderType === 'delivery' ? 'active' : '' }}">🚚 {{ __('checkout.delivery_to_address') }}</a>
                    <a href="{{ route('checkout.index', ['order_type' => 'pickup']) }}" class="{{ $activeOrderType === 'pickup' ? 'active' : '' }}">🏬 {{ __('checkout.pickup_from_restaurant') }}</a>
                </div>
                <div class="elite-order-meta">
                    <span>{{ __('checkout.confirm_order') }}</span>
                    <span>{{ __('checkout.currency_egp') }} • Premium experience</span>
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
                    <p class="elite-section-intro">{{ __('checkout.customer_name') }} {{ __('checkout.phone_number') }} لضمان تأكيد الطلب سريعًا.</p>
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
                            <div class="elite-checkout-note elite-note-muted mt-2">اكتب 10 أرقام فقط بدون +20</div>
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
                    <div class="elite-checkout-divider"></div>

                    @if($savedAddresses->count())
                        <label class="elite-checkout-label">{{ __('checkout.choose_saved_address') }}</label>
                        <select id="savedAddressSelect" class="form-select mb-3">
                            <option value="">{{ __('checkout.choose_saved_address_placeholder') }}</option>
                            @foreach($savedAddresses as $address)
                                <option
                                    value="{{ $address->id }}"
                                    data-address="{{ $address->address_line }}"
                                    data-area="{{ $address->area }}"
                                    data-lat="{{ $address->latitude }}"
                                    data-lng="{{ $address->longitude }}"
                                    data-name="{{ $address->recipient_name }}"
                                    data-phone="{{ $address->phone }}"
                                    @selected(old('selected_address_id', $defaultAddress?->id) == $address->id)
                                >
                                    {{ $address->label }} - {{ $address->address_line }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="selected_address_id" id="selectedAddressIdInput" value="{{ old('selected_address_id', $defaultAddress?->id) }}">
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
                    <h3 class="mb-2">{{ __('checkout.payment_method') }}</h3>
                    <div class="payment-choices">
                        <label class="payment-choice">
                            <input type="radio" name="payment_method" value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'checked' : '' }}>
                            <span class="icon"><i class="bi bi-cash-coin"></i></span>
                            <span>
                                <span class="title">{{ __('checkout.cash_on_delivery') }}</span>
                                <span class="desc">الدفع عند الاستلام بكل بساطة</span>
                            </span>
                        </label>
                        @if($canUsePaymob)
                        <label class="payment-choice">
                            <input type="radio" name="payment_method" value="paymob" {{ old('payment_method') === 'paymob' ? 'checked' : '' }}>
                            <span class="icon"><i class="bi bi-credit-card-2-front"></i></span>
                            <span>
                                <span class="title">دفع إلكتروني</span>
                                <span class="desc">عبر Paymob</span>
                            </span>
                        </label>
                        @endif
                    </div>
                </div>

                <div class="elite-checkout-card">
                    <h3 class="mb-2">{{ __('checkout.notes') }}</h3>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="elite-checkout-card">
                    <h3 class="mb-2">{{ __('checkout.coupon_code') }}</h3>
                    @if($canUseCoupons)
                    <div class="input-group">
                        <input type="text" name="coupon_code" id="couponCodeInput" class="form-control" value="{{ $couponCode }}" placeholder="{{ __('checkout.coupon_code_placeholder') }}">
                        @if(Route::has('checkout.apply-coupon'))
                            <button type="submit" formaction="{{ route('checkout.apply-coupon') }}" class="btn btn-outline-secondary">{{ __('checkout.apply_coupon') }}</button>
                        @else
                            <button type="button" class="btn btn-outline-secondary" disabled title="Coupon endpoint unavailable">{{ __('checkout.apply_coupon') }}</button>
                        @endif
                    </div>
                    <div class="elite-checkout-note mt-2">{{ __('checkout.coupon_hint') }}</div>
                    @else
                    <input type="hidden" name="coupon_code" value="">
                    <div class="elite-checkout-note mt-2">{{ config('subscription.blocked_message') }}</div>
                    @endif
                </div>

                <button id="confirmOrderBtn" class="elite-checkout-submit w-100">{{ __('checkout.confirm_order') }}</button>
                <div class="elite-checkout-note mt-2">
                    {{ app(\App\Services\SubscriptionService::class)->featureEnabled('otp') ? 'بعد تأكيد الطلب هنحوّلك مباشرة لصفحة التحقق على واتساب.' : 'سيتم تأكيد الطلب مباشرة بدون خطوة OTP.' }}
                </div>
            </form>
        </section>

        <aside class="elite-checkout-summary">
            <div class="elite-summary-header">
                <h3>{{ __('checkout.order_summary') }}</h3>
                <span class="elite-summary-badge">{{ count($cart) }}</span>
            </div>
            <div class="elite-checkout-items">
                @foreach($cart as $item)
                    <div class="elite-checkout-item">
                        <span>
                            {{ $item['name'] }} × {{ $item['quantity'] }}
                            @if(!empty($item['notes']))
                                <small class="d-block text-muted">ملاحظات: {{ $item['notes'] }}</small>
                            @endif
                        </span>
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
    const selectedAddressIdInput = document.getElementById('selectedAddressIdInput');
    const customerNameInput = document.querySelector('input[name=\"customer_name\"]');


    const paymentChoices = Array.from(document.querySelectorAll('.payment-choice'));
    const syncPaymentCards = () => {
        paymentChoices.forEach((choice) => {
            const input = choice.querySelector('input[name="payment_method"]');
            choice.classList.toggle('active', !!input?.checked);
        });
    };

    paymentChoices.forEach((choice) => {
        choice.addEventListener('click', () => {
            const input = choice.querySelector('input[name="payment_method"]');
            if (!input) return;
            input.checked = true;
            syncPaymentCards();
        });
    });

    syncPaymentCards();

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
        const applySavedAddress = async function () {
            const selected = this.options[this.selectedIndex];
            if (selectedAddressIdInput) {
                selectedAddressIdInput.value = selected.value || '';
            }
            if (!selected.value) return;

            const address = selected.getAttribute('data-address') || '';
            const area = selected.getAttribute('data-area') || '';
            const lat = parseFloat(selected.getAttribute('data-lat'));
            const lng = parseFloat(selected.getAttribute('data-lng'));
            const contactName = selected.getAttribute('data-name') || '';
            const contactPhone = selected.getAttribute('data-phone') || '';

            addressInput.value = address;
            areaInput.value = area;
            if (contactName && customerNameInput) customerNameInput.value = contactName;
            if (contactPhone && customerPhoneInput) customerPhoneInput.value = sanitizeLocalEgyptianPhone(contactPhone);

            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);
                updateLatLng(lat, lng);
            }
        };

        savedAddressSelect.addEventListener('change', applySavedAddress);
        if (savedAddressSelect.value) {
            applySavedAddress.call(savedAddressSelect);
        }
    }

    function sanitizeLocalEgyptianPhone(value) {
        let digits = (value || '').replace(/\D/g, '');

        if (digits.startsWith('20') && digits.length >= 12) {
            digits = digits.slice(2);
        }

        if (digits.startsWith('0') && digits.length === 11) {
            digits = digits.slice(1);
        }

        if (digits.length > 10) {
            digits = digits.slice(-10);
        }

        return digits;
    }

    if (customerPhoneInput) {
        customerPhoneInput.value = sanitizeLocalEgyptianPhone(customerPhoneInput.value);
        customerPhoneInput.addEventListener('input', function () {
            customerPhoneInput.value = sanitizeLocalEgyptianPhone(customerPhoneInput.value);
        });
    }
</script>
@endsection
