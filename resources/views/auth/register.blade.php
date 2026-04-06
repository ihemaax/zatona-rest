<x-guest-layout :title="__('site.register')" subtitle="أنشئ حساب جديد في ثواني وابدأ الطلب والمتابعة مباشرة.">
    <form method="POST" action="{{ route('register') }}" class="row g-3">
        @csrf

        <div class="col-12">
            <label for="name" class="auth-label">{{ __('Name') }}</label>
            <input id="name" class="form-control auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-danger small" />
        </div>

        <div class="col-12">
            <label for="email" class="auth-label">{{ __('Email') }}</label>
            <input id="email" class="form-control auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger small" />
        </div>

        <div class="col-12">
            <label for="password" class="auth-label">{{ __('Password') }}</label>
            <input id="password" class="form-control auth-input" type="password" name="password" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger small" />
        </div>

        <div class="col-12">
            <label for="password_confirmation" class="auth-label">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" class="form-control auth-input" type="password" name="password_confirmation" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-danger small" />
        </div>

        <div class="col-12 d-grid mt-2">
            <button type="submit" class="auth-btn">{{ __('Register') }}</button>
        </div>

        <div class="col-12 text-center mt-1">
            <a href="{{ route('login') }}" class="auth-link">{{ __('Already registered?') }}</a>
        </div>
    </form>
</x-guest-layout>
