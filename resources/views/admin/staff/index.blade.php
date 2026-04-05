@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الموظفين';
    $pageSubtitle = 'إدارة حسابات الموظفين وتحديد الأدوار والصلاحيات التشغيلية';
@endphp

@section('content')
<style>
    .staff-page{
        display:grid;
        gap:18px;
    }

    .staff-page-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        flex-wrap:wrap;
        gap:16px;
    }

    .staff-page-title{
        margin:0 0 6px;
        font-size:1.15rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .staff-page-subtitle{
        margin:0;
        color:var(--admin-text-faint);
        font-size:.82rem;
        font-weight:700;
        line-height:1.7;
        max-width:760px;
    }

    .btn-add-staff{
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
        white-space:nowrap;
    }

    .btn-add-staff:hover{
        color:#fff;
        opacity:.97;
    }

    .staff-main-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .staff-main-head{
        padding:18px 18px 0;
    }

    .staff-main-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .staff-main-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .staff-main-body{
        padding:18px;
        padding-top:14px;
    }

    .staff-desktop-table{
        display:block;
    }

    .staff-mobile-list{
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

    .staff-name{
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

    .status-inactive{
        background:#fff1f2;
        color:#be123c;
    }

    .staff-role-chip{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:5px 10px;
        border-radius:999px;
        font-size:.71rem;
        font-weight:800;
        white-space:nowrap;
        background:#eef2ff;
        color:#4f46e5;
    }

    .staff-actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
    }

    .btn-edit-staff{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:36px;
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

    .btn-edit-staff:hover{
        background:#ebe4da;
        color:#302821;
    }

    .btn-delete-staff{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:36px;
        padding:8px 12px;
        border-radius:12px;
        background:#fff1f2;
        border:1px solid #fecdd3;
        color:#be123c;
        font-size:.78rem;
        font-weight:800;
        transition:.18s ease;
    }

    .btn-delete-staff:hover{
        background:#ffe4e6;
        color:#9f1239;
    }

    .staff-mobile-card{
        background:#fffdfa;
        border:1px solid #ebe3d7;
        border-radius:20px;
        padding:16px;
        box-shadow:0 10px 22px rgba(35,31,27,.05);
    }

    .staff-mobile-card + .staff-mobile-card{
        margin-top:12px;
    }

    .staff-mobile-name{
        font-size:.98rem;
        font-weight:900;
        color:#231f1b;
        margin-bottom:12px;
    }

    .staff-mobile-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
        margin-bottom:14px;
    }

    .staff-mobile-box{
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:14px;
        padding:10px 12px;
    }

    .staff-mobile-label{
        font-size:.72rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:4px;
    }

    .staff-mobile-value{
        font-size:.88rem;
        color:#443b33;
        font-weight:800;
        word-break:break-word;
        line-height:1.6;
    }

    .staff-mobile-status{
        margin-bottom:14px;
    }

    .staff-mobile-actions{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
    }

    .staff-mobile-actions a,
    .staff-mobile-actions form{
        flex:1 1 140px;
    }

    .staff-mobile-actions .btn-delete-staff,
    .staff-mobile-actions .btn-edit-staff{
        width:100%;
    }

    .staff-empty{
        text-align:center;
        padding:32px 16px;
        font-size:.86rem;
        color:#8a847a;
        background:#faf6f1;
        border:1px dashed #e6ddd1;
        border-radius:16px;
        font-weight:700;
    }

    .pagination-wrap{
        margin-top:18px;
    }

    .text-muted{
        color:#8a847a !important;
    }

    @media (max-width: 767.98px){
        .staff-main-card{
            border-radius:20px;
        }

        .staff-main-head,
        .staff-main-body{
            padding-left:14px;
            padding-right:14px;
        }

        .staff-desktop-table{
            display:none;
        }

        .staff-mobile-list{
            display:block;
        }

        .staff-mobile-grid{
            grid-template-columns:1fr;
        }

        .staff-page-title{
            font-size:1rem;
        }

        .btn-add-staff{
            width:100%;
        }
    }
</style>

<div class="staff-page">
    <div class="staff-page-head">
        <div>
            <h1 class="staff-page-title">إدارة الموظفين</h1>
            <p class="staff-page-subtitle">إدارة حسابات الموظفين داخل النظام، وتحديد الأدوار الوظيفية، وربط الحسابات بالفروع، والتحكم في صلاحيات الوصول بصورة منظمة واحترافية.</p>
        </div>

        <a href="{{ route('admin.staff.create') }}" class="btn-add-staff">إضافة موظف جديد</a>
    </div>

    <section class="staff-main-card">
        <div class="staff-main-head">
            <h2 class="staff-main-title">قائمة الموظفين</h2>
            <p class="staff-main-subtitle">استعرض بيانات الموظفين الحالية، وادخل إلى صفحة التعديل لإدارة البيانات والصلاحيات أو حذف الحساب عند الحاجة.</p>
        </div>

        <div class="staff-main-body">
            <div class="staff-desktop-table">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الدور الوظيفي</th>
                                <th>الفرع</th>
                                <th>الحالة</th>
                                <th style="min-width:220px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staff as $member)
                                <tr>
                                    <td><span class="staff-name">{{ $member->name }}</span></td>
                                    <td>{{ $member->email }}</td>
                                    <td>
                                        <span class="staff-role-chip">{{ $roles[$member->role] ?? $member->role }}</span>
                                    </td>
                                    <td>{{ $member->branch?->name ?? 'غير مرتبط بفرع' }}</td>
                                    <td>
                                        @if($member->is_active)
                                            <span class="ops-status status-active">نشط</span>
                                        @else
                                            <span class="ops-status status-inactive">موقوف</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="staff-actions">
                                            <a href="{{ route('admin.staff.edit', $member->id) }}" class="btn-edit-staff">تعديل البيانات</a>

                                            <form action="{{ route('admin.staff.destroy', $member->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الموظف؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete-staff">حذف</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="staff-empty">لا توجد حسابات موظفين مضافة حتى الآن.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="staff-mobile-list">
                @forelse($staff as $member)
                    <div class="staff-mobile-card">
                        <div class="staff-mobile-name">{{ $member->name }}</div>

                        <div class="staff-mobile-grid">
                            <div class="staff-mobile-box">
                                <div class="staff-mobile-label">البريد الإلكتروني</div>
                                <div class="staff-mobile-value">{{ $member->email }}</div>
                            </div>

                            <div class="staff-mobile-box">
                                <div class="staff-mobile-label">الدور الوظيفي</div>
                                <div class="staff-mobile-value">{{ $roles[$member->role] ?? $member->role }}</div>
                            </div>

                            <div class="staff-mobile-box">
                                <div class="staff-mobile-label">الفرع</div>
                                <div class="staff-mobile-value">{{ $member->branch?->name ?? 'غير مرتبط بفرع' }}</div>
                            </div>
                        </div>

                        <div class="staff-mobile-status">
                            @if($member->is_active)
                                <span class="ops-status status-active">نشط</span>
                            @else
                                <span class="ops-status status-inactive">موقوف</span>
                            @endif
                        </div>

                        <div class="staff-mobile-actions">
                            <a href="{{ route('admin.staff.edit', $member->id) }}" class="btn-edit-staff">تعديل البيانات</a>

                            <form action="{{ route('admin.staff.destroy', $member->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الموظف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete-staff">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="staff-empty">لا توجد حسابات موظفين مضافة حتى الآن.</div>
                @endforelse
            </div>

            <div class="pagination-wrap">
                {{ $staff->links() }}
            </div>
        </div>
    </section>
</div>
@endsection