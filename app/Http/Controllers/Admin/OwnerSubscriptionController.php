<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OwnerSubscriptionUpdateRequest;
use App\Services\OwnerSubscriptionManager;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class OwnerSubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected OwnerSubscriptionManager $ownerSubscriptionManager,
    ) {
    }

    public function edit()
    {
        $current = $this->subscriptionService->currentSubscription()?->load('updatedBy');
        $planSlug = $this->subscriptionService->currentPlan();
        $status = $this->subscriptionService->subscriptionStatus();

        $statusUi = $status === 'suspended' ? 'paused' : $status;
        $startsAt = optional($current)->starts_at;
        $endsAt = $this->subscriptionService->subscriptionEndsAt();
        $now = now();

        $daysRemaining = null;
        if ($endsAt instanceof CarbonInterface) {
            $daysRemaining = $endsAt->isFuture() ? $now->diffInDays($endsAt) : 0;
        }

        $statusBadgeMap = [
            'active' => ['label' => 'نشط', 'class' => 'is-active'],
            'pending' => ['label' => 'قيد الانتظار', 'class' => 'is-pending'],
            'paused' => ['label' => 'موقوف مؤقتًا', 'class' => 'is-paused'],
            'expired' => ['label' => 'منتهي', 'class' => 'is-expired'],
            'cancelled' => ['label' => 'ملغي', 'class' => 'is-cancelled'],
        ];

        $statusMeta = $statusBadgeMap[$statusUi] ?? ['label' => $statusUi, 'class' => 'is-pending'];

        $actionLabels = [
            'activate_now' => 'تفعيل الآن',
            'pause' => 'إيقاف مؤقت',
            'end_subscription' => 'إنهاء الاشتراك',
            'cancel_subscription' => 'إلغاء الاشتراك',
            'reactivate' => 'إعادة التفعيل',
            'renew_30' => 'تجديد 30 يوم',
            'renew_90' => 'تجديد 90 يوم',
            'renew_365' => 'تجديد سنة',
            'extend_7' => 'تمديد 7 أيام',
            'extend_15' => 'تمديد 15 يوم',
            'extend_30' => 'تمديد شهر',
            'manual_update' => 'تعديل يدوي',
        ];

        return view('admin.owner-subscription.edit', [
            'current' => $current,
            'planSlug' => $planSlug,
            'subscriptionStatus' => $statusUi,
            'statusMeta' => $statusMeta,
            'plans' => (array) config('subscription.plans', []),
            'statuses' => ['active', 'pending', 'paused', 'expired', 'cancelled'],
            'planFeatures' => $this->subscriptionService->currentEnabledFeatures(),
            'planLimits' => $this->subscriptionService->planLimits($planSlug),
            'startsAt' => $startsAt,
            'endsAt' => $endsAt,
            'daysRemaining' => $daysRemaining,
            'isExpired' => $statusUi === 'expired' || ($endsAt instanceof CarbonInterface && $endsAt->isPast()),
            'lastActionLabel' => $actionLabels[(string) ($current?->last_action ?? '')] ?? 'غير متوفر',
        ]);
    }

    public function update(OwnerSubscriptionUpdateRequest $request)
    {
        $data = $request->validated();

        $actorId = auth()->id();
        $quickAction = $data['quick_action'] ?? null;

        if ($quickAction) {
            $result = $this->ownerSubscriptionManager->applyQuickAction(
                action: $quickAction,
                planSlug: $data['plan_slug'],
                adminNote: $data['admin_note'] ?? null,
                actorId: $actorId,
            );

            return redirect()->route('admin.owner.subscription.edit')->with('success', $result['message']);
        }

        $startsAt = !empty($data['starts_at']) ? Carbon::parse($data['starts_at']) : null;
        $endsAt = !empty($data['ends_at']) ? Carbon::parse($data['ends_at']) : null;

        $this->ownerSubscriptionManager->updateSubscriptionManually([
            'plan_slug' => $data['plan_slug'],
            'subscription_status' => $data['subscription_status'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'admin_note' => $data['admin_note'] ?? null,
        ], $actorId);

        return redirect()
            ->route('admin.owner.subscription.edit')
            ->with('success', 'تم حفظ إعدادات الاشتراك بنجاح.');
    }
}
