<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
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
            . "style-src 'self' 'unsafe-inline' https:; "
            . "script-src 'self' 'unsafe-inline' https:; "
            . "connect-src 'self' https: wss:;";

        $response->headers->set('Content-Security-Policy', $csp);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
