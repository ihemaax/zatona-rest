@extends('layouts.admin')

@php
    $pageTitle = 'إضافة منتج';
    $pageSubtitle = 'إضافة منتج جديد للمنيو والطلبات';
@endphp

@section('content')
<div class="admin-card p-4">
    <div class="section-title">إضافة منتج جديد</div>
    <div class="section-subtitle">املأ البيانات الأساسية للمنتج ثم احفظه</div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
        @csrf

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">اسم المنتج</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">القسم</label>
            <select name="category_id" class="form-select" required>
                <option value="">اختر القسم</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">السعر</label>
            <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', 0) }}" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">الصورة</label>
            <input type="file" name="image" class="form-control">
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">الوصف</label>
            <textarea name="description" rows="5" class="form-control">{{ old('description') }}</textarea>
        </div>

        <div class="col-12">
            <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" name="is_available" value="1" {{ old('is_available', 1) ? 'checked' : '' }}>
                <label class="form-check-label">المنتج متاح للطلب</label>
            </div>
        </div>

        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
            <button type="submit" class="btn-admin">حفظ المنتج</button>
            <a href="{{ route('admin.products.index') }}" class="btn-admin-soft">الرجوع للمنتجات</a>
        </div>
    </form>
</div>
@endsection