<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production')) {
            $forwardedProto = strtolower((string) $request->headers->get('x-forwarded-proto', ''));
            $isHttps = $request->isSecure() || $forwardedProto === 'https';

            if (!$isHttps) {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        return $next($request);
    }
}
