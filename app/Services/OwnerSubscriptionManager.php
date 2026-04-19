<?php

namespace App\Services;

use App\Models\SiteSubscription;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class OwnerSubscriptionManager
{
    public function __construct(protected SubscriptionService $subscriptionService)
    {
    }

    public function updateSubscriptionManually(array $payload, ?int $actorId = null): SiteSubscription
    {
        $attributes = [
            'plan_slug' => $payload['plan_slug'],
            'subscription_status' => $this->normalizeStatus($payload['subscription_status'] ?? 'pending'),
            'updated_by_user_id' => $actorId,
            'last_action' => 'manual_update',
        ];

        if (array_key_exists('starts_at', $payload)) {
            $attributes['starts_at'] = $payload['starts_at'];
        }

        if (array_key_exists('ends_at', $payload)) {
            $attributes['ends_at'] = $payload['ends_at'];
        }

        if (array_key_exists('admin_note', $payload)) {
            $attributes['admin_note'] = $payload['admin_note'];
        }

        return $this->persist($attributes);
    }

    public function applyQuickAction(string $action, string $planSlug, ?string $adminNote = null, ?int $actorId = null): array
    {
        $current = $this->subscriptionService->currentSubscription();

        $startsAt = optional($current)->starts_at;
        $endsAt = optional($current)->ends_at;

        [$payload, $message] = match ($action) {
            'activate_now' => $this->activateSubscription($planSlug, $startsAt, $endsAt),
            'pause' => $this->pauseSubscription($planSlug, $startsAt, $endsAt),
            'end_subscription' => $this->expireSubscription($planSlug, $startsAt),
            'cancel_subscription' => $this->cancelSubscription($planSlug, $startsAt),
            'reactivate' => $this->reactivateSubscription($planSlug, $startsAt, $endsAt),
            'renew_30' => $this->renewSubscription($planSlug, $startsAt, $endsAt, 30, 'renew_30', 'تم تجديد الاشتراك 30 يومًا.'),
            'renew_90' => $this->renewSubscription($planSlug, $startsAt, $endsAt, 90, 'renew_90', 'تم تجديد الاشتراك 90 يومًا.'),
            'renew_365' => $this->renewSubscription($planSlug, $startsAt, $endsAt, 365, 'renew_365', 'تم تجديد الاشتراك سنة كاملة.'),
            'extend_7' => $this->renewSubscription($planSlug, $startsAt, $endsAt, 7, 'extend_7', 'تم تمديد الاشتراك 7 أيام.'),
            'extend_15' => $this->renewSubscription($planSlug, $startsAt, $endsAt, 15, 'extend_15', 'تم تمديد الاشتراك 15 يومًا.'),
            'extend_30' => $this->renewSubscription($planSlug, $startsAt, $endsAt, 30, 'extend_30', 'تم تمديد الاشتراك شهرًا كاملًا.'),
            default => throw new InvalidArgumentException('Unknown subscription quick action: ' . $action),
        };

        if ($adminNote !== null) {
            $payload['admin_note'] = $adminNote;
        }

        if ($actorId !== null) {
            $payload['updated_by_user_id'] = $actorId;
        }

        $this->persist($payload);

        return ['message' => $message];
    }

    public function activateSubscription(string $planSlug, $startsAt, $endsAt): array
    {
        $now = now();
        $durationDays = $this->resolveDurationDays($startsAt, $endsAt);

        return [[
            'plan_slug' => $planSlug,
            'subscription_status' => 'active',
            'starts_at' => $now,
            'ends_at' => $now->copy()->addDays($durationDays),
            'last_action' => 'activate_now',
        ], 'تم تفعيل الاشتراك بنجاح.'];
    }

    public function pauseSubscription(string $planSlug, $startsAt, $endsAt): array
    {
        return [[
            'plan_slug' => $planSlug,
            'subscription_status' => 'suspended',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'last_action' => 'pause',
        ], 'تم إيقاف الاشتراك مؤقتًا.'];
    }

    public function expireSubscription(string $planSlug, $startsAt): array
    {
        return [[
            'plan_slug' => $planSlug,
            'subscription_status' => 'expired',
            'starts_at' => $startsAt,
            'ends_at' => now(),
            'last_action' => 'end_subscription',
        ], 'تم إنهاء الاشتراك.'];
    }

    public function cancelSubscription(string $planSlug, $startsAt): array
    {
        return [[
            'plan_slug' => $planSlug,
            'subscription_status' => 'cancelled',
            'starts_at' => $startsAt,
            'ends_at' => now(),
            'last_action' => 'cancel_subscription',
        ], 'تم إلغاء الاشتراك.'];
    }

    public function reactivateSubscription(string $planSlug, $startsAt, $endsAt): array
    {
        $now = now();

        if ($endsAt instanceof CarbonInterface && $endsAt->isFuture()) {
            return [[
                'plan_slug' => $planSlug,
                'subscription_status' => 'active',
                'starts_at' => $startsAt ?? $now,
                'ends_at' => $endsAt,
                'last_action' => 'reactivate',
            ], 'تمت إعادة تفعيل الاشتراك بنجاح.'];
        }

        return [[
            'plan_slug' => $planSlug,
            'subscription_status' => 'active',
            'starts_at' => $now,
            'ends_at' => $now->copy()->addDays($this->resolveDurationDays($startsAt, $endsAt)),
            'last_action' => 'reactivate',
        ], 'تمت إعادة تفعيل الاشتراك بنجاح.'];
    }

    public function renewSubscription(string $planSlug, $startsAt, $endsAt, int $days, string $action, string $message): array
    {
        $now = now();

        $baseEnd = ($endsAt instanceof CarbonInterface && $endsAt->isFuture())
            ? $endsAt->copy()
            : $now->copy();

        $newStart = ($startsAt instanceof CarbonInterface && $startsAt->isPast())
            ? $startsAt
            : $now;

        return [[
            'plan_slug' => $planSlug,
            'subscription_status' => 'active',
            'starts_at' => $newStart,
            'ends_at' => $baseEnd->addDays($days),
            'last_action' => $action,
        ], $message];
    }

    protected function persist(array $attributes): SiteSubscription
    {
        return $this->subscriptionService->updateCurrentSubscription($attributes);
    }

    protected function resolveDurationDays($startsAt, $endsAt): int
    {
        if ($startsAt instanceof CarbonInterface && $endsAt instanceof CarbonInterface && $endsAt->greaterThan($startsAt)) {
            $days = $startsAt->diffInDays($endsAt);
            if ($days > 0 && $days <= 366) {
                return (int) $days;
            }
        }

        return 30;
    }

    protected function normalizeStatus(string $status): string
    {
        return $status === 'paused' ? 'suspended' : $status;
    }
}
