<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);
        View::share('cspNonce', $nonce);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('X-XSS-Protection', '0');

        $csp = "default-src 'self'; "
            . "base-uri 'self'; "
            . "frame-ancestors 'self'; "
            . "form-action 'self'; "
            . "object-src 'none'; "
            . "img-src 'self' data: https:; "
            . "font-src 'self' data: https:; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com; "
            . "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com; "
            . "connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com wss:; ";

        $response->headers->set('Content-Security-Policy', $csp);

        if ($request->isSecure() || strtolower((string) $request->headers->get('x-forwarded-proto')) === 'https') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
