@extends('layouts.admin')

@php
    $title = 'Owner Subscription Control';
    $pageTitle = 'Subscription Control';
    $pageSubtitle = 'إدارة الاشتراك الحالي للموقع (Owner Only)';
@endphp

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">الحالة الحالية</h2>
            <div class="row g-3">
                <div class="col-md-3"><div class="p-3 bg-light rounded-3"><small class="text-muted d-block">الباقة الحالية</small><strong>{{ $planSlug }}</strong></div></div>
                <div class="col-md-3"><div class="p-3 bg-light rounded-3"><small class="text-muted d-block">الحالة الفعلية</small><strong>{{ $subscriptionStatus }}</strong></div></div>
                <div class="col-md-3"><div class="p-3 bg-light rounded-3"><small class="text-muted d-block">starts_at</small><strong>{{ optional($current?->starts_at)->format('Y-m-d H:i') ?? '-' }}</strong></div></div>
                <div class="col-md-3"><div class="p-3 bg-light rounded-3"><small class="text-muted d-block">ends_at</small><strong>{{ optional($endsAt)->format('Y-m-d H:i') ?? '-' }}</strong></div></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">تعديل الاشتراك الحالي</h2>
            <form method="POST" action="{{ route('admin.owner.subscription.update') }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">plan_slug</label>
                    <select name="plan_slug" class="form-select" required>
                        @foreach($plans as $plan)
                            <option value="{{ $plan }}" @selected(old('plan_slug', $planSlug) === $plan)>{{ $plan }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">subscription_status</label>
                    <select name="subscription_status" class="form-select" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('subscription_status', $current?->subscription_status ?? $subscriptionStatus) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">starts_at</label>
                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($current?->starts_at)->format('Y-m-d\\TH:i')) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">ends_at</label>
                    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($current?->ends_at)->format('Y-m-d\\TH:i')) }}">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary px-4">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Features المفعلة فعليًا</h2>
            @if(empty($planFeatures))
                <p class="text-muted mb-0">لا توجد features مفعلة لهذه الباقة.</p>
            @else
                <div class="d-flex flex-wrap gap-2">
                    @foreach($planFeatures as $feature)
                        <span class="badge text-bg-success">{{ $feature }}</span>
                    @endforeach
                </div>
            @endif

            <hr class="my-4">

            <h3 class="h6 mb-2">limits</h3>
            @if(empty($planLimits))
                <p class="text-muted mb-0">لا توجد limits معرفة حاليًا.</p>
            @else
                <ul class="mb-0">
                    @foreach($planLimits as $key => $value)
                        <li><strong>{{ $key }}:</strong> {{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
