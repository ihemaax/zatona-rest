@extends('layouts.admin')

@php
    $title = 'إدارة اشتراك العميل';
    $pageTitle = 'إدارة الاشتراك';
    $pageSubtitle = 'تحكم سريع وذكي في حالة الاشتراك وتجديداته من شاشة واحدة.';

    $currentPlanConfig = $plans[$planSlug] ?? null;
    $currentPlanLabel = $currentPlanConfig['label'] ?? strtoupper((string) $planSlug);

    $formattedStart = $startsAt?->format('Y-m-d h:i A') ?? 'غير محدد';
    $formattedEnd = $endsAt?->format('Y-m-d h:i A') ?? 'غير محدد';

    $remainingLabel = is_null($daysRemaining)
        ? 'غير محدد'
        : ($daysRemaining > 0 ? $daysRemaining.' يوم' : 'انتهى');
@endphp

@section('content')
<style>
    .subx-summary{
        border:none;
        border-radius:1.2rem;
        padding:1.5rem;
        background:linear-gradient(135deg,#162034,#24385f);
        color:#fff;
        box-shadow:0 20px 40px rgba(15,23,42,.2);
    }
    .subx-status-badge{
        display:inline-flex;
        align-items:center;
        padding:.45rem .9rem;
        border-radius:999px;
        font-size:.85rem;
        font-weight:700;
        color:#fff;
    }
    .subx-status-badge.is-active{background:#16a34a;}
    .subx-status-badge.is-pending{background:#f59e0b;color:#111827;}
    .subx-status-badge.is-paused{background:#9a6b18;}
    .subx-status-badge.is-expired{background:#dc2626;}
    .subx-status-badge.is-cancelled{background:#7f1d1d;}
    .subx-metric-card{
        border:none;
        border-radius:1rem;
        background:#fff;
        box-shadow:0 12px 28px rgba(15,23,42,.08);
        padding:1rem;
        height:100%;
    }
    .subx-metric-label{font-size:.8rem;color:#6b7280;margin-bottom:.25rem;display:block;}
    .subx-metric-value{font-size:1.1rem;font-weight:700;color:#111827;}
    .subx-card{border:none;border-radius:1rem;box-shadow:0 10px 24px rgba(15,23,42,.08);}
    .subx-card .card-header{background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:1rem 1.25rem;}
    .subx-card .card-body{padding:1.25rem;}
    .subx-actions-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.75rem;}
    .subx-action-btn{width:100%;font-weight:700;border-radius:.7rem;padding:.65rem .75rem;}
    .subx-inline-actions{display:flex;flex-wrap:wrap;gap:.5rem;}
    .subx-inline-actions button{border-radius:.65rem;}
    .subx-footer-note{font-size:.85rem;color:#64748b;}
    .subx-feature-badge{background:#ecfeff;color:#0f766e;border:1px solid #99f6e4;border-radius:999px;padding:.32rem .65rem;font-weight:600;font-size:.78rem;}
    .subx-limit-list li{padding:.35rem 0;border-bottom:1px dashed #e5e7eb;}
    .subx-limit-list li:last-child{border-bottom:none;}
</style>

<div class="container-fluid">
    <div class="subx-summary mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
            <div>
                <div class="mb-2 text-white-50">الخطة الحالية</div>
                <h2 class="h3 mb-2">{{ $currentPlanLabel }}</h2>
                <div class="subx-status-badge {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</div>
            </div>
            <div class="text-lg-end">
                <div class="mb-1">متبقي: <strong>{{ $remainingLabel }}</strong></div>
                <div class="mb-1">تاريخ البداية: <strong>{{ $formattedStart }}</strong></div>
                <div>تاريخ النهاية: <strong>{{ $formattedEnd }}</strong></div>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-3">
                <div class="subx-metric-card">
                    <span class="subx-metric-label">حالة الاشتراك</span>
                    <div class="subx-metric-value">{{ $statusMeta['label'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="subx-metric-card">
                    <span class="subx-metric-label">هل الاشتراك منتهي؟</span>
                    <div class="subx-metric-value">{{ $isExpired ? 'نعم' : 'لا' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="subx-metric-card">
                    <span class="subx-metric-label">آخر تحديث</span>
                    <div class="subx-metric-value">{{ optional($current?->updated_at)->format('Y-m-d h:i A') ?? 'غير متوفر' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="subx-metric-card">
                    <span class="subx-metric-label">آخر منفذ للتعديل</span>
                    <div class="subx-metric-value">{{ $current?->updatedBy?->name ?? 'غير متوفر' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <form id="subscriptionControlForm" method="POST" action="{{ route('admin.owner.subscription.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="quick_action" id="quickActionInput">

                <div class="card subx-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h3 class="h6 mb-1">إجراءات سريعة</h3>
                            <p class="text-muted mb-0">نفّذ أوامر الاشتراك اليومية بضغطة واحدة.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="subx-actions-grid">
                            <button type="button" class="btn btn-success subx-action-btn" data-quick-action="activate_now">تفعيل الاشتراك الآن</button>
                            <button type="button" class="btn btn-warning subx-action-btn" data-quick-action="pause">إيقاف الاشتراك مؤقتًا</button>
                            <button type="button" class="btn btn-danger subx-action-btn" data-quick-action="end_subscription">إنهاء الاشتراك</button>
                            <button type="button" class="btn btn-outline-success subx-action-btn" data-quick-action="reactivate">إعادة التفعيل</button>
                            <button type="button" class="btn btn-outline-primary subx-action-btn" data-quick-action="renew_30">تجديد 30 يوم</button>
                            <button type="button" class="btn btn-outline-primary subx-action-btn" data-quick-action="renew_90">تجديد 90 يوم</button>
                            <button type="button" class="btn btn-outline-primary subx-action-btn" data-quick-action="renew_365">تجديد 365 يوم</button>
                            <button type="button" class="btn btn-outline-secondary subx-action-btn" data-quick-action="extend_7">تمديد 7 أيام</button>
                            <button type="button" class="btn btn-outline-secondary subx-action-btn" data-quick-action="extend_15">تمديد 15 يوم</button>
                            <button type="button" class="btn btn-outline-secondary subx-action-btn" data-quick-action="extend_30">تمديد شهر</button>
                        </div>
                        <div class="subx-footer-note mt-3">كل إجراء ينفذ فورًا مع حفظ التعديلات ورسالة نجاح واضحة.</div>
                    </div>
                </div>

                <div class="card subx-card mb-4">
                    <div class="card-header">
                        <h3 class="h6 mb-1">إدارة مرنة للحالة والتواريخ</h3>
                        <p class="text-muted mb-0">يمكنك تعديل القيم يدويًا عند الحاجة، أو استخدام الأزرار الذكية لتسريع العمل.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الباقة الحالية</label>
                                <select name="plan_slug" class="form-select @error('plan_slug') is-invalid @enderror" required>
                                    @foreach($plans as $slug => $plan)
                                        <option value="{{ $slug }}" @selected(old('plan_slug', $planSlug) === $slug)>
                                            {{ $plan['label'] ?? strtoupper($slug) }} ({{ $slug }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">حالة الاشتراك</label>
                                <select name="subscription_status" class="form-select @error('subscription_status') is-invalid @enderror" required>
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

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تاريخ البداية</label>
                                <input type="datetime-local" name="starts_at" id="startsAtInput" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', optional($startsAt)->format('Y-m-d\\TH:i')) }}">
                                @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تاريخ النهاية</label>
                                <input type="datetime-local" name="ends_at" id="endsAtInput" class="form-control @error('ends_at') is-invalid @enderror" value="{{ old('ends_at', optional($endsAt)->format('Y-m-d\\TH:i')) }}">
                                @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="subx-inline-actions mt-3">
                            <button type="button" class="btn btn-sm btn-outline-dark" data-fill-start-now>يبدأ الآن</button>
                            <button type="button" class="btn btn-sm btn-outline-dark" data-fill-end-days="30">ينتهي بعد 30 يوم</button>
                            <button type="button" class="btn btn-sm btn-outline-dark" data-fill-end-days="90">ينتهي بعد 3 شهور</button>
                            <button type="button" class="btn btn-sm btn-outline-dark" data-fill-end-days="365">ينتهي بعد سنة</button>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-semibold">ملاحظات إدارية</label>
                            <textarea name="admin_note" rows="3" class="form-control @error('admin_note') is-invalid @enderror" placeholder="اكتب ملاحظة توضح سبب التعديل أو قرار الإدارة...">{{ old('admin_note', $current?->admin_note) }}</textarea>
                            @error('admin_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-primary px-4">حفظ التغييرات يدويًا</button>
                            <span class="text-muted align-self-center">استخدم هذا الزر فقط عند التعديل اليدوي للحالة أو التواريخ.</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-xl-4">
            <div class="card subx-card mb-4">
                <div class="card-header">
                    <h3 class="h6 mb-1">الميزات المفعلة حاليًا</h3>
                </div>
                <div class="card-body">
                    @if(empty($planFeatures))
                        <p class="text-muted mb-0">لا توجد ميزات مفعلة لهذه الباقة.</p>
                    @else
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($planFeatures as $feature)
                                <span class="subx-feature-badge">{{ $feature }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card subx-card mb-4">
                <div class="card-header">
                    <h3 class="h6 mb-1">حدود الباقة</h3>
                </div>
                <div class="card-body">
                    @if(empty($planLimits))
                        <p class="text-muted mb-0">لا توجد حدود معرفة للباقة الحالية.</p>
                    @else
                        <ul class="list-unstyled mb-0 subx-limit-list">
                            @foreach($planLimits as $key => $value)
                                <li><strong>{{ $key }}</strong>: {{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card subx-card">
                <div class="card-header">
                    <h3 class="h6 mb-1">سجل آخر نشاط</h3>
                </div>
                <div class="card-body">
                    <div class="mb-2"><span class="text-muted">آخر تعديل:</span> {{ optional($current?->updated_at)->format('Y-m-d h:i A') ?? 'غير متوفر' }}</div>
                    <div class="mb-2"><span class="text-muted">تم بواسطة:</span> {{ $current?->updatedBy?->name ?? 'غير متوفر' }}</div>
                    <div><span class="text-muted">الملاحظة:</span> {{ $current?->admin_note ?: 'لا توجد ملاحظات.' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
    (() => {
        const form = document.getElementById('subscriptionControlForm');
        const quickActionInput = document.getElementById('quickActionInput');
        const startsAtInput = document.getElementById('startsAtInput');
        const endsAtInput = document.getElementById('endsAtInput');

        if (!form || !quickActionInput) {
            return;
        }

        const toLocalDatetime = (date) => {
            const pad = (n) => String(n).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        };

        document.querySelectorAll('[data-quick-action]').forEach((button) => {
            button.addEventListener('click', () => {
                quickActionInput.value = button.dataset.quickAction;
                form.submit();
            });
        });

        document.querySelector('[data-fill-start-now]')?.addEventListener('click', () => {
            if (!startsAtInput) return;
            const now = new Date();
            startsAtInput.value = toLocalDatetime(now);
        });

        document.querySelectorAll('[data-fill-end-days]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!endsAtInput) return;
                const now = new Date();
                const days = Number(button.dataset.fillEndDays || 0);
                now.setDate(now.getDate() + days);
                endsAtInput.value = toLocalDatetime(now);
            });
        });
    })();
</script>
@endpush
@endsection
