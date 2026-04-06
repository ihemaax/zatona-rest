@extends('layouts.admin')

@php
    $pageTitle = 'شاشة المطبخ';
    $pageSubtitle = 'استقبال الطلبات المؤكدة ومتابعة التحضير لايف';
    $confirmedCount = $orders->where('status', 'confirmed')->count();
    $preparingCount = $orders->where('status', 'preparing')->count();
@endphp

@section('content')
<style>
    .kitchen-grid { display:grid; gap:16px; }
    .kitchen-card { background:#fffdf9; border:1px solid #e7dfd3; border-radius:20px; box-shadow:0 10px 20px rgba(35,31,27,.06); }
    .kitchen-head { padding:16px 18px; border-bottom:1px solid #efe6da; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .kitchen-title { margin:0; font-size:1rem; font-weight:900; }
    .kitchen-sub { margin:2px 0 0; color:#8a847a; font-size:.78rem; font-weight:700; }
    .live-chip { background:#ecfdf3; color:#15803d; border:1px solid #bbf7d0; border-radius:999px; padding:6px 10px; font-size:.72rem; font-weight:900; }
    .kitchen-body { padding:16px; }
    .kitchen-table-wrap { overflow:auto; }
    .kitchen-table { width:100%; min-width:980px; border-collapse:separate; border-spacing:0; }
    .kitchen-table th, .kitchen-table td { padding:12px; border-bottom:1px solid #efe7dd; vertical-align:top; }
    .kitchen-table th { background:#f8f4ee; font-size:.74rem; color:#7b7268; text-transform:uppercase; letter-spacing:.04em; }
    .order-no { font-weight:900; color:#231f1b; }
    .chip { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:999px; font-size:.72rem; font-weight:800; }
    .chip-confirmed { background:#fff7ed; color:#b45309; }
    .chip-preparing { background:#eef2ff; color:#4338ca; }
    .chip-delivery { background:#eaf1f8; color:#5d7a9a; }
    .chip-pickup { background:#edf8ef; color:#4f7458; }
    .items-list { margin:0; padding-inline-start:18px; }
    .items-list li { margin-bottom:4px; }
    .actions-col { display:flex; flex-wrap:wrap; gap:8px; }
    .btn-kitchen { border:none; border-radius:12px; min-height:36px; padding:8px 12px; font-weight:800; font-size:.78rem; }
    .btn-start { background:#eef2ff; color:#3730a3; }
    .btn-ready { background:#ecfdf3; color:#166534; }
    .empty-state { text-align:center; padding:24px; color:#8a847a; font-weight:700; }
</style>

<div class="kitchen-grid">
    <section class="kitchen-card">
        <div class="kitchen-head">
            <div>
                <h2 class="kitchen-title">طلبات المطبخ المؤكدة</h2>
                <p class="kitchen-sub">الطلب يظهر هنا تلقائيًا بمجرد تحوله إلى حالة <strong>confirmed</strong> (تأكيد).</p>
                <p class="kitchen-sub mb-0">التسلسل: <strong>pending → confirmed → preparing → جاهز للتسليم/التوصيل</strong>.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="live-chip">مؤكد: <strong id="confirmedCount">{{ $confirmedCount }}</strong></span>
                <span class="live-chip">قيد التحضير: <strong id="preparingCount">{{ $preparingCount }}</strong></span>
                <span class="live-chip">Live تحديث كل 5 ثواني</span>
            </div>
        </div>

        <div class="kitchen-body">
            <div class="kitchen-table-wrap">
                <table class="kitchen-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>الفرع</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>المنتجات</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="kitchenOrdersBody">
                        @forelse($orders as $order)
                            <tr>
                                <td class="order-no">{{ $order->order_number }}</td>
                                <td>
                                    <div>{{ $order->customer_name }}</div>
                                    <small class="text-muted">{{ $order->created_at?->format('Y-m-d h:i A') }}</small>
                                </td>
                                <td>{{ $order->branch?->name ?? '-' }}</td>
                                <td>
                                    @if($order->order_type === 'delivery')
                                        <span class="chip chip-delivery">توصيل</span>
                                    @else
                                        <span class="chip chip-pickup">استلام</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->status === 'confirmed')
                                        <span class="chip chip-confirmed">مؤكد</span>
                                    @else
                                        <span class="chip chip-preparing">قيد التحضير</span>
                                    @endif
                                </td>
                                <td>
                                    <ul class="items-list">
                                        @foreach($order->items as $item)
                                            <li>{{ $item->product?->name ?? 'منتج' }} × {{ (int) $item->quantity }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <div class="actions-col">
                                        <form method="POST" action="{{ route('admin.kitchen.start', $order->id) }}">
                                            @csrf
                                            <button class="btn-kitchen btn-start" type="submit">بدء التحضير</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.kitchen.ready', $order->id) }}">
                                            @csrf
                                            <button class="btn-kitchen btn-ready" type="submit">
                                                {{ $order->order_type === 'delivery' ? 'جاهز للتوصيل' : 'جاهز للتسليم' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">لا توجد طلبات مؤكدة في المطبخ حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders instanceof \Illuminate\Pagination\AbstractPaginator)
                <div class="mt-3 d-flex justify-content-center">{{ $orders->links() }}</div>
            @endif
        </div>
    </section>
</div>

<script>
    (() => {
        const body = document.getElementById('kitchenOrdersBody');
        if (!body) return;

        const csrf = @json(csrf_token());
        const startBase = @json(url('/admin/kitchen'));

        const statusChip = (status) => {
            if (status === 'confirmed') {
                return '<span class="chip chip-confirmed">مؤكد</span>';
            }
            return '<span class="chip chip-preparing">قيد التحضير</span>';
        };

        const typeChip = (type) => type === 'delivery'
            ? '<span class="chip chip-delivery">توصيل</span>'
            : '<span class="chip chip-pickup">استلام</span>';

        const itemList = (items = []) => {
            if (!items.length) return '<span class="text-muted">-</span>';
            return `<ul class="items-list">${items.map((item) => `<li>${item.name} × ${item.quantity}</li>`).join('')}</ul>`;
        };

        const render = (orders = []) => {
            const confirmedCount = orders.filter((order) => order.status === 'confirmed').length;
            const preparingCount = orders.filter((order) => order.status === 'preparing').length;
            const confirmedEl = document.getElementById('confirmedCount');
            const preparingEl = document.getElementById('preparingCount');
            if (confirmedEl) confirmedEl.textContent = String(confirmedCount);
            if (preparingEl) preparingEl.textContent = String(preparingCount);

            if (!orders.length) {
                body.innerHTML = '<tr><td colspan="7" class="empty-state">لا توجد طلبات مؤكدة في المطبخ حالياً.</td></tr>';
                return;
            }

            body.innerHTML = orders.map((order) => {
                const readyLabel = order.order_type === 'delivery' ? 'جاهز للتوصيل' : 'جاهز للتسليم';
                return `
                    <tr>
                        <td class="order-no">${order.order_number || ('#' + order.id)}</td>
                        <td>
                            <div>${order.customer_name || '-'}</div>
                            <small class="text-muted">${order.created_at || ''}</small>
                        </td>
                        <td>${order.branch_name || '-'}</td>
                        <td>${typeChip(order.order_type)}</td>
                        <td>${statusChip(order.status)}</td>
                        <td>${itemList(order.items)}</td>
                        <td>
                            <div class="actions-col">
                                <form method="POST" action="${startBase}/${order.id}/start">
                                    <input type="hidden" name="_token" value="${csrf}">
                                    <button class="btn-kitchen btn-start" type="submit">بدء التحضير</button>
                                </form>
                                <form method="POST" action="${startBase}/${order.id}/ready">
                                    <input type="hidden" name="_token" value="${csrf}">
                                    <button class="btn-kitchen btn-ready" type="submit">${readyLabel}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        };

        const poll = async () => {
            try {
                const response = await fetch(@json(route('admin.kitchen.poll')), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });
                if (!response.ok) return;
                const data = await response.json();
                render(data.orders || []);
            } catch (_) {
                // تجاهل أخطاء الشبكة المؤقتة
            }
        };

        setInterval(poll, 5000);
    })();
</script>
@endsection
