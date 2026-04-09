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
                    <input id="otpCodeInput" type="text" name="otp_code" class="form-control" maxlength="6" required autofocus>
                    @error('otp_code')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <p id="otpButtonHint" class="otp-hint text-muted small mb-2">
                    الزرار بيتحرك يمين وشمال ومش هتعرف تدوس عليه غير بعد كتابة الكود كامل.
                </p>

                <div id="otpButtonTrack" class="otp-button-track">
                    <button id="otpConfirmButton" type="submit" class="btn btn-brand otp-confirm-btn is-evasive">تأكيد وإكمال الطلب</button>
                </div>
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

    .otp-button-track {
        position: relative;
        min-height: 54px;
    }

    .otp-confirm-btn {
        width: 100%;
        transition: transform .22s ease, box-shadow .25s ease;
        box-shadow: 0 8px 20px rgba(9, 84, 67, .22);
    }

    .otp-confirm-btn.is-evasive {
        animation: otpHorizontalRun .7s linear infinite alternate, otpPulse 1.05s ease-in-out infinite alternate;
        will-change: transform;
        cursor: not-allowed;
    }

    @keyframes otpPulse {
        from { box-shadow: 0 8px 20px rgba(9, 84, 67, .16); }
        to { box-shadow: 0 14px 26px rgba(9, 84, 67, .28); }
    }

    @keyframes otpHorizontalRun {
        from { transform: translateX(-38%); }
        to { transform: translateX(38%); }
    }
</style>

<script nonce="{{ $cspNonce }}">
    (() => {
        const codeInput = document.getElementById('otpCodeInput');
        const button = document.getElementById('otpConfirmButton');
        const form = button?.closest('form');
        const hint = document.getElementById('otpButtonHint');

        if (!codeInput || !button || !hint || !form) return;

        const hasCompleteCode = () => /^\d{6}$/.test(codeInput.value.trim());

        const activateEvasiveMode = () => {
            if (hasCompleteCode()) return;
            button.classList.add('is-evasive');
            button.classList.remove('is-locked');
            hint.textContent = 'الزرار هيثبت لما تكتب كود التحقق كامل.';
        };

        const lockButton = () => {
            button.classList.remove('is-evasive');
            button.classList.add('is-locked');
            button.style.transform = 'translateX(0)';
            hint.textContent = 'كدا تقدر تدوس على الزرار ✅';
        };

        const evadeCursor = (event) => {
            if (hasCompleteCode()) return;
            const rect = button.getBoundingClientRect();
            const x = event.clientX;
            const closeToButton = x >= (rect.left - 80) && x <= (rect.right + 80);

            if (!closeToButton) return;

            const runLeft = Math.random() > 0.5 ? -1 : 1;
            const distance = (button.parentElement?.clientWidth || rect.width) * 0.34 * runLeft;
            button.style.transform = `translateX(${distance}px)`;
        };

        const syncButtonState = () => {
            if (hasCompleteCode()) {
                lockButton();
            } else {
                activateEvasiveMode();
            }
        };

        form.addEventListener('submit', (event) => {
            if (!hasCompleteCode()) {
                event.preventDefault();
                activateEvasiveMode();
            }
        });

        codeInput.addEventListener('input', syncButtonState);
        document.addEventListener('mousemove', evadeCursor);

        syncButtonState();
    })();
</script>
@endpush
