@extends('layouts.admin')

@php
    $pageTitle = $pageTitle ?? 'عرض تجريبي للنظام';
    $pageSubtitle = $pageSubtitle ?? 'كل الشاشات هنا للعرض فقط وبدون بيانات';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start">
                    <div>
                        <h4 class="mb-1 fw-bold">{{ $demoSectionTitle }}</h4>
                        <div class="text-muted">المسار: <code>/demo/admin/{{ $demoSectionPath }}</code></div>
                        <p class="mb-0 mt-2 text-muted">هذه شاشة ديمو للمعاينة قبل الشراء. لا يمكن تنفيذ أي عمليات فعلية.</p>
                    </div>
                    <span class="badge text-bg-warning px-3 py-2" style="font-size:.85rem;">Demo Mode</span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-body">
                        <div class="text-muted small">إجمالي العناصر</div>
                        <div class="fs-3 fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-body">
                        <div class="text-muted small">العناصر النشطة</div>
                        <div class="fs-3 fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-body">
                        <div class="text-muted small">الإجراءات اليوم</div>
                        <div class="fs-3 fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-body">
                        <div class="text-muted small">التنبيهات</div>
                        <div class="fs-3 fw-bold">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>العنصر</th>
                                <th>الحالة</th>
                                <th>آخر تحديث</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    لا توجد بيانات فعلية في نسخة الديمو.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
