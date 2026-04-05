@extends('layouts.admin')

@php
    $pageTitle = 'إعدادات المنيو الإلكتروني';
    $pageSubtitle = 'إدارة بيانات المنيو الرقمي والرابط العام وإعدادات العرض بصورة احترافية';
@endphp

@section('content')
<style>
    .digital-settings-page{
        display:grid;
        gap:18px;
    }

    .digital-settings-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .digital-settings-head{
        padding:18px 18px 0;
    }

    .digital-settings-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .digital-settings-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .digital-settings-body{
        padding:18px;
    }

    .digital-form-grid{
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

    .preview-box{
        margin-top:12px;
    }

    .preview-thumb{
        border:1px solid #eadfce;
        border-radius:16px;
        object-fit:cover;
        display:block;
        background:#f7f1e9;
    }

    .logo-thumb{
        width:90px;
        height:90px;
    }

    .banner-thumb{
        width:100%;
        max-width:240px;
        height:96px;
    }

    .toggle-grid{
        display:grid;
        grid-template-columns:repeat(3, minmax(0,1fr));
        gap:16px;
    }

    .toggle-card{
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:18px;
        padding:14px;
        display:flex;
        align-items:flex-start;
        gap:10px;
    }

    .form-check-input{
        width:1.1rem;
        height:1.1rem;
        margin-top:2px;
        border-color:#cdbfaa;
        box-shadow:none;
        flex-shrink:0;
    }

    .form-check-input:checked{
        background-color:#6f7f5f;
        border-color:#6f7f5f;
    }

    .toggle-label{
        color:#443b33;
        font-size:.85rem;
        font-weight:800;
        line-height:1.6;
        margin-bottom:4px;
    }

    .toggle-note{
        color:#8a847a;
        font-size:.74rem;
        font-weight:700;
        line-height:1.7;
    }

    .btn-save-settings{
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

    .btn-save-settings:hover{
        color:#fff;
        opacity:.97;
    }

    .link-section{
        margin-top:22px;
        padding-top:22px;
        border-top:1px solid #ebe3d7;
    }

    .link-title{
        margin:0 0 6px;
        font-size:.96rem;
        font-weight:900;
        color:#231f1b;
    }

    .link-subtitle{
        margin:0 0 14px;
        color:#8a847a;
        font-size:.78rem;
        font-weight:700;
        line-height:1.7;
    }

    .link-grid{
        display:grid;
        grid-template-columns:minmax(0,1fr) 220px;
        gap:12px;
        align-items:center;
    }

    .btn-open-menu{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:46px;
        padding:10px 16px;
        border-radius:14px;
        background:#f3eee7;
        border:1px solid #e3d9cc;
        color:#443b33;
        font-size:.82rem;
        font-weight:800;
        text-decoration:none;
        transition:.18s ease;
        width:100%;
    }

    .btn-open-menu:hover{
        background:#ebe4da;
        color:#302821;
    }

    @media (max-width: 991.98px){
        .field-col-6,
        .field-col-4{
            grid-column:span 12;
        }

        .toggle-grid{
            grid-template-columns:1fr;
        }

        .link-grid{
            grid-template-columns:1fr;
        }
    }

    @media (max-width: 767.98px){
        .digital-settings-card{
            border-radius:20px;
        }

        .digital-settings-head,
        .digital-settings-body{
            padding-left:14px;
            padding-right:14px;
        }

        .btn-save-settings{
            width:100%;
        }
    }
</style>

<div class="digital-settings-page">
    <section class="digital-settings-card">
        <div class="digital-settings-head">
            <h2 class="digital-settings-title">إعدادات المنيو الإلكتروني</h2>
            <p class="digital-settings-subtitle">هذا المنيو الرقمي مستقل عن منيو الطلبات الخاص بالموقع، ويمكن من هنا إدارة بياناته العامة والرابط المخصص وإعدادات الظهور بشكل كامل.</p>
        </div>

        <div class="digital-settings-body">
            <form action="{{ route('admin.digital-menu.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="digital-form-grid">
                    <div class="field-card field-col-6">
                        <label class="form-label">عنوان المنيو</label>
                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            value="{{ old('title', $setting->title) }}"
                            placeholder="مثال: منيو المطعم"
                            required
                        >
                        <div class="field-hint">يظهر هذا العنوان في الواجهة الرئيسية للمنيو الإلكتروني أمام العملاء.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">المعرف الخاص بالرابط (Slug)</label>
                        <input
                            type="text"
                            name="slug"
                            class="form-control"
                            value="{{ old('slug', $setting->slug) }}"
                            placeholder="example-menu"
                            required
                        >
                        <div class="field-hint">يستخدم هذا الحقل في إنشاء الرابط العام للمنيو، لذلك يفضل أن يكون واضحاً ومختصراً.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">العنوان الفرعي</label>
                        <input
                            type="text"
                            name="subtitle"
                            class="form-control"
                            value="{{ old('subtitle', $setting->subtitle) }}"
                            placeholder="مثال: أفضل الأطباق والعروض اليومية"
                        >
                        <div class="field-hint">نص تعريفي قصير يظهر أسفل عنوان المنيو لإبراز هوية المكان أو نوع الخدمة.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">رقم الهاتف</label>
                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="{{ old('phone', $setting->phone) }}"
                            placeholder="مثال: 01000000000"
                        >
                        <div class="field-hint">يمكن عرضه كوسيلة تواصل مباشرة داخل صفحة المنيو الإلكتروني.</div>
                    </div>

                    <div class="field-card field-col-12">
                        <label class="form-label">العنوان</label>
                        <input
                            type="text"
                            name="address"
                            class="form-control"
                            value="{{ old('address', $setting->address) }}"
                            placeholder="أدخل عنوان الفرع أو الموقع"
                        >
                        <div class="field-hint">يظهر هذا العنوان داخل بيانات المنيو لمساعدة العميل على الوصول إلى المكان بسهولة.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">الشعار (Logo)</label>
                        <input type="file" name="logo" class="form-control">
                        <div class="field-hint">يفضل استخدام صورة واضحة بخلفية نظيفة لتظهر بشكل احترافي داخل المنيو.</div>

                        @if($setting->logo)
                            <div class="preview-box">
                                <img src="{{ asset('storage/' . $setting->logo) }}" alt="logo" class="preview-thumb logo-thumb">
                            </div>
                        @endif
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">البانر الرئيسي</label>
                        <input type="file" name="banner" class="form-control">
                        <div class="field-hint">يفضل استخدام صورة عريضة مناسبة لواجهة المنيو لإبراز الهوية البصرية بشكل أقوى.</div>

                        @if($setting->banner)
                            <div class="preview-box">
                                <img src="{{ asset('storage/' . $setting->banner) }}" alt="banner" class="preview-thumb banner-thumb">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="toggle-grid mt-3">
                    <div class="toggle-card">
                        <input class="form-check-input" type="checkbox" name="show_prices" value="1" id="show_prices_toggle" {{ $setting->show_prices ? 'checked' : '' }}>
                        <div>
                            <label class="toggle-label" for="show_prices_toggle">إظهار الأسعار</label>
                            <div class="toggle-note">عند التفعيل، يتم عرض أسعار المنتجات داخل المنيو الإلكتروني للعميل.</div>
                        </div>
                    </div>

                    <div class="toggle-card">
                        <input class="form-check-input" type="checkbox" name="show_descriptions" value="1" id="show_descriptions_toggle" {{ $setting->show_descriptions ? 'checked' : '' }}>
                        <div>
                            <label class="toggle-label" for="show_descriptions_toggle">إظهار الأوصاف</label>
                            <div class="toggle-note">عند التفعيل، يتم عرض الوصف المختصر أسفل كل منتج داخل المنيو.</div>
                        </div>
                    </div>

                    <div class="toggle-card">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_toggle" {{ $setting->is_active ? 'checked' : '' }}>
                        <div>
                            <label class="toggle-label" for="is_active_toggle">تفعيل المنيو الإلكتروني</label>
                            <div class="toggle-note">عند إيقاف هذا الخيار لن يكون المنيو متاحاً للعرض عبر الرابط العام.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn-save-settings">حفظ الإعدادات</button>
                </div>
            </form>

            <div class="link-section">
                <h3 class="link-title">الرابط العام للمنيو الإلكتروني</h3>
                <p class="link-subtitle">يمكنك نسخ هذا الرابط ومشاركته مباشرة مع العملاء أو استخدامه في الطباعة والمواد التسويقية.</p>

                <div class="link-grid">
                    <input type="text" readonly class="form-control" value="{{ route('digital.menu.show', $setting->slug) }}">
                    <a href="{{ route('digital.menu.show', $setting->slug) }}" target="_blank" class="btn-open-menu">فتح المنيو الإلكتروني</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection