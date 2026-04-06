<x-guest-layout :title="'تأكيد الهوية'" subtitle="لأمان حسابك، يرجى إدخال كلمة المرور الحالية قبل المتابعة.">
    <form method="POST" action="{{ route('password.confirm') }}" class="row g-3">
        @csrf

        <div class="col-12">
            <label for="password" class="auth-label">{{ __('Password') }}</label>
            <input id="password" class="form-control auth-input" type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger small" />
        </div>

        <div class="col-12 d-grid mt-2">
            <button type="submit" class="auth-btn">{{ __('Confirm') }}</button>
        </div>
    </form>
</x-guest-layout>
