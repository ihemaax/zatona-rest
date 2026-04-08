@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width:560px">
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h1 class="h4 mb-2">تأكيد رقم واتساب</h1>
            <p class="text-muted mb-4">هنبعتلك كود بس علشان نتأكد إنك جعان 😄</p>

            <div class="mb-3 text-muted">
                الرقم: <strong dir="ltr">+20{{ $phone }}</strong>
            </div>

            <form method="POST" action="{{ route('checkout.otp.verify') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">كود التحقق</label>
                    <input type="text" name="otp_code" class="form-control" maxlength="6" required autofocus>
                    @error('otp_code')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-brand w-100">تأكيد وإكمال الطلب</button>
            </form>

            <form method="POST" action="{{ route('checkout.otp.resend') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-outline-secondary w-100">إعادة إرسال الكود</button>
            </form>
        </div>
    </div>
</div>
@endsection
