@extends('layouts.admin')

@section('content')
<style>
    .cashier-layout { display: grid; grid-template-columns: 1fr 370px; gap: 1rem; }
    .cashier-panel { background: #fff; border-radius: 16px; box-shadow: 0 8px 24px rgba(15, 23, 42, .06); border: 1px solid #eef2ff; }
    .cashier-products { max-height: calc(100vh - 250px); overflow: auto; }
    .product-card { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; padding: .85rem; text-align: right; width: 100%; transition: .15s ease; }
    .product-card:hover { border-color: #0d6efd; box-shadow: 0 6px 16px rgba(13, 110, 253, .12); transform: translateY(-1px); }
    .product-card .name { font-weight: 700; color: #111827; }
    .product-card .cat { font-size: .82rem; color: #6b7280; }
    .product-card .price { color: #0d6efd; font-weight: 800; }
    .cart-lines { max-height: 42vh; overflow: auto; }
    .cart-line { border: 1px solid #edf2f7; border-radius: 12px; padding: .65rem; }
    .qty-control { display: inline-flex; border: 1px solid #dbe2ea; border-radius: 10px; overflow: hidden; }
    .qty-control button { border: 0; background: #f8fafc; width: 32px; font-weight: 700; }
    .qty-control input { border: 0; width: 48px; text-align: center; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: .35rem; font-size: .95rem; }
    .summary-total { font-size: 1.12rem; font-weight: 800; color: #0f172a; }
    .sticky-cart { position: sticky; top: 88px; }
    @media (max-width: 991.98px) { .cashier-layout { grid-template-columns: 1fr; } .sticky-cart { position: static; } }
</style>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">شاشة الكاشير - {{ $branch->name }}</h1>
            <div class="text-muted small">واجهة سريعة ومنظمة للطلبات داخل المطعم</div>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()?->hasPermission('manage_cashier'))
                <a class="btn btn-outline-primary" href="{{ route('admin.cashier.index', ['branch_id' => $branch->id]) }}">إدارة المنيو</a>
            @endif
            <a class="btn btn-outline-secondary" href="{{ route('admin.orders.pickup') }}">طلبات الاستلام</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.cashier.checkout', $branch) }}" id="cashierCheckoutForm">
        @csrf

        <div class="cashier-layout">
            <section class="cashier-panel p-3 p-lg-4">
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-md-8">
                        <input id="cashierSearch" type="text" class="form-control form-control-lg" placeholder="ابحث باسم المنتج...">
                    </div>
                    <div class="col-md-4">
                        <select id="cashierCategoryFilter" class="form-select form-select-lg">
                            <option value="">كل الأقسام</option>
                            @foreach($menuItems->pluck('product.category.name')->filter()->unique()->values() as $categoryName)
                                <option value="{{ $categoryName }}">{{ $categoryName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="cashier-products">
                    <div class="row g-3" id="productsGrid">
                        @foreach($menuItems as $item)
                            <div class="col-sm-6 col-xl-4 product-item"
                                 data-name="{{ mb_strtolower($item->product->name) }}"
                                 data-category="{{ $item->product->category?->name }}">
                                <button type="button"
                                        class="product-card cashier-add-btn"
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->product->name }}"
                                        data-price="{{ (float) ($item->price ?? $item->product->price) }}">
                                    <div class="name">{{ $item->product->name }}</div>
                                    <div class="cat">{{ $item->product->category?->name ?? 'بدون قسم' }}</div>
                                    <div class="price mt-2">{{ number_format($item->price ?? $item->product->price, 2) }} ج.م</div>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <aside class="cashier-panel p-3 sticky-cart">
                <h2 class="h6 mb-3">تفاصيل الفاتورة</h2>

                <div class="mb-2">
                    <label class="form-label">اسم العميل</label>
                    <input type="text" class="form-control" name="customer_name" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" class="form-control" name="customer_phone" placeholder="اختياري">
                </div>
                <div class="mb-3">
                    <label class="form-label">ملاحظات</label>
                    <textarea class="form-control" name="notes" rows="2"></textarea>
                </div>

                <div class="cart-lines mb-3" id="cashierCartLines">
                    <div class="alert alert-warning py-2 mb-0" id="cashierEmptyState">لم يتم اختيار أصناف بعد.</div>
                </div>

                <div class="border-top pt-2">
                    <div class="summary-row"><span>عدد الأصناف</span><strong id="itemsCount">0</strong></div>
                    <div class="summary-row"><span>الإجمالي الفرعي</span><strong id="cashierSubtotal">0.00 ج.م</strong></div>
                    <div class="summary-row summary-total"><span>الإجمالي النهائي</span><span id="cashierTotal">0.00 ج.م</span></div>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-success btn-lg" type="submit">حفظ الطلب وطباعة الفاتورة</button>
                    <button class="btn btn-light border" type="button" id="clearCartBtn">مسح السلة</button>
                </div>
            </aside>
        </div>
    </form>
</div>

<script nonce="{{ $cspNonce }}">
(() => {
    const lines = [];
    const form = document.getElementById('cashierCheckoutForm');
    const linesContainer = document.getElementById('cashierCartLines');
    const totalEl = document.getElementById('cashierTotal');
    const subtotalEl = document.getElementById('cashierSubtotal');
    const itemsCountEl = document.getElementById('itemsCount');
    const emptyState = document.getElementById('cashierEmptyState');
    const searchInput = document.getElementById('cashierSearch');
    const categoryFilter = document.getElementById('cashierCategoryFilter');

    const formatCurrency = (value) => `${Number(value).toFixed(2)} ج.م`;

    function getTotals() {
        const subtotal = lines.reduce((sum, line) => sum + (line.price * line.qty), 0);
        const count = lines.reduce((sum, line) => sum + line.qty, 0);
        return {subtotal, count};
    }

    function renderLines() {
        linesContainer.querySelectorAll('.cart-line').forEach(el => el.remove());

        if (lines.length === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }

        lines.forEach((line, idx) => {
            const wrap = document.createElement('div');
            wrap.className = 'cart-line mb-2';
            wrap.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong>${line.name}</strong>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove="${idx}">حذف</button>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="qty-control">
                        <button type="button" data-dec="${idx}">-</button>
                        <input type="number" min="1" value="${line.qty}" data-qty="${idx}">
                        <button type="button" data-inc="${idx}">+</button>
                    </div>
                    <div class="fw-semibold">${formatCurrency(line.price * line.qty)}</div>
                </div>
            `;
            linesContainer.appendChild(wrap);
        });

        const totals = getTotals();
        itemsCountEl.textContent = totals.count;
        subtotalEl.textContent = formatCurrency(totals.subtotal);
        totalEl.textContent = formatCurrency(totals.subtotal);
    }

    function addItem(id, name, price) {
        const found = lines.find(line => line.id === id);
        if (found) {
            found.qty += 1;
        } else {
            lines.push({id, name, price, qty: 1});
        }
        renderLines();
    }

    document.querySelectorAll('.cashier-add-btn').forEach(btn => {
        btn.addEventListener('click', () => addItem(Number(btn.dataset.id), btn.dataset.name, Number(btn.dataset.price)));
    });

    linesContainer.addEventListener('click', (event) => {
        const removeIdx = event.target.getAttribute('data-remove');
        if (removeIdx !== null) {
            lines.splice(Number(removeIdx), 1);
            renderLines();
            return;
        }

        const incIdx = event.target.getAttribute('data-inc');
        if (incIdx !== null) {
            lines[Number(incIdx)].qty += 1;
            renderLines();
            return;
        }

        const decIdx = event.target.getAttribute('data-dec');
        if (decIdx !== null) {
            const row = lines[Number(decIdx)];
            row.qty = Math.max(1, row.qty - 1);
            renderLines();
        }
    });

    linesContainer.addEventListener('change', (event) => {
        const idx = event.target.getAttribute('data-qty');
        if (idx !== null) {
            lines[Number(idx)].qty = Math.max(1, Number(event.target.value || 1));
            renderLines();
        }
    });

    document.getElementById('clearCartBtn').addEventListener('click', () => {
        lines.splice(0, lines.length);
        renderLines();
    });

    function filterProducts() {
        const query = searchInput.value.trim().toLowerCase();
        const category = categoryFilter.value;

        document.querySelectorAll('.product-item').forEach(item => {
            const name = item.dataset.name || '';
            const itemCategory = item.dataset.category || '';
            const matchesName = !query || name.includes(query);
            const matchesCategory = !category || itemCategory === category;
            item.style.display = (matchesName && matchesCategory) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);

    form.addEventListener('submit', (event) => {
        if (lines.length === 0) {
            event.preventDefault();
            alert('اختر منتج واحد على الأقل.');
            return;
        }

        form.querySelectorAll('input[data-cashier-item]').forEach(el => el.remove());

        lines.forEach((line, index) => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = `items[${index}][id]`;
            idInput.value = line.id;
            idInput.setAttribute('data-cashier-item', '1');
            form.appendChild(idInput);

            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = `items[${index}][qty]`;
            qtyInput.value = line.qty;
            qtyInput.setAttribute('data-cashier-item', '1');
            form.appendChild(qtyInput);
        });
    });
})();
</script>
@endsection
