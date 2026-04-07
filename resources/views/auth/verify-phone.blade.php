<x-guest-layout :title="'تأكيد رقم واتساب'" subtitle="بعتنالك كود على الواتساب عشان نتأكد إنك جعان فعلًا 😄">
    @if (session('success'))
        <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger py-2 px-3 mb-3">{{ session('error') }}</div>
    @endif

    <div class="mb-3 text-muted small">
        رقم الواتساب: <strong dir="ltr">{{ $phone }}</strong>
    </div>

    <form method="POST" action="{{ route('register.phone.verify') }}" class="row g-3">
        @csrf

        <div class="col-12">
            <label for="otp_code" class="auth-label">كود التحقق</label>
            <input id="otp_code" class="form-control auth-input" type="text" name="otp_code" maxlength="6" required autofocus>
            <x-input-error :messages="$errors->get('otp_code')" class="mt-2 text-danger small" />
        </div>

        <div class="col-12 d-grid mt-2">
            <button type="submit" class="auth-btn">تأكيد الكود</button>
        </div>
    </form>

    <form method="POST" action="{{ route('register.phone.verify.resend') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">إعادة إرسال الكود</button>
    </form>
</x-guest-layout>
