@extends('layouts.app')

@section('content')
@php
    $title = __('site.brand');
    $metaDescription = 'Online ordering experience for faster checkout and clear delivery tracking.';
@endphp

<div class="card-shell p-5 front-content-page">
    <h1 class="section-title">من نحن</h1>

    <p class="text-muted">
        {{ $setting->restaurant_name ?? __('site.brand') }} يقدم تجربة طلب أونلاين عملية وسريعة مع تركيز قوي على الجودة، الالتزام، وسهولة الاستخدام.
    </p>

    <p class="text-muted">
        نعمل على تطوير المنصة باستمرار لتحسين سرعة الطلب، وضوح التتبع، ودقة التوصيل، وتقديم تجربة أفضل لعملائنا يومًا بعد يوم.
    </p>

    <div class="row g-4 mt-4">
        <div class="col-md-4">
            <div class="card-shell p-4 h-100">
                <h5 class="fw-bold">جودة</h5>
                <p class="text-muted mb-0">مكونات طازجة ومعايير إعداد ثابتة لكل طلب.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-shell p-4 h-100">
                <h5 class="fw-bold">سرعة</h5>
                <p class="text-muted mb-0">معالجة أسرع للطلبات ومتابعة لحظية للحالة.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-shell p-4 h-100">
                <h5 class="fw-bold">مرونة</h5>
                <p class="text-muted mb-0">خيارات تخصيص متعددة بما يناسب تفضيلاتك.</p>
            </div>
        </div>
    </div>
</div>
@endsection
