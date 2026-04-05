@extends('layouts.admin')

@php
    $pageTitle = 'التقارير';
    $pageSubtitle = 'متابعة المبيعات والطلبات والمنتجات والفروع من لوحة تحليل موحدة';
@endphp

@section('content')
<style>
    .reports-page{
        display:grid;
        gap:18px;
    }

    .reports-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .reports-card-head{
        padding:18px 18px 0;
    }

    .reports-card-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .reports-card-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
        line-height:1.7;
    }

    .reports-card-body{
        padding:18px;
    }

    .report-filter-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .report-filter-head{
        padding:18px 18px 0;
    }

    .report-filter-body{
        padding:18px;
    }

    .report-filter-summary{
        color:#8a847a;
        font-size:.8rem;
        font-weight:700;
        line-height:1.8;
    }

    .report-filter-actions{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin:14px 0 16px;
    }

    .report-form-grid{
        display:grid;
        grid-template-columns:repeat(12, minmax(0,1fr));
        gap:16px;
        align-items:end;
    }

    .field-col-12{ grid-column:span 12; }
    .field-col-4{ grid-column:span 4; }
    .field-col-3{ grid-column:span 3; }

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

    .report-form-actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .btn-report-primary{
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

    .btn-report-primary:hover{
        color:#fff;
        opacity:.97;
    }

    .btn-report-soft{
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

    .btn-report-soft:hover{
        background:#ebe4da;
        color:#302821;
    }

    .reports-stats-grid{
        display:grid;
        grid-template-columns:repeat(4, minmax(0,1fr));
        gap:18px;
    }

    .report-stat-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        padding:18px;
        height:100%;
    }

    .report-stat-title{
        font-size:.76rem;
        font-weight:800;
        color:#8a847a;
        margin-bottom:10px;
        letter-spacing:.02em;
    }

    .report-stat-value{
        font-size:1.8rem;
        font-weight:900;
        color:#231f1b;
        line-height:1.05;
        letter-spacing:-.03em;
        margin-bottom:6px;
    }

    .report-stat-note{
        color:#6f6a61;
        font-size:.78rem;
        font-weight:700;
        line-height:1.7;
    }

    .report-stat-card.primary .report-stat-value{ color:#5c6a4f; }
    .report-stat-card.info .report-stat-value{ color:#5d7a9a; }
    .report-stat-card.warn .report-stat-value{ color:#9d7a44; }
    .report-stat-card.danger .report-stat-value{ color:#9a5d63; }

    .report-summary-grid{
        display:grid;
        grid-template-columns:repeat(6, minmax(0,1fr));
        gap:12px;
    }

    .report-summary-box{
        border:1px solid #ebe3d7;
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border-radius:18px;
        padding:14px;
    }

    .report-summary-label{
        font-size:.73rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:8px;
    }

    .report-summary-value{
        font-size:1.1rem;
        font-weight:900;
        color:#231f1b;
        line-height:1.1;
    }

    .report-section{
        display:grid;
        gap:18px;
    }

    .report-desktop-table{
        display:block;
    }

    .report-mobile-list{
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

    .delay-badge{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 10px;
        border-radius:999px;
        background:#fff7e8;
        color:#b7791f;
        font-weight:800;
        font-size:.76rem;
        white-space:nowrap;
    }

    .report-mobile-card{
        background:#fffdfa;
        border:1px solid #ebe3d7;
        border-radius:20px;
        padding:16px;
        box-shadow:0 10px 22px rgba(35,31,27,.05);
    }

    .report-mobile-card + .report-mobile-card{
        margin-top:12px;
    }

    .report-mobile-title{
        font-size:.96rem;
        font-weight:900;
        color:#231f1b;
        margin-bottom:12px;
    }

    .report-mobile-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
    }

    .report-mobile-box{
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:14px;
        padding:10px 12px;
    }

    .report-mobile-label{
        font-size:.72rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:4px;
    }

    .report-mobile-value{
        font-size:.88rem;
        color:#443b33;
        font-weight:800;
        word-break:break-word;
        line-height:1.6;
    }

    .reports-empty{
        text-align:center;
        padding:32px 16px;
        font-size:.86rem;
        color:#8a847a;
        background:#faf6f1;
        border:1px dashed #e6ddd1;
        border-radius:16px;
        font-weight:700;
    }

    @media (max-width: 1199.98px){
        .reports-stats-grid{
            grid-template-columns:repeat(2, minmax(0,1fr));
        }

        .report-summary-grid{
            grid-template-columns:repeat(3, minmax(0,1fr));
        }
    }

    @media (max-width: 991.98px){
        .field-col-4,
        .field-col-3{
            grid-column:span 12;
        }
    }

    @media (max-width: 767.98px){
        .reports-stats-grid,
        .report-summary-grid{
            grid-template-columns:1fr;
        }

        .report-filter-card,
        .reports-card,
        .report-stat-card{
            border-radius:20px;
        }

        .report-filter-head,
        .report-filter-body,
        .reports-card-head,
        .reports-card-body{
            padding-left:14px;
            padding-right:14px;
        }

        .report-filter-actions,
        .report-form-actions{
            flex-direction:column;
        }

        .btn-report-primary,
        .btn-report-soft{
            width:100%;
        }

        .report-desktop-table{
            display:none;
        }

        .report-mobile-list{
            display:block;
        }

        .report-mobile-grid{
            grid-template-columns:1fr;
        }

        .report-stat-value{
            font-size:1.55rem;
        }
    }
</style>

<div class="reports-page">

    <section class="report-filter-card">
        <div class="report-filter-head">
            <h2 class="reports-card-title">فلترة التقارير</h2>
            <p class="reports-card-subtitle">
                @if(isset($selectedBranch) && $selectedBranch)
                    الفرع المحدد: <strong>{{ $selectedBranch->name }}</strong>
                @else
                    الفرع المحدد: <strong>جميع الفروع</strong>
                @endif
                — من <strong>{{ $fromDate }}</strong> إلى <strong>{{ $toDate }}</strong>
            </p>
        </div>

        <div class="report-filter-body">
            <div class="report-filter-actions">
                <a href="{{ route('admin.reports.export.excel', request()->query()) }}" class="btn-report-primary">
                    تصدير Excel
                </a>

                <a href="{{ route('admin.reports.export.pdf', request()->query()) }}" class="btn-report-soft">
                    تصدير PDF
                </a>
            </div>

            <form method="GET" action="{{ route('admin.reports.index') }}">
                <div class="report-form-grid">
                    @if(isset($branches) && $branches->count())
                        <div class="field-card field-col-3">
                            <label class="form-label">الفرع</label>
                            <select name="branch_id" class="form-select">
                                <option value="">كل الفروع</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="field-card field-col-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                    </div>

                    <div class="field-card field-col-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                    </div>

                    <div class="field-col-3">
                        <div class="report-form-actions">
                            <button type="submit" class="btn-report-primary">عرض النتائج</button>
                            <a href="{{ route('admin.reports.index') }}" class="btn-report-soft">إعادة التعيين</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="reports-stats-grid">
        <div class="report-stat-card primary">
            <div class="report-stat-title">إجمالي المبيعات</div>
            <div class="report-stat-value">{{ number_format($totalSales, 2) }} ج.م</div>
            <div class="report-stat-note">إجمالي المبيعات المحققة خلال الفترة المحددة.</div>
        </div>

        <div class="report-stat-card info">
            <div class="report-stat-title">مبيعات اليوم</div>
            <div class="report-stat-value">{{ number_format($todaySales, 2) }} ج.م</div>
            <div class="report-stat-note">مقارنة بأمس: {{ $salesDiff >= 0 ? '+' : '' }}{{ number_format($salesDiff, 2) }} ج.م</div>
        </div>

        <div class="report-stat-card warn">
            <div class="report-stat-title">إجمالي الطلبات</div>
            <div class="report-stat-value">{{ $ordersCount }}</div>
            <div class="report-stat-note">عدد الطلبات المسجلة خلال الفترة المحددة.</div>
        </div>

        <div class="report-stat-card danger">
            <div class="report-stat-title">توصيل / استلام</div>
            <div class="report-stat-value">{{ $deliveryOrders }} / {{ $pickupOrders }}</div>
            <div class="report-stat-note">توزيع الطلبات حسب نوع الخدمة خلال نفس الفترة.</div>
        </div>
    </section>

    <section class="reports-card">
        <div class="reports-card-head">
            <h2 class="reports-card-title">ملخص حالات الطلبات</h2>
            <p class="reports-card-subtitle">توزيع حالات الطلبات خلال الفترة المحددة لمتابعة سير التشغيل بشكل أوضح.</p>
        </div>

        <div class="reports-card-body">
            <div class="report-summary-grid">
                <div class="report-summary-box">
                    <div class="report-summary-label">قيد المراجعة</div>
                    <div class="report-summary-value">{{ $pendingOrders }}</div>
                </div>

                <div class="report-summary-box">
                    <div class="report-summary-label">تم التأكيد</div>
                    <div class="report-summary-value">{{ $confirmedOrders }}</div>
                </div>

                <div class="report-summary-box">
                    <div class="report-summary-label">قيد التحضير</div>
                    <div class="report-summary-value">{{ $preparingOrders }}</div>
                </div>

                <div class="report-summary-box">
                    <div class="report-summary-label">خرج للتوصيل</div>
                    <div class="report-summary-value">{{ $outForDeliveryOrders }}</div>
                </div>

                <div class="report-summary-box">
                    <div class="report-summary-label">تم التسليم</div>
                    <div class="report-summary-value">{{ $deliveredOrders }}</div>
                </div>

                <div class="report-summary-box">
                    <div class="report-summary-label">تم الإلغاء</div>
                    <div class="report-summary-value">{{ $cancelledOrders }}</div>
                </div>
            </div>
        </div>
    </section>

    @if(isset($branchesPerformance) && $branchesPerformance->count())
    <section class="reports-card">
        <div class="reports-card-head">
            <h2 class="reports-card-title">أداء الفروع</h2>
            <p class="reports-card-subtitle">ترتيب الفروع حسب الأداء والمبيعات خلال الفترة المحددة.</p>
        </div>

        <div class="reports-card-body">
            <div class="report-desktop-table">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>الفرع</th>
                                <th>عدد الطلبات</th>
                                <th>إجمالي المبيعات</th>
                                <th>قيد المراجعة</th>
                                <th>تم التسليم</th>
                                <th>تم الإلغاء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchesPerformance as $branch)
                                <tr>
                                    <td><strong>{{ $branch->name }}</strong></td>
                                    <td>{{ $branch->orders_count }}</td>
                                    <td>{{ number_format($branch->sales_total, 2) }} ج.م</td>
                                    <td>{{ $branch->pending_count }}</td>
                                    <td>{{ $branch->delivered_count }}</td>
                                    <td>{{ $branch->cancelled_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"><div class="reports-empty">لا توجد بيانات أداء متاحة للفروع في الفترة المحددة.</div></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="report-mobile-list mt-3">
                @forelse($branchesPerformance as $branch)
                    <div class="report-mobile-card">
                        <div class="report-mobile-title">{{ $branch->name }}</div>
                        <div class="report-mobile-grid">
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">عدد الطلبات</div>
                                <div class="report-mobile-value">{{ $branch->orders_count }}</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">إجمالي المبيعات</div>
                                <div class="report-mobile-value">{{ number_format($branch->sales_total, 2) }} ج.م</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">قيد المراجعة / تم التسليم / تم الإلغاء</div>
                                <div class="report-mobile-value">{{ $branch->pending_count }} / {{ $branch->delivered_count }} / {{ $branch->cancelled_count }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="reports-empty">لا توجد بيانات أداء متاحة للفروع في الفترة المحددة.</div>
                @endforelse
            </div>
        </div>
    </section>
    @endif

    <section class="reports-card">
        <div class="reports-card-head">
            <h2 class="reports-card-title">أكثر المنتجات مبيعًا</h2>
            <p class="reports-card-subtitle">المنتجات الأعلى طلبًا مرتبة حسب الكمية خلال الفترة المحددة.</p>
        </div>

        <div class="reports-card-body">
            <div class="report-desktop-table">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>إجمالي الكمية</th>
                                <th>إجمالي الإيراد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $product)
                                <tr>
                                    <td><strong>{{ $product->product_name }}</strong></td>
                                    <td>{{ $product->total_quantity }}</td>
                                    <td>{{ number_format($product->total_revenue, 2) }} ج.م</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3"><div class="reports-empty">لا توجد بيانات كافية للمنتجات خلال الفترة المحددة.</div></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="report-mobile-list mt-3">
                @forelse($topProducts as $product)
                    <div class="report-mobile-card">
                        <div class="report-mobile-title">{{ $product->product_name }}</div>
                        <div class="report-mobile-grid">
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">إجمالي الكمية</div>
                                <div class="report-mobile-value">{{ $product->total_quantity }}</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">إجمالي الإيراد</div>
                                <div class="report-mobile-value">{{ number_format($product->total_revenue, 2) }} ج.م</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="reports-empty">لا توجد بيانات كافية للمنتجات خلال الفترة المحددة.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="reports-card">
        <div class="reports-card-head">
            <h2 class="reports-card-title">الطلبات المتأخرة</h2>
            <p class="reports-card-subtitle">طلبات ما زالت مفتوحة وتجاوزت مدة المتابعة المتوقعة.</p>
        </div>

        <div class="reports-card-body">
            <div class="report-desktop-table">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>الفرع</th>
                                <th>الحالة</th>
                                <th>التأخير</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($delayedOrders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->branch?->name ?? '-' }}</td>
                                    <td>{{ $order->status }}</td>
                                    <td><span class="delay-badge">{{ $order->delay_minutes }} دقيقة</span></td>
                                    <td>{{ number_format($order->total, 2) }} ج.م</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"><div class="reports-empty">لا توجد طلبات متأخرة ضمن الفترة المحددة.</div></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="report-mobile-list mt-3">
                @forelse($delayedOrders as $order)
                    <div class="report-mobile-card">
                        <div class="report-mobile-title">{{ $order->order_number }}</div>
                        <div class="report-mobile-grid">
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">العميل</div>
                                <div class="report-mobile-value">{{ $order->customer_name }}</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">الفرع</div>
                                <div class="report-mobile-value">{{ $order->branch?->name ?? '-' }}</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">الحالة</div>
                                <div class="report-mobile-value">{{ $order->status }}</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">التأخير</div>
                                <div class="report-mobile-value"><span class="delay-badge">{{ $order->delay_minutes }} دقيقة</span></div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">الإجمالي</div>
                                <div class="report-mobile-value">{{ number_format($order->total, 2) }} ج.م</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="reports-empty">لا توجد طلبات متأخرة ضمن الفترة المحددة.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="reports-card">
        <div class="reports-card-head">
            <h2 class="reports-card-title">آخر الطلبات</h2>
            <p class="reports-card-subtitle">أحدث الطلبات المسجلة داخل الفترة المحددة.</p>
        </div>

        <div class="reports-card-body">
            <div class="report-desktop-table">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>الفرع</th>
                                <th>الحالة</th>
                                <th>الإجمالي</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestOrders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->branch?->name ?? '-' }}</td>
                                    <td>{{ $order->status }}</td>
                                    <td>{{ number_format($order->total, 2) }} ج.م</td>
                                    <td>{{ $order->created_at?->format('Y-m-d h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"><div class="reports-empty">لا توجد طلبات متاحة في الفترة المحددة.</div></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="report-mobile-list mt-3">
                @forelse($latestOrders as $order)
                    <div class="report-mobile-card">
                        <div class="report-mobile-title">{{ $order->order_number }}</div>
                        <div class="report-mobile-grid">
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">العميل</div>
                                <div class="report-mobile-value">{{ $order->customer_name }}</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">الفرع</div>
                                <div class="report-mobile-value">{{ $order->branch?->name ?? '-' }}</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">الحالة</div>
                                <div class="report-mobile-value">{{ $order->status }}</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">الإجمالي</div>
                                <div class="report-mobile-value">{{ number_format($order->total, 2) }} ج.م</div>
                            </div>
                            <div class="report-mobile-box">
                                <div class="report-mobile-label">التاريخ</div>
                                <div class="report-mobile-value">{{ $order->created_at?->format('Y-m-d h:i A') }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="reports-empty">لا توجد طلبات متاحة في الفترة المحددة.</div>
                @endforelse
            </div>
        </div>
    </section>

</div>
@endsection