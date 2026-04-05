@extends('layouts.admin')

@php
    $pageTitle = 'QR ورابط المنيو الإلكتروني';
    $pageSubtitle = 'إدارة رابط المنيو وتحميل رمز QR ومشاركته بسهولة';
@endphp

@section('content')
<style>
    .qr-page{
        display:grid;
        gap:18px;
    }

    .qr-grid{
        display:grid;
        grid-template-columns:minmax(0,1fr) 420px;
        gap:18px;
        align-items:start;
    }

    .qr-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .qr-card-head{
        padding:18px 18px 0;
    }

    .qr-card-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .qr-card-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .qr-card-body{
        padding:18px;
    }

    .qr-info-block + .qr-info-block{
        margin-top:20px;
        padding-top:20px;
        border-top:1px solid #ebe3d7;
    }

    .form-label{
        display:block;
        margin-bottom:8px;
        color:#6f6a61;
        font-size:.82rem;
        font-weight:800;
    }

    .form-control{
        background:#fffdfa;
        border:1px solid #ddd3c7;
        color:#443b33;
        border-radius:14px;
        min-height:46px;
        font-weight:700;
    }

    .form-control:focus{
        background:#fffdfa;
        color:#231f1b;
        border-color:#b9ad9e;
        box-shadow:0 0 0 .2rem rgba(111,127,95,.10);
    }

    .link-input{
        direction:ltr;
        text-align:left;
        font-weight:800;
    }

    .btn-row{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-top:14px;
    }

    .btn-primary-action{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:44px;
        padding:10px 18px;
        border:none;
        border-radius:14px;
        font-size:.82rem;
        font-weight:900;
        color:#fff;
        text-decoration:none;
        background:linear-gradient(135deg,#6f7f5f 0%,#8d9d7c 100%);
        box-shadow:0 12px 22px rgba(111,127,95,.16);
        transition:.18s ease;
    }

    .btn-primary-action:hover{
        color:#fff;
        opacity:.97;
    }

    .btn-secondary-action{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:44px;
        padding:10px 18px;
        border-radius:14px;
        background:#f3eee7;
        border:1px solid #e3d9cc;
        color:#443b33;
        font-size:.82rem;
        font-weight:800;
        text-decoration:none;
        transition:.18s ease;
    }

    .btn-secondary-action:hover{
        background:#ebe4da;
        color:#302821;
    }

    .note-box{
        background:#faf6f1;
        border:1px solid #e6ddd1;
        border-radius:18px;
        padding:16px;
    }

    .note-title{
        margin:0 0 6px;
        font-size:.88rem;
        font-weight:900;
        color:#231f1b;
    }

    .note-text{
        margin:0;
        color:#8a847a;
        font-size:.78rem;
        font-weight:700;
        line-height:1.8;
    }

    .qr-preview-shell{
        text-align:center;
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
    }

    .qr-preview-wrap{
        display:inline-block;
        background:#fff;
        border:1px solid #eadfce;
        border-radius:24px;
        padding:18px;
        box-shadow:0 10px 24px rgba(35,31,27,.06);
    }

    .qr-preview-image{
        width:100%;
        max-width:320px;
        height:auto;
        display:block;
    }

    .qr-preview-caption{
        margin-top:14px;
        color:#8a847a;
        font-size:.78rem;
        font-weight:700;
        line-height:1.7;
    }

    @media (max-width: 991.98px){
        .qr-grid{
            grid-template-columns:1fr;
        }
    }

    @media (max-width: 767.98px){
        .qr-card{
            border-radius:20px;
        }

        .qr-card-head,
        .qr-card-body{
            padding-left:14px;
            padding-right:14px;
        }

        .btn-row{
            flex-direction:column;
        }

        .btn-primary-action,
        .btn-secondary-action{
            width:100%;
        }
    }
</style>

<div class="qr-page">
    <div class="qr-grid">
        <section class="qr-card">
            <div class="qr-card-head">
                <h2 class="qr-card-title">رابط المنيو الإلكتروني</h2>
                <p class="qr-card-subtitle">استخدم هذا الرابط لمشاركة المنيو الرقمي مع العملاء داخل المطعم أو عبر أي وسيلة تسويقية بسهولة.</p>
            </div>

            <div class="qr-card-body">
                <div class="qr-info-block">
                    <label class="form-label">رابط المنيو</label>
                    <input type="text" id="digitalMenuLink" class="form-control link-input" value="{{ $menuUrl }}" readonly>

                    <div class="btn-row">
                        <button type="button" class="btn-primary-action" id="copyMenuLinkBtn">نسخ الرابط</button>
                        <a href="{{ $menuUrl }}" target="_blank" class="btn-secondary-action">فتح المنيو الإلكتروني</a>
                    </div>
                </div>

                <div class="qr-info-block">
                    <h3 class="qr-card-title" style="font-size:.95rem;">خيارات الاستخدام السريع</h3>
                    <p class="qr-card-subtitle">يمكنك تحميل رمز QR كصورة جاهزة أو فتح نسخة مخصصة للطباعة المباشرة.</p>

                    <div class="btn-row">
                        <a href="{{ route('admin.digital-menu.qr.download') }}" class="btn-primary-action">تحميل QR Code</a>
                        <a href="{{ route('admin.digital-menu.qr.print') }}" target="_blank" class="btn-secondary-action">فتح نسخة الطباعة</a>
                    </div>
                </div>

                <div class="qr-info-block">
                    <div class="note-box">
                        <h4 class="note-title">معلومة مهمة</h4>
                        <p class="note-text">عند تعديل رابط المنيو من صفحة الإعدادات، يتم تحديث الرابط العام ورمز QR تلقائياً دون الحاجة إلى أي إجراء إضافي.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="qr-card qr-preview-shell">
            <div class="qr-card-head">
                <h2 class="qr-card-title">معاينة رمز QR</h2>
                <p class="qr-card-subtitle">يمكن للعميل مسح الرمز باستخدام الهاتف للوصول مباشرة إلى صفحة المنيو الإلكتروني.</p>
            </div>

            <div class="qr-card-body text-center">
                <div class="qr-preview-wrap">
                    <img src="{{ route('admin.digital-menu.qr.image') }}" alt="QR Code" class="qr-preview-image">
                </div>

                <div class="qr-preview-caption">
                    امسح الرمز بكاميرا الهاتف للتأكد من أن الرابط يعمل بشكل صحيح قبل الطباعة أو المشاركة.
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const copyBtn = document.getElementById('copyMenuLinkBtn');
    const input = document.getElementById('digitalMenuLink');

    copyBtn?.addEventListener('click', async function () {
        try {
            await navigator.clipboard.writeText(input.value);
            copyBtn.textContent = 'تم نسخ الرابط بنجاح';
            setTimeout(() => copyBtn.textContent = 'نسخ الرابط', 1800);
        } catch (e) {
            input.select();
            document.execCommand('copy');
            copyBtn.textContent = 'تم نسخ الرابط بنجاح';
            setTimeout(() => copyBtn.textContent = 'نسخ الرابط', 1800);
        }
    });
});
</script>
@endsection