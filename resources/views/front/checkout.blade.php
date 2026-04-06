@extends('layouts.app')

@section('content')
@php
    $title = __('site.brand');
    $metaDescription = 'Online ordering experience for faster checkout and clear delivery tracking.';
@endphp

<h2 class="section-title mb-4">{{ __('checkout.complete_order') }}</h2>

@php
    $subtotal = collect($cart)->sum('total');
    $delivery = $setting->delivery_fee ?? 25;
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 380px;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .delivery-fields, .pickup-fields {
        display: none;
    }
</style>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-shell p-4">
            <form action="{{ route('checkout.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('checkout.customer_name') }}</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->check() ? auth()->user()->name : '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('checkout.phone_number') }}</label>
                        <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}" required>
                    </div>

                    <input type="hidden" name="order_type" id="orderTypeSelect" value="{{ old('order_type', $selectedOrderType ?? 'delivery') }}">

                    <div class="card-shell p-3">
                        <strong>{{ __('checkout.receiving_method_label') }}</strong>
                        @if(old('order_type', $selectedOrderType ?? 'delivery') === 'pickup')
                            {{ __('checkout.pickup_from_restaurant') }}
                        @else
                            {{ __('checkout.delivery_to_address') }}
                        @endif
                    </div>

                    <div class="col-12 pickup-fields" id="pickupFields">
                        <label class="form-label">{{ __('checkout.choose_branch') }}</label>
                        <select name="branch_id" class="form-select">
                            <option value="">{{ __('checkout.choose_branch_placeholder') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                    {{ $branch->name }} - {{ $branch->address }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="delivery-fields" id="deliveryFields">
                        @if($savedAddresses->count())
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('checkout.choose_saved_address') }}</label>
                                <select id="savedAddressSelect" class="form-select">
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
                            </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label">{{ __('checkout.detailed_address') }}</label>
                            <input type="text" name="address_line" id="address_line" class="form-control" value="{{ old('address_line') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('checkout.area') }}</label>
                            <input type="text" name="area" id="area" class="form-control" value="{{ old('area') }}">
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <button type="button" id="useCurrentLocationBtn" class="btn btn-brand w-100">
                                {{ __('checkout.use_current_location') }}
                            </button>
                        </div>

                        <div class="col-12">
                            <div id="locationStatus" class="small text-muted"></div>
                            <div class="small text-muted mt-1">{{ __('checkout.network_issue_hint') }}</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('checkout.select_location_on_map') }}</label>
                            <div id="map"></div>
                        </div>

                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

                        <div class="col-md-6">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="save_address" name="save_address">
                                <label class="form-check-label" for="save_address">
                                    {{ __('checkout.save_this_address') }}
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="make_default" name="make_default">
                                <label class="form-check-label" for="make_default">
                                    {{ __('checkout.make_default_address') }}
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('checkout.address_name') }}</label>
                            <input type="text" name="address_label" class="form-control" placeholder="{{ __('checkout.address_name_placeholder') }}">
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">{{ __('checkout.notes') }}</label>
                    <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                </div>

                <div class="mt-3">
                    <label class="form-label">{{ __('checkout.payment_method') }}</label>
                    <input type="text" class="form-control" value="{{ __('checkout.cash_on_delivery') }}" disabled>
                </div>

                <button class="btn btn-brand btn-lg w-100 mt-4">{{ __('checkout.confirm_order') }}</button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-shell p-4">
            <h5 class="fw-bold mb-3">{{ __('checkout.order_summary') }}</h5>

            @foreach($cart as $item)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                    <span>{{ number_format($item['total'], 2) }} {{ __('checkout.currency_egp') }}</span>
                </div>
            @endforeach

            <hr>

            <div class="d-flex justify-content-between mb-2">
                <span>{{ __('checkout.subtotal') }}</span>
                <span>{{ number_format($subtotal, 2) }} {{ __('checkout.currency_egp') }}</span>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>{{ __('checkout.delivery_fee') }}</span>
                <span id="deliveryFeeText">{{ number_format($delivery, 2) }} {{ __('checkout.currency_egp') }}</span>
            </div>

            <hr>

            <div class="d-flex justify-content-between fw-bold">
                <span>{{ __('checkout.final_total') }}</span>
                <span id="finalTotalText">{{ number_format($subtotal + $delivery, 2) }} {{ __('checkout.currency_egp') }}</span>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const defaultLat = 31.2001;
    const defaultLng = 29.9187;
    const deliveryFee = {{ (float)($setting->delivery_fee ?? 0) }};
    const subtotal = {{ (float)$subtotal }};
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

    latInput.value = defaultLat;
    lngInput.value = defaultLng;

    function toggleOrderTypeFields() {
        if (orderTypeSelect.value === 'pickup') {
            deliveryFields.style.display = 'none';
            pickupFields.style.display = 'block';
            deliveryFeeText.textContent = textZeroDelivery;
            finalTotalText.textContent = subtotal.toFixed(2) + ' ' + currencyText;
        } else {
            deliveryFields.style.display = 'contents';
            pickupFields.style.display = 'none';
            deliveryFeeText.textContent = deliveryFee.toFixed(2) + ' ' + currencyText;
            finalTotalText.textContent = (subtotal + deliveryFee).toFixed(2) + ' ' + currencyText;
        }
    }

    toggleOrderTypeFields();
    if (orderTypeSelect) {
        toggleOrderTypeFields();
    }

    function setStatus(message, type = 'muted') {
        locationStatus.className = 'small';
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