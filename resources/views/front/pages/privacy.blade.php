@extends('layouts.app')

@section('content')
@php
    $title = __('site.brand');
    $metaDescription = 'Online ordering experience for faster checkout and clear delivery tracking.';
@endphp

<div class="card-shell p-5 front-content-page">
    <h1 class="section-title">سياسة الخصوصية</h1>

    <p class="text-muted">
        نحن ملتزمون بحماية بياناتك الشخصية واستخدامها فقط لتقديم الخدمة وتحسين تجربة الطلب.
    </p>

    <ul class="text-muted">
        <li>نجمع فقط البيانات اللازمة لإتمام الطلب مثل الاسم، الهاتف، العنوان، وتفاصيل الطلب.</li>
        <li>لا نبيع بياناتك لأي طرف ثالث.</li>
        <li>قد نشارك البيانات مع أطراف تشغيلية لازمة للتوصيل أو الدعم الفني فقط.</li>
        <li>يمكنك طلب تعديل أو حذف بياناتك عبر التواصل مع الدعم.</li>
        <li>قد نقوم بتحديث هذه السياسة دوريًا، وأي تحديث يظهر على هذه الصفحة.</li>
    </ul>
</div>
@endsection
