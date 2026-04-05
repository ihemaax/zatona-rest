@extends('layouts.admin')

@php
    $pageTitle = 'إضافة موظف';
    $pageSubtitle = 'إنشاء حساب جديد وتحديد الدور والصلاحيات';
@endphp

@section('content')
<div class="admin-card p-4">
    <form action="{{ route('admin.staff.store') }}" method="POST" class="row g-3">
        @csrf

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">كلمة المرور</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">الدور</label>
            <select name="role" class="form-select" required>
                @foreach($roles as $key => $label)
                    @if($key !== \App\Models\User::ROLE_SUPER_ADMIN)
                        <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">الفرع</label>
            <select name="branch_id" class="form-select">
                <option value="">بدون فرع</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-check mt-4 pt-2">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                <label class="form-check-label">الحساب نشط</label>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">الصلاحيات</label>
            <div class="row g-2">
                @foreach($permissionLabels as $key => $label)
                    <div class="col-12 col-md-6">
                        <div class="form-check border rounded-3 p-3 bg-white">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}" id="perm_{{ $key }}"
                                {{ in_array($key, old('permissions', []), true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="perm_{{ $key }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
            <button type="submit" class="btn-admin">حفظ الموظف</button>
            <a href="{{ route('admin.staff.index') }}" class="btn-admin-soft">الرجوع</a>
        </div>
    </form>
</div>
@endsection