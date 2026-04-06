@extends('layouts.app')

@section('content')
@php
    $title = __('site.brand');
    $metaDescription = 'Online ordering experience for faster checkout and clear delivery tracking.';
@endphp

<div class="card-shell p-5">
    <h1 class="section-title">اتصل بنا</h1>

    <div class="row g-4">
        <div class="col-md-6">
            <h5 class="fw-bold mb-3">بيانات التواصل</h5>
            <p><strong>اسم المطعم:</strong> {{ $setting->restaurant_name ?? __('site.brand') }}</p>
            <p><strong>الهاتف:</strong> {{ $setting->restaurant_phone ?? '-' }}</p>
            <p><strong>العنوان:</strong> {{ $setting->restaurant_address ?? '-' }}</p>
            <p><strong>الحالة:</strong> {{ ($setting && $setting->is_open) ? 'مفتوح الآن' : 'مغلق الآن' }}</p>
        </div>

        <div class="col-md-6">
            <div class="card-shell p-4">
                <h5 class="fw-bold mb-3">نموذج تواصل سريع</h5>
                <form class="row g-3" action="mailto:{{ config('mail.from.address', 'support@example.com') }}" method="post" enctype="text/plain">
                    <div class="col-12">
                        <label class="form-label">الاسم</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">رسالتك</label>
                        <textarea class="form-control" name="message" rows="4" required></textarea>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-brand" type="submit">إرسال</button>
                    </div>
                </form>
                <p class="text-muted small mt-3 mb-0">متوسط زمن الرد: خلال 15-30 دقيقة في أوقات العمل.</p>
            </div>
        </div>
    </div>
</div>
@endsection
