@extends('layouts.admin')

@php
    $pageTitle = 'الطلبات الجاهزة للاستلام';
    $pageSubtitle = 'طلبات خرجت من المطبخ وجاهزة للتسليم أو إسناد الدليفري';
@endphp

@section('content')
<div class="admin-card p-4 mb-4">
    <div class="section-title mb-1">دورة التشغيل</div>
    <div class="section-subtitle mb-0">Pending → Confirmed → Preparing → Ready (Ready for Pickup) → Delivery Assignment/Customer Pickup</div>
</div>

<div class="admin-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-title mb-0">طلبات التوصيل الجاهزة</div>
        <span class="filter-pill active">{{ $deliveryOrders->count() }} طلب</span>
    </div>

    <div class="admin-table-wrap">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الفرع</th>
                    <th>إسناد للدليفري</th>
                </tr>
            </thead>
            <tbody id="readyDeliveryOrdersBody">
                @forelse($deliveryOrders as $order)
                    <tr data-order-id="{{ $order->id }}">
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->branch?->name ?? '-' }}</td>
                        <td>
                            <form action="{{ route('admin.orders.assign-delivery', $order->id) }}" method="POST" class="d-flex gap-2 flex-wrap">
                                @csrf
                                @method('PATCH')
                                <select name="delivery_user_id" class="form-select" style="max-width:240px;" required>
                                    <option value="">اختار موظف دليفري</option>
                                    @foreach($deliveryUsers as $deliveryUser)
                                        <option value="{{ $deliveryUser->id }}">{{ $deliveryUser->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-outline-primary">إسناد</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">لا توجد طلبات توصيل جاهزة حالياً.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-title mb-0">طلبات الاستلام من الفرع الجاهزة</div>
        <span class="filter-pill active">{{ $pickupOrders->count() }} طلب</span>
    </div>

    <div class="admin-table-wrap">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الفرع</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody id="readyPickupOrdersBody">
                @forelse($pickupOrders as $order)
                    <tr data-order-id="{{ $order->id }}">
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->branch?->name ?? '-' }}</td>
                        <td>
                            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="d-flex gap-2 flex-wrap">
                                @csrf
                                <input type="hidden" name="order_type" value="pickup">
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="btn btn-outline-success">تم التسليم للعميل</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">لا توجد طلبات استلام جاهزة حالياً.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    (() => {
        const deliveryBody = document.getElementById('readyDeliveryOrdersBody');
        const pickupBody = document.getElementById('readyPickupOrdersBody');
        if (!deliveryBody || !pickupBody) return;

        const csrf = @json(csrf_token());
        const assignBase = @json(route('admin.orders.index', absolute: false));
        const deliveryUsers = @json($deliveryUsers->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values());
        const statusUrlBase = @json(route('admin.orders.status', ['order' => '__ORDER__'], false));
        const pollUrl = @json(route('admin.orders.ready.poll', absolute: false));
        const statusUrl = (id) => statusUrlBase.replace('__ORDER__', id);

        let seenReadyOrderIds = new Set([
            ...Array.from(deliveryBody.querySelectorAll('tr[data-order-id]')).map((row) => Number(row.dataset.orderId)),
            ...Array.from(pickupBody.querySelectorAll('tr[data-order-id]')).map((row) => Number(row.dataset.orderId)),
        ].filter(Boolean));

        const showNotification = (text) => {
            const popup = document.createElement('div');
            popup.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;background:#1d4ed8;color:#fff;padding:12px 16px;border-radius:12px;box-shadow:0 10px 28px rgba(0,0,0,.2);font-weight:800;font-size:.85rem;';
            popup.textContent = text;
            document.body.appendChild(popup);
            setTimeout(() => {
                popup.style.opacity = '0';
                popup.style.transition = 'opacity .35s ease';
            }, 2500);
            setTimeout(() => popup.remove(), 3000);
        };

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const deliveryOptionsHtml = () => [
            '<option value="">اختار موظف دليفري</option>',
            ...deliveryUsers.map((user) => `<option value="${user.id}">${escapeHtml(user.name)}</option>`),
        ].join('');

        const renderDeliveryOrders = (orders = []) => {
            if (!orders.length) {
                deliveryBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">لا توجد طلبات توصيل جاهزة حالياً.</td></tr>';
                return;
            }

            deliveryBody.innerHTML = orders.map((order) => `
                <tr data-order-id="${order.id}">
                    <td>${escapeHtml(order.order_number || ('#' + order.id))}</td>
                    <td>${escapeHtml(order.customer_name || '-')}</td>
                    <td>${escapeHtml(order.branch_name || '-')}</td>
                    <td>
                        <form action="${assignBase}/${order.id}/assign-delivery" method="POST" class="d-flex gap-2 flex-wrap">
                            <input type="hidden" name="_token" value="${csrf}">
                            <input type="hidden" name="_method" value="PATCH">
                            <select name="delivery_user_id" class="form-select" style="max-width:240px;" required>
                                ${deliveryOptionsHtml()}
                            </select>
                            <button type="submit" class="btn btn-outline-primary">إسناد</button>
                        </form>
                    </td>
                </tr>
            `).join('');
        };

        const renderPickupOrders = (orders = []) => {
            if (!orders.length) {
                pickupBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">لا توجد طلبات استلام جاهزة حالياً.</td></tr>';
                return;
            }

            pickupBody.innerHTML = orders.map((order) => `
                <tr data-order-id="${order.id}">
                    <td>${escapeHtml(order.order_number || ('#' + order.id))}</td>
                    <td>${escapeHtml(order.customer_name || '-')}</td>
                    <td>${escapeHtml(order.branch_name || '-')}</td>
                    <td>
                        <form action="${statusUrl(order.id)}" method="POST" class="d-flex gap-2 flex-wrap">
                            <input type="hidden" name="_token" value="${csrf}">
                            <input type="hidden" name="order_type" value="pickup">
                            <input type="hidden" name="status" value="delivered">
                            <button type="submit" class="btn btn-outline-success">تم التسليم للعميل</button>
                        </form>
                    </td>
                </tr>
            `).join('');
        };

        const updateCounters = (counts = {}) => {
            const pills = document.querySelectorAll('.filter-pill.active');
            if (pills[0]) pills[0].textContent = `${Number(counts.delivery || 0)} طلب`;
            if (pills[1]) pills[1].textContent = `${Number(counts.pickup || 0)} طلب`;
        };

        const notifyNewReadyOrders = (deliveryOrders = [], pickupOrders = []) => {
            const all = [...deliveryOrders, ...pickupOrders];
            const current = new Set(all.map((order) => Number(order.id)).filter(Boolean));
            const newOnes = all.filter((order) => !seenReadyOrderIds.has(Number(order.id)));

            if (newOnes.length) {
                showNotification(`وصل ${newOnes.length} طلب جديد لمرحلة الطلبات الجاهزة.`);
            }

            seenReadyOrderIds = current;
        };

        const poll = async () => {
            try {
                const response = await fetch(pollUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });

                if (!response.ok) return;

                const data = await response.json();
                const deliveryOrders = data.delivery_orders || [];
                const pickupOrders = data.pickup_orders || [];

                notifyNewReadyOrders(deliveryOrders, pickupOrders);
                updateCounters(data.counts || {});
                renderDeliveryOrders(deliveryOrders);
                renderPickupOrders(pickupOrders);
            } catch (_) {
                // تجاهل أخطاء الشبكة المؤقتة
            }
        };

        poll();
        setInterval(poll, 3000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                poll();
            }
        });
    })();
</script>
@endsection
