@extends('layouts.admin')

@php
    $pageTitle = 'إضافة فرع جديد';
    $pageSubtitle = 'إضافة فرع جديد إلى النظام وتجهيزه لطلبات الاستلام ومتابعة التشغيل';
@endphp

@section('content')
<style>
    .branch-create-page{
        display:grid;
        gap:18px;
    }

    .branch-create-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .branch-create-head{
        padding:18px 18px 0;
    }

    .branch-create-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .branch-create-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .branch-create-body{
        padding:18px;
    }

    .branch-form-grid{
        display:grid;
        grid-template-columns:repeat(12, minmax(0,1fr));
        gap:16px;
    }

    .field-col-12{ grid-column:span 12; }
    .field-col-6{ grid-column:span 6; }

    .field-card{
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:18px;
        padding:14px;
        min-width:0;
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

    .form-control::placeholder{
        color:#9a9084;
    }

    .field-hint{
        margin-top:8px;
        color:#8a847a;
        font-size:.75rem;
        font-weight:700;
        line-height:1.7;
    }

    .branch-actions{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-top:18px;
    }

    .btn-save-branch{
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
    }

    .btn-save-branch:hover{
        color:#fff;
        opacity:.97;
    }

    .btn-back-branch{
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

    .btn-back-branch:hover{
        background:#ebe4da;
        color:#302821;
    }

    @media (max-width: 991.98px){
        .field-col-6{
            grid-column:span 12;
        }
    }

    @media (max-width: 767.98px){
        .branch-create-card{
            border-radius:20px;
        }

        .branch-create-head,
        .branch-create-body{
            padding-left:14px;
            padding-right:14px;
        }

        .branch-actions{
            flex-direction:column;
        }

        .btn-save-branch,
        .btn-back-branch{
            width:100%;
        }
    }
</style>

<div class="branch-create-page">
    <section class="branch-create-card">
        <div class="branch-create-head">
            <h2 class="branch-create-title">إضافة فرع جديد</h2>
            <p class="branch-create-subtitle">أدخل البيانات الأساسية للفرع ليتم اعتماده داخل النظام وإتاحته ضمن خيارات الاستلام وإدارة التشغيل.</p>
        </div>

        <div class="branch-create-body">
            <form action="{{ route('admin.branches.store') }}" method="POST">
                @csrf
                <input type="hidden" name="is_active" value="1">

                <div class="branch-form-grid">
                    <div class="field-card field-col-6">
                        <label class="form-label">اسم الفرع</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name') }}"
                            placeholder="مثال: فرع وسط البلد"
                            required
                        >
                        <div class="field-hint">اكتب اسمًا واضحًا للفرع كما سيظهر داخل النظام وضمن خيارات الاستلام للعميل.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">رقم الهاتف</label>
                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="{{ old('phone') }}"
                            placeholder="مثال: 01000000000"
                        >
                        <div class="field-hint">يمكن استخدام رقم الهاتف كوسيلة تواصل مباشرة مع هذا الفرع عند الحاجة.</div>
                    </div>

                    <div class="field-card field-col-12">
                        <label class="form-label">العنوان</label>
                        <input
                            type="text"
                            name="address"
                            class="form-control"
                            value="{{ old('address') }}"
                            placeholder="أدخل عنوان الفرع بالتفصيل"
                        >
                        <div class="field-hint">يفضل كتابة عنوان واضح ومختصر لتسهيل الوصول إلى الفرع وإدارته داخلياً.</div>
                    </div>
                </div>

                <div class="branch-actions">
                    <button type="submit" class="btn-save-branch">حفظ بيانات الفرع</button>
                    <a href="{{ route('admin.branches.index') }}" class="btn-back-branch">الرجوع إلى قائمة الفروع</a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
