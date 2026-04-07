@extends('layouts.admin')

@php
    $pageTitle = 'إضافة موظف';
    $pageSubtitle = 'إنشاء حساب جديد وتحديد الدور، والصلاحيات تُضبط تلقائياً حسب الوظيفة';
    $rolePermissionsMap = collect(\App\Models\User::availableRoles())
        ->keys()
        ->mapWithKeys(fn ($role) => [$role => \App\Models\User::defaultPermissionsByRole($role)])
        ->all();
    $permissionLabels = \App\Models\User::permissionLabels();
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
                <option value="{{ \App\Models\User::ROLE_MANAGER }}" {{ old('role') === \App\Models\User::ROLE_MANAGER ? 'selected' : '' }}>مدير</option>
                <option value="{{ \App\Models\User::ROLE_BRANCH_STAFF }}" {{ old('role') === \App\Models\User::ROLE_BRANCH_STAFF ? 'selected' : '' }}>موظف فرع</option>
                <option value="{{ \App\Models\User::ROLE_CASHIER }}" {{ old('role') === \App\Models\User::ROLE_CASHIER ? 'selected' : '' }}>كاشير</option>
                <option value="{{ \App\Models\User::ROLE_KITCHEN }}" {{ old('role') === \App\Models\User::ROLE_KITCHEN ? 'selected' : '' }}>مطبخ</option>
                <option value="{{ \App\Models\User::ROLE_DELIVERY }}" {{ old('role') === \App\Models\User::ROLE_DELIVERY ? 'selected' : '' }}>دليفري</option>
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
            <label class="form-label fw-bold">الصلاحيات (تلقائي)</label>
            <div class="border rounded-3 p-3 bg-white">
                <div class="small text-muted mb-2">
                    الصلاحيات بتتحدد تلقائيًا حسب الدور الوظيفي، ومش محتاجة تعديل يدوي.
                </div>
                <ul class="mb-0 ps-3" id="auto-permissions-list"></ul>
            </div>
        </div>

        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
            <button type="submit" class="btn-admin">حفظ الموظف</button>
            <a href="{{ route('admin.staff.index') }}" class="btn-admin-soft">الرجوع</a>
        </div>
    </form>
</div>

<script nonce="{{ $cspNonce }}">
    (() => {
        const roleSelect = document.querySelector('select[name="role"]');
        const list = document.getElementById('auto-permissions-list');
        if (!roleSelect || !list) return;

        const permissionLabels = @json($permissionLabels);
        const rolePermissionsMap = @json($rolePermissionsMap);

        const renderPermissions = () => {
            const selectedRole = roleSelect.value;
            const permissions = rolePermissionsMap[selectedRole] || [];

            if (!permissions.length) {
                list.innerHTML = '<li class="text-muted">لا توجد صلاحيات لهذا الدور.</li>';
                return;
            }

            list.innerHTML = permissions
                .map((permission) => `<li>${permissionLabels[permission] || permission}</li>`)
                .join('');
        };

        roleSelect.addEventListener('change', renderPermissions);
        renderPermissions();
    })();
</script>
@endsection
