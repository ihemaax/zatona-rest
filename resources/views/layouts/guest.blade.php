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

    <style>
        :root{
            --auth-bg: #f6f3ee;
            --auth-card: #fffdf9;
            --auth-border: #e9e3d8;
            --auth-text: #222;
            --auth-muted: #6f6a61;
            --auth-primary: #6f7f5f;
            --auth-primary-dark: #5c6a4f;
            --auth-shadow: 0 18px 40px rgba(60,52,40,.10);
        }

        *{ box-sizing: border-box; }

        body{
            margin:0;
            min-height:100vh;
            font-family:'Cairo', Tahoma, Arial, sans-serif;
            background: linear-gradient(180deg, #f7f4ef 0%, #fbf9f5 52%, #f2eee7 100%);
            color: var(--auth-text);
        }

        .auth-shell{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 24px 14px;
        }

        .auth-card{
            width:100%;
            max-width:540px;
            background: var(--auth-card);
            border: 1px solid var(--auth-border);
            border-radius: 24px;
            box-shadow: var(--auth-shadow);
            overflow:hidden;
        }

        .auth-head{
            padding: 20px 22px 16px;
            border-bottom: 1px solid #efe8dc;
            background: linear-gradient(180deg, #fffdf9 0%, #f9f6f0 100%);
            text-align:center;
        }

        .auth-logo-link{
            width:82px;
            height:82px;
            margin:0 auto 10px;
            border-radius:50%;
            overflow:hidden;
            border:1px solid #ebe3d6;
            background:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 12px 24px rgba(60,52,40,.12);
        }

        .auth-logo-image{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .auth-logo-fallback{
            width:100%;
            height:100%;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#f4efe6;
            color:var(--auth-primary-dark);
            font-size:1.8rem;
            font-weight:900;
        }

        .auth-brand{
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--auth-text);
            text-decoration:none;
        }

        .auth-sub{
            margin: 6px 0 0;
            font-size: .9rem;
            color: var(--auth-muted);
            font-weight: 700;
        }

        .auth-body{ padding: 20px 22px 22px; }

        .auth-label{ font-weight: 800; color: #3f392f; margin-bottom: 6px; font-size: .9rem; }

        .auth-input{
            border:1px solid #ddd3c7;
            background:#fff;
            border-radius:12px;
            min-height:46px;
            font-weight:700;
        }

        .auth-input:focus{
            border-color:#b7c6a6;
            box-shadow:0 0 0 .2rem rgba(111,127,95,.12);
        }

        .auth-btn{
            min-height:46px;
            border:none;
            border-radius:12px;
            font-weight:900;
            background: linear-gradient(135deg, var(--auth-primary-dark) 0%, var(--auth-primary) 100%);
            color:#fff;
            box-shadow:0 12px 24px rgba(111,127,95,.16);
        }

        .auth-btn:hover{ color:#fff; opacity:.96; }

        .auth-link{ color: var(--auth-primary-dark); font-weight:800; text-decoration:none; }
        .auth-link:hover{ text-decoration:underline; }

        .auth-footer-note{
            text-align:center;
            margin-top: 14px;
            color: var(--auth-muted);
            font-size: .84rem;
            font-weight:700;
        }
    </style>
</head>
<body>
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
