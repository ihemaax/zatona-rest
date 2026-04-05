@extends('layouts.admin')

@php
    $pageTitle = 'تعديل بيانات الموظف';
    $pageSubtitle = $staff->name;
    $currentPermissions = old('permissions', $staff->permissions ?? []);
@endphp

@section('content')
<style>
    .staff-edit-page{
        display:grid;
        gap:18px;
    }

    .staff-edit-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .staff-edit-head{
        padding:18px 18px 0;
    }

    .staff-edit-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .staff-edit-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .staff-edit-body{
        padding:18px;
    }

    .staff-form-grid{
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

    .permissions-wrap{
        display:grid;
        grid-template-columns:repeat(2, minmax(0,1fr));
        gap:12px;
    }

    .permission-card{
        background:#fffdfa;
        border:1px solid #ebe3d7;
        border-radius:16px;
        padding:14px;
        display:flex;
        align-items:center;
        gap:10px;
        min-width:0;
        transition:.18s ease;
    }

    .permission-card:hover{
        background:#fcf8f3;
        border-color:#e1d6c8;
    }

    .permission-label{
        color:#443b33;
        font-size:.84rem;
        font-weight:800;
        line-height:1.6;
    }

    .section-block-title{
        margin:0 0 6px;
        font-size:.95rem;
        font-weight:900;
        color:#231f1b;
    }

    .section-block-subtitle{
        margin:0 0 14px;
        color:#8a847a;
        font-size:.78rem;
        font-weight:700;
        line-height:1.7;
    }

    .staff-actions{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-top:18px;
    }

    .btn-save-staff{
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

    .btn-save-staff:hover{
        color:#fff;
        opacity:.97;
    }

    .btn-back-staff{
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

    .btn-back-staff:hover{
        background:#ebe4da;
        color:#302821;
    }

    @media (max-width: 991.98px){
        .field-col-6{
            grid-column:span 12;
        }

        .permissions-wrap{
            grid-template-columns:1fr;
        }
    }

    @media (max-width: 767.98px){
        .staff-edit-card{
            border-radius:20px;
        }

        .staff-edit-head,
        .staff-edit-body{
            padding-left:14px;
            padding-right:14px;
        }

        .staff-actions{
            flex-direction:column;
        }

        .btn-save-staff,
        .btn-back-staff{
            width:100%;
        }
    }
</style>

<div class="staff-edit-page">
    <section class="staff-edit-card">
        <div class="staff-edit-head">
            <h2 class="staff-edit-title">تعديل بيانات الموظف</h2>
            <p class="staff-edit-subtitle">قم بتحديث البيانات الأساسية، تحديد الدور الوظيفي، ربط الموظف بالفرع المناسب، وإدارة الصلاحيات التشغيلية بصورة واضحة ومنظمة.</p>
        </div>

        <div class="staff-edit-body">
            <form action="{{ route('admin.staff.update', $staff->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="staff-form-grid">
                    <div class="field-card field-col-6">
                        <label class="form-label">الاسم الكامل</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $staff->name) }}"
                            placeholder="أدخل اسم الموظف"
                            required
                        >
                        <div class="field-hint">يظهر هذا الاسم داخل لوحة التحكم وفي بيانات الحساب الداخلية.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email', $staff->email) }}"
                            placeholder="example@email.com"
                            required
                        >
                        <div class="field-hint">يُستخدم البريد الإلكتروني في تسجيل الدخول واستقبال الإشعارات حسب إعدادات النظام.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="اترك هذا الحقل فارغاً إذا لم تكن هناك حاجة للتغيير"
                        >
                        <div class="field-hint">لن يتم تعديل كلمة المرور الحالية إلا إذا تم إدخال كلمة مرور جديدة.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">الدور الوظيفي</label>
                        <select name="role" class="form-select" required>
                            <option value="{{ \App\Models\User::ROLE_MANAGER }}" {{ old('role', $staff->role) === \App\Models\User::ROLE_MANAGER ? 'selected' : '' }}>مدير</option>
                            <option value="{{ \App\Models\User::ROLE_BRANCH_STAFF }}" {{ old('role', $staff->role) === \App\Models\User::ROLE_BRANCH_STAFF ? 'selected' : '' }}>موظف فرع</option>
                            <option value="{{ \App\Models\User::ROLE_CASHIER }}" {{ old('role', $staff->role) === \App\Models\User::ROLE_CASHIER ? 'selected' : '' }}>كاشير</option>
                            <option value="{{ \App\Models\User::ROLE_KITCHEN }}" {{ old('role', $staff->role) === \App\Models\User::ROLE_KITCHEN ? 'selected' : '' }}>مطبخ</option>
                            <option value="{{ \App\Models\User::ROLE_DELIVERY }}" {{ old('role', $staff->role) === \App\Models\User::ROLE_DELIVERY ? 'selected' : '' }}>دليفري</option>
                        </select>
                        <div class="field-hint">يحدد الدور مستوى الوصول والمهام المتاحة داخل لوحة الإدارة.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <label class="form-label">الفرع المرتبط</label>
                        <select name="branch_id" class="form-select">
                            <option value="">بدون فرع محدد</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) old('branch_id', $staff->branch_id) === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="field-hint">يمكن ربط الموظف بفرع محدد لتسهيل توزيع المهام ومتابعة العمليات التشغيلية.</div>
                    </div>

                    <div class="field-card field-col-6">
                        <div class="toggle-card">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                id="staff_is_active"
                                {{ old('is_active', $staff->is_active) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="staff_is_active">الحساب مفعل وجاهز للاستخدام</label>
                        </div>
                        <div class="field-hint">عند إلغاء التفعيل، يتم إيقاف استخدام الحساب دون حذف بياناته من النظام.</div>
                    </div>

                    <div class="field-card field-col-12">
                        <h3 class="section-block-title">الصلاحيات</h3>
                        <p class="section-block-subtitle">حدد الصلاحيات التي يمكن للموظف الوصول إليها بما يتوافق مع مسؤولياته داخل النظام.</p>

                        <div class="permissions-wrap">
                            @foreach($permissionLabels as $key => $label)
                                <label class="permission-card" for="perm_{{ $key }}">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $key }}"
                                        id="perm_{{ $key }}"
                                        {{ in_array($key, $currentPermissions, true) ? 'checked' : '' }}
                                    >
                                    <span class="permission-label">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="staff-actions">
                    <button type="submit" class="btn-save-staff">حفظ التعديلات</button>
                    <a href="{{ route('admin.staff.index') }}" class="btn-back-staff">الرجوع إلى قائمة الموظفين</a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
