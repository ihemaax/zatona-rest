<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('site.brand') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @php
        $manifestPath = public_path('build/manifest.json');
        $hasThemeCss = false;
        $hasPremiumCss = false;
        $hasAuthCss = false;
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
            $hasThemeCss = isset($manifest['resources/css/front-theme.css']);
            $hasPremiumCss = isset($manifest['resources/css/front-premium-theme.css']);
            $hasAuthCss = isset($manifest['resources/css/auth-premium.css']);
        }
    @endphp
    @if($hasThemeCss && $hasPremiumCss && $hasAuthCss)
        @vite(['resources/css/front-theme.css', 'resources/css/front-premium-theme.css', 'resources/css/auth-premium.css'])
    @else
        <style>{!! file_get_contents(resource_path('css/front-theme.css')) !!}</style>
        <style>{!! file_get_contents(resource_path('css/front-premium-theme.css')) !!}</style>
        <style>{!! file_get_contents(resource_path('css/auth-premium.css')) !!}</style>
    @endif
</head>
<body class="front-theme front-theme--{{ \App\Support\FrontThemeManager::activeKey(\App\Models\Setting::query()->first()) }}">
@php
    $authSetting = \App\Models\Setting::query()->first();
    $authLogo = \App\Support\MediaUrl::fromPath($authSetting?->logo);
@endphp

<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-head">
            <a href="{{ route('home') }}" class="auth-logo-link" aria-label="{{ __('site.brand') }}">
                @if($authLogo)
                    <img src="{{ $authLogo }}" alt="{{ __('site.brand') }}" class="auth-logo-image">
                @else
                    <span class="auth-logo-fallback">{{ mb_substr(__('site.brand'), 0, 1) }}</span>
                @endif
            </a>
            <a href="{{ route('home') }}" class="auth-brand">{{ __('site.brand') }}</a>
            <p class="auth-sub">{{ $subtitle ?? 'تجربة دخول وتسجيل مريحة ومتوافقة مع تصميم المنصة.' }}</p>
        </div>

        <div class="auth-body">
            {{ $slot }}
            <div class="auth-footer-note">© {{ date('Y') }} {{ __('site.brand') }}</div>
        </div>
    </div>
</div>
</body>
</html>
