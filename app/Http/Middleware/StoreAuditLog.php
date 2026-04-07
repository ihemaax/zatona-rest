<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreAuditLog
{
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        '_token',
        'token',
        'otp_code',
        'remember_token',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldSkip($request)) {
            return $response;
        }

        $user = $request->user();

        AuditLog::query()->create([
            'user_id' => $user?->id,
            'user_type' => $user?->user_type,
            'user_role' => $user?->role,
            'user_email' => $user?->email,
            'event' => $this->resolveEvent($request),
            'method' => $request->method(),
            'path' => '/' . ltrim($request->path(), '/'),
            'route_name' => $request->route()?->getName(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => $response->getStatusCode(),
            'metadata' => [
                'query' => $request->query(),
                'payload' => $this->sanitizePayload($request->all()),
                'referer' => $request->headers->get('referer'),
            ],
        ]);

        return $response;
    }

    protected function shouldSkip(Request $request): bool
    {
        if ($request->is('up') || $request->is('build/*') || $request->is('storage/*')) {
            return true;
        }

        return false;
    }

    protected function resolveEvent(Request $request): string
    {
        return match (true) {
            $request->routeIs('login') && $request->isMethod('POST') => 'auth.login',
            $request->routeIs('logout') && $request->isMethod('POST') => 'auth.logout',
            str_starts_with((string) $request->route()?->getName(), 'admin.') => 'admin.action',
            default => 'http_request',
        };
    }

    protected function sanitizePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (in_array($key, $this->sensitiveFields, true)) {
                $payload[$key] = '[FILTERED]';
                continue;
            }

            if (is_array($value)) {
                $payload[$key] = $this->sanitizePayload($value);
                continue;
            }

            if (is_object($value)) {
                $payload[$key] = '[OBJECT:' . $value::class . ']';
                continue;
            }

            if (is_string($value) && mb_strlen($value) > 2000) {
                $payload[$key] = mb_substr($value, 0, 2000) . '...';
            }
        }

        return $payload;
    }

}
