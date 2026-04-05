@extends('layouts.app')

@section('content')
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
                <h5 class="fw-bold mb-3">تواصل معنا</h5>
                <p class="text-muted mb-0">
                    يمكنك التواصل معنا عبر الهاتف أو زيارة المطعم مباشرة. وفي الإصدارات القادمة يمكن إضافة نموذج مراسلة مباشر.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection