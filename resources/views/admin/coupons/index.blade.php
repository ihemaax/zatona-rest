@extends('layouts.admin')

@php
    $pageTitle = 'كوبونات الخصم';
    $pageSubtitle = 'إدارة أكواد الخصم وتفعيلها للعملاء';
@endphp

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="admin-card p-4 h-100">
            <div class="section-title">إنشاء كوبون</div>
            <div class="section-subtitle">أضف كود خصم جديد لاستخدامه في صفحة الشيك أوت</div>

            <form action="{{ route('admin.coupons.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label fw-bold">الكود</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" required placeholder="WELCOME10">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold">النوع</label>
                    <select name="type" class="form-select" required>
                        <option value="fixed">مبلغ ثابت</option>
                        <option value="percent">نسبة %</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold">القيمة</label>
                    <input type="number" step="0.01" min="0.01" name="value" class="form-control" value="{{ old('value') }}" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold">حد أدنى للطلب</label>
                    <input type="number" step="0.01" min="0" name="min_order_total" class="form-control" value="{{ old('min_order_total', 0) }}">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold">حد أقصى للخصم</label>
                    <input type="number" step="0.01" min="0" name="max_discount" class="form-control" value="{{ old('max_discount') }}">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold">حد الاستخدام</label>
                    <input type="number" min="1" name="usage_limit" class="form-control" value="{{ old('usage_limit') }}">
                </div>
                <div class="col-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">نشط</label>
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold">بداية التفعيل</label>
                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold">نهاية التفعيل</label>
                    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                </div>
                <div class="col-12">
                    <button class="btn-admin" type="submit">إنشاء الكوبون</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="admin-card p-4">
            <div class="section-title">الكوبونات الحالية</div>
            <div class="section-subtitle">تحديث أو تعطيل أو حذف الكوبونات</div>

            <div class="admin-table-wrap">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>الخصم</th>
                            <th>الاستخدام</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td class="fw-bold">{{ $coupon->code }}</td>
                            <td>
                                {{ $coupon->type === 'percent' ? rtrim(rtrim(number_format($coupon->value, 2), '0'), '.') . '%' : number_format($coupon->value, 2) . ' ج.م' }}
                                <div class="small text-muted">الحد الأدنى: {{ number_format($coupon->min_order_total, 2) }} ج.م</div>
                            </td>
                            <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</td>
                            <td>
                                @if($coupon->is_active)
                                    <span class="order-type-chip order-type-pickup">نشط</span>
                                @else
                                    <span class="status-chip status-cancelled">معطل</span>
                                @endif
                            </td>
                            <td style="min-width: 280px;">
                                <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="row g-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-4">
                                        <select name="type" class="form-select form-select-sm">
                                            <option value="fixed" @selected($coupon->type === 'fixed')>ثابت</option>
                                            <option value="percent" @selected($coupon->type === 'percent')>نسبة</option>
                                        </select>
                                    </div>
                                    <div class="col-4"><input type="number" step="0.01" min="0.01" class="form-control form-control-sm" name="value" value="{{ $coupon->value }}" required></div>
                                    <div class="col-4">
                                        <select name="is_active" class="form-select form-select-sm">
                                            <option value="1" @selected($coupon->is_active)>نشط</option>
                                            <option value="0" @selected(!$coupon->is_active)>معطل</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-sm btn-outline-secondary w-100" type="submit">حفظ</button>
                                    </div>
                                </form>
                                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="mt-2" onsubmit="return confirm('حذف الكوبون؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger w-100" type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">لا توجد كوبونات حالياً.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $coupons->links() }}</div>
        </div>
    </div>
</div>
@endsection
