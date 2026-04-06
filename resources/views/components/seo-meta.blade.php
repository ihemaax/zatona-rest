@props([
    'title' => null,
    'description' => null,
    'image' => null,
])

@php
    $appName = config('app.name', __('site.brand'));
    $resolvedTitle = $title ?: $appName;
    $resolvedDescription = $description ?: 'اطلب وجبتك بسهولة من المنيو الإلكتروني مع تتبع حالة الطلب وخيارات توصيل أو استلام.';
    $resolvedImage = $image ?: asset('favicon.ico');
    $canonical = url()->current();
    $locale = app()->getLocale() === 'ar' ? 'ar_EG' : 'en_US';
    $seoSetting = \App\Models\Setting::query()->first();
    $restaurantName = $seoSetting->restaurant_name ?? $appName;
@endphp

<title>{{ $resolvedTitle }}</title>
<meta name="description" content="{{ $resolvedDescription }}">
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="index,follow,max-image-preview:large">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $restaurantName }}">
<meta property="og:locale" content="{{ $locale }}">
<meta property="og:title" content="{{ $resolvedTitle }}">
<meta property="og:description" content="{{ $resolvedDescription }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $resolvedImage }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $resolvedTitle }}">
<meta name="twitter:description" content="{{ $resolvedDescription }}">
<meta name="twitter:image" content="{{ $resolvedImage }}">

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Restaurant',
    'name' => $restaurantName,
    'telephone' => $seoSetting->restaurant_phone ?? null,
    'address' => $seoSetting->restaurant_address ?? null,
    'url' => config('app.url'),
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
</script>
