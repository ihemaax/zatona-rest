@extends('layouts.admin')

@php
    $pageTitle = 'منتجات المنيو الإلكتروني';
    $pageSubtitle = 'إدارة منتجات العرض الرقمي المخصصة للعملاء داخل المطعم بصورة منظمة واحترافية';
@endphp

@section('content')
<style>
    .digital-items-page{
        display:grid;
        gap:18px;
    }

    .digital-items-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .digital-items-head{
        padding:18px 18px 0;
    }

    .digital-items-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .digital-items-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .digital-items-body{
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

    .btn-save-item{
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

    .btn-save-item:hover{
        color:#fff;
        opacity:.97;
    }

    .digital-items-desktop{
        display:block;
    }

    .digital-items-mobile{
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

    .item-thumb{
        width:56px;
        height:56px;
        border-radius:14px;
        object-fit:cover;
        border:1px solid #eadfce;
        display:block;
    }

    .item-thumb-placeholder{
        width:56px;
        height:56px;
        border-radius:14px;
        background:#f1ebe3;
        border:1px solid #eadfce;
    }

    .item-name{
        font-weight:900;
        color:#231f1b;
    }

    .item-description{
        max-width:280px;
        line-height:1.7;
        color:#6f6a61;
        font-weight:700;
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

    .status-available{
        background:#ecfdf3;
        color:#15803d;
    }

    .status-unavailable{
        background:#fff1f2;
        color:#be123c;
    }

    .badge-chip{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:5px 10px;
        border-radius:999px;
        font-size:.71rem;
        font-weight:800;
        white-space:nowrap;
        background:#fff7e8;
        color:#b7791f;
    }

    .inline-item-form{
        display:grid;
        grid-template-columns:repeat(12, minmax(0,1fr));
        gap:8px;
    }

    .inline-item-form .form-control,
    .inline-item-form .form-select{
        min-height:38px;
        border-radius:12px;
        font-size:.8rem;
    }

    .inline-col-12{ grid-column:span 12; }
    .inline-col-6{ grid-column:span 6; }
    .inline-col-4{ grid-column:span 4; }
    .inline-col-3{ grid-column:span 3; }

    .inline-actions{
        display:flex;
        gap:8px;
        align-items:flex-start;
        flex-wrap:wrap;
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

    .digital-item-card{
        background:#fffdfa;
        border:1px solid #ebe3d7;
        border-radius:20px;
        padding:16px;
        box-shadow:0 10px 22px rgba(35,31,27,.05);
    }

    .digital-item-card + .digital-item-card{
        margin-top:12px;
    }

    .digital-item-head{
        display:flex;
        gap:12px;
        align-items:flex-start;
        margin-bottom:12px;
    }

    .digital-item-img{
        width:62px;
        height:62px;
        border-radius:14px;
        object-fit:cover;
        border:1px solid #eadfce;
        flex-shrink:0;
    }

    .digital-item-placeholder{
        width:62px;
        height:62px;
        border-radius:14px;
        background:#f1ebe3;
        border:1px solid #eadfce;
        flex-shrink:0;
    }

    .digital-item-name{
        font-size:.95rem;
        font-weight:900;
        color:#231f1b;
        margin-bottom:4px;
    }

    .digital-item-description{
        font-size:.78rem;
        color:#8a847a;
        font-weight:700;
        line-height:1.7;
        margin-bottom:8px;
    }

    .digital-item-price{
        font-size:.88rem;
        color:#443b33;
        font-weight:900;
    }

    .digital-item-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
        margin-bottom:14px;
    }

    .digital-item-box{
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:14px;
        padding:10px 12px;
    }

    .digital-item-label{
        font-size:.72rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:4px;
    }

    .digital-item-value{
        font-size:.88rem;
        color:#443b33;
        font-weight:800;
        word-break:break-word;
        line-height:1.6;
    }

    .digital-item-status{
        margin-bottom:14px;
    }

    .mobile-item-form{
        display:grid;
        gap:10px;
    }

    .mobile-item-form .form-control,
    .mobile-item-form .form-select{
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
        .field-col-4,
        .field-col-3{
            grid-column:span 12;
        }

        .inline-col-6,
        .inline-col-4,
        .inline-col-3{
            grid-column:span 12;
        }
    }

    @media (max-width: 767.98px){
        .digital-items-card{
            border-radius:20px;
        }

        .digital-items-head,
        .digital-items-body{
            padding-left:14px;
            padding-right:14px;
        }

        .digital-items-desktop{
            display:none;
        }

        .digital-items-mobile{
            display:block;
        }

        .digital-item-grid{
            grid-template-columns:1fr;
        }

        .btn-save-item{
            width:100%;
        }
    }
</style>

<div class="digital-items-page">
    <section class="digital-items-card">
        <div class="digital-items-head">
            <h2 class="digital-items-title">إضافة منتج جديد</h2>
            <p class="digital-items-subtitle">أضف منتجاً جديداً إلى المنيو الإلكتروني وحدد القسم والسعر والوصف والصورة وترتيب الظهور بما يضمن عرضاً واضحاً وجذاباً للعملاء.</p>
        </div>

        <div class="digital-items-body">
            <form action="{{ route('admin.digital-menu.items.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="digital-form-grid">
                    <div class="field-card field-col-4">
                        <label class="form-label">القسم</label>
                        <select name="digital_menu_category_id" class="form-select" required>
                            <option value="">اختر القسم المناسب</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="field-hint">حدد القسم الذي سيظهر داخله المنتج داخل المنيو الإلكتروني.</div>
                    </div>

                    <div class="field-card field-col-4">
                        <label class="form-label">اسم المنتج</label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: برجر دبل" required>
                        <div class="field-hint">اكتب اسم المنتج بصيغة واضحة ومناسبة للعرض أمام العميل.</div>
                    </div>

                    <div class="field-card field-col-4">
                        <label class="form-label">السعر</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="0" placeholder="0.00">
                        <div class="field-hint">أدخل سعر المنتج كما سيظهر داخل القائمة الرقمية.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="اكتب وصفاً مختصراً يساعد العميل على فهم المنتج بشكل أفضل"></textarea>
                        <div class="field-hint">يفضل أن يكون الوصف مختصراً وواضحاً ويبرز أهم مميزات المنتج.</div>
                    </div>

                    <div class="field-card field-col-3">
                        <label class="form-label">شارة ترويجية</label>
                        <input type="text" name="badge" class="form-control" placeholder="مثال: جديد">
                        <div class="field-hint">يمكن استخدامها لإبراز حالة خاصة مثل جديد أو الأكثر طلباً.</div>
                    </div>

                    <div class="field-card field-col-3">
                        <label class="form-label">ترتيب الظهور</label>
                        <input type="number" name="sort_order" class="form-control" value="0" placeholder="0">
                        <div class="field-hint">القيمة الأقل تظهر أولاً داخل القسم.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">صورة المنتج</label>
                        <input type="file" name="image" class="form-control">
                        <div class="field-hint">يفضل استخدام صورة واضحة ومناسبة للعرض داخل شاشة المنيو.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <div class="toggle-card">
                            <input class="form-check-input" type="checkbox" name="is_available" value="1" checked id="new_digital_item_available">
                            <label class="form-check-label" for="new_digital_item_available">المنتج متاح للعرض حالياً</label>
                        </div>
                        <div class="field-hint">يمكن لاحقاً إخفاء المنتج أو إيقاف توفره دون حذفه من النظام.</div>
                    </div>

                    <div class="field-col-12">
                        <button type="submit" class="btn-save-item">إضافة المنتج</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @foreach($categories as $category)
        <section class="digital-items-card">
            <div class="digital-items-head">
                <h2 class="digital-items-title">{{ $category->name }}</h2>
                <p class="digital-items-subtitle">{{ $category->items->count() }} منتج داخل هذا القسم، ويمكنك من هنا مراجعة المنتجات الحالية وتحديث بياناتها بسرعة.</p>
            </div>

            <div class="digital-items-body">
                <div class="digital-items-desktop">
                    <div class="ops-table-wrap">
                        <table class="ops-table">
                            <thead>
                                <tr>
                                    <th>الصورة</th>
                                    <th>الاسم</th>
                                    <th>الوصف</th>
                                    <th>السعر</th>
                                    <th>الشارة</th>
                                    <th>الترتيب</th>
                                    <th>الحالة</th>
                                    <th style="min-width:320px;">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($category->items as $item)
                                    <tr>
                                        <td>
                                            @if($item->image)
                                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="item-thumb">
                                            @else
                                                <div class="item-thumb-placeholder"></div>
                                            @endif
                                        </td>
                                        <td><span class="item-name">{{ $item->name }}</span></td>
                                        <td><div class="item-description">{{ \Illuminate\Support\Str::limit($item->description, 80) ?: 'لا يوجد وصف مضاف لهذا المنتج.' }}</div></td>
                                        <td>{{ number_format($item->price, 2) }} ج.م</td>
                                        <td>
                                            @if($item->badge)
                                                <span class="badge-chip">{{ $item->badge }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $item->sort_order }}</td>
                                        <td>
                                            @if($item->is_available)
                                                <span class="ops-status status-available">متاح</span>
                                            @else
                                                <span class="ops-status status-unavailable">غير متاح</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.digital-menu.items.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="inline-item-form">
                                                @csrf
                                                @method('PUT')

                                                <input type="hidden" name="digital_menu_category_id" value="{{ $category->id }}">

                                                <div class="inline-col-6">
                                                    <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                                                </div>

                                                <div class="inline-col-3">
                                                    <input type="number" step="0.01" name="price" class="form-control" value="{{ $item->price }}">
                                                </div>

                                                <div class="inline-col-3">
                                                    <input type="number" name="sort_order" class="form-control" value="{{ $item->sort_order }}">
                                                </div>

                                                <div class="inline-col-6">
                                                    <input type="text" name="badge" class="form-control" value="{{ $item->badge }}" placeholder="شارة ترويجية">
                                                </div>

                                                <div class="inline-col-6">
                                                    <select name="is_available" class="form-select">
                                                        <option value="1" {{ $item->is_available ? 'selected' : '' }}>متاح</option>
                                                        <option value="0" {{ !$item->is_available ? 'selected' : '' }}>غير متاح</option>
                                                    </select>
                                                </div>

                                                <div class="inline-col-12">
                                                    <textarea name="description" rows="2" class="form-control">{{ $item->description }}</textarea>
                                                </div>

                                                <div class="inline-col-6">
                                                    <input type="file" name="image" class="form-control">
                                                </div>

                                                <div class="inline-col-6">
                                                    <div class="inline-actions">
                                                        <button type="submit" class="btn-edit-inline">حفظ التعديلات</button>
                                            </form>

                                                        <form action="{{ route('admin.digital-menu.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا المنتج؟')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-delete-inline">حذف المنتج</button>
                                                        </form>
                                                    </div>
                                                </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="digital-empty">لا توجد منتجات مضافة داخل هذا القسم حتى الآن.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="digital-items-mobile">
                    @forelse($category->items as $item)
                        <div class="digital-item-card">
                            <div class="digital-item-head">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="digital-item-img">
                                @else
                                    <div class="digital-item-placeholder"></div>
                                @endif

                                <div class="flex-grow-1">
                                    <div class="digital-item-name">{{ $item->name }}</div>
                                    <div class="digital-item-description">{{ \Illuminate\Support\Str::limit($item->description, 80) ?: 'لا يوجد وصف مضاف لهذا المنتج.' }}</div>
                                    <div class="digital-item-price">{{ number_format($item->price, 2) }} ج.م</div>
                                </div>
                            </div>

                            <div class="digital-item-grid">
                                <div class="digital-item-box">
                                    <div class="digital-item-label">الترتيب</div>
                                    <div class="digital-item-value">{{ $item->sort_order }}</div>
                                </div>

                                <div class="digital-item-box">
                                    <div class="digital-item-label">الشارة</div>
                                    <div class="digital-item-value">{{ $item->badge ?: 'لا توجد شارة' }}</div>
                                </div>
                            </div>

                            <div class="digital-item-status">
                                @if($item->is_available)
                                    <span class="ops-status status-available">متاح</span>
                                @else
                                    <span class="ops-status status-unavailable">غير متاح</span>
                                @endif
                            </div>

                            <form action="{{ route('admin.digital-menu.items.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="mobile-item-form">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="digital_menu_category_id" value="{{ $category->id }}">

                                <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>

                                <input type="number" step="0.01" name="price" class="form-control" value="{{ $item->price }}">

                                <input type="number" name="sort_order" class="form-control" value="{{ $item->sort_order }}">

                                <input type="text" name="badge" class="form-control" value="{{ $item->badge }}" placeholder="شارة ترويجية">

                                <textarea name="description" rows="2" class="form-control">{{ $item->description }}</textarea>

                                <select name="is_available" class="form-select">
                                    <option value="1" {{ $item->is_available ? 'selected' : '' }}>متاح</option>
                                    <option value="0" {{ !$item->is_available ? 'selected' : '' }}>غير متاح</option>
                                </select>

                                <input type="file" name="image" class="form-control">

                                <button type="submit" class="btn-edit-inline w-100">حفظ التعديلات</button>
                            </form>

                            <form action="{{ route('admin.digital-menu.items.destroy', $item->id) }}" method="POST" class="mt-2" onsubmit="return confirm('هل تريد حذف هذا المنتج؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete-inline w-100">حذف المنتج</button>
                            </form>
                        </div>
                    @empty
                        <div class="digital-empty">لا توجد منتجات مضافة داخل هذا القسم حتى الآن.</div>
                    @endforelse
                </div>
            </div>
        </section>
    @endforeach
</div>
@endsection