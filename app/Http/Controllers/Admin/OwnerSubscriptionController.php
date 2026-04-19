<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OwnerSubscriptionManager;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        ]);
    }

    public function update(Request $request)
    {
        $plans = array_keys((array) config('subscription.plans', []));

        $data = $request->validate([
            'plan_slug' => ['required', 'string', Rule::in($plans)],
            'subscription_status' => ['required', 'string', Rule::in(['active', 'pending', 'paused', 'expired', 'cancelled'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'quick_action' => ['nullable', 'string', Rule::in([
                'activate_now',
                'pause',
                'end_subscription',
                'reactivate',
                'renew_30',
                'renew_90',
                'renew_365',
                'extend_7',
                'extend_15',
                'extend_30',
            ])],
        ]);

        $actorId = auth()->id();
        $quickAction = $data['quick_action'] ?? null;

        if ($quickAction) {
            $result = $this->ownerSubscriptionManager->runQuickAction(
                action: $quickAction,
                planSlug: $data['plan_slug'],
                adminNote: $data['admin_note'] ?? null,
                actorId: $actorId,
            );

            return redirect()->route('admin.owner.subscription.edit')->with('success', $result['message']);
        }

        $startsAt = !empty($data['starts_at']) ? Carbon::parse($data['starts_at']) : null;
        $endsAt = !empty($data['ends_at']) ? Carbon::parse($data['ends_at']) : null;

        $this->ownerSubscriptionManager->manualUpdate([
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
