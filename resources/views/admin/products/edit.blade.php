@extends('layouts.admin')

@php
    $pageTitle = 'تعديل المنتج';
    $pageSubtitle = $product->name;
@endphp

@section('content')
<style>
    .product-edit-page{
        display:grid;
        gap:18px;
    }

    .product-edit-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .product-edit-head{
        padding:18px 18px 0;
    }

    .product-edit-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .product-edit-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .product-edit-body{
        padding:18px;
    }

    .product-form-grid{
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

    .form-control::placeholder,
    textarea.form-control::placeholder{
        color:#9a9084;
    }

    .field-hint{
        margin-top:8px;
        color:#8a847a;
        font-size:.75rem;
        font-weight:700;
        line-height:1.7;
    }

    .image-preview-box{
        margin-top:12px;
    }

    .image-preview-thumb{
        width:90px;
        height:90px;
        object-fit:cover;
        border-radius:16px;
        border:1px solid #eadfce;
        display:block;
        background:#f7f1e9;
    }

    .toggle-card{
        display:flex;
        align-items:flex-start;
        gap:10px;
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:18px;
        padding:14px;
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

    .product-actions{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-top:18px;
    }

    .btn-save-product{
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

    .btn-save-product:hover{
        color:#fff;
        opacity:.97;
    }

    .btn-soft-product{
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

    .btn-soft-product:hover{
        background:#ebe4da;
        color:#302821;
    }

    @media (max-width: 991.98px){
        .field-col-6{
            grid-column:span 12;
        }
    }

    @media (max-width: 767.98px){
        .product-edit-card{
            border-radius:20px;
        }

        .product-edit-head,
        .product-edit-body{
            padding-left:14px;
            padding-right:14px;
        }

        .product-actions{
            flex-direction:column;
        }

        .btn-save-product,
        .btn-soft-product{
            width:100%;
        }
    }
</style>

<div class="product-edit-page">
    <section class="product-edit-card">
        <div class="product-edit-head">
            <h2 class="product-edit-title">تعديل المنتج</h2>
            <p class="product-edit-subtitle">قم بتحديث بيانات المنتج الحالية، وتعديل الصورة والوصف والسعر والحالة التشغيلية بما يضمن عرضاً منظماً ودقيقاً داخل النظام.</p>
        </div>

        <div class="product-edit-body">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="product-form-grid">
                    <div class="field-card field-col-6">
                        <label class="form-label">اسم المنتج</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $product->name) }}"
                            placeholder="أدخل اسم المنتج"
                            required
                        >
                        <div class="field-hint">اكتب اسم المنتج بصيغة واضحة كما سيظهر داخل الواجهة ولوحة الإدارة.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">القسم</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">اختر القسم</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id', $product->category_id) === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="field-hint">حدد القسم المناسب لضمان ظهور المنتج في مكانه الصحيح داخل المنيو.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">السعر</label>
                        <input
                            type="number"
                            step="0.01"
                            name="price"
                            class="form-control"
                            value="{{ old('price', $product->price) }}"
                            placeholder="0.00"
                            required
                        >
                        <div class="field-hint">أدخل سعر المنتج بدقة كما سيظهر للعميل أثناء الطلب.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">الصورة الجديدة</label>
                        <input type="file" name="image" class="form-control">
                        <div class="field-hint">يمكنك رفع صورة جديدة لتحسين عرض المنتج، أو ترك الحقل فارغاً للاحتفاظ بالصورة الحالية.</div>

                        @if($product->image)
                            <div class="image-preview-box">
                                <img
                                    src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                    class="image-preview-thumb"
                                >
                            </div>
                        @endif
                    </div>

                    <div class="field-card field-col-12">
                        <label class="form-label">الوصف</label>
                        <textarea
                            name="description"
                            rows="5"
                            class="form-control"
                            placeholder="اكتب وصفاً مختصراً يوضح مكونات المنتج أو مميزاته"
                        >{{ old('description', $product->description) }}</textarea>
                        <div class="field-hint">يساعد الوصف الواضح في تحسين تجربة العميل وفهم تفاصيل المنتج بشكل أسرع.</div>
                    </div>

                    <div class="field-col-12">
                        <div class="toggle-card">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_available"
                                value="1"
                                id="product_is_available"
                                {{ old('is_available', $product->is_available) ? 'checked' : '' }}
                            >
                            <div>
                                <label class="toggle-label" for="product_is_available">المنتج متاح للطلب</label>
                                <div class="toggle-note">عند إلغاء هذا الخيار، لن يكون المنتج متاحاً ضمن المنتجات القابلة للطلب حتى مع الاحتفاظ ببياناته داخل النظام.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="product-actions">
                    <button type="submit" class="btn-save-product">حفظ التعديلات</button>
                    <a href="{{ route('admin.products.index') }}" class="btn-soft-product">الرجوع إلى المنتجات</a>
                    <a href="{{ route('admin.products.options.index', $product->id) }}" class="btn-soft-product">إدارة الاختيارات</a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection