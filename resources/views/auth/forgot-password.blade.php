<x-guest-layout :title="'استعادة كلمة المرور'" subtitle="أدخل بريدك الإلكتروني وسنرسل لك رابطًا آمنًا لتعيين كلمة مرور جديدة.">
    <x-auth-session-status class="alert alert-success py-2 px-3 mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="row g-3">
        @csrf

        <div class="col-12">
            <label for="email" class="auth-label">{{ __('Email') }}</label>
            <input id="email" class="form-control auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger small" />
        </div>

        <div class="col-12 d-grid mt-2">
            <button type="submit" class="auth-btn">{{ __('Email Password Reset Link') }}</button>
        </div>

        <div class="col-12 text-center mt-1">
            <a href="{{ route('login') }}" class="auth-link">{{ __('site.login') }}</a>
        </div>
    </form>
</x-guest-layout>
