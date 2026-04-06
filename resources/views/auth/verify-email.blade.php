<x-guest-layout :title="'تأكيد البريد الإلكتروني'" subtitle="تم إرسال رابط التفعيل إلى بريدك، فعّل حسابك للمتابعة.">
    <div class="alert alert-light border mb-3" style="font-weight:700;color:#5d564d;">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success py-2 px-3 mb-3">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="d-grid gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="auth-btn w-100">{{ __('Resend Verification Email') }}</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-light w-100" style="min-height:46px;border-radius:12px;font-weight:800;">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
