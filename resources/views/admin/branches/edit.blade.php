@extends('layouts.admin')

@php
    $pageTitle = 'تعديل بيانات الفرع';
    $pageSubtitle = 'تحديث معلومات الفرع الحالي وإدارة حالته التشغيلية';
@endphp

@section('content')
<style>
    .branch-edit-page{
        display:grid;
        gap:18px;
    }

    .branch-edit-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .branch-edit-head{
        padding:18px 18px 0;
    }

    .branch-edit-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .branch-edit-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .branch-edit-body{
        padding:18px;
    }

    .branch-form-grid{
        display:grid;
        grid-template-columns:repeat(12, minmax(0,1fr));
        gap:16px;
    }

    .field-col-12{ grid-column:span 12; }
    .field-col-6{ grid-column:span 6; }
    .field-col-4{ grid-column:span 4; }

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
        .field-col-6,
        .field-col-4{
            grid-column:span 12;
        }
    }

    @media (max-width: 767.98px){
        .branch-edit-card{
            border-radius:20px;
        }

        .branch-edit-head,
        .branch-edit-body{
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

<div class="branch-edit-page">
    <section class="branch-edit-card">
        <div class="branch-edit-head">
            <h2 class="branch-edit-title">تعديل بيانات الفرع</h2>
            <p class="branch-edit-subtitle">قم بتحديث معلومات الفرع الحالية بما يشمل بيانات التواصل والموقع والحالة التشغيلية لضمان دقة البيانات داخل النظام.</p>
        </div>

        <div class="branch-edit-body">
            <form action="{{ route('admin.branches.update', $branch->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="branch-form-grid">
                    <div class="field-card field-col-6">
                        <label class="form-label">اسم الفرع</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $branch->name) }}"
                            placeholder="مثال: فرع وسط البلد"
                            required
                        >
                        <div class="field-hint">يظهر اسم الفرع داخل لوحة التحكم وخيارات الاستلام، لذلك يفضل أن يكون واضحاً وسهل التعرف عليه.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">رقم الهاتف</label>
                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="{{ old('phone', $branch->phone) }}"
                            placeholder="مثال: 01000000000"
                        >
                        <div class="field-hint">يمكن استخدام رقم الهاتف كوسيلة تواصل مباشرة مع الفرع في العمليات اليومية.</div>
                    </div>

                    <div class="field-card field-col-12">
                        <label class="form-label">العنوان</label>
                        <input
                            type="text"
                            name="address"
                            class="form-control"
                            value="{{ old('address', $branch->address) }}"
                            placeholder="أدخل عنوان الفرع بالتفصيل"
                            required
                        >
                        <div class="field-hint">اكتب عنواناً واضحاً لتسهيل الوصول إلى الفرع وتحسين تنظيم بيانات الفروع داخل النظام.</div>
                    </div>

                    <div class="field-card field-col-4">
                        <label class="form-label">خط العرض (Latitude)</label>
                        <input
                            type="text"
                            name="latitude"
                            class="form-control"
                            value="{{ old('latitude', $branch->latitude) }}"
                            placeholder="مثال: 30.0444"
                        >
                        <div class="field-hint">يمكن استخدامه لاحقاً في الخرائط أو تحديد الموقع الجغرافي بدقة.</div>
                    </div>

                    <div class="field-card field-col-4">
                        <label class="form-label">خط الطول (Longitude)</label>
                        <input
                            type="text"
                            name="longitude"
                            class="form-control"
                            value="{{ old('longitude', $branch->longitude) }}"
                            placeholder="مثال: 31.2357"
                        >
                        <div class="field-hint">يساعد في ربط الفرع بالإحداثيات الفعلية عند استخدام الخرائط أو الخدمات المكانية.</div>
                    </div>

                    <div class="field-card field-col-4">
                        <label class="form-label">الحالة</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" @selected(old('is_active', $branch->is_active) == 1)>نشط</option>
                            <option value="0" @selected(old('is_active', $branch->is_active) == 0)>غير نشط</option>
                        </select>
                        <div class="field-hint">الفروع النشطة فقط تكون جاهزة للاستخدام حسب إعدادات النظام وسير العمل الحالي.</div>
                    </div>
                </div>

                <div class="branch-actions">
                    <button type="submit" class="btn-save-branch">حفظ التعديلات</button>
                    <a href="{{ route('admin.branches.index') }}" class="btn-back-branch">الرجوع إلى قائمة الفروع</a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection