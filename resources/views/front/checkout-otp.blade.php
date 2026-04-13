@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width:560px">
    <div class="otp-whatsapp-note mb-3">
        بعتنالك كود على واتساب علشان نتأكد انك جعان
    </div>

    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(isset($otpSent) && !$otpSent)
        <div class="alert alert-warning">
            تعذر إرسال الكود تلقائيًا حاليًا. اضغط «إعادة إرسال الكود» وجرب مرة ثانية.
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h1 class="h4 mb-2">تأكيد رقم واتساب</h1>
            <p class="text-muted mb-4">تم تسجيل بيانات الطلب. فضلاً أكمل التحقق بالكود لإرسال الطلب.</p>

            <div class="mb-3 text-muted">
                رقم واتساب: <strong dir="ltr">{{ $phone }}</strong>
            </div>

            <form method="POST" action="{{ route('checkout.otp.verify') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="otpCodeInput">كود التحقق</label>
                    <input id="otpCodeInput" type="text" name="otp_code" class="form-control" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" value="{{ old('otp_code') }}" required autofocus>
                    @error('otp_code')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div class="form-text">أدخل كود مكوّن من 6 أرقام.</div>
                </div>

                <button id="otpConfirmButton" type="submit" class="btn btn-brand w-100">
                    <span class="otp-submit-text">تأكيد وإكمال الطلب</span>
                    <span class="otp-submit-loading d-none" role="status" aria-live="polite">
                        <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                        جاري التحقق...
                    </span>
                </button>
            </form>

            <form method="POST" action="{{ route('checkout.otp.resend') }}" class="mt-4">
                @csrf
                <button type="submit" class="btn btn-outline-secondary w-100">إعادة إرسال الكود</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .otp-whatsapp-note {
        background: linear-gradient(135deg, rgba(13, 166, 152, .16), rgba(7, 93, 126, .14));
        border: 1px solid rgba(13, 166, 152, .2);
        color: #0f3f46;
        padding: .85rem 1rem;
        border-radius: 14px;
        font-weight: 700;
        text-align: center;
    }

    #otpConfirmButton {
        min-height: 44px;
    }
</style>

<script nonce="{{ $cspNonce }}">
    (() => {
        const form = document.querySelector('form[action="{{ route('checkout.otp.verify') }}"]');
        const button = document.getElementById('otpConfirmButton');
        const submitText = document.querySelector('.otp-submit-text');
        const loadingText = document.querySelector('.otp-submit-loading');

        if (!form || !button || !submitText || !loadingText) return;

        form.addEventListener('submit', () => {
            button.disabled = true;
            submitText.classList.add('d-none');
            loadingText.classList.remove('d-none');
        });

    })();
</script>
@endpush
