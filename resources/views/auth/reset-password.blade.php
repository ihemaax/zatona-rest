<x-guest-layout :title="'تعيين كلمة مرور جديدة'" subtitle="أنشئ كلمة مرور قوية لإكمال استعادة حسابك بأمان.">
    <form method="POST" action="{{ route('password.store') }}" class="row g-3">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="col-12">
            <label for="email" class="auth-label">{{ __('Email') }}</label>
            <input id="email" class="form-control auth-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
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
            <button type="submit" class="auth-btn">{{ __('Reset Password') }}</button>
        </div>
    </form>
</x-guest-layout>
