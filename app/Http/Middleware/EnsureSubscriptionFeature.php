<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionFeature
{
    public function __construct(protected SubscriptionService $subscriptionService)
    {
    }

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($this->subscriptionService->featureEnabled($feature)) {
            return $next($request);
        }

        $message = (string) config('subscription.blocked_message', 'هذه الميزة غير متاحة في الباقة الحالية.');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->back()->with('error', $message);
    }
}
