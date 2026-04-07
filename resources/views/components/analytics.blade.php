@php
    $measurementId = config('services.analytics.measurement_id');
@endphp

@if($measurementId && config('services.analytics.load_external_script'))
    <script nonce="{{ $cspNonce }}" async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
    <script nonce="{{ $cspNonce }}">
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $measurementId }}');
    </script>
@endif
