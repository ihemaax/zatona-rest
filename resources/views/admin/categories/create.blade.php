@extends('layouts.admin')

@php
    $pageTitle = 'إضافة قسم جديد';
    $pageSubtitle = 'إنشاء قسم جديد داخل قائمة المنيو وتنظيم المنتجات بشكل احترافي';
@endphp

@section('content')
<style>
    .category-create-page{
        display:grid;
        gap:18px;
    }

    .category-create-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .category-create-head{
        padding:18px 18px 0;
    }

    .category-create-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .category-create-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .category-create-body{
        padding:18px;
    }

    .category-form-grid{
        display:grid;
        grid-template-columns:repeat(12, minmax(0,1fr));
        gap:16px;
    }

    .field-col-6{ grid-column:span 6; }
    .field-col-4{ grid-column:span 4; }
    .field-col-12{ grid-column:span 12; }

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

    .form-control,
    .form-select{
        background:#fffdfa;
        border:1px solid #ddd3c7;
        color:#443b33;
        border-radius:14px;
        min-height:46px;
        font-weight:700;
    }

    .form-control:focus,
    .form-select:focus{
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

    .category-actions{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-top:18px;
    }

    .btn-save-category{
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

    .btn-save-category:hover{
        color:#fff;
        opacity:.97;
    }

    .btn-back-category{
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

    .btn-back-category:hover{
        background:#ebe4da;
        color:#302821;
    }

    @media (max-width: 991.98px){
        .field-col-6,
        .field-col-4{
            grid-column:span 12;
        }
    }

    @media (max-width: 767.98px){
        .category-create-card{
            border-radius:20px;
        }

        .category-create-head,
        .category-create-body{
            padding-left:14px;
            padding-right:14px;
        }

        .category-actions{
            flex-direction:column;
        }

        .btn-save-category,
        .btn-back-category{
            width:100%;
        }
    }
</style>

<div class="category-create-page">
    <section class="category-create-card">
        <div class="category-create-head">
            <h2 class="category-create-title">بيانات القسم</h2>
            <p class="category-create-subtitle">أدخل المعلومات الأساسية لإضافة قسم جديد إلى المنيو وتنظيم عرض المنتجات بطريقة واضحة واحترافية.</p>
        </div>

        <div class="category-create-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="category-form-grid">
                    <div class="field-card field-col-6">
                        <label class="form-label">اسم القسم</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name') }}"
                            placeholder="مثال: الوجبات الرئيسية"
                            required
                        >
                        <div class="field-hint">اكتب اسمًا واضحًا يظهر للعميل داخل قائمة المنيو.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">الرابط التعريفي (Slug)</label>
                        <input
                            type="text"
                            name="slug"
                            class="form-control"
                            value="{{ old('slug') }}"
                            placeholder="مثال: main-meals"
                        >
                        <div class="field-hint">يُستخدم لتنظيم الرابط الداخلي، ويمكن تركه فارغًا ليتم إنشاؤه لاحقًا.</div>
                    </div>

                    <div class="field-card field-col-4">
                        <label class="form-label">حالة القسم</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" @selected(old('is_active', 1) == 1)>نشط</option>
                            <option value="0" @selected(old('is_active') == 0)>غير نشط</option>
                        </select>
                        <div class="field-hint">الأقسام النشطة فقط تكون جاهزة للاستخدام والعرض حسب إعدادات النظام.</div>
                    </div>
                </div>

                <div class="category-actions">
                    <button type="submit" class="btn-save-category">حفظ القسم</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn-back-category">الرجوع إلى الأقسام</a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection