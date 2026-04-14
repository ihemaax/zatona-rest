<?php

namespace App\Services;

use App\Models\SiteSubscription;
use Carbon\CarbonInterface;

class SubscriptionService
{
    protected ?SiteSubscription $cachedSubscription = null;

    public function currentSubscription(): ?SiteSubscription
    {
        if ($this->cachedSubscription !== null) {
            return $this->cachedSubscription;
        }

        $this->cachedSubscription = SiteSubscription::query()
            ->where('is_current', true)
            ->latest('id')
            ->first();

        return $this->cachedSubscription;
    }

    public function currentPlan(): string
    {
        $plan = (string) optional($this->currentSubscription())->plan_slug;

        if ($plan !== '' && $this->planExists($plan)) {
            return $plan;
        }

        return (string) config('subscription.default_plan', 'menu');
    }

    public function subscriptionStatus(): string
    {
        return $this->resolveStatus($this->currentSubscription());
    }

    public function subscriptionActive(): bool
    {
        return $this->subscriptionStatus() === 'active';
    }

    public function subscriptionEndsAt(): ?CarbonInterface
    {
        return optional($this->currentSubscription())->ends_at;
    }

    public function featureEnabled(string $feature): bool
    {
        if ($this->subscriptionActive()) {
            return $this->planHasFeature($feature);
        }

        return in_array($feature, (array) config('subscription.grace_features_when_inactive', []), true)
            && $this->planHasFeature($feature);
    }

    public function planHasFeature(string $feature, ?string $planSlug = null): bool
    {
        if (!$this->featureExists($feature)) {
            return false;
        }

        $features = $this->resolvedFeatures($planSlug ?? $this->currentPlan());

        return in_array($feature, $features, true);
    }

    public function planLimits(?string $planSlug = null): array
    {
        $planSlug ??= $this->currentPlan();

        $planLimits = (array) data_get(config('subscription.plans'), $planSlug . '.limits', []);

        if ($planSlug !== $this->currentPlan()) {
            return $planLimits;
        }

        $overrides = (array) optional($this->currentSubscription())->limits;

        return array_merge($planLimits, $overrides);
    }

    protected function resolveStatus(?SiteSubscription $subscription): string
    {
        if (!$subscription) {
            return (string) config('subscription.fallback_status', 'pending');
        }

        $status = (string) $subscription->subscription_status;

        if (!in_array($status, (array) config('subscription.statuses', []), true)) {
            return (string) config('subscription.fallback_status', 'pending');
        }

        if (in_array($status, ['suspended', 'cancelled'], true)) {
            return $status;
        }

        if ($status === 'active') {
            if ($subscription->starts_at && now()->lt($subscription->starts_at)) {
                return 'pending';
            }

            if ($subscription->ends_at && now()->gt($subscription->ends_at)) {
                return 'expired';
            }

            return 'active';
        }

        if ($status === 'pending' && $subscription->starts_at && now()->gte($subscription->starts_at)) {
            if (!$subscription->ends_at || now()->lte($subscription->ends_at)) {
                return 'active';
            }

            return 'expired';
        }

        return $status;
    }

    protected function resolvedFeatures(string $planSlug): array
    {
        $planFeatures = (array) data_get(config('subscription.plans'), $planSlug . '.features', []);

        if ($planSlug !== $this->currentPlan()) {
            return $planFeatures;
        }

        $overrides = (array) optional($this->currentSubscription())->features;

        if ($overrides === []) {
            return $planFeatures;
        }

        $merged = array_fill_keys($planFeatures, true);

        foreach ($overrides as $feature => $enabled) {
            if (!$this->featureExists((string) $feature)) {
                continue;
            }

            $merged[(string) $feature] = (bool) $enabled;
        }

        return array_keys(array_filter($merged, fn (bool $enabled) => $enabled));
    }

    protected function planExists(string $planSlug): bool
    {
        return array_key_exists($planSlug, (array) config('subscription.plans', []));
    }

    protected function featureExists(string $feature): bool
    {
        return in_array($feature, (array) config('subscription.features', []), true);
    }
}
