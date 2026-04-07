<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSessionFingerprint
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Keep fingerprint stable to avoid accidental logout behind proxies/CDN.
            $fingerprint = hash('sha256', strtolower(trim((string) $request->userAgent())));
            $sessionFingerprint = (string) $request->session()->get('session_fingerprint');

            if ($sessionFingerprint === '' || !hash_equals($sessionFingerprint, $fingerprint)) {
                $request->session()->put('session_fingerprint', $fingerprint);
            }
        }

        return $next($request);
    }
}
