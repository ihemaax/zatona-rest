@extends('layouts.app')

@section('content')
@php
    $title = __('site.brand');
    $metaDescription = 'Online ordering experience for faster checkout and clear delivery tracking.';
@endphp

<div class="card-shell p-5">
    <h1 class="section-title">الأسئلة الشائعة</h1>

    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                    كيف يمكنني الطلب؟
                </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    اختر المنتجات من المنيو، أضفها للسلة، ثم أكمل الطلب وحدد طريقة الاستلام (توصيل أو استلام من الفرع).
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                    ما هي طرق الدفع المتاحة؟
                </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    حاليًا الدفع المتاح هو كاش عند الاستلام. وسيتم إضافة وسائل دفع إلكترونية في الإصدارات القادمة.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                    ما متوسط وقت التوصيل؟
                </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    غالبًا بين 30 و60 دقيقة حسب الضغط والمنطقة، ويمكن متابعة حالة الطلب مباشرة من صفحة "طلباتي".
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                    هل يمكنني تعديل أو إلغاء الطلب؟
                </button>
            </h2>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    يمكن الإلغاء قبل بدء التحضير. بعد بدء التحضير يتم التعامل مع الطلب حسب سياسة المطعم وحالة التنفيذ.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
