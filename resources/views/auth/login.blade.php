@extends('layouts.app')

@section('content')
@php
    $title = __('site.login') . ' | ' . __('site.brand');
    $metaDescription = 'تسجيل الدخول إلى حسابك لمتابعة طلباتك وإدارة بياناتك بسرعة وأمان.';
@endphp

<style>
    .auth-wrap{max-width:980px;margin:0 auto;padding-bottom:40px;}
    .auth-shell{display:grid;grid-template-columns:1.05fr .95fr;gap:18px;align-items:stretch;}
    .auth-card{background:var(--fb-card);border:1px solid var(--fb-border);border-radius:24px;box-shadow:var(--fb-shadow);padding:24px;}
    .auth-hero{background:linear-gradient(135deg,var(--fb-primary-dark),var(--fb-primary));color:#fff;}
    .auth-title{font-size:1.55rem;font-weight:900;margin-bottom:6px;}
    .auth-sub{opacity:.92;line-height:1.8;font-weight:700;}
    .auth-feature{display:flex;gap:10px;align-items:flex-start;margin-top:14px;font-weight:700;}
    .auth-feature i{font-size:1.1rem;}
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
            <h1 class="auth-title">{{ __('site.login') }}</h1>
            <p class="auth-sub">ارجع بسرعة لحسابك لمتابعة حالة الطلبات، إعادة الطلب بنقرة واحدة، وإدارة بياناتك بسهولة.</p>

            <div class="auth-feature"><i class="bi bi-shield-check"></i><span>تسجيل دخول آمن ومشفر.</span></div>
            <div class="auth-feature"><i class="bi bi-receipt-cutoff"></i><span>متابعة مباشرة لكل طلباتك السابقة والحالية.</span></div>
            <div class="auth-feature"><i class="bi bi-lightning-charge"></i><span>إتمام الطلبات بشكل أسرع من أي وقت.</span></div>
        </aside>

        <section class="auth-card">
            <x-auth-session-status class="mb-3" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="auth-label" for="email">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control auth-input" required autofocus autocomplete="username">
                </div>

                <div class="mb-3">
                    <label class="auth-label" for="password">{{ __('Password') }}</label>
                    <input id="password" type="password" name="password" class="form-control auth-input" required autocomplete="current-password">
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <label for="remember_me" class="form-check m-0 d-flex gap-2 align-items-center">
                        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    @if(Route::has('password.request'))
                        <a class="auth-muted-link" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                    @endif
                </div>

                <button class="auth-btn" type="submit">{{ __('Log in') }}</button>
            </form>

            <div class="mt-3 text-center text-muted">
                ما عندكش حساب؟
                <a class="auth-muted-link" href="{{ route('register') }}">{{ __('site.register') }}</a>
            </div>
        </section>
    </div>
</div>
@endsection
