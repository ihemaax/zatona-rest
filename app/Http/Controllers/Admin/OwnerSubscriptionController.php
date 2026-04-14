<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OwnerSubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptionService)
    {
    }

    public function edit()
    {
        $current = $this->subscriptionService->currentSubscription();
        $planSlug = $this->subscriptionService->currentPlan();

        return view('admin.owner-subscription.edit', [
            'current' => $current,
            'planSlug' => $planSlug,
            'subscriptionStatus' => $this->subscriptionService->subscriptionStatus(),
            'plans' => array_keys((array) config('subscription.plans', [])),
            'statuses' => (array) config('subscription.statuses', []),
            'planFeatures' => $this->subscriptionService->currentEnabledFeatures(),
            'planLimits' => $this->subscriptionService->planLimits($planSlug),
            'endsAt' => $this->subscriptionService->subscriptionEndsAt(),
        ]);
    }

    public function update(Request $request)
    {
        $plans = array_keys((array) config('subscription.plans', []));
        $statuses = (array) config('subscription.statuses', []);

        $data = $request->validate([
            'plan_slug' => ['required', 'string', Rule::in($plans)],
            'subscription_status' => ['required', 'string', Rule::in($statuses)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->subscriptionService->updateCurrentSubscription([
            'plan_slug' => $data['plan_slug'],
            'subscription_status' => $data['subscription_status'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.owner.subscription.edit')
            ->with('success', 'تم تحديث الاشتراك الحالي بنجاح.');
    }
}
