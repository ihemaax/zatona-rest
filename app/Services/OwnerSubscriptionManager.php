<?php

namespace App\Services;

use App\Models\SiteSubscription;
use Carbon\CarbonInterface;

class OwnerSubscriptionManager
{
    public function __construct(protected SubscriptionService $subscriptionService)
    {
    }

    public function manualUpdate(array $payload, ?int $actorId = null): SiteSubscription
    {
        return $this->subscriptionService->updateCurrentSubscription([
            'plan_slug' => $payload['plan_slug'],
            'subscription_status' => $this->normalizeStatus($payload['subscription_status'] ?? 'pending'),
            'starts_at' => $payload['starts_at'] ?? null,
            'ends_at' => $payload['ends_at'] ?? null,
            'admin_note' => $payload['admin_note'] ?? null,
            'updated_by_user_id' => $actorId,
        ]);
    }

    public function runQuickAction(string $action, string $planSlug, ?string $adminNote = null, ?int $actorId = null): array
    {
        $current = $this->subscriptionService->currentSubscription();
        $now = now();

        $startsAt = optional($current)->starts_at;
        $endsAt = optional($current)->ends_at;
        $status = $this->normalizeStatus((string) (optional($current)->subscription_status ?? 'pending'));

        [$startsAt, $endsAt, $status, $message] = match ($action) {
            'activate_now' => $this->activateNow($startsAt, $endsAt),
            'pause' => [$startsAt, $endsAt, 'suspended', 'تم إيقاف الاشتراك مؤقتًا.'],
            'end_subscription' => [$startsAt, $now, 'expired', 'تم إنهاء الاشتراك.'],
            'reactivate' => $this->reactivate($startsAt, $endsAt),
            'renew_30' => $this->extend($startsAt, $endsAt, 30, 'تم تجديد الاشتراك 30 يومًا.'),
            'renew_90' => $this->extend($startsAt, $endsAt, 90, 'تم تجديد الاشتراك 90 يومًا.'),
            'renew_365' => $this->extend($startsAt, $endsAt, 365, 'تم تجديد الاشتراك 365 يومًا.'),
            'extend_7' => $this->extend($startsAt, $endsAt, 7, 'تم تمديد الاشتراك 7 أيام.'),
            'extend_15' => $this->extend($startsAt, $endsAt, 15, 'تم تمديد الاشتراك 15 يومًا.'),
            'extend_30' => $this->extend($startsAt, $endsAt, 30, 'تم تمديد الاشتراك شهرًا كاملًا.'),
            default => [$startsAt, $endsAt, $status, 'تم تحديث الاشتراك.'],
        };

        $this->subscriptionService->updateCurrentSubscription([
            'plan_slug' => $planSlug,
            'subscription_status' => $status,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'admin_note' => $adminNote,
            'updated_by_user_id' => $actorId,
        ]);

        return ['message' => $message];
    }

    protected function activateNow($startsAt, $endsAt): array
    {
        $now = now();
        $durationDays = $this->resolveDurationDays($startsAt, $endsAt);

        return [$now, $now->copy()->addDays($durationDays), 'active', 'تم تفعيل الاشتراك بنجاح.'];
    }

    protected function reactivate($startsAt, $endsAt): array
    {
        $now = now();

        if ($endsAt instanceof CarbonInterface && $endsAt->isFuture()) {
            return [$startsAt ?? $now, $endsAt, 'active', 'تمت إعادة تفعيل الاشتراك بنجاح.'];
        }

        return [$now, $now->copy()->addDays($this->resolveDurationDays($startsAt, $endsAt)), 'active', 'تمت إعادة تفعيل الاشتراك بنجاح.'];
    }

    protected function extend($startsAt, $endsAt, int $days, string $message): array
    {
        $now = now();

        $baseEnd = ($endsAt instanceof CarbonInterface && $endsAt->isFuture())
            ? $endsAt->copy()
            : $now->copy();

        $newStart = ($startsAt instanceof CarbonInterface && $startsAt->isPast())
            ? $startsAt
            : $now;

        return [$newStart, $baseEnd->addDays($days), 'active', $message];
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
