@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الفروع';
    $pageSubtitle = 'إدارة بيانات الفروع ومعلومات التواصل بصورة واضحة ومنظمة على مختلف الأجهزة';
@endphp

@section('content')
<style>
    .branches-page{
        display:grid;
        gap:18px;
    }

    .branches-page-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        flex-wrap:wrap;
        gap:16px;
    }

    .branches-page-title{
        margin:0 0 6px;
        font-size:1.15rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .branches-page-subtitle{
        margin:0;
        color:var(--admin-text-faint);
        font-size:.82rem;
        font-weight:700;
        line-height:1.7;
        max-width:760px;
    }

    .btn-add-branch{
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

    .btn-add-branch:hover{
        color:#fff;
        opacity:.97;
    }

    .branches-main-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .branches-main-head{
        padding:18px 18px 0;
    }

    .branches-main-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .branches-main-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .branches-main-body{
        padding:18px;
        padding-top:14px;
    }

    .branches-desktop-table{
        display:block;
    }

    .branches-mobile-list{
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

    .branch-id{
        color:#8a847a;
        font-weight:800;
    }

    .branch-name{
        font-weight:900;
        color:#231f1b;
    }

    .branch-actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
    }

    .btn-edit-branch{
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

    .btn-edit-branch:hover{
        background:#ebe4da;
        color:#302821;
    }

    .btn-delete-branch{
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

    .btn-delete-branch:hover{
        background:#ffe4e6;
        color:#9f1239;
    }

    .branch-mobile-card{
        background:#fffdfa;
        border:1px solid #ebe3d7;
        border-radius:20px;
        padding:16px;
        box-shadow:0 10px 22px rgba(35,31,27,.05);
    }

    .branch-mobile-card + .branch-mobile-card{
        margin-top:12px;
    }

    .branch-mobile-title{
        font-size:.98rem;
        font-weight:900;
        color:#231f1b;
        margin-bottom:12px;
    }

    .branch-mobile-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
        margin-bottom:14px;
    }

    .branch-mobile-box{
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:14px;
        padding:10px 12px;
    }

    .branch-mobile-label{
        font-size:.72rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:4px;
    }

    .branch-mobile-value{
        font-size:.88rem;
        color:#443b33;
        font-weight:800;
        word-break:break-word;
        line-height:1.6;
    }

    .branch-mobile-actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:10px;
    }

    .branch-mobile-actions a,
    .branch-mobile-actions form{
        flex:1 1 120px;
    }

    .branch-mobile-actions form button{
        width:100%;
    }

    .branches-empty{
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
        .branches-main-card{
            border-radius:20px;
        }

        .branches-main-head,
        .branches-main-body{
            padding-left:14px;
            padding-right:14px;
        }

        .branches-desktop-table{
            display:none;
        }

        .branches-mobile-list{
            display:block;
        }

        .branch-mobile-grid{
            grid-template-columns:1fr;
        }

        .branches-page-title{
            font-size:1rem;
        }

        .btn-add-branch{
            width:100%;
        }
    }
</style>

<div class="branches-page">
    <div class="branches-page-head">
        <div>
            <h1 class="branches-page-title">إدارة الفروع</h1>
            <p class="branches-page-subtitle">من خلال هذه الصفحة يمكنك متابعة بيانات الفروع، وتحديث معلومات التواصل والعناوين، والوصول السريع إلى إجراءات التعديل أو الحذف بطريقة منظمة وسهلة الاستخدام.</p>
        </div>

        <a href="{{ route('admin.branches.create') }}" class="btn-add-branch">إضافة فرع جديد</a>
    </div>

    <section class="branches-main-card">
        <div class="branches-main-head">
            <h2 class="branches-main-title">قائمة الفروع</h2>
            <p class="branches-main-subtitle">استعرض جميع الفروع الحالية داخل النظام، وادخل إلى صفحة التعديل لتحديث البيانات أو حذف الفرع عند الحاجة.</p>
        </div>

        <div class="branches-main-body">
            <div class="branches-desktop-table">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الفرع</th>
                                <th>رقم الهاتف</th>
                                <th>العنوان</th>
                                <th style="min-width:220px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $branch)
                                <tr>
                                    <td><span class="branch-id">#{{ $branch->id }}</span></td>
                                    <td><span class="branch-name">{{ $branch->name }}</span></td>
                                    <td>{{ $branch->phone ?? 'غير متوفر' }}</td>
                                    <td>{{ $branch->address ?? 'غير متوفر' }}</td>
                                    <td>
                                        <div class="branch-actions">
                                            <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn-edit-branch">تعديل البيانات</a>
                                            <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الفرع؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete-branch">حذف</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="branches-empty">لا توجد فروع مضافة داخل النظام حتى الآن.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="branches-mobile-list">
                @forelse($branches as $branch)
                    <div class="branch-mobile-card">
                        <div class="branch-mobile-title">{{ $branch->name }}</div>

                        <div class="branch-mobile-grid">
                            <div class="branch-mobile-box">
                                <div class="branch-mobile-label">رقم الفرع</div>
                                <div class="branch-mobile-value">#{{ $branch->id }}</div>
                            </div>

                            <div class="branch-mobile-box">
                                <div class="branch-mobile-label">رقم الهاتف</div>
                                <div class="branch-mobile-value">{{ $branch->phone ?? 'غير متوفر' }}</div>
                            </div>

                            <div class="branch-mobile-box">
                                <div class="branch-mobile-label">العنوان</div>
                                <div class="branch-mobile-value">{{ $branch->address ?? 'غير متوفر' }}</div>
                            </div>
                        </div>

                        <div class="branch-mobile-actions">
                            <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn-edit-branch">تعديل البيانات</a>
                            <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الفرع؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete-branch">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="branches-empty">لا توجد فروع مضافة داخل النظام حتى الآن.</div>
                @endforelse
            </div>

            @if($branches instanceof \Illuminate\Pagination\AbstractPaginator)
                <div class="pagination-wrap">
                    {{ $branches->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
@endsection