@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h4 mb-1">شاشة الكاشير - {{ $branch->name }}</h1>
            <p class="text-muted mb-0">اختيار سريع للأصناف + إنشاء فاتورة وطباعتها مباشرة.</p>
        </div>
        @if(auth()->user()?->hasPermission('manage_cashier'))
            <a class="btn btn-outline-primary" href="{{ route('admin.cashier.index', ['branch_id' => $branch->id]) }}">إدارة منيو الكاشير</a>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.cashier.checkout', $branch) }}" id="cashierCheckoutForm">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($menuItems as $item)
                                <div class="col-sm-6 col-xl-4">
                                    <button type="button" class="btn w-100 text-start border p-3 h-100 cashier-add-btn"
                                            data-id="{{ $item->id }}"
                                            data-name="{{ $item->product->name }}"
                                            data-price="{{ (float) ($item->price ?? $item->product->price) }}">
                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                        <div class="small text-muted">{{ $item->product->category?->name }}</div>
                                        <div class="mt-2 text-primary fw-bold">{{ number_format($item->price ?? $item->product->price, 2) }} ج.م</div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h6 mb-3">بيانات الفاتورة</h2>
                        <div class="mb-2">
                            <label class="form-label">اسم العميل</label>
                            <input type="text" class="form-control" name="customer_name" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">رقم الهاتف (اختياري)</label>
                            <input type="text" class="form-control" name="customer_phone">
                        </div>
                        <div>
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 mb-3">سلة الكاشير</h2>
                        <div id="cashierCartLines" class="mb-2">
                            <div class="alert alert-warning py-2 mb-0" id="cashierEmptyState">لم يتم اختيار أصناف بعد.</div>
                        </div>

                        <div class="border-top pt-2 mt-3 d-flex justify-content-between fw-bold">
                            <span>الإجمالي</span>
                            <span id="cashierTotal">0.00 ج.م</span>
                        </div>

                        <button class="btn btn-success w-100 mt-3" type="submit">إنشاء الفاتورة</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(() => {
    const lines = [];
    const form = document.getElementById('cashierCheckoutForm');
    const linesContainer = document.getElementById('cashierCartLines');
    const totalEl = document.getElementById('cashierTotal');
    const emptyState = document.getElementById('cashierEmptyState');

    function formatCurrency(value) {
        return `${Number(value).toFixed(2)} ج.م`;
    }

    function renderLines() {
        linesContainer.querySelectorAll('.cashier-line').forEach(el => el.remove());

        if (lines.length === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }

        let total = 0;

        lines.forEach((line, idx) => {
            total += line.price * line.qty;
            const wrap = document.createElement('div');
            wrap.className = 'border rounded p-2 mb-2 cashier-line';
            wrap.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <strong>${line.name}</strong>
                    <button type="button" class="btn btn-sm btn-link text-danger" data-remove="${idx}">حذف</button>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <input type="number" class="form-control form-control-sm" min="1" value="${line.qty}" data-qty="${idx}">
                    <div class="small text-nowrap">${formatCurrency(line.price * line.qty)}</div>
                </div>
            `;
            linesContainer.appendChild(wrap);
        });

        totalEl.textContent = formatCurrency(total);
    }

    document.querySelectorAll('.cashier-add-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = Number(btn.dataset.id);
            const name = btn.dataset.name;
            const price = Number(btn.dataset.price);

            const found = lines.find(line => line.id === id);
            if (found) {
                found.qty += 1;
            } else {
                lines.push({ id, name, price, qty: 1 });
            }
            renderLines();
        });
    });

    linesContainer.addEventListener('click', (event) => {
        const removeIdx = event.target.getAttribute('data-remove');
        if (removeIdx !== null) {
            lines.splice(Number(removeIdx), 1);
            renderLines();
        }
    });

    linesContainer.addEventListener('change', (event) => {
        const idx = event.target.getAttribute('data-qty');
        if (idx !== null) {
            const qty = Math.max(1, Number(event.target.value || 1));
            lines[Number(idx)].qty = qty;
            renderLines();
        }
    });

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
