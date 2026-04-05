@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الأقسام';
    $pageSubtitle = 'تنظيم الأقسام بشكل أسهل على كل الشاشات';
@endphp

@section('content')
<style>
    .categories-desktop-table{
        display:block;
    }

    .categories-mobile-list{
        display:none;
    }

    .category-mobile-card{
        background:#fff;
        border:1px solid #dbe5f0;
        border-radius:20px;
        padding:16px;
        box-shadow:0 8px 18px rgba(15,23,42,.05);
    }

    .category-mobile-card + .category-mobile-card{
        margin-top:12px;
    }

    .category-mobile-title{
        font-size:1rem;
        font-weight:900;
        color:#0f172a;
        margin-bottom:10px;
    }

    .category-mobile-box{
        background:#f8fbff;
        border:1px solid #e7eef7;
        border-radius:14px;
        padding:10px 12px;
        margin-bottom:10px;
    }

    .category-mobile-label{
        font-size:.76rem;
        color:#64748b;
        font-weight:700;
        margin-bottom:4px;
    }

    .category-mobile-value{
        font-size:.9rem;
        color:#0f172a;
        font-weight:800;
        word-break:break-word;
    }

    @media (max-width: 767.98px){
        .categories-desktop-table{
            display:none;
        }

        .categories-mobile-list{
            display:block;
        }
    }
</style>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="admin-card p-4 h-100">
            <div class="section-title">إضافة قسم جديد</div>
            <div class="section-subtitle">أنشئ قسمًا جديدًا للمنتجات</div>

            <form action="{{ route('admin.categories.store') }}" method="POST" class="row g-3">
                @csrf

                <div class="col-12">
                    <label class="form-label fw-bold">اسم القسم</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">الوصف</label>
                    <textarea name="description" rows="4" class="form-control"></textarea>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                        <label class="form-check-label">نشط</label>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn-admin">إضافة القسم</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="admin-card p-4">
            <div class="section-title">القائمة الحالية</div>
            <div class="section-subtitle">عرض وتعديل الأقسام الموجودة</div>

            <div class="categories-desktop-table admin-table-wrap">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>القسم</th>
                            <th>الوصف</th>
                            <th>الحالة</th>
                            <th style="min-width:260px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td class="fw-bold">{{ $category->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($category->description, 80) ?: '-' }}</td>
                                <td>
                                    @if($category->is_active)
                                        <span class="order-type-chip order-type-pickup">نشط</span>
                                    @else
                                        <span class="status-chip status-cancelled">مخفي</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="d-flex flex-wrap gap-2">
                                        @csrf
                                        @method('PUT')

                                        <input type="text" name="name" class="form-control form-control-sm" style="max-width:140px;" value="{{ $category->name }}" required>
                                        <input type="text" name="description" class="form-control form-control-sm" style="max-width:180px;" value="{{ $category->description }}">
                                        <select name="is_active" class="form-select form-select-sm" style="max-width:100px;">
                                            <option value="1" {{ $category->is_active ? 'selected' : '' }}>نشط</option>
                                            <option value="0" {{ !$category->is_active ? 'selected' : '' }}>مخفي</option>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-outline-secondary">حفظ</button>
                                    </form>

                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="mt-2" onsubmit="return confirm('هل أنت متأكد من حذف القسم؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">لا توجد أقسام حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="categories-mobile-list">
                @forelse($categories as $category)
                    <div class="category-mobile-card">
                        <div class="category-mobile-title">{{ $category->name }}</div>

                        <div class="category-mobile-box">
                            <div class="category-mobile-label">الوصف</div>
                            <div class="category-mobile-value">{{ $category->description ?: '-' }}</div>
                        </div>

                        <div class="mb-3">
                            @if($category->is_active)
                                <span class="order-type-chip order-type-pickup">نشط</span>
                            @else
                                <span class="status-chip status-cancelled">مخفي</span>
                            @endif
                        </div>

                        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="row g-2">
                            @csrf
                            @method('PUT')

                            <div class="col-12">
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $category->name }}" required>
                            </div>

                            <div class="col-12">
                                <input type="text" name="description" class="form-control form-control-sm" value="{{ $category->description }}">
                            </div>

                            <div class="col-12">
                                <select name="is_active" class="form-select form-select-sm">
                                    <option value="1" {{ $category->is_active ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ !$category->is_active ? 'selected' : '' }}>مخفي</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-secondary w-100">حفظ</button>
                            </div>
                        </form>

                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="mt-2" onsubmit="return confirm('هل أنت متأكد من حذف القسم؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">حذف</button>
                        </form>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">لا توجد أقسام حتى الآن.</div>
                @endforelse
            </div>

            @if($categories instanceof \Illuminate\Pagination\AbstractPaginator)
                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection