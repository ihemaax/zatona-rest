@extends('layouts.admin')

@php
    $pageTitle = 'لوحة التحكم';
    $pageSubtitle = 'متابعة الطلبات والمبيعات والتشغيل اليومي بشكل واضح ومنظم';
@endphp

@section('content')
<style>
    .ops-dashboard{
        display:grid;
        gap:18px;
    }

    .ops-strip{
        background: linear-gradient(180deg, #fffdfa 0%, #faf6f1 100%);
        border: 1px solid #e7dfd3;
        border-radius: 24px;
        box-shadow: 0 18px 40px rgba(35,31,27,.06);
        overflow: hidden;
    }

    .ops-strip-grid{
        display:grid;
        grid-template-columns: repeat(4, minmax(0,1fr));
    }

    .ops-strip-item{
        padding:20px 18px;
        position:relative;
        min-width:0;
    }

    .ops-strip-item + .ops-strip-item{
        border-inline-start:1px solid #ece4d8;
    }

    .ops-strip-label{
        font-size:.76rem;
        font-weight:800;
        color:#8a847a;
        margin-bottom:10px;
        letter-spacing:.02em;
    }

    .ops-strip-value{
        font-size:1.8rem;
        font-weight:900;
        color:#231f1b;
        line-height:1.05;
        letter-spacing:-.03em;
        margin-bottom:6px;
    }

    .ops-strip-note{
        font-size:.78rem;
        color:#6f6a61;
        font-weight:700;
    }

    .ops-strip-item.primary .ops-strip-value{ color:#5c6a4f; }
    .ops-strip-item.info .ops-strip-value{ color:#5d7a9a; }
    .ops-strip-item.warn .ops-strip-value{ color:#9d7a44; }
    .ops-strip-item.success .ops-strip-value{ color:#4f7458; }

    .ops-grid{
        display:grid;
        grid-template-columns: 1.15fr .85fr;
        gap:18px;
        align-items:start;
    }

    .ops-card{
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 24px;
        box-shadow: var(--shadow-sm);
        overflow:hidden;
    }

    .ops-card-head{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
        padding:18px 18px 0;
    }

    .ops-card-title{
        margin:0;
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .ops-card-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
    }

    .ops-card-body{
        padding:18px;
    }

    .ops-hero{
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 26%),
            linear-gradient(135deg, #6f7f5f 0%, #8d9d7c 100%);
        color:#fff;
        border:none;
        box-shadow: 0 20px 42px rgba(111,127,95,.18);
    }

    .ops-hero .ops-card-title,
    .ops-hero .ops-card-subtitle{
        color:#fff;
    }

    .ops-hero .ops-card-subtitle{
        opacity:.85;
    }

    .ops-focus-grid{
        display:grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap:12px;
        margin-top:8px;
    }

    .ops-focus-box{
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 18px;
        padding:14px;
        backdrop-filter: blur(8px);
    }

    .ops-focus-label{
        font-size:.72rem;
        font-weight:800;
        color:rgba(255,255,255,.82);
        margin-bottom:8px;
    }

    .ops-focus-value{
        font-size:1.2rem;
        font-weight:900;
        color:#fff;
        line-height:1.1;
    }

    .ops-focus-note{
        margin-top:6px;
        font-size:.74rem;
        color:rgba(255,255,255,.78);
        font-weight:700;
    }

    .ops-actions{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
    }

    .ops-action{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:42px;
        padding:10px 14px;
        border-radius:999px;
        background:#f3eee7;
        border:1px solid #e3d9cc;
        color:#443b33;
        font-size:.82rem;
        font-weight:800;
        text-decoration:none;
        transition:.18s ease;
    }

    .ops-action:hover{
        background:var(--admin-primary);
        border-color:var(--admin-primary);
        color:#fff;
    }

    .ops-split{
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap:12px;
    }

    .ops-stat-box{
        border:1px solid #ebe3d7;
        background:linear-gradient(180deg, #fffdfa 0%, #f8f4ee 100%);
        border-radius:18px;
        padding:14px;
    }

    .ops-stat-box .lbl{
        font-size:.73rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:8px;
    }

    .ops-stat-box .val{
        font-size:1.12rem;
        font-weight:900;
        color:#231f1b;
        line-height:1.1;
    }

    .ops-stat-box.delivery .val{ color:#5d7a9a; }
    .ops-stat-box.pickup .val{ color:#4f7458; }
    .ops-stat-box.attention .val{ color:#9d7a44; }
    .ops-stat-box.today .val{ color:#6f7f5f; }

    .ops-branches{
        display:grid;
        gap:10px;
    }

    .ops-branch{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        border:1px solid #ece4d8;
        background:#fffdfa;
        border-radius:16px;
        padding:12px 14px;
    }

    .ops-branch-name{
        font-size:.88rem;
        font-weight:800;
        color:#231f1b;
        margin-bottom:4px;
    }

    .ops-branch-address{
        font-size:.75rem;
        color:#8a847a;
        font-weight:700;
        line-height:1.6;
    }

    .ops-branch-count{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:72px;
        padding:7px 12px;
        border-radius:999px;
        background:#eef2e8;
        color:#5c6a4f;
        font-size:.76rem;
        font-weight:900;
        white-space:nowrap;
    }

    .ops-notifications{
        display:grid;
        gap:10px;
        max-height:430px;
        overflow:auto;
        padding-right:2px;
        scrollbar-width:thin;
        scrollbar-color:#d4cabd transparent;
    }

    .ops-notifications::-webkit-scrollbar{ width:5px; }
    .ops-notifications::-webkit-scrollbar-thumb{
        background:#d4cabd;
        border-radius:999px;
    }

    .ops-note-item{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:12px;
        border:1px solid #ece4d8;
        background:#fffdfa;
        border-radius:16px;
        padding:12px 14px;
        transition:.18s ease;
    }

    .ops-note-item:hover{
        transform:translateY(-1px);
        box-shadow:0 10px 22px rgba(35,31,27,.05);
    }

    .ops-note-left{
        display:flex;
        gap:10px;
        min-width:0;
    }

    .ops-note-dot{
        width:10px;
        height:10px;
        border-radius:50%;
        background:#6f7f5f;
        box-shadow:0 0 0 5px rgba(111,127,95,.10);
        flex-shrink:0;
        margin-top:5px;
    }

    .ops-note-title{
        font-size:.86rem;
        font-weight:800;
        color:#231f1b;
        margin-bottom:5px;
    }

    .ops-note-meta{
        font-size:.75rem;
        color:#8a847a;
        line-height:1.7;
        font-weight:700;
    }

    .ops-empty{
        text-align:center;
        padding:32px 16px;
        font-size:.86rem;
        color:#8a847a;
        background:#faf6f1;
        border:1px dashed #e6ddd1;
        border-radius:16px;
        font-weight:700;
    }

    .ops-mini-btn{
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
        white-space:nowrap;
    }

    .ops-mini-btn:hover{
        background:#ebe4da;
        color:#302821;
    }

    .ops-table-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .ops-table-head{
        padding:18px 18px 0;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:12px;
    }

    .ops-table-body{
        padding:18px;
        padding-top:14px;
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

    .ops-order-id{
        display:flex;
        align-items:center;
        gap:8px;
        font-weight:800;
        color:#231f1b;
    }

    .ops-new-dot{
        display:inline-block;
        width:8px;
        height:8px;
        border-radius:50%;
        background:#6f7f5f;
        box-shadow:0 0 0 4px rgba(111,127,95,.10);
        flex-shrink:0;
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

    .status-pending{ background:#fff7e8; color:#b7791f; }
    .status-confirmed{ background:#edf4ff; color:#2563eb; }
    .status-preparing{ background:#f5efff; color:#7c3aed; }
    .status-delivery{ background:#ecfeff; color:#0f766e; }
    .status-delivered{ background:#ecfdf3; color:#15803d; }
    .status-cancelled{ background:#fff1f2; color:#be123c; }

    .ops-type{
        display:inline-flex;
        align-items:center;
        padding:5px 10px;
        border-radius:999px;
        font-size:.71rem;
        font-weight:800;
        white-space:nowrap;
    }

    .ops-type-delivery{ background:#eef2ff; color:#4f46e5; }
    .ops-type-pickup{ background:#ecfdf3; color:#15803d; }

    @media (max-width: 1199.98px){
        .ops-strip-grid{
            grid-template-columns: repeat(2, minmax(0,1fr));
        }

        .ops-strip-item:nth-child(3),
        .ops-strip-item:nth-child(4){
            border-top:1px solid #ece4d8;
        }

        .ops-strip-item:nth-child(3){
            border-inline-start:none;
        }

        .ops-grid{
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px){
        .ops-dashboard{
            gap:14px;
        }

        .ops-strip{
            border-radius:18px;
        }

        .ops-strip-grid{
            grid-template-columns:1fr;
        }

        .ops-strip-item{
            padding:16px 14px;
        }

        .ops-strip-item + .ops-strip-item{
            border-inline-start:none;
            border-top:1px solid #ece4d8;
        }

        .ops-strip-value{
            font-size:1.45rem;
        }

        .ops-card,
        .ops-table-card{
            border-radius:20px;
        }

        .ops-card-head,
        .ops-card-body,
        .ops-table-head,
        .ops-table-body{
            padding-left:14px;
            padding-right:14px;
        }

        .ops-focus-grid{
            grid-template-columns:1fr;
        }

        .ops-split{
            grid-template-columns:1fr;
        }

        .ops-note-item{
            flex-direction:column;
            align-items:stretch;
        }

        .ops-note-item .ops-mini-btn{
            width:100%;
        }

        .ops-table-wrap{
            overflow:auto;
            -webkit-overflow-scrolling:touch;
        }

        .ops-table{
            min-width:720px;
        }
    }
</style>

<div class="ops-dashboard">

    {{-- شريط المؤشرات الرئيسي --}}
    <section class="ops-strip">
        <div class="ops-strip-grid">
            <div class="ops-strip-item primary">
                <div class="ops-strip-label">إجمالي الطلبات</div>
                <div class="ops-strip-value" id="ordersCountCard">{{ $ordersCount }}</div>
                <div class="ops-strip-note">كل الطلبات المسجلة في النظام</div>
            </div>

            <div class="ops-strip-item info">
                <div class="ops-strip-label">طلبات اليوم</div>
                <div class="ops-strip-value" id="todayOrdersCard">{{ $todayOrders }}</div>
                <div class="ops-strip-note">الطلبات التي أُنشئت اليوم</div>
            </div>

            <div class="ops-strip-item warn">
                <div class="ops-strip-label">طلبات تحتاج متابعة</div>
                <div class="ops-strip-value" id="needAttentionCard">{{ $newOrders + $pendingOrders }}</div>
                <div class="ops-strip-note">جديدة أو ما زالت معلقة</div>
            </div>

            <div class="ops-strip-item success">
                <div class="ops-strip-label">مبيعات اليوم</div>
                <div class="ops-strip-value" id="todaySalesCard">{{ number_format($todaySales,2) }} ج.م</div>
                <div class="ops-strip-note">إجمالي المبيعات المحققة اليوم</div>
            </div>
        </div>
    </section>

    <section class="ops-grid">
        <div class="ops-card ops-hero">
            <div class="ops-card-head">
                <div>
                    <h2 class="ops-card-title">ملخص التشغيل اليومي</h2>
                    <p class="ops-card-subtitle">نظرة سريعة تساعدك على متابعة الأداء واتخاذ القرار بسرعة</p>
                </div>

                <div class="ops-actions">
                    <a href="{{ route('admin.orders.index') }}" class="ops-action">جميع الطلبات</a>
                    <a href="{{ route('admin.orders.delivery') }}" class="ops-action">طلبات التوصيل</a>
                    <a href="{{ route('admin.orders.pickup') }}" class="ops-action">طلبات الاستلام</a>
                    <a href="{{ route('admin.branches.index') }}" class="ops-action">الفروع</a>
                </div>
            </div>

            <div class="ops-card-body">
                <div class="ops-focus-grid">
                    <div class="ops-focus-box">
                        <div class="ops-focus-label">طلبات جديدة</div>
                        <div class="ops-focus-value" id="newOrdersCount">{{ $newOrders }}</div>
                        <div class="ops-focus-note">تحتاج مراجعة مباشرة</div>
                    </div>

                    <div class="ops-focus-box">
                        <div class="ops-focus-label">طلبات معلقة</div>
                        <div class="ops-focus-value" id="pendingOrdersCard">{{ $pendingOrders }}</div>
                        <div class="ops-focus-note">بانتظار تأكيد أو تحديث</div>
                    </div>

                    <div class="ops-focus-box">
                        <div class="ops-focus-label">عدد الفروع</div>
                        <div class="ops-focus-value" id="branchesCountCard">{{ $branchesStats->count() }}</div>
                        <div class="ops-focus-note">الفروع المتاحة داخل النظام</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ops-card">
            <div class="ops-card-head">
                <div>
                    <h2 class="ops-card-title">أداء التشغيل</h2>
                    <p class="ops-card-subtitle">ملخص حسب نوع الطلب وحالة المتابعة</p>
                </div>
            </div>

            <div class="ops-card-body">
                <div class="ops-split">
                    <div class="ops-stat-box delivery">
                        <div class="lbl">طلبات التوصيل</div>
                        <div class="val" id="deliveryOrdersCard">{{ $deliveryOrders }}</div>
                    </div>

                    <div class="ops-stat-box pickup">
                        <div class="lbl">طلبات الاستلام</div>
                        <div class="val" id="pickupOrdersCard">{{ $pickupOrders }}</div>
                    </div>

                    <div class="ops-stat-box delivery">
                        <div class="lbl">مبيعات التوصيل</div>
                        <div class="val" id="deliverySalesCard">{{ number_format($deliverySales,2) }} ج.م</div>
                    </div>

                    <div class="ops-stat-box pickup">
                        <div class="lbl">مبيعات الاستلام</div>
                        <div class="val" id="pickupSalesCard">{{ number_format($pickupSales,2) }} ج.م</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ops-grid">
        <div class="ops-card">
            <div class="ops-card-head">
                <div>
                    <h2 class="ops-card-title">مركز الإشعارات</h2>
                    <p class="ops-card-subtitle">أحدث الطلبات الجديدة التي تحتاج إلى متابعة</p>
                </div>

                <a href="{{ route('admin.orders.index') }}" class="ops-mini-btn">
                    عرض جميع الطلبات
                </a>
            </div>

            <div class="ops-card-body">
                <div id="newOrdersNotifications" class="ops-notifications">
                    @forelse($latestOrders->where('is_seen_by_admin', false)->take(6) as $order)
                        <div class="ops-note-item">
                            <div class="ops-note-left">
                                <span class="ops-note-dot"></span>
                                <div>
                                    <div class="ops-note-title">{{ $order->order_number }} — {{ $order->customer_name }}</div>
                                    <div class="ops-note-meta">
                                        {{ $order->order_type === 'delivery' ? 'طلب توصيل' : 'طلب استلام' }}
                                        @if($order->branch) — {{ $order->branch->name }} @endif
                                        — {{ $order->created_at->format('h:i A') }}
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('admin.orders.show', $order->id) }}" class="ops-mini-btn">فتح الطلب</a>
                        </div>
                    @empty
                        <div class="ops-empty" id="newOrdersEmptyState">لا توجد إشعارات جديدة في الوقت الحالي</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="ops-card">
            <div class="ops-card-head">
                <div>
                    <h2 class="ops-card-title">ملخص الفروع</h2>
                    <p class="ops-card-subtitle">متابعة سريعة لعدد الطلبات المسجلة لكل فرع</p>
                </div>

                <a href="{{ route('admin.branches.index') }}" class="ops-mini-btn">إدارة الفروع</a>
            </div>

            <div class="ops-card-body">
                <div id="branchesSummaryBox" class="ops-branches">
                    @forelse($branchesStats as $branch)
                        <div class="ops-branch">
                            <div>
                                <div class="ops-branch-name">{{ $branch->name }}</div>
                                <div class="ops-branch-address">{{ $branch->address }}</div>
                            </div>

                            <span class="ops-branch-count">{{ $branch->orders_count }} طلب</span>
                        </div>
                    @empty
                        <div class="ops-empty">لا توجد فروع مضافة حالياً</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="ops-grid">
        <div class="ops-table-card">
            <div class="ops-table-head">
                <div>
                    <h2 class="ops-card-title">أحدث طلبات التوصيل</h2>
                    <p class="ops-card-subtitle">آخر طلبات التوصيل المسجلة في النظام</p>
                </div>

                <a href="{{ route('admin.orders.delivery') }}" class="ops-mini-btn">عرض الكل</a>
            </div>

            <div class="ops-table-body">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>الحالة</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="deliveryLatestTableBody">
                            @forelse($deliveryLatest as $order)
                                <tr>
                                    <td>
                                        <div class="ops-order-id">
                                            @if(!$order->is_seen_by_admin)
                                                <span class="ops-new-dot"></span>
                                            @endif
                                            <span>{{ $order->order_number }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>
                                        @php
                                            $sc = match($order->status){
                                                'pending'=>'ops-status status-pending',
                                                'confirmed'=>'ops-status status-confirmed',
                                                'preparing'=>'ops-status status-preparing',
                                                'out_for_delivery'=>'ops-status status-delivery',
                                                'delivered'=>'ops-status status-delivered',
                                                default=>'ops-status status-cancelled'
                                            };
                                        @endphp
                                        <span class="{{ $sc }}">{{ $order->status }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show',$order->id) }}" class="ops-mini-btn">عرض</a>
                                    </td>
                                </tr>
                            @empty
                                <tr id="deliveryLatestEmptyRow">
                                    <td colspan="4" class="text-center text-muted py-4" style="font-size:.84rem;">لا توجد طلبات توصيل حالياً</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ops-table-card">
            <div class="ops-table-head">
                <div>
                    <h2 class="ops-card-title">أحدث طلبات الاستلام</h2>
                    <p class="ops-card-subtitle">آخر طلبات الاستلام من الفروع</p>
                </div>

                <a href="{{ route('admin.orders.pickup') }}" class="ops-mini-btn">عرض الكل</a>
            </div>

            <div class="ops-table-body">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>الفرع</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="pickupLatestTableBody">
                            @forelse($pickupLatest as $order)
                                <tr>
                                    <td>
                                        <div class="ops-order-id">
                                            @if(!$order->is_seen_by_admin)
                                                <span class="ops-new-dot"></span>
                                            @endif
                                            <span>{{ $order->order_number }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->branch?->name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show',$order->id) }}" class="ops-mini-btn">عرض</a>
                                    </td>
                                </tr>
                            @empty
                                <tr id="pickupLatestEmptyRow">
                                    <td colspan="4" class="text-center text-muted py-4" style="font-size:.84rem;">لا توجد طلبات استلام حالياً</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section class="ops-table-card">
        <div class="ops-table-head">
            <div>
                <h2 class="ops-card-title">آخر الطلبات</h2>
                <p class="ops-card-subtitle">عرض مباشر لآخر الطلبات مع النوع والحالة والإجمالي</p>
            </div>

            <a href="{{ route('admin.orders.index') }}" class="btn-admin">كل الطلبات</a>
        </div>

        <div class="ops-table-body">
            <div class="ops-table-wrap">
                <table class="ops-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>النوع</th>
                            <th>الفرع</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="latestOrdersTableBody">
                        @forelse($latestOrders as $order)
                            <tr>
                                <td>
                                    <div class="ops-order-id">
                                        @if(!$order->is_seen_by_admin)
                                            <span class="ops-new-dot"></span>
                                        @endif
                                        <span>{{ $order->order_number }}</span>
                                    </div>
                                </td>
                                <td>{{ $order->customer_name }}</td>
                                <td>
                                    @if($order->order_type === 'delivery')
                                        <span class="ops-type ops-type-delivery">توصيل</span>
                                    @else
                                        <span class="ops-type ops-type-pickup">استلام</span>
                                    @endif
                                </td>
                                <td>{{ $order->branch?->name ?? '-' }}</td>
                                <td style="font-weight:800;">{{ number_format($order->total,2) }} ج.م</td>
                                <td>
                                    @php
                                        $sc = match($order->status){
                                            'pending'=>'ops-status status-pending',
                                            'confirmed'=>'ops-status status-confirmed',
                                            'preparing'=>'ops-status status-preparing',
                                            'out_for_delivery'=>'ops-status status-delivery',
                                            'delivered'=>'ops-status status-delivered',
                                            default=>'ops-status status-cancelled'
                                        };
                                    @endphp
                                    <span class="{{ $sc }}">{{ $order->status }}</span>
                                </td>
                                <td style="color:#8a847a;font-size:.78rem;">{{ $order->created_at->format('Y-m-d h:i A') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show',$order->id) }}" class="ops-mini-btn">تفاصيل</a>
                                </td>
                            </tr>
                        @empty
                            <tr id="latestOrdersEmptyRow">
                                <td colspan="8" class="text-center text-muted py-4" style="font-size:.84rem;">لا توجد طلبات مسجلة حالياً</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = id => document.getElementById(id);

    const els = {
        ordersCount:    $('ordersCountCard'),
        todayOrders:    $('todayOrdersCard'),
        pendingOrders:  $('pendingOrdersCard'),
        newOrders:      $('newOrdersCount'),
        deliveryOrders: $('deliveryOrdersCard'),
        pickupOrders:   $('pickupOrdersCard'),
        todaySales:     $('todaySalesCard'),
        branchesCount:  $('branchesCountCard'),
        deliverySales:  $('deliverySalesCard'),
        pickupSales:    $('pickupSalesCard'),
        needAttention:  $('needAttentionCard'),
        badge:          $('newOrdersBadge'),
        sidebarBadge:   $('sidebarNewOrdersCount'),
        notifs:         $('newOrdersNotifications'),
        latestBody:     $('latestOrdersTableBody'),
        deliveryBody:   $('deliveryLatestTableBody'),
        pickupBody:     $('pickupLatestTableBody'),
        branches:       $('branchesSummaryBox'),
    };

    let lastCount = parseInt(els.newOrders?.textContent || '0', 10);
    let fetching  = false;

    const esc = s => String(s ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;');
    const money = v => `${Number(v).toFixed(2)} ج.م`;
    const dot   = seen => seen ? '' : '<span class="ops-new-dot"></span>';

    const statusChip = s => {
        const m = {
            pending:'ops-status status-pending',
            confirmed:'ops-status status-confirmed',
            preparing:'ops-status status-preparing',
            out_for_delivery:'ops-status status-delivery',
            delivered:'ops-status status-delivered'
        };
        return `<span class="${m[s] ?? 'ops-status status-cancelled'}">${esc(s)}</span>`;
    };

    const typeChip = t => t === 'delivery'
        ? '<span class="ops-type ops-type-delivery">توصيل</span>'
        : '<span class="ops-type ops-type-pickup">استلام</span>';

    const playSound = () => { try { new Audio('/sounds/new-order.mp3').play(); } catch(e){} };

    const renderNotifs = items => {
        if (!els.notifs) return;
        if (!items.length) {
            els.notifs.innerHTML = '<div class="ops-empty" id="newOrdersEmptyState">لا توجد إشعارات جديدة في الوقت الحالي</div>';
            return;
        }

        els.notifs.innerHTML = items.map(o => `
            <div class="ops-note-item">
                <div class="ops-note-left">
                    <span class="ops-note-dot"></span>
                    <div>
                        <div class="ops-note-title">${esc(o.order_number)} — ${esc(o.customer_name)}</div>
                        <div class="ops-note-meta">
                            ${o.order_type === 'delivery' ? 'طلب توصيل' : 'طلب استلام'}
                            ${o.branch_name ? ' — ' + esc(o.branch_name) : ''}
                            — ${esc(o.created_at)}
                        </div>
                    </div>
                </div>
                <a href="${esc(o.show_url)}" class="ops-mini-btn">فتح الطلب</a>
            </div>
        `).join('');
    };

    const renderLatest = items => {
        if (!els.latestBody) return;
        if (!items.length) {
            els.latestBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">لا توجد طلبات مسجلة حالياً</td></tr>';
            return;
        }

        els.latestBody.innerHTML = items.map(o => `
            <tr>
                <td><div class="ops-order-id">${dot(o.is_seen_by_admin)}<span>${esc(o.order_number)}</span></div></td>
                <td>${esc(o.customer_name)}</td>
                <td>${typeChip(o.order_type)}</td>
                <td>${o.branch_name ? esc(o.branch_name) : '-'}</td>
                <td style="font-weight:800">${money(o.total)}</td>
                <td>${statusChip(o.status)}</td>
                <td style="color:#8a847a;font-size:.78rem">${esc(o.created_at)}</td>
                <td><a href="${esc(o.show_url)}" class="ops-mini-btn">تفاصيل</a></td>
            </tr>
        `).join('');
    };

    const renderDelivery = items => {
        if (!els.deliveryBody) return;
        if (!items.length) {
            els.deliveryBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">لا توجد طلبات توصيل حالياً</td></tr>';
            return;
        }

        els.deliveryBody.innerHTML = items.map(o => `
            <tr>
                <td><div class="ops-order-id">${dot(o.is_seen_by_admin)}<span>${esc(o.order_number)}</span></div></td>
                <td>${esc(o.customer_name)}</td>
                <td>${statusChip(o.status)}</td>
                <td><a href="${esc(o.show_url)}" class="ops-mini-btn">عرض</a></td>
            </tr>
        `).join('');
    };

    const renderPickup = items => {
        if (!els.pickupBody) return;
        if (!items.length) {
            els.pickupBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">لا توجد طلبات استلام حالياً</td></tr>';
            return;
        }

        els.pickupBody.innerHTML = items.map(o => `
            <tr>
                <td><div class="ops-order-id">${dot(o.is_seen_by_admin)}<span>${esc(o.order_number)}</span></div></td>
                <td>${esc(o.customer_name)}</td>
                <td>${o.branch_name ? esc(o.branch_name) : '-'}</td>
                <td><a href="${esc(o.show_url)}" class="ops-mini-btn">عرض</a></td>
            </tr>
        `).join('');
    };

    const renderBranches = items => {
        if (!els.branches) return;
        if (!items.length) {
            els.branches.innerHTML = '<div class="ops-empty">لا توجد فروع مضافة حالياً</div>';
            return;
        }

        els.branches.innerHTML = items.map(b => `
            <div class="ops-branch">
                <div>
                    <div class="ops-branch-name">${esc(b.name)}</div>
                    <div class="ops-branch-address">${esc(b.address ?? '')}</div>
                </div>
                <span class="ops-branch-count">${b.orders_count} طلب</span>
            </div>
        `).join('');
    };

    async function poll() {
        if (fetching) return;
        fetching = true;

        try {
            const res = await fetch("{{ secure_url('/admin/dashboard/poll') }}", {
                headers: {
                    'X-Requested-With':'XMLHttpRequest',
                    'Accept':'application/json'
                },
                cache:'no-store'
            });

            if (!res.ok) return;

            const data = await res.json();
            const c = data.cards || {};
            const cur = parseInt(data.new_orders_count || 0, 10);

            if (cur > lastCount) playSound();
            lastCount = cur;

            const set = (el, v) => { if (el) el.textContent = v; };

            set(els.ordersCount, c.orders_count ?? 0);
            set(els.todayOrders, c.today_orders ?? 0);
            set(els.pendingOrders, c.pending_orders ?? 0);
            set(els.newOrders, c.new_orders ?? 0);
            set(els.deliveryOrders, c.delivery_orders ?? 0);
            set(els.pickupOrders, c.pickup_orders ?? 0);
            set(els.todaySales, money(c.today_sales ?? 0));
            set(els.branchesCount, c.branches_count ?? 0);
            set(els.deliverySales, money(c.delivery_sales ?? 0));
            set(els.pickupSales, money(c.pickup_sales ?? 0));
            set(els.needAttention, +(c.new_orders ?? 0) + +(c.pending_orders ?? 0));
            set(els.sidebarBadge, cur);

            renderNotifs(data.notifications || []);
            renderLatest(data.latest_orders || []);
            renderDelivery(data.delivery_latest || []);
            renderPickup(data.pickup_latest || []);
            renderBranches(data.branches_stats || []);
        } catch (e) {
            console.log('Poll error:', e);
        } finally {
            fetching = false;
        }
    }

    setInterval(poll, 5000);
});
</script>
@endpush