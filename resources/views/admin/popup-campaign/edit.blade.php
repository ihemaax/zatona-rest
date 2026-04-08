@extends('layouts.admin')

@php
    $pageTitle = 'الإعلان المنبثق';
    $pageSubtitle = 'التحكم في النافذة التي تظهر للعميل عند فتح الموقع';
@endphp

@section('content')
<div class="admin-card p-4">
    <form method="POST" action="{{ route('admin.popup-campaign.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-bold">تفعيل الإعلان</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $popup->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label">مفعل</label>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">عنوان الإعلان</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $popup->title) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">نص الزر</label>
                <input type="text" name="button_text" class="form-control" value="{{ old('button_text', $popup->button_text) }}">
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">وصف الإعلان</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $popup->description) }}</textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">رابط الزر</label>
                <input type="text" name="button_url" class="form-control" value="{{ old('button_url', $popup->button_url) }}" placeholder="/offers أو رابط كامل">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">صورة الإعلان</label>
                <input type="file" name="image" class="form-control">
                @if($popup->image)
                    <div class="mt-2">
                        <img src="{{ \App\Support\MediaUrl::fromPath($popup->image) }}" alt="popup" style="max-width:180px; border-radius:12px;">
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">يبدأ من</label>
                <input type="datetime-local" name="starts_at" class="form-control"
                    value="{{ old('starts_at', $popup->starts_at ? $popup->starts_at->format('Y-m-d\TH:i') : '') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">ينتهي في</label>
                <input type="datetime-local" name="ends_at" class="form-control"
                    value="{{ old('ends_at', $popup->ends_at ? $popup->ends_at->format('Y-m-d\TH:i') : '') }}">
            </div>

            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="show_once_per_user" value="1" {{ old('show_once_per_user', $popup->show_once_per_user ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label">إظهار مرة واحدة فقط لكل زائر</label>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn-admin">حفظ التعديلات</button>
            </div>
        </div>
    </form>
</div>
@endsection
