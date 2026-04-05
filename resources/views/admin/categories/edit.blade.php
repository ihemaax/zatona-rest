@extends('layouts.admin')

@php
    $pageTitle = 'تعديل القسم';
    $pageSubtitle = 'تحديث بيانات القسم الحالي';
@endphp

@section('content')
<div class="admin-card p-4">
    <div class="section-title">تعديل بيانات القسم</div>
    <div class="section-subtitle">يمكنك تعديل اسم القسم والحالة من هنا</div>

    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">اسم القسم</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">الحالة</label>
                <select name="is_active" class="form-select" required>
                    <option value="1" @selected(old('is_active', $category->is_active) == 1)>نشط</option>
                    <option value="0" @selected(old('is_active', $category->is_active) == 0)>موقوف</option>
                </select>
            </div>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <button class="btn-admin">حفظ التعديل</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-admin-soft">رجوع</a>
        </div>
    </form>
</div>
@endsection