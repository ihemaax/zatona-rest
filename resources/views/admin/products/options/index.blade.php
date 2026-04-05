@extends('layouts.admin')

@php
    $pageTitle = 'خيارات المنتج';
    $pageSubtitle = $product->name;
@endphp

@section('content')
<style>
    .options-page-card{
        padding:20px;
    }

    .option-item-mobile{
        background:#fff;
        border:1px solid #dbe5f0;
        border-radius:16px;
        padding:12px;
    }

    .option-item-mobile + .option-item-mobile{
        margin-top:10px;
    }

    .option-item-mobile-actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:10px;
    }

    .option-item-mobile-actions form{
        width:100%;
    }

    .group-header-actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    @media (max-width: 767.98px){
        .options-page-card{
            padding:16px;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <div class="section-title mb-1">خيارات المنتج: {{ $product->name }}</div>
        <div class="section-subtitle mb-0">إضافة مجموعات اختيارات مثل الصوصات، الأحجام، والأنواع</div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.products.index') }}" class="btn-admin-soft">الرجوع للمنتجات</a>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-admin-soft">تعديل المنتج</a>
    </div>
</div>

<div class="admin-card options-page-card mb-4">
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="admin-card blue p-3 h-100">
                <div class="section-subtitle mb-1">اسم المنتج</div>
                <div class="fw-bold">{{ $product->name }}</div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="admin-card green p-3 h-100">
                <div class="section-subtitle mb-1">السعر الأساسي</div>
                <div class="fw-bold">{{ number_format($product->price, 2) }} ج.م</div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="admin-card orange p-3 h-100">
                <div class="section-subtitle mb-1">مجموعات الاختيارات</div>
                <div class="fw-bold">{{ $groups->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="admin-card options-page-card mb-4">
    <div class="section-title">إضافة مجموعة خيارات جديدة</div>
    <div class="section-subtitle">مثال: الصوصات / حجم الوجبة / إضافات جانبية</div>

    <form action="{{ route('admin.products.options.store', $product->id) }}" method="POST" class="row g-3 mt-1">
        @csrf
        <input type="hidden" name="is_active" value="1">

        <div class="col-12 col-md-4">
            <label class="form-label fw-bold">اسم المجموعة</label>
            <input type="text" name="name" class="form-control" placeholder="مثال: اختر الصوص" required>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label fw-bold">النوع</label>
            <select name="type" class="form-select" required>
                <option value="single">اختيار واحد</option>
                <option value="multiple">أكثر من اختيار</option>
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label fw-bold">إجباري؟</label>
            <select name="is_required" class="form-select" required>
                <option value="1">نعم</option>
                <option value="0">لا</option>
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label fw-bold">الحد الأقصى</label>
            <input type="number" name="max_selection" class="form-control" min="1" placeholder="للمتعدد فقط">
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label fw-bold">الترتيب</label>
            <input type="number" name="sort_order" class="form-control" value="0">
        </div>

        <div class="col-12">
            <button type="submit" class="btn-admin">إضافة المجموعة</button>
        </div>
    </form>
</div>

@if($groups->count())
    @foreach($groups as $group)
        <div class="admin-card options-page-card mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <div class="section-title mb-1">{{ $group->name }}</div>
                    <div class="section-subtitle mb-0">
                        النوع: {{ $group->type === 'single' ? 'اختيار واحد' : 'اختيارات متعددة' }}
                        — {{ $group->is_required ? 'إجباري' : 'اختياري' }}
                    </div>
                </div>

                <div class="group-header-actions">
                    <form action="{{ route('admin.products.options.destroy', [$product->id, $group->id]) }}" method="POST" onsubmit="return confirm('حذف هذه المجموعة؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">حذف المجموعة</button>
                    </form>
                </div>
            </div>

            <div class="admin-card p-3 mb-3">
                <form action="{{ route('admin.products.options.update', [$product->id, $group->id]) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="is_active" value="1">

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold">اسم المجموعة</label>
                        <input type="text" name="name" class="form-control" value="{{ $group->name }}" required>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold">النوع</label>
                        <select name="type" class="form-select" required>
                            <option value="single" {{ $group->type === 'single' ? 'selected' : '' }}>اختيار واحد</option>
                            <option value="multiple" {{ $group->type === 'multiple' ? 'selected' : '' }}>أكثر من اختيار</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold">إجباري؟</label>
                        <select name="is_required" class="form-select" required>
                            <option value="1" {{ $group->is_required ? 'selected' : '' }}>نعم</option>
                            <option value="0" {{ !$group->is_required ? 'selected' : '' }}>لا</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold">الحد الأقصى</label>
                        <input type="number" name="max_selection" class="form-control" min="1" value="{{ $group->max_selection }}">
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold">الترتيب</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $group->sort_order ?? 0 }}">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn-admin-soft">حفظ بيانات المجموعة</button>
                    </div>
                </form>
            </div>

            <div class="admin-card p-3 mb-3">
                <div class="section-title mb-2">إضافة عنصر داخل المجموعة</div>

                <form action="{{ route('admin.products.options.items.store', [$product->id, $group->id]) }}" method="POST" class="row g-3">
                    @csrf
                    <input type="hidden" name="is_default" value="0">
                    <input type="hidden" name="is_active" value="1">

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold">اسم العنصر</label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: صوص رانش" required>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold">سعر إضافي</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="0">
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold">الترتيب</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>

                    <div class="col-12 col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn-admin w-100">إضافة</button>
                    </div>
                </form>
            </div>

            @if($group->items->count())
                <div class="admin-table-wrap d-none d-md-block">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>العنصر</th>
                                <th>السعر الإضافي</th>
                                <th>الترتيب</th>
                                <th style="min-width:230px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->items as $item)
                                <tr>
                                    <td class="fw-bold">{{ $item->name }}</td>
                                    <td>{{ number_format($item->price, 2) }} ج.م</td>
                                    <td>{{ $item->sort_order ?? 0 }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <form action="{{ route('admin.products.options.items.update', [$product->id, $group->id, $item->id]) }}" method="POST" class="d-flex flex-wrap gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="is_default" value="0">
                                                <input type="hidden" name="is_active" value="1">

                                                <input type="text" name="name" class="form-control form-control-sm" style="max-width:150px;" value="{{ $item->name }}" required>
                                                <input type="number" step="0.01" name="price" class="form-control form-control-sm" style="max-width:100px;" value="{{ $item->price }}">
                                                <input type="number" name="sort_order" class="form-control form-control-sm" style="max-width:90px;" value="{{ $item->sort_order ?? 0 }}">

                                                <button type="submit" class="btn btn-sm btn-outline-secondary">حفظ</button>
                                            </form>

                                            <form action="{{ route('admin.products.options.items.destroy', [$product->id, $group->id, $item->id]) }}" method="POST" onsubmit="return confirm('حذف هذا العنصر؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-block d-md-none">
                    @foreach($group->items as $item)
                        <div class="option-item-mobile">
                            <div class="fw-bold mb-2">{{ $item->name }}</div>
                            <div class="small text-muted mb-1">السعر الإضافي</div>
                            <div class="mb-2">{{ number_format($item->price, 2) }} ج.م</div>
                            <div class="small text-muted mb-1">الترتيب</div>
                            <div>{{ $item->sort_order ?? 0 }}</div>

                            <div class="option-item-mobile-actions">
                                <form action="{{ route('admin.products.options.items.update', [$product->id, $group->id, $item->id]) }}" method="POST" class="row g-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="is_default" value="0">
                                    <input type="hidden" name="is_active" value="1">

                                    <div class="col-12">
                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $item->name }}" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" step="0.01" name="price" class="form-control form-control-sm" value="{{ $item->price }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $item->sort_order ?? 0 }}">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-outline-secondary w-100">حفظ</button>
                                    </div>
                                </form>

                                <form action="{{ route('admin.products.options.items.destroy', [$product->id, $group->id, $item->id]) }}" method="POST" onsubmit="return confirm('حذف هذا العنصر؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">حذف</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted py-3">لا توجد عناصر داخل هذه المجموعة حتى الآن.</div>
            @endif
        </div>
    @endforeach
@else
    <div class="admin-card p-5 text-center">
        <div class="section-title">لا توجد مجموعات خيارات</div>
        <div class="section-subtitle mb-0">ابدأ بإضافة مجموعة مثل الصوصات أو الأحجام لهذا المنتج.</div>
    </div>
@endif
@endsection