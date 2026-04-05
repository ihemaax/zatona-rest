@extends('layouts.admin')

@php
    $pageTitle = 'أقسام المنيو الإلكتروني';
    $pageSubtitle = 'إدارة أقسام المنيو الرقمي وتنظيم ظهورها بصورة واضحة واحترافية';
@endphp

@section('content')
<style>
    .digital-categories-page{
        display:grid;
        gap:18px;
    }

    .digital-categories-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .digital-categories-head{
        padding:18px 18px 0;
    }

    .digital-categories-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .digital-categories-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .digital-categories-body{
        padding:18px;
    }

    .digital-form-grid{
        display:grid;
        grid-template-columns:repeat(12, minmax(0,1fr));
        gap:16px;
    }

    .field-col-12{ grid-column:span 12; }
    .field-col-6{ grid-column:span 6; }
    .field-col-3{ grid-column:span 3; }

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

    .toggle-card{
        display:flex;
        align-items:center;
        gap:10px;
        min-height:100%;
    }

    .form-check-input{
        width:1.1rem;
        height:1.1rem;
        margin-top:0;
        border-color:#cdbfaa;
        box-shadow:none;
    }

    .form-check-input:checked{
        background-color:#6f7f5f;
        border-color:#6f7f5f;
    }

    .form-check-label{
        color:#443b33;
        font-size:.85rem;
        font-weight:800;
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

    .digital-categories-desktop{
        display:block;
    }

    .digital-categories-mobile{
        display:none;
    }

    .ops-table-wrap{
        border:1px solid #ebe3d7;
        border-radius:18px;
        overflow:hidden;
        background:#fffdfa;
    }

    .ops-table{
        width:100%;
        margin:0;
        border-collapse:separate;
        border-spacing:0;
    }

    .ops-table thead th{
        background:#f8f4ee;
        color:#7b7268;
        font-size:.74rem;
        font-weight:900;
        letter-spacing:.04em;
        text-transform:uppercase;
        padding:14px 14px;
        white-space:nowrap;
        border-bottom:1px solid #e9e1d5;
    }

    .ops-table tbody td{
        color:#554d45;
        font-size:.84rem;
        padding:14px 14px;
        border-bottom:1px solid #efe7dd;
        vertical-align:middle;
        font-weight:700;
        background:#fffdfa;
    }

    .ops-table tbody tr:last-child td{
        border-bottom:none;
    }

    .ops-table tbody tr:hover td{
        background:#fcf8f3;
    }

    .category-name{
        font-weight:900;
        color:#231f1b;
    }

    .ops-status{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:5px 10px;
        border-radius:999px;
        font-size:.71rem;
        font-weight:800;
        white-space:nowrap;
    }

    .ops-status::before{
        content:'';
        width:6px;
        height:6px;
        border-radius:50%;
        background:currentColor;
        flex-shrink:0;
    }

    .status-active{
        background:#ecfdf3;
        color:#15803d;
    }

    .status-hidden{
        background:#fff1f2;
        color:#be123c;
    }

    .inline-edit-form{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        align-items:center;
    }

    .inline-edit-form .form-control,
    .inline-edit-form .form-select{
        min-height:38px;
        border-radius:12px;
        font-size:.8rem;
    }

    .input-name{ max-width:180px; }
    .input-sort{ max-width:90px; }
    .input-status{ max-width:120px; }

    .actions-stack{
        display:flex;
        flex-direction:column;
        gap:8px;
    }

    .btn-edit-inline{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:38px;
        padding:8px 12px;
        border-radius:12px;
        background:#f3eee7;
        border:1px solid #e3d9cc;
        color:#443b33;
        font-size:.78rem;
        font-weight:800;
        text-decoration:none;
        transition:.18s ease;
    }

    .btn-edit-inline:hover{
        background:#ebe4da;
        color:#302821;
    }

    .btn-delete-inline{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:38px;
        padding:8px 12px;
        border-radius:12px;
        background:#fff1f2;
        border:1px solid #fecdd3;
        color:#be123c;
        font-size:.78rem;
        font-weight:800;
        transition:.18s ease;
    }

    .btn-delete-inline:hover{
        background:#ffe4e6;
        color:#9f1239;
    }

    .digital-category-card{
        background:#fffdfa;
        border:1px solid #ebe3d7;
        border-radius:20px;
        padding:16px;
        box-shadow:0 10px 22px rgba(35,31,27,.05);
    }

    .digital-category-card + .digital-category-card{
        margin-top:12px;
    }

    .digital-category-name{
        font-size:.98rem;
        font-weight:900;
        color:#231f1b;
        margin-bottom:12px;
    }

    .digital-category-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
        margin-bottom:14px;
    }

    .digital-category-box{
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:14px;
        padding:10px 12px;
    }

    .digital-category-label{
        font-size:.72rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:4px;
    }

    .digital-category-value{
        font-size:.88rem;
        color:#443b33;
        font-weight:800;
        word-break:break-word;
        line-height:1.6;
    }

    .digital-category-status{
        margin-bottom:14px;
    }

    .mobile-edit-form{
        display:grid;
        gap:10px;
    }

    .mobile-edit-form .form-control,
    .mobile-edit-form .form-select{
        min-height:42px;
        border-radius:12px;
        font-size:.8rem;
    }

    .digital-empty{
        text-align:center;
        padding:32px 16px;
        font-size:.86rem;
        color:#8a847a;
        background:#faf6f1;
        border:1px dashed #e6ddd1;
        border-radius:16px;
        font-weight:700;
    }

    .text-muted{
        color:#8a847a !important;
    }

    @media (max-width: 991.98px){
        .field-col-6,
        .field-col-3{
            grid-column:span 12;
        }
    }

    @media (max-width: 767.98px){
        .digital-categories-card{
            border-radius:20px;
        }

        .digital-categories-head,
        .digital-categories-body{
            padding-left:14px;
            padding-right:14px;
        }

        .digital-categories-desktop{
            display:none;
        }

        .digital-categories-mobile{
            display:block;
        }

        .digital-category-grid{
            grid-template-columns:1fr;
        }

        .btn-save-category{
            width:100%;
        }
    }
</style>

<div class="digital-categories-page">
    <section class="digital-categories-card">
        <div class="digital-categories-head">
            <h2 class="digital-categories-title">إضافة قسم جديد</h2>
            <p class="digital-categories-subtitle">أضف قسماً جديداً إلى المنيو الإلكتروني لتسهيل تنظيم المنتجات وتحسين تجربة العرض داخل القائمة الرقمية.</p>
        </div>

        <div class="digital-categories-body">
            <form action="{{ route('admin.digital-menu.categories.store') }}" method="POST">
                @csrf

                <div class="digital-form-grid">
                    <div class="field-card field-col-6">
                        <label class="form-label">اسم القسم</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="مثال: العروض الخاصة"
                            required
                        >
                        <div class="field-hint">اكتب اسماً واضحاً يظهر للعميل داخل المنيو الإلكتروني.</div>
                    </div>

                    <div class="field-card field-col-3">
                        <label class="form-label">ترتيب الظهور</label>
                        <input
                            type="number"
                            name="sort_order"
                            class="form-control"
                            value="0"
                            placeholder="0"
                        >
                        <div class="field-hint">القيمة الأقل تظهر أولاً داخل القائمة.</div>
                    </div>

                    <div class="field-card field-col-3">
                        <div class="toggle-card">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="new_digital_category_active">
                            <label class="form-check-label" for="new_digital_category_active">القسم نشط وجاهز للعرض</label>
                        </div>
                        <div class="field-hint">يمكن إخفاء القسم لاحقاً دون الحاجة إلى حذفه من النظام.</div>
                    </div>

                    <div class="field-col-12">
                        <button type="submit" class="btn-save-category">إضافة القسم</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="digital-categories-card">
        <div class="digital-categories-head">
            <h2 class="digital-categories-title">الأقسام الحالية</h2>
            <p class="digital-categories-subtitle">استعرض الأقسام الحالية، وعدل بياناتها بسرعة، وتحكم في ترتيب ظهورها وحالتها داخل المنيو الإلكتروني.</p>
        </div>

        <div class="digital-categories-body">
            <div class="digital-categories-desktop">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>القسم</th>
                                <th>عدد المنتجات</th>
                                <th>الترتيب</th>
                                <th>الحالة</th>
                                <th style="min-width:280px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td><span class="category-name">{{ $category->name }}</span></td>
                                    <td>{{ $category->items_count }}</td>
                                    <td>{{ $category->sort_order }}</td>
                                    <td>
                                        @if($category->is_active)
                                            <span class="ops-status status-active">نشط</span>
                                        @else
                                            <span class="ops-status status-hidden">مخفي</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="actions-stack">
                                            <form action="{{ route('admin.digital-menu.categories.update', $category->id) }}" method="POST" class="inline-edit-form">
                                                @csrf
                                                @method('PUT')

                                                <input
                                                    type="text"
                                                    name="name"
                                                    class="form-control input-name"
                                                    value="{{ $category->name }}"
                                                    required
                                                >

                                                <input
                                                    type="number"
                                                    name="sort_order"
                                                    class="form-control input-sort"
                                                    value="{{ $category->sort_order }}"
                                                >

                                                <select name="is_active" class="form-select input-status">
                                                    <option value="1" {{ $category->is_active ? 'selected' : '' }}>نشط</option>
                                                    <option value="0" {{ !$category->is_active ? 'selected' : '' }}>مخفي</option>
                                                </select>

                                                <button type="submit" class="btn-edit-inline">حفظ التعديلات</button>
                                            </form>

                                            <form action="{{ route('admin.digital-menu.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا القسم؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete-inline">حذف القسم</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="digital-empty">لا توجد أقسام مضافة حتى الآن داخل المنيو الإلكتروني.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="digital-categories-mobile">
                @forelse($categories as $category)
                    <div class="digital-category-card">
                        <div class="digital-category-name">{{ $category->name }}</div>

                        <div class="digital-category-grid">
                            <div class="digital-category-box">
                                <div class="digital-category-label">عدد المنتجات</div>
                                <div class="digital-category-value">{{ $category->items_count }}</div>
                            </div>

                            <div class="digital-category-box">
                                <div class="digital-category-label">ترتيب الظهور</div>
                                <div class="digital-category-value">{{ $category->sort_order }}</div>
                            </div>
                        </div>

                        <div class="digital-category-status">
                            @if($category->is_active)
                                <span class="ops-status status-active">نشط</span>
                            @else
                                <span class="ops-status status-hidden">مخفي</span>
                            @endif
                        </div>

                        <form action="{{ route('admin.digital-menu.categories.update', $category->id) }}" method="POST" class="mobile-edit-form">
                            @csrf
                            @method('PUT')

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ $category->name }}"
                                required
                            >

                            <input
                                type="number"
                                name="sort_order"
                                class="form-control"
                                value="{{ $category->sort_order }}"
                            >

                            <select name="is_active" class="form-select">
                                <option value="1" {{ $category->is_active ? 'selected' : '' }}>نشط</option>
                                <option value="0" {{ !$category->is_active ? 'selected' : '' }}>مخفي</option>
                            </select>

                            <button type="submit" class="btn-edit-inline w-100">حفظ التعديلات</button>
                        </form>

                        <form action="{{ route('admin.digital-menu.categories.destroy', $category->id) }}" method="POST" class="mt-2" onsubmit="return confirm('هل تريد حذف هذا القسم؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete-inline w-100">حذف القسم</button>
                        </form>
                    </div>
                @empty
                    <div class="digital-empty">لا توجد أقسام مضافة حتى الآن داخل المنيو الإلكتروني.</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection