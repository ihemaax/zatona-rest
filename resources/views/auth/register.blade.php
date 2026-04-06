@extends('layouts.app')

@section('content')
@php
    $title = __('site.register') . ' | ' . __('site.brand');
    $metaDescription = 'أنشئ حسابك الآن لتجربة طلب أسرع، تتبع أفضل، وحفظ بياناتك وعناوينك بسهولة.';
@endphp

<style>
    .auth-wrap{max-width:1020px;margin:0 auto;padding-bottom:40px;}
    .auth-shell{display:grid;grid-template-columns:1fr 1fr;gap:18px;align-items:stretch;}
    .auth-card{background:var(--fb-card);border:1px solid var(--fb-border);border-radius:24px;box-shadow:var(--fb-shadow);padding:24px;}
    .auth-hero{background:linear-gradient(135deg,#5f6e52,#7f8f70);color:#fff;}
    .auth-title{font-size:1.55rem;font-weight:900;margin-bottom:6px;}
    .auth-sub{opacity:.92;line-height:1.8;font-weight:700;}
    .auth-bullet{margin-top:12px;display:flex;gap:10px;align-items:flex-start;font-weight:700;}
    .auth-label{font-weight:800;margin-bottom:6px;display:block;}
    .auth-input{border:1px solid #ddd4c8;border-radius:12px;padding:.68rem .85rem;background:#fffaf4;}
    .auth-input:focus{border-color:#b5c3a7;box-shadow:0 0 0 .2rem rgba(111,127,95,.14);background:#fff;}
    .auth-btn{width:100%;min-height:46px;border:none;border-radius:12px;background:linear-gradient(135deg,var(--fb-primary-dark),var(--fb-primary));color:#fff;font-weight:900;}
    .auth-muted-link{color:var(--fb-primary-dark);font-weight:800;text-decoration:none;}
    .auth-muted-link:hover{text-decoration:underline;}
    @media (max-width: 991px){.auth-shell{grid-template-columns:1fr;}}
</style>

<div class="auth-wrap">
    <div class="auth-shell">
        <aside class="auth-card auth-hero">
            <h1 class="auth-title">{{ __('site.register') }}</h1>
            <p class="auth-sub">أنشئ حسابك في ثواني واستمتع بتجربة طلب أكثر احترافية وسرعة.</p>

            <div class="auth-bullet"><i class="bi bi-person-check"></i><span>حفظ بياناتك لتجربة أسرع في الطلب القادم.</span></div>
            <div class="auth-bullet"><i class="bi bi-geo-alt"></i><span>إدارة عناوينك بسهولة وإعادة استخدامها مباشرة.</span></div>
            <div class="auth-bullet"><i class="bi bi-clock-history"></i><span>سجل كامل لطلباتك السابقة.</span></div>
        </aside>

        <section class="auth-card">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label class="auth-label" for="name">{{ __('Name') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control auth-input" required autofocus autocomplete="name">
                </div>

                <div class="mb-3">
                    <label class="auth-label" for="email">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control auth-input" required autocomplete="username">
                </div>

                <div class="mb-3">
                    <label class="auth-label" for="password">{{ __('Password') }}</label>
                    <input id="password" type="password" name="password" class="form-control auth-input" required autocomplete="new-password">
                </div>

                <div class="mb-4">
                    <label class="auth-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control auth-input" required autocomplete="new-password">
                </div>

                <button class="auth-btn" type="submit">{{ __('Register') }}</button>
            </form>

            <div class="mt-3 text-center text-muted">
                {{ __('Already registered?') }}
                <a class="auth-muted-link" href="{{ route('login') }}">{{ __('site.login') }}</a>
            </div>
        </section>
    </div>
</div>
@endsection
