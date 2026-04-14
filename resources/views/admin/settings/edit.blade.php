@extends('layouts.admin')

@php
    $pageTitle = 'إعدادات المطعم';
    $pageSubtitle = 'إدارة بيانات المطعم الأساسية والتحكم في حالة التشغيل بصورة احترافية';
@endphp

@section('content')
<style>
    .restaurant-settings-page{
        display:grid;
        gap:18px;
    }

    .restaurant-settings-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .restaurant-settings-head{
        padding:18px 18px 0;
    }

    .restaurant-settings-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .restaurant-settings-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .restaurant-settings-body{
        padding:18px;
    }

    .restaurant-form-grid{
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

    .settings-actions{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-top:18px;
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

    @media (max-width: 991.98px){
        .field-col-6{
            grid-column:span 12;
        }
    }

    @media (max-width: 767.98px){
        .restaurant-settings-card{
            border-radius:20px;
        }

        .restaurant-settings-head,
        .restaurant-settings-body{
            padding-left:14px;
            padding-right:14px;
        }

        .settings-actions{
            flex-direction:column;
        }

        .btn-save-settings{
            width:100%;
        }
    }
</style>

<div class="restaurant-settings-page">
    <section class="restaurant-settings-card">
        <div class="restaurant-settings-head">
            <h2 class="restaurant-settings-title">إعدادات المطعم</h2>
            <p class="restaurant-settings-subtitle">قم بتحديث البيانات الأساسية للمطعم بما يشمل الاسم ووسائل التواصل والعنوان ورسوم التوصيل وحالة التشغيل، بالإضافة إلى الشعار والبانر الخاصين بالواجهة.</p>
        </div>

        <div class="restaurant-settings-body">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="restaurant-form-grid">
                    <div class="field-card field-col-6">
                        <label class="form-label">اسم المطعم</label>
                        <input
                            type="text"
                            name="restaurant_name"
                            class="form-control"
                            value="{{ old('restaurant_name', $setting->restaurant_name ?? '') }}"
                            placeholder="أدخل اسم المطعم"
                        >
                        <div class="field-hint">يظهر هذا الاسم في واجهات النظام والصفحات المخصصة للعملاء حسب الإعدادات الحالية.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">رقم الهاتف</label>
                        <input
                            type="text"
                            name="restaurant_phone"
                            class="form-control"
                            value="{{ old('restaurant_phone', $setting->restaurant_phone ?? '') }}"
                            placeholder="مثال: 01000000000"
                        >
                        <div class="field-hint">يُستخدم رقم الهاتف كوسيلة تواصل رئيسية مع العملاء عند الحاجة.</div>
                    </div>

                    <div class="field-card field-col-12">
                        <label class="form-label">العنوان</label>
                        <input
                            type="text"
                            name="restaurant_address"
                            class="form-control"
                            value="{{ old('restaurant_address', $setting->restaurant_address ?? '') }}"
                            placeholder="أدخل عنوان المطعم"
                        >
                        <div class="field-hint">يفضل كتابة عنوان واضح ومختصر ليسهل ظهوره واستخدامه داخل النظام.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">رسوم التوصيل</label>
                        <input
                            type="number"
                            step="0.01"
                            name="delivery_fee"
                            class="form-control"
                            value="{{ old('delivery_fee', $setting->delivery_fee ?? 0) }}"
                            placeholder="0.00"
                        >
                        <div class="field-hint">حدد قيمة رسوم التوصيل الافتراضية التي تُستخدم أثناء إنشاء الطلبات.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">حالة المطعم</label>
                        <select name="is_open" class="form-select">
                            <option value="1" {{ old('is_open', $setting->is_open ?? false) ? 'selected' : '' }}>مفتوح</option>
                            <option value="0" {{ !old('is_open', $setting->is_open ?? false) ? 'selected' : '' }}>مغلق</option>
                        </select>
                        <div class="field-hint">تحدد هذه الحالة ما إذا كان المطعم متاحاً لاستقبال الطلبات في الوقت الحالي أم لا.</div>
                    </div>

                    <div class="field-card field-col-12">
                        <label class="form-label">Front Theme</label>
                        @featureEnabled('theme_switching')
                        <select name="front_theme" class="form-select">
                            @foreach(($frontThemes ?? []) as $themeKey => $theme)
                                <option value="{{ $themeKey }}" {{ old('front_theme', $setting->front_theme ?? config('front_themes.fallback')) === $themeKey ? 'selected' : '' }}>
                                    {{ $theme['name'] ?? $themeKey }}
                                </option>
                            @endforeach
                        </select>
                        @php
                            $selectedTheme = ($frontThemes ?? [])[old('front_theme', $setting->front_theme ?? config('front_themes.fallback'))] ?? null;
                        @endphp
                        <div class="field-hint">
                            <strong>الوصف:</strong> {{ $selectedTheme['description'] ?? 'ثيم واجهة العميل الحالي.' }}<br>
                            <strong>Preview:</strong> {{ $selectedTheme['preview'] ?? '-' }}
                        </div>
                        @else
                        <input type="hidden" name="front_theme" value="{{ $setting->front_theme ?? config('front_themes.fallback') }}">
                        <div class="field-hint">{{ config('subscription.blocked_message') }}</div>
                        @endfeatureEnabled
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">الشعار</label>
                        <input type="file" name="logo" class="form-control">
                        <div class="field-hint">يفضل استخدام شعار واضح بخلفية مناسبة ليظهر بشكل احترافي داخل الواجهة.</div>

                        @if(!empty($setting->logo))
                            <div class="preview-box">
                                <img
                                    src="{{ \App\Support\MediaUrl::fromPath($setting->logo) }}"
                                    alt="logo"
                                    class="preview-thumb logo-thumb"
                                >
                            </div>
                        @endif
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">البانر</label>
                        <input type="file" name="banner" class="form-control">
                        <div class="field-hint">يفضل استخدام صورة عريضة مناسبة للعرض في الواجهة الرئيسية أو الصفحات التعريفية.</div>

                        @if(!empty($setting->banner))
                            <div class="preview-box">
                                <img
                                    src="{{ \App\Support\MediaUrl::fromPath($setting->banner) }}"
                                    alt="banner"
                                    class="preview-thumb banner-thumb"
                                >
                            </div>
                        @endif
                    </div>
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn-save-settings">حفظ الإعدادات</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
