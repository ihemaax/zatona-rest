@extends('layouts.admin')

@php
    $pageTitle = 'تفاصيل الطلب';
    $pageSubtitle = $order->order_number ?? ('ORD-' . $order->id);

    $statusClass = match($order->status) {
        'pending' => 'ops-status status-pending',
        'confirmed' => 'ops-status status-confirmed',
        'preparing' => 'ops-status status-preparing',
        'out_for_delivery' => 'ops-status status-delivery',
        'delivered' => 'ops-status status-delivered',
        default => 'ops-status status-cancelled',
    };

    $statusLabel = match($order->status) {
        'pending' => 'قيد المراجعة',
        'confirmed' => 'تم التأكيد',
        'preparing' => 'قيد التحضير',
        'out_for_delivery' => 'خرج للتوصيل',
        'delivered' => 'تم التسليم',
        default => 'تم الإلغاء',
    };

    $paymentLabel = match($order->payment_method) {
        'cash' => 'الدفع نقداً',
        'cash_on_delivery' => 'الدفع عند الاستلام',
        default => $order->payment_method ?: 'غير محدد',
    };
@endphp

@section('content')
<style>
    .order-show-layout{
        display:grid;
        grid-template-columns:minmax(0,1.3fr) minmax(320px,.7fr);
        gap:18px;
        align-items:start;
    }

    .order-panel{
        background:var(--admin-surface);
        border:1px solid var(--admin-border);
        border-radius:24px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }

    .order-panel-header{
        padding:18px 18px 0;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
    }

    .order-panel-title{
        font-size:1rem;
        font-weight:900;
        margin:0;
        color:var(--admin-text);
        letter-spacing:-.01em;
    }

    .order-panel-subtitle{
        margin:4px 0 0;
        color:var(--admin-text-faint);
        font-size:.8rem;
        font-weight:700;
    }

    .order-panel-body{
        padding:18px;
    }

    .order-summary-grid{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:12px;
        margin-bottom:18px;
    }

    .order-summary-card{
        border:1px solid #ebe3d7;
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border-radius:18px;
        padding:14px;
    }

    .order-summary-label{
        font-size:.73rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:8px;
    }

    .order-summary-value{
        font-size:1.02rem;
        font-weight:900;
        color:#231f1b;
        line-height:1.35;
        word-break:break-word;
    }

    .order-info-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:12px;
    }

    .order-info-card{
        border:1px solid #ebe3d7;
        background:#fffdfa;
        border-radius:18px;
        padding:14px;
        min-width:0;
    }

    .order-info-card.full{
        grid-column:1 / -1;
    }

    .order-info-label{
        font-size:.73rem;
        color:#8a847a;
        font-weight:800;
        margin-bottom:8px;
    }

    .order-info-value{
        font-size:.9rem;
        color:#443b33;
        font-weight:800;
        line-height:1.7;
        word-break:break-word;
    }

    .items-desktop-wrap{
        display:block;
    }

    .items-mobile-list{
        display:none;
    }

    .items-table-wrap{
        border:1px solid #ebe3d7;
        border-radius:18px;
        overflow:hidden;
        background:#fffdfa;
    }

    .items-table{
        width:100%;
        min-width:720px;
        border-collapse:separate;
        border-spacing:0;
        margin:0;
    }

    .items-table thead th{
        background:#f8f4ee;
        color:#7b7268;
        font-size:.74rem;
        font-weight:900;
        letter-spacing:.04em;
        padding:14px 14px;
        white-space:nowrap;
        border-bottom:1px solid #e9e1d5;
    }

    .items-table tbody td{
        color:#554d45;
        font-size:.84rem;
        padding:14px 14px;
        border-bottom:1px solid #efe7dd;
        vertical-align:top;
        font-weight:700;
        background:#fffdfa;
    }

    .items-table tbody tr:last-child td{
        border-bottom:none;
    }

    .items-table tbody tr:hover td{
        background:#fcf8f3;
    }

    .item-product-name{
        font-weight:900;
        color:#231f1b;
    }

    .item-options{
        margin-top:6px;
        color:#8a847a;
        font-size:.82rem;
        line-height:1.7;
        font-weight:700;
    }

    .item-mobile-card{
        background:#fffdfa;
        border:1px solid #ebe3d7;
        border-radius:18px;
        padding:14px;
    }

    .item-mobile-card + .item-mobile-card{
        margin-top:12px;
    }

    .item-mobile-title{
        font-weight:900;
        color:#231f1b;
        margin-bottom:10px;
    }

    .item-mobile-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
    }

    .item-mobile-box{
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:14px;
        padding:10px 12px;
    }

    .item-mobile-label{
        font-size:.72rem;
        color:#8a847a;
        margin-bottom:4px;
        font-weight:800;
    }

    .item-mobile-value{
        font-size:.9rem;
        color:#443b33;
        font-weight:800;
        word-break:break-word;
    }

    .totals-box{
        margin-top:16px;
        margin-inline-start:auto;
        max-width:360px;
        background:linear-gradient(180deg,#fffdfa 0%,#f8f4ee 100%);
        border:1px solid #ebe3d7;
        border-radius:20px;
        padding:14px 16px;
    }

    .total-row{
        display:flex;
        justify-content:space-between;
        gap:12px;
        padding:8px 0;
        color:#6f6a61;
        font-weight:800;
        font-size:.85rem;
    }

    .total-row.final{
        border-top:1px solid #e4dbcf;
        margin-top:8px;
        padding-top:12px;
        font-size:1rem;
        font-weight:900;
        color:#231f1b;
    }

    .side-form-grid{
        display:grid;
        gap:14px;
    }

    .sticky-side{
        position:sticky;
        top:96px;
    }

    .helper-box{
        background:#faf6f1;
        border:1px solid #e6ddd1;
        border-radius:16px;
        padding:14px;
        color:#6f6a61;
        font-size:.84rem;
        line-height:1.7;
        font-weight:700;
    }

    .btn-save-order{
        width:100%;
        border:none;
        border-radius:16px;
        padding:14px 16px;
        font-weight:900;
        color:#fff;
        background:linear-gradient(135deg,#6f7f5f 0%,#8d9d7c 100%);
        box-shadow:0 12px 22px rgba(111,127,95,.16);
    }

    .btn-save-order:hover{
        color:#fff;
        opacity:.97;
    }

    .btn-back-order{
        width:100%;
        border-radius:16px;
        padding:12px 16px;
        font-weight:800;
        border:1px solid #e3d9cc;
        background:#f3eee7;
        color:#443b33;
        text-align:center;
        text-decoration:none;
        transition:.18s ease;
    }

    .btn-back-order:hover{
        color:#302821;
        background:#ebe4da;
    }

    .form-label{
        color:#6f6a61;
        font-weight:800;
        font-size:.82rem;
        margin-bottom:8px;
    }

    .form-select,
    .form-control{
        background:#fffdfa;
        border:1px solid #ddd3c7;
        color:#443b33;
        border-radius:14px;
        min-height:46px;
        font-weight:700;
    }

    textarea.form-control{
        min-height:auto;
    }

    .form-select:focus,
    .form-control:focus{
        background:#fffdfa;
        color:#231f1b;
        border-color:#b9ad9e;
        box-shadow:0 0 0 .2rem rgba(111,127,95,.10);
    }

    .form-control::placeholder,
    textarea.form-control::placeholder{
        color:#9a9084;
    }

    .text-muted{
        color:#8a847a !important;
    }

    .ops-status{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 10px;
        border-radius:999px;
        font-size:.72rem;
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
        padding:6px 10px;
        border-radius:999px;
        font-size:.72rem;
        font-weight:800;
        white-space:nowrap;
    }

    .order-type-delivery{
        background:#eef2ff;
        color:#4f46e5;
    }

    .order-type-pickup{
        background:#ecfdf3;
        color:#15803d;
    }

    @media (max-width: 1199.98px){
        .order-summary-grid{
            grid-template-columns:repeat(2,minmax(0,1fr));
        }
    }

    @media (max-width: 991.98px){
        .order-show-layout{
            grid-template-columns:1fr;
        }

        .sticky-side{
            position:static;
        }
    }

    @media (max-width: 767.98px){
        .order-panel,
        .totals-box{
            border-radius:20px;
        }

        .order-panel-header,
        .order-panel-body{
            padding-left:14px;
            padding-right:14px;
        }

        .order-summary-grid{
            grid-template-columns:1fr;
        }

        .order-info-grid{
            grid-template-columns:1fr;
        }

        .items-desktop-wrap{
            display:none;
        }

        .items-mobile-list{
            display:block;
        }

        .item-mobile-grid{
            grid-template-columns:1fr;
        }

        .totals-box{
            max-width:none;
        }
    }
</style>

<div class="order-show-layout">
    <div>
        <div class="order-panel mb-4">
            <div class="order-panel-header">
                <div>
                    <h2 class="order-panel-title">ملخص الطلب {{ $order->order_number }}</h2>
                    <p class="order-panel-subtitle">عرض شامل لبيانات الطلب والعميل ونوع الخدمة ومعلومات التنفيذ</p>
                </div>
            </div>

            <div class="order-panel-body">
                <div class="order-summary-grid">
                    <div class="order-summary-card">
                        <div class="order-summary-label">رقم الطلب</div>
                        <div class="order-summary-value">{{ $order->order_number }}</div>
                    </div>

                    <div class="order-summary-card">
                        <div class="order-summary-label">الحالة الحالية</div>
                        <div class="order-summary-value">
                            <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    </div>

                    <div class="order-summary-card">
                        <div class="order-summary-label">نوع الطلب</div>
                        <div class="order-summary-value">
                            @if($order->order_type === 'delivery')
                                <span class="order-type-chip order-type-delivery">توصيل إلى العنوان</span>
                            @else
                                <span class="order-type-chip order-type-pickup">استلام من الفرع</span>
                            @endif
                        </div>
                    </div>

                    <div class="order-summary-card">
                        <div class="order-summary-label">إجمالي قيمة الطلب</div>
                        <div class="order-summary-value">{{ number_format($order->total, 2) }} ج.م</div>
                    </div>
                </div>

                <div class="order-info-grid">
                    <div class="order-info-card">
                        <div class="order-info-label">اسم العميل</div>
                        <div class="order-info-value">{{ $order->customer_name }}</div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-label">رقم الهاتف</div>
                        <div class="order-info-value">{{ $order->customer_phone }}</div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-label">طريقة الدفع</div>
                        <div class="order-info-value">{{ $paymentLabel }}</div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-label">تاريخ إنشاء الطلب</div>
                        <div class="order-info-value">{{ $order->created_at?->format('Y-m-d h:i A') }}</div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-label">الفرع المسؤول</div>
                        <div class="order-info-value">{{ $order->branch?->name ?? 'غير محدد' }}</div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-label">عنوان الفرع</div>
                        <div class="order-info-value">{{ $order->branch?->address ?? 'غير متوفر' }}</div>
                    </div>

                    <div class="order-info-card full">
                        <div class="order-info-label">
                            @if($order->order_type === 'delivery')
                                عنوان التوصيل
                            @else
                                بيانات الاستلام
                            @endif
                        </div>
                        <div class="order-info-value">
                            {{ $order->address_line ?: 'لا توجد بيانات مضافة' }}
                            @if($order->area)
                                <div class="mt-1 text-muted fw-semibold">{{ $order->area }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="order-info-card full">
                        <div class="order-info-label">ملاحظات العميل</div>
                        <div class="order-info-value">{{ $order->notes ?: 'لا توجد ملاحظات مضافة من العميل.' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-panel">
            <div class="order-panel-header">
                <div>
                    <h3 class="order-panel-title">عناصر الطلب</h3>
                    <p class="order-panel-subtitle">مراجعة الأصناف المضافة والكميات والأسعار والتفاصيل الإضافية</p>
                </div>
            </div>

            <div class="order-panel-body">
                <div class="items-desktop-wrap">
                    <div class="items-table-wrap">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>الصنف</th>
                                    <th>سعر الوحدة</th>
                                    <th>الكمية</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="item-product-name">{{ $item->product_name }}</div>

                                            @if(!empty($item->selected_options))
                                                <div class="item-options">
                                                    @foreach($item->selected_options as $groupName => $selectedValues)
                                                        <div>
                                                            <strong>{{ $groupName }}:</strong>
                                                            @if(is_array($selectedValues))
                                                                {{ implode('، ', $selectedValues) }}
                                                            @else
                                                                {{ $selectedValues }}
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ number_format($item->price, 2) }} ج.م</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->total, 2) }} ج.م</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">لا توجد أصناف مضافة داخل هذا الطلب.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="items-mobile-list">
                    @forelse($order->items as $item)
                        <div class="item-mobile-card">
                            <div class="item-mobile-title">{{ $item->product_name }}</div>

                            @if(!empty($item->selected_options))
                                <div class="item-options mb-3">
                                    @foreach($item->selected_options as $groupName => $selectedValues)
                                        <div>
                                            <strong>{{ $groupName }}:</strong>
                                            @if(is_array($selectedValues))
                                                {{ implode('، ', $selectedValues) }}
                                            @else
                                                {{ $selectedValues }}
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="item-mobile-grid">
                                <div class="item-mobile-box">
                                    <div class="item-mobile-label">سعر الوحدة</div>
                                    <div class="item-mobile-value">{{ number_format($item->price, 2) }} ج.م</div>
                                </div>

                                <div class="item-mobile-box">
                                    <div class="item-mobile-label">الكمية</div>
                                    <div class="item-mobile-value">{{ $item->quantity }}</div>
                                </div>

                                <div class="item-mobile-box">
                                    <div class="item-mobile-label">الإجمالي</div>
                                    <div class="item-mobile-value">{{ number_format($item->total, 2) }} ج.م</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">لا توجد أصناف مضافة داخل هذا الطلب.</div>
                    @endforelse
                </div>

                <div class="totals-box">
                    <div class="total-row">
                        <span>الإجمالي الفرعي</span>
                        <span>{{ number_format($order->subtotal, 2) }} ج.م</span>
                    </div>

                    <div class="total-row">
                        <span>رسوم التوصيل</span>
                        <span>{{ number_format($order->delivery_fee, 2) }} ج.م</span>
                    </div>

                    <div class="total-row final">
                        <span>الإجمالي النهائي</span>
                        <span>{{ number_format($order->total, 2) }} ج.م</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="order-panel sticky-side">
            <div class="order-panel-header">
                <div>
                    <h3 class="order-panel-title">إدارة حالة الطلب</h3>
                    <p class="order-panel-subtitle">تحديث الحالة الحالية والوقت المتوقع وإضافة ملاحظات تشغيلية واضحة</p>
                </div>
            </div>

            <div class="order-panel-body">
                @php($isDeliveryUser = auth()->user()?->role === \App\Models\User::ROLE_DELIVERY)

                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="side-form-grid">
                    @csrf

                    @unless($isDeliveryUser)
                        <div>
                            <label class="form-label">نوع الطلب</label>
                            <select name="order_type" class="form-select">
                                <option value="delivery" {{ $order->order_type === 'delivery' ? 'selected' : '' }}>توصيل</option>
                                <option value="pickup" {{ $order->order_type === 'pickup' ? 'selected' : '' }}>استلام</option>
                            </select>
                        </div>
                    @endunless

                    <div>
                        <label class="form-label">حالة الطلب</label>
                        <select name="status" class="form-select" required>
                            @if($isDeliveryUser)
                                <option value="out_for_delivery" {{ $order->status === 'out_for_delivery' ? 'selected' : '' }}>خرج للتوصيل</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>تم الإلغاء</option>
                            @else
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                                <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>تم التأكيد</option>
                                <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>قيد التحضير</option>

                                @if($order->order_type === 'delivery')
                                    <option value="out_for_delivery" {{ $order->status === 'out_for_delivery' ? 'selected' : '' }}>خرج للتوصيل</option>
                                @endif

                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>تم الإلغاء</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="form-label">
                            @if($order->order_type === 'delivery' || $isDeliveryUser)
                                الوقت المتوقع للتوصيل بالدقائق
                            @else
                                الوقت المتوقع للاستلام بالدقائق
                            @endif
                        </label>
                        <input
                            type="number"
                            name="estimated_delivery_minutes"
                            class="form-control"
                            min="1"
                            max="300"
                            value="{{ old('estimated_delivery_minutes', $order->estimated_delivery_minutes) }}"
                            placeholder="مثال: 30"
                        >
                    </div>

                    <div>
                        <label class="form-label">ملاحظة تشغيلية</label>
                        <textarea
                            name="status_note"
                            rows="4"
                            class="form-control"
                            placeholder="مثال: تم بدء التحضير / المندوب في الطريق / الطلب جاهز للاستلام"
                        >{{ old('status_note', $order->status_note) }}</textarea>
                    </div>

                    <div class="helper-box">
                        <div class="fw-bold mb-1">موعد التنفيذ المتوقع الحالي</div>
                        <div>
                            {{ $order->estimated_delivery_at ? $order->estimated_delivery_at->format('Y-m-d h:i A') : 'لم يتم تحديد موعد متوقع بعد' }}
                        </div>
                    </div>

                    <button type="submit" class="btn-save-order">حفظ التحديثات</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn-back-order">الرجوع إلى قائمة الطلبات</a>
                </form>

                @unless($isDeliveryUser)
                    <div class="admin-card p-4 mt-4">
                        <h5 class="fw-bold mb-3">إدارة الدليفري</h5>

                        <form action="{{ url('/admin/orders/' . $order->id . '/assign-delivery') }}" method="POST" class="d-flex flex-wrap gap-2 mb-3">
                            @csrf
                            @method('PATCH')

                            <select name="delivery_user_id" class="form-select" style="max-width: 320px;" required>
                                <option value="">اختر الدليفري</option>
                                @foreach($deliveryUsers as $deliveryUser)
                                    <option value="{{ $deliveryUser->id }}" {{ $order->delivery_user_id == $deliveryUser->id ? 'selected' : '' }}>
                                        {{ $deliveryUser->name }} - {{ $deliveryUser->email }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn-admin">إسناد للدليفري</button>
                        </form>

                        @if($order->deliveryUser)
                            <div class="mb-0">
                                <strong>الدليفري الحالي:</strong>
                                {{ $order->deliveryUser->name }} - {{ $order->deliveryUser->email }}
                            </div>
                        @endif
                    </div>
                @endunless
            </div>
        </div>
    </div>
</div>
@endsection
