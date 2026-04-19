@extends('layouts.admin')

@php
    $title = 'إدارة الاشتراك';
    $pageTitle = 'إدارة الاشتراك';
    $pageSubtitle = 'تحكم واضح وسريع في حالة الاشتراك بدون تعقيد.';

    $currentPlanConfig = $plans[$planSlug] ?? null;
    $currentPlanLabel = $currentPlanConfig['label'] ?? strtoupper((string) $planSlug);

    $formattedStart = $startsAt?->format('Y-m-d h:i A') ?? 'غير محدد';
    $formattedEnd = $endsAt?->format('Y-m-d h:i A') ?? 'غير محدد';
    $remainingLabel = is_null($daysRemaining) ? 'غير محدد' : ($daysRemaining > 0 ? $daysRemaining.' يوم' : 'منتهي');
@endphp

@section('content')
<style>
    .sub-page {max-width: 1100px; margin: 0 auto;}
    .sub-card {border: 1px solid #e5e7eb; border-radius: 16px; background: #fff; box-shadow: 0 8px 22px rgba(15,23,42,.06);}
    .sub-card + .sub-card {margin-top: 1rem;}
    .sub-head {padding: 1rem 1.25rem; border-bottom: 1px solid #eef2f7;}
    .sub-body {padding: 1.25rem;}
    .sub-title {font-size: 1rem; font-weight: 700; margin: 0; color: #0f172a;}
    .sub-muted {color: #64748b; font-size: .88rem;}

    .sub-overview-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(120px, 1fr));
        gap: .9rem;
        align-items: center;
    }
    .sub-overview-item small {display:block; color:#64748b; font-size:.78rem; margin-bottom:.2rem;}
    .sub-overview-item strong {font-size:.98rem; color:#111827;}

    .sub-status {
        display:inline-flex; align-items:center; justify-content:center;
        border-radius:999px; padding:.4rem .8rem; font-size:.82rem; font-weight:700;
        border: 1px solid transparent;
    }
    .sub-status.is-active{background:#ecfdf3;color:#15803d;border-color:#86efac;}
    .sub-status.is-pending{background:#fffbeb;color:#b45309;border-color:#fde68a;}
    .sub-status.is-paused{background:#f8fafc;color:#475569;border-color:#cbd5e1;}
    .sub-status.is-expired{background:#fef2f2;color:#b91c1c;border-color:#fecaca;}
    .sub-status.is-cancelled{background:#7f1d1d;color:#fff;border-color:#7f1d1d;}

    .sub-actions-grid {
        display:grid;
        grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
        gap:.65rem;
    }
    .sub-action-btn {border-radius: 10px; font-weight: 600; padding:.55rem .75rem;}

    .sub-form-grid {display:grid; grid-template-columns: repeat(2, minmax(220px, 1fr)); gap: .9rem;}
    .sub-form-group label {font-weight:600; font-size:.86rem; color:#334155; margin-bottom:.35rem; display:block;}
    .sub-footer-note {margin-top:1rem; padding-top:.8rem; border-top:1px dashed #dbe3ef; color:#64748b; font-size:.84rem;}

    @media (max-width: 992px) {
        .sub-overview-grid {grid-template-columns: repeat(2, minmax(140px, 1fr));}
        .sub-form-grid {grid-template-columns: 1fr;}
    }
</style>

<div class="sub-page">
    <form id="subscriptionControlForm" method="POST" action="{{ route('admin.owner.subscription.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="quick_action" id="quickActionInput">

        {{-- 1) Subscription Overview --}}
        <section class="sub-card">
            <header class="sub-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="sub-title">ملخص الاشتراك</h2>
                    <div class="sub-muted">عرض مختصر للحالة الحالية من أول نظرة.</div>
                </div>
                <span class="sub-status {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
            </header>
            <div class="sub-body">
                <div class="sub-overview-grid">
                    <div class="sub-overview-item">
                        <small>الباقة الحالية</small>
                        <strong>{{ $currentPlanLabel }}</strong>
                    </div>
                    <div class="sub-overview-item">
                        <small>تاريخ البداية</small>
                        <strong>{{ $formattedStart }}</strong>
                    </div>
                    <div class="sub-overview-item">
                        <small>تاريخ النهاية</small>
                        <strong>{{ $formattedEnd }}</strong>
                    </div>
                    <div class="sub-overview-item">
                        <small>الأيام المتبقية</small>
                        <strong>{{ $remainingLabel }}</strong>
                    </div>
                    <div class="sub-overview-item">
                        <small>الوضع الحالي</small>
                        <strong>{{ $isExpired ? 'منتهي' : ($subscriptionStatus === 'paused' ? 'موقوف مؤقتًا' : 'نشط') }}</strong>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2) Primary Actions --}}
        <section class="sub-card">
            <header class="sub-head">
                <h2 class="sub-title">الإجراءات السريعة</h2>
            </header>
            <div class="sub-body">
                <div class="sub-actions-grid">
                    <button type="button" class="btn btn-primary sub-action-btn" data-quick-action="activate_now">تفعيل الآن</button>
                    <button type="button" class="btn btn-outline-primary sub-action-btn" data-quick-action="renew_30">تجديد 30 يوم</button>
                    <button type="button" class="btn btn-outline-primary sub-action-btn" data-quick-action="renew_90">تجديد 90 يوم</button>
                    <button type="button" class="btn btn-outline-primary sub-action-btn" data-quick-action="renew_365">تجديد سنة</button>
                    <button type="button" class="btn btn-outline-secondary sub-action-btn" data-quick-action="pause">إيقاف مؤقت</button>
                    <button type="button" class="btn btn-outline-success sub-action-btn" data-quick-action="reactivate">إعادة التفعيل</button>
                    <button type="button" class="btn btn-outline-danger sub-action-btn" data-quick-action="end_subscription">إنهاء الاشتراك</button>
                </div>
                <div class="sub-footer-note">هذه الأزرار تستخدم نفس منطق الحفظ الخلفي المعتمد في التعديل اليدوي.</div>
            </div>
        </section>

        {{-- 3) Manual Edit --}}
        <section class="sub-card">
            <header class="sub-head">
                <h2 class="sub-title">التعديل اليدوي</h2>
            </header>
            <div class="sub-body">
                <div class="sub-form-grid">
                    <div class="sub-form-group">
                        <label for="plan_slug">الباقة الحالية</label>
                        <select id="plan_slug" name="plan_slug" class="form-select @error('plan_slug') is-invalid @enderror" required>
                            @foreach($plans as $slug => $plan)
                                <option value="{{ $slug }}" @selected(old('plan_slug', $planSlug) === $slug)>
                                    {{ $plan['label'] ?? strtoupper($slug) }} ({{ $slug }})
                                </option>
                            @endforeach
                        </select>
                        @error('plan_slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="sub-form-group">
                        <label for="subscription_status">حالة الاشتراك</label>
                        <select id="subscription_status" name="subscription_status" class="form-select @error('subscription_status') is-invalid @enderror" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(old('subscription_status', $subscriptionStatus) === $status)>
                                    @switch($status)
                                        @case('active') نشط @break
                                        @case('pending') قيد الانتظار @break
                                        @case('paused') موقوف مؤقتًا @break
                                        @case('expired') منتهي @break
                                        @case('cancelled') ملغي @break
                                        @default {{ $status }}
                                    @endswitch
                                </option>
                            @endforeach
                        </select>
                        @error('subscription_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="sub-form-group">
                        <label for="starts_at">تاريخ البداية</label>
                        <input id="starts_at" type="datetime-local" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', optional($startsAt)->format('Y-m-d\\TH:i')) }}">
                        @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="sub-form-group">
                        <label for="ends_at">تاريخ النهاية</label>
                        <input id="ends_at" type="datetime-local" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror" value="{{ old('ends_at', optional($endsAt)->format('Y-m-d\\TH:i')) }}">
                        @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="sub-form-group mt-3">
                    <label for="admin_note">ملاحظات إدارية</label>
                    <textarea id="admin_note" name="admin_note" rows="3" class="form-control @error('admin_note') is-invalid @enderror" placeholder="اكتب ملاحظة مختصرة عند الحاجة...">{{ old('admin_note', $current?->admin_note) }}</textarea>
                    @error('admin_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <button type="submit" class="btn btn-primary px-4">حفظ التعديلات</button>
                    <div class="sub-muted">آخر عملية: {{ $lastActionLabel }} • آخر تحديث: {{ optional($current?->updated_at)->format('Y-m-d h:i A') ?? 'غير متوفر' }}</div>
                </div>
            </div>
        </section>
    </form>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
    (() => {
        const form = document.getElementById('subscriptionControlForm');
        const quickActionInput = document.getElementById('quickActionInput');

        if (!form || !quickActionInput) return;

        document.querySelectorAll('[data-quick-action]').forEach((button) => {
            button.addEventListener('click', () => {
                quickActionInput.value = button.dataset.quickAction;
                form.submit();
            });
        });
    })();
</script>
@endpush
@endsection
