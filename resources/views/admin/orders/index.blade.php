@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الطلبات';
    $pageSubtitle = 'لوحة تشغيل متكاملة لمتابعة طلبات التوصيل والاستلام بكفاءة ووضوح';

    $currentType = $pageType ?? 'all';
    $ordersCollection = $orders instanceof \Illuminate\Pagination\AbstractPaginator ? $orders->getCollection() : collect($orders);

    $ordersCount = $ordersCollection->count();
    $newOrdersCount = $ordersCollection->where('is_seen_by_admin', false)->count();
    $pendingOrdersCount = $ordersCollection->where('status', 'pending')->count();

    $statusLabels = [
        'pending' => 'قيد المراجعة',
        'confirmed' => 'تم التأكيد',
        'preparing' => 'قيد التحضير',
        'out_for_delivery' => 'خرج للتوصيل',
        'ready_for_pickup' => 'جاهز للاستلام',
        'delivered' => 'تم التسليم',
        'cancelled' => 'تم الإلغاء',
    ];
@endphp

@section('content')
<style>
    .orders-page{
        display:grid;
        gap:18px;
    }

    .orders-page-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        flex-wrap:wrap;
        gap:16px;
    }

    .orders-page-title{
        font-size:1.15rem;
        font-weight:900;
        color:var(--admin-text);
        margin:0 0 6px;
        letter-spacing:-.01em;
    }

    .orders-page-subtitle{
        margin:0;
        color:var(--admin-text-faint);
        font-size:.82rem;
        font-weight:700;
        max-width:760px;
        line-height:1.7;
    }

    .filter-pills{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
    }

    .filter-pill{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:42px;
        padding:10px 16px;
        border-radius:999px;
        background:#f3eee7;
        border:1px solid #e3d9cc;
        color:#443b33;
        font-weight:800;
        font-size:.82rem;
        text-decoration:none;
        transition:.18s ease;
        white-space:nowrap;
    }

    .filter-pill:hover{
        background:#ebe4da;
        color:#302821;
    }

    .filter-pill.active{
        background:linear-gradient(135deg,#6f7f5f 0%, #8d9d7c 100%);
        border-color:#6f7f5f;
        color:#fff;
        box-shadow:0 12px 22px rgba(111,127,95,.16);
    }

    .orders-stats-grid{
        display:grid;
        grid-template-columns:repeat(3, minmax(0,1fr));
        gap:18px;
    }

    .orders-stat-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        padding:18px;
    }

    .orders-stat-title{
        font-size:.76rem;
        font-weight:800;
        color:#8a847a;
        margin-bottom:10px;
        letter-spacing:.02em;
    }

    .orders-stat-value{
        font-size:1.8rem;
        font-weight:900;
        color:#231f1b;
        line-height:1.05;
        letter-spacing:-.03em;
        margin-bottom:6px;
    }

    .orders-stat-note{
        color:#6f6a61;
        font-size:.78rem;
        font-weight:700;
        line-height:1.7;
    }

    .orders-stat-card.primary .orders-stat-value{ color:#5c6a4f; }
    .orders-stat-card.info .orders-stat-value{ color:#5d7a9a; }
    .orders-stat-card.warn .orders-stat-value{ color:#9d7a44; }

    .orders-main-card{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .orders-main-head{
        padding:18px 18px 0;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:12px;
    }

    .orders-section-title{
        font-size:1rem;
        font-weight:900;
        color:var(--admin-text);
        margin:0;
        letter-spacing:-.01em;
    }

    .orders-section-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
    }

    .orders-main-body{
        padding:18px;
        padding-top:14px;
    }

    .orders-desktop-table{
        display:block;
    }

    .orders-mobile-list{
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

    .ops-table tbody tr.row-unseen td{
        background:#fbf7f1;
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

    .order-type-chip{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:5px 10px;
        border-radius:999px;
        font-size:.71rem;
        font-weight:800;
        white-space:nowrap;
    }

    .order-type-delivery{ background:#eef2ff; color:#4f46e5; }
    .order-type-pickup{ background:#ecfdf3; color:#15803d; }

    .btn-order-details{
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
        transition:.18s ease;
    }

    .btn-order-details:hover{
        background:#ebe4da;
        color:#302821;
    }

    .orders-mobile-list{
        display:none;
    }

    .order-mobile-card{
        background:#fffdfa;
        border:1px solid #ebe3d7;
        border-radius:20px;
        padding:16px;
        box-shadow:0 10px 22px rgba(35,31,27,.05);
    }

    .order-mobile-card + .order-mobile-card{
        margin-top:12px;
    }

    .order-mobile-top{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:10px;
        margin-bottom:14px;
    }

    .order-mobile-number-wrap{
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
    }

    .order-mobile-number{
        font-size:.98rem;
        font-weight:900;
        color:#231f1b;
    }

    .order-mobile-date{
        font-size:.78rem;
        color:#8a847a;
        margin-top:4px;
        font-weight:700;
    }

    .order-mobile-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
        margin-bottom:14px;
    }

    .order-mobile-box{
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:14px;
        padding:10px 12px;
    }

    .order-mobile-label{
        font-size:.72rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:4px;
    }

    .order-mobile-value{
        font-size:.88rem;
        color:#443b33;
        font-weight:800;
        word-break:break-word;
        line-height:1.6;
    }

    .order-mobile-actions{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
    }

    .order-mobile-actions .btn-order-details{
        flex:1 1 140px;
    }

    .orders-empty{
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

    @media (max-width: 991.98px){
        .orders-stats-grid{
            grid-template-columns:1fr;
        }
    }

    @media (max-width: 767.98px){
        .orders-page{
            gap:14px;
        }

        .orders-page-title{
            font-size:1rem;
        }

        .orders-main-card,
        .orders-stat-card{
            border-radius:20px;
        }

        .orders-main-head,
        .orders-main-body{
            padding-left:14px;
            padding-right:14px;
        }

        .orders-desktop-table{
            display:none;
        }

        .orders-mobile-list{
            display:block;
        }

        .order-mobile-grid{
            grid-template-columns:1fr;
        }

        .filter-pills{
            width:100%;
        }

        .filter-pill{
            flex:1 1 calc(50% - 10px);
        }
    }
</style>

<div class="orders-page">
    <div class="orders-page-head">
        <div>
            <h1 class="orders-page-title">إدارة الطلبات</h1>
            <p class="orders-page-subtitle">لوحة متابعة مركزية تتيح لك مراجعة الطلبات، متابعة حالتها، والوصول السريع إلى تفاصيل التنفيذ من مختلف الأجهزة بسهولة ووضوح.</p>
        </div>

        <div class="filter-pills">
            <a href="{{ route('admin.orders.index') }}" class="filter-pill {{ $currentType === 'all' ? 'active' : '' }}">جميع الطلبات</a>
            <a href="{{ route('admin.orders.delivery') }}" class="filter-pill {{ $currentType === 'delivery' ? 'active' : '' }}">طلبات التوصيل</a>
            <a href="{{ route('admin.orders.pickup') }}" class="filter-pill {{ $currentType === 'pickup' ? 'active' : '' }}">طلبات الاستلام</a>
        </div>
    </div>

    <section class="orders-stats-grid">
        <div class="orders-stat-card primary">
            <div class="orders-stat-title">إجمالي الطلبات المعروضة</div>
            <div class="orders-stat-value" id="ordersCurrentPageCount">{{ $ordersCount }}</div>
            <div class="orders-stat-note">إجمالي عدد الطلبات الموجودة داخل هذا القسم حالياً.</div>
        </div>

        <div class="orders-stat-card info">
            <div class="orders-stat-title">طلبات جديدة</div>
            <div class="orders-stat-value" id="ordersNewCount">{{ $newOrdersCount }}</div>
            <div class="orders-stat-note">طلبات لم يتم الاطلاع عليها بعد وتحتاج إلى متابعة أولية.</div>
        </div>

        <div class="orders-stat-card warn">
            <div class="orders-stat-title">طلبات قيد المراجعة</div>
            <div class="orders-stat-value" id="ordersPendingCount">{{ $pendingOrdersCount }}</div>
            <div class="orders-stat-note">طلبات ما زالت بانتظار الإجراء المناسب أو تحديث الحالة التشغيلية.</div>
        </div>
    </section>

    <section class="orders-main-card">
        <div class="orders-main-head">
            <div>
                <h2 class="orders-section-title">قائمة الطلبات</h2>
                <p class="orders-section-subtitle">استعرض الطلبات الحالية وافتح أي طلب للاطلاع على بياناته الكاملة وتحديث حالته بشكل مباشر.</p>
            </div>
        </div>

        <div class="orders-main-body">
            <div class="orders-desktop-table">
                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>اسم العميل</th>
                                <th>رقم الهاتف</th>
                                <th>نوع الطلب</th>
                                <th>الفرع</th>
                                <th>الإجمالي</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="ordersDesktopTableBody">
                            @forelse($orders as $order)
                                @php
                                    $statusClass = match($order->status) {
                                        'pending' => 'ops-status status-pending',
                                        'confirmed' => 'ops-status status-confirmed',
                                        'preparing' => 'ops-status status-preparing',
                                        'out_for_delivery' => 'ops-status status-delivery',
                                        'ready_for_pickup' => 'ops-status status-confirmed',
                                        'delivered' => 'ops-status status-delivered',
                                        default => 'ops-status status-cancelled',
                                    };

                                    $statusLabel = $statusLabels[$order->status] ?? 'تم الإلغاء';
                                @endphp
                                <tr class="{{ !$order->is_seen_by_admin ? 'row-unseen' : '' }}">
                                    <td>
                                        <div class="ops-order-id">
                                            @if(!$order->is_seen_by_admin)
                                                <span class="ops-new-dot"></span>
                                            @endif
                                            <strong>{{ $order->order_number }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->customer_phone }}</td>
                                    <td>
                                        @if($order->order_type === 'delivery')
                                            <span class="order-type-chip order-type-delivery">توصيل</span>
                                        @else
                                            <span class="order-type-chip order-type-pickup">استلام</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->branch?->name ?? '-' }}</td>
                                    <td>{{ number_format($order->total, 2) }} ج.م</td>
                                    <td><span class="{{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td>{{ $order->created_at?->format('Y-m-d h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-order-details">عرض التفاصيل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="orders-empty">لا توجد طلبات متاحة داخل هذا القسم حالياً.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="orders-mobile-list" id="ordersMobileList">
                @forelse($orders as $order)
                    @php
                        $statusClass = match($order->status) {
                            'pending' => 'ops-status status-pending',
                            'confirmed' => 'ops-status status-confirmed',
                            'preparing' => 'ops-status status-preparing',
                            'out_for_delivery' => 'ops-status status-delivery',
                            'ready_for_pickup' => 'ops-status status-confirmed',
                            'delivered' => 'ops-status status-delivered',
                            default => 'ops-status status-cancelled',
                        };

                        $statusLabel = $statusLabels[$order->status] ?? 'تم الإلغاء';
                    @endphp

                    <div class="order-mobile-card">
                        <div class="order-mobile-top">
                            <div>
                                <div class="order-mobile-number-wrap">
                                    @if(!$order->is_seen_by_admin)
                                        <span class="ops-new-dot"></span>
                                    @endif
                                    <div class="order-mobile-number">{{ $order->order_number }}</div>
                                </div>
                                <div class="order-mobile-date">{{ $order->created_at?->format('Y-m-d h:i A') }}</div>
                            </div>

                            <div>
                                <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                        </div>

                        <div class="order-mobile-grid">
                            <div class="order-mobile-box">
                                <div class="order-mobile-label">اسم العميل</div>
                                <div class="order-mobile-value">{{ $order->customer_name }}</div>
                            </div>

                            <div class="order-mobile-box">
                                <div class="order-mobile-label">رقم الهاتف</div>
                                <div class="order-mobile-value">{{ $order->customer_phone }}</div>
                            </div>

                            <div class="order-mobile-box">
                                <div class="order-mobile-label">نوع الطلب</div>
                                <div class="order-mobile-value">{{ $order->order_type === 'delivery' ? 'توصيل' : 'استلام' }}</div>
                            </div>

                            <div class="order-mobile-box">
                                <div class="order-mobile-label">الفرع</div>
                                <div class="order-mobile-value">{{ $order->branch?->name ?? '-' }}</div>
                            </div>

                            <div class="order-mobile-box">
                                <div class="order-mobile-label">إجمالي الطلب</div>
                                <div class="order-mobile-value">{{ number_format($order->total, 2) }} ج.م</div>
                            </div>
                        </div>

                        <div class="order-mobile-actions">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-order-details">عرض التفاصيل</a>
                        </div>
                    </div>
                @empty
                    <div class="orders-empty">لا توجد طلبات متاحة داخل هذا القسم حالياً.</div>
                @endforelse
            </div>

            @if($orders instanceof \Illuminate\Pagination\AbstractPaginator)
                <div class="pagination-wrap">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const desktopBody = document.getElementById('ordersDesktopTableBody');
    const mobileList = document.getElementById('ordersMobileList');
    const currentPageCountEl = document.getElementById('ordersCurrentPageCount');
    const newCountEl = document.getElementById('ordersNewCount');
    const pendingCountEl = document.getElementById('ordersPendingCount');
    const sidebarNewOrdersCountEl = document.getElementById('sidebarNewOrdersCount');

    let isFetching = false;
    let lastNewOrdersCount = parseInt(newCountEl?.textContent || '0', 10);
    const currentType = @json($currentType);

    const statusLabels = {
        pending: 'قيد المراجعة',
        confirmed: 'تم التأكيد',
        preparing: 'قيد التحضير',
        out_for_delivery: 'خرج للتوصيل',
        ready_for_pickup: 'جاهز للاستلام',
        delivered: 'تم التسليم',
        cancelled: 'تم الإلغاء',
    };

    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function statusChip(status) {
        const map = {
            pending: 'ops-status status-pending',
            confirmed: 'ops-status status-confirmed',
            preparing: 'ops-status status-preparing',
            out_for_delivery: 'ops-status status-delivery',
            ready_for_pickup: 'ops-status status-confirmed',
            delivered: 'ops-status status-delivered',
            cancelled: 'ops-status status-cancelled',
        };

        const label = statusLabels[status] ?? 'تم الإلغاء';
        return `<span class="${map[status] ?? 'ops-status status-cancelled'}">${escapeHtml(label)}</span>`;
    }

    function orderTypeChip(type) {
        return type === 'delivery'
            ? '<span class="order-type-chip order-type-delivery">توصيل</span>'
            : '<span class="order-type-chip order-type-pickup">استلام</span>';
    }

    function formatMoney(value) {
        return `${Number(value).toFixed(2)} ج.م`;
    }

    function playSound() {
        const audio = new Audio('/sounds/new-order.mp3');
        audio.play().catch(() => {});
    }

    function renderDesktopRows(orders) {
        if (!desktopBody) return;

        if (!orders.length) {
            desktopBody.innerHTML = `
                <tr>
                    <td colspan="9">
                        <div class="orders-empty">لا توجد طلبات متاحة داخل هذا القسم حالياً.</div>
                    </td>
                </tr>
            `;
            return;
        }

        desktopBody.innerHTML = orders.map(order => `
            <tr class="${order.is_seen_by_admin ? '' : 'row-unseen'}">
                <td>
                    <div class="ops-order-id">
                        ${order.is_seen_by_admin ? '' : '<span class="ops-new-dot"></span>'}
                        <strong>${escapeHtml(order.order_number)}</strong>
                    </div>
                </td>
                <td>${escapeHtml(order.customer_name)}</td>
                <td>${escapeHtml(order.customer_phone)}</td>
                <td>${orderTypeChip(order.order_type)}</td>
                <td>${order.branch_name ? escapeHtml(order.branch_name) : '-'}</td>
                <td>${formatMoney(order.total)}</td>
                <td>${statusChip(order.status)}</td>
                <td>${escapeHtml(order.created_at)}</td>
                <td><a href="${escapeHtml(order.show_url)}" class="btn-order-details">عرض التفاصيل</a></td>
            </tr>
        `).join('');
    }

    function renderMobileCards(orders) {
        if (!mobileList) return;

        if (!orders.length) {
            mobileList.innerHTML = `<div class="orders-empty">لا توجد طلبات متاحة داخل هذا القسم حالياً.</div>`;
            return;
        }

        mobileList.innerHTML = orders.map(order => `
            <div class="order-mobile-card">
                <div class="order-mobile-top">
                    <div>
                        <div class="order-mobile-number-wrap">
                            ${order.is_seen_by_admin ? '' : '<span class="ops-new-dot"></span>'}
                            <div class="order-mobile-number">${escapeHtml(order.order_number)}</div>
                        </div>
                        <div class="order-mobile-date">${escapeHtml(order.created_at)}</div>
                    </div>
                    <div>${statusChip(order.status)}</div>
                </div>

                <div class="order-mobile-grid">
                    <div class="order-mobile-box">
                        <div class="order-mobile-label">اسم العميل</div>
                        <div class="order-mobile-value">${escapeHtml(order.customer_name)}</div>
                    </div>

                    <div class="order-mobile-box">
                        <div class="order-mobile-label">رقم الهاتف</div>
                        <div class="order-mobile-value">${escapeHtml(order.customer_phone)}</div>
                    </div>

                    <div class="order-mobile-box">
                        <div class="order-mobile-label">نوع الطلب</div>
                        <div class="order-mobile-value">${order.order_type === 'delivery' ? 'توصيل' : 'استلام'}</div>
                    </div>

                    <div class="order-mobile-box">
                        <div class="order-mobile-label">الفرع</div>
                        <div class="order-mobile-value">${order.branch_name ? escapeHtml(order.branch_name) : '-'}</div>
                    </div>

                    <div class="order-mobile-box">
                        <div class="order-mobile-label">إجمالي الطلب</div>
                        <div class="order-mobile-value">${formatMoney(order.total)}</div>
                    </div>
                </div>

                <div class="order-mobile-actions">
                    <a href="${escapeHtml(order.show_url)}" class="btn-order-details">عرض التفاصيل</a>
                </div>
            </div>
        `).join('');
    }

    async function pollOrdersPage() {
        if (isFetching) return;
        isFetching = true;

        try {
            const response = await fetch(`{{ route('admin.orders.poll', absolute: false) }}?type=${encodeURIComponent(currentType)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });

            if (!response.ok) {
                isFetching = false;
                return;
            }

            const data = await response.json();
            const counts = data.counts || {};
            const orders = data.orders || [];
            const currentNew = parseInt(counts.new || 0, 10);

            if (currentNew > lastNewOrdersCount) {
                playSound();
            }

            lastNewOrdersCount = currentNew;

            if (currentPageCountEl) currentPageCountEl.textContent = counts.current_page_total ?? 0;
            if (newCountEl) newCountEl.textContent = counts.new ?? 0;
            if (pendingCountEl) pendingCountEl.textContent = counts.pending ?? 0;
            if (sidebarNewOrdersCountEl) sidebarNewOrdersCountEl.textContent = counts.new ?? 0;

            renderDesktopRows(orders);
            renderMobileCards(orders);
        } catch (error) {
            console.log('Orders polling error:', error);
        } finally {
            isFetching = false;
        }
    }

    setInterval(pollOrdersPage, 5000);
});
</script>
@endpush
