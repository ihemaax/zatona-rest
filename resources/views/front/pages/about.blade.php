@extends('layouts.app')

@section('content')
<div class="card-shell p-5">
    <h1 class="section-title">من نحن</h1>

    <p class="text-muted">
        {{ $setting->restaurant_name ?? __('site.brand') }} هو مطعم يقدم تجربة طلب أونلاين سهلة وعصرية،
        مع التركيز على الجودة، السرعة، وتخصيص الطلب حسب رغبة العميل.
    </p>

    <p class="text-muted">
        هدفنا هو تقديم تجربة مريحة من بداية تصفح المنيو وحتى استلام الطلب، مع متابعة واضحة لحالة الأوردر
        وإمكانية اختيار الإضافات والتخصيصات بسهولة.
    </p>

    <div class="row g-4 mt-4">
        <div class="col-md-4">
            <div class="card-shell p-4 h-100">
                <h5 class="fw-bold">جودة</h5>
                <p class="text-muted mb-0">نهتم بتقديم وجبات بجودة عالية وتجهيز ممتاز.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-shell p-4 h-100">
                <h5 class="fw-bold">سرعة</h5>
                <p class="text-muted mb-0">نسعى لتوصيل الطلبات بسرعة مع تحديث الحالة أولًا بأول.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-shell p-4 h-100">
                <h5 class="fw-bold">مرونة</h5>
                <p class="text-muted mb-0">خيارات كثيرة لتخصيص الطلب بالطريقة التي تفضلها.</p>
            </div>
        </div>
    </div>
</div>
@endsection