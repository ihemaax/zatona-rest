@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width:560px">
    <div class="otp-whatsapp-note mb-3">
        بعتنالك كود على واتساب علشان نتأكد إنك معانا 👌
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
                    الزرار بيتحرك لحد ما تكتب كود التحقق كامل.
                </p>

                <div id="otpButtonZone" class="otp-button-zone">
                    <button id="otpConfirmButton" type="submit" class="btn btn-brand otp-confirm-btn">تأكيد وإكمال الطلب</button>
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

    .otp-button-zone {
        position: relative;
        min-height: 82px;
        border-radius: 16px;
        border: 1px dashed rgba(9, 84, 67, .35);
        background: linear-gradient(180deg, rgba(9, 84, 67, .03), rgba(9, 84, 67, .08));
        overflow: hidden;
    }

    .otp-confirm-btn {
        position: absolute;
        inset-inline-start: 50%;
        top: 50%;
        width: calc(100% - 1.25rem);
        max-width: 460px;
        transform: translate(-50%, -50%);
        transition: top .45s ease, inset-inline-start .45s ease, transform .45s ease, box-shadow .25s ease;
        box-shadow: 0 8px 20px rgba(9, 84, 67, .22);
        z-index: 2;
    }

    .otp-confirm-btn.is-evasive {
        animation: otpPulse 1.2s ease-in-out infinite alternate;
    }

    @keyframes otpPulse {
        from { box-shadow: 0 8px 20px rgba(9, 84, 67, .16); }
        to { box-shadow: 0 14px 26px rgba(9, 84, 67, .28); }
    }
</style>

<script nonce="{{ $cspNonce }}">
    (() => {
        const codeInput = document.getElementById('otpCodeInput');
        const button = document.getElementById('otpConfirmButton');
        const zone = document.getElementById('otpButtonZone');
        const hint = document.getElementById('otpButtonHint');

        if (!codeInput || !button || !zone || !hint) return;

        const hasCompleteCode = () => /^\d{6}$/.test(codeInput.value.trim());
        const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;
        let moveTimer = null;

        const placeButton = (xRatio, yRatio) => {
            const zoneRect = zone.getBoundingClientRect();
            const buttonRect = button.getBoundingClientRect();
            const padding = 8;
            const maxX = Math.max(zoneRect.width - buttonRect.width - (padding * 2), 0);
            const maxY = Math.max(zoneRect.height - buttonRect.height - (padding * 2), 0);

            const nextX = (maxX * xRatio) + padding;
            const nextY = (maxY * yRatio) + padding;

            button.style.insetInlineStart = `${nextX + (buttonRect.width / 2)}px`;
            button.style.top = `${nextY + (buttonRect.height / 2)}px`;
            button.style.transform = 'translate(-50%, -50%)';
        };

        const randomMove = () => {
            if (hasCompleteCode()) return;
            placeButton(Math.random(), Math.random());
        };

        const activateEvasiveMode = () => {
            button.classList.add('is-evasive');
            hint.textContent = 'الزرار هيثبت أول ما تكتب كود التحقق كامل.';
            randomMove();

            if (moveTimer) window.clearInterval(moveTimer);
            moveTimer = window.setInterval(randomMove, 1050);
        };

        const lockButton = () => {
            button.classList.remove('is-evasive');
            if (moveTimer) window.clearInterval(moveTimer);
            moveTimer = null;

            button.style.insetInlineStart = '50%';
            button.style.top = '50%';
            button.style.transform = 'translate(-50%, -50%)';
            hint.textContent = 'كدا تقدر تدوس على الزرار ✅';
        };

        const syncButtonState = () => {
            if (hasCompleteCode()) {
                lockButton();
            } else if (!isTouchDevice) {
                activateEvasiveMode();
            } else {
                hint.textContent = 'اكتب كود التحقق كامل علشان تقدر تأكد الطلب.';
            }
        };

        if (!isTouchDevice) {
            button.addEventListener('mouseenter', () => {
                if (!hasCompleteCode()) randomMove();
            });
        }

        codeInput.addEventListener('input', syncButtonState);
        window.addEventListener('resize', () => {
            if (!hasCompleteCode() && !isTouchDevice) randomMove();
        });

        syncButtonState();
    })();
</script>
@endpush
