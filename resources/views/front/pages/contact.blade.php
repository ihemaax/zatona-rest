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
                <h5 class="fw-bold mb-3">قنوات التواصل الحالية</h5>
                <div class="d-grid gap-2">
                    <a href="tel:{{ preg_replace('/\\D+/', '', (string) ($setting->restaurant_phone ?? '')) }}"
                       class="btn btn-outline-dark">
                        <i class="bi bi-telephone me-1"></i>
                        اتصال هاتفي مباشر
                    </a>

                    <a href="{{ route('pages.faq') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-question-circle me-1"></i>
                        الأسئلة الشائعة
                    </a>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    حاليًا الدعم متاح عبر الهاتف فقط. سيتم إضافة نظام تذاكر/رسائل داخلي في تحديث لاحق.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
