<x-guest-layout :title="__('site.login')" subtitle="سجل دخولك لإدارة حسابك ومتابعة طلباتك بسهولة.">
    <x-auth-session-status class="alert alert-success py-2 px-3 mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="row g-3">
        @csrf

        <div class="col-12">
            <label for="email" class="auth-label">{{ __('Email') }}</label>
            <input id="email" class="form-control auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger small" />
        </div>

        <div class="col-12">
            <label for="password" class="auth-label">{{ __('Password') }}</label>
            <input id="password" class="form-control auth-input" type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger small" />
        </div>

        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <label for="remember_me" class="form-check m-0">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                <span class="form-check-label">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
        </div>

        <div class="col-12 d-grid mt-2">
            <button type="submit" class="auth-btn">{{ __('Log in') }}</button>
        </div>

        <div class="col-12 text-center mt-1">
            <a href="{{ route('register') }}" class="auth-link">{{ __('site.register') }}</a>
        </div>
    </form>
</x-guest-layout>
