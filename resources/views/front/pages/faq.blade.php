@extends('layouts.app')

@section('content')
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
                    يمكنك تصفح المنيو، اختيار المنتجات، تخصيص الطلب، ثم إتمامه من السلة.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                    ما هي طريقة الدفع المتاحة؟
                </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    حاليًا الدفع المتاح هو كاش عند الاستلام.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                    هل يمكنني متابعة حالة الطلب؟
                </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    نعم، يمكنك متابعة حالة الطلب من صفحة "طلباتي" بعد تسجيل الدخول.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                    هل يمكن حفظ العنوان؟
                </button>
            </h2>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    نعم، يمكنك حفظ العنوان لاستخدامه لاحقًا عند الطلبات القادمة.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection