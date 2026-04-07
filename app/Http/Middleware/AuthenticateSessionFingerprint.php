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
            $fingerprint = hash('sha256', sprintf('%s|%s', $request->userAgent(), $request->ip()));
            $sessionFingerprint = (string) $request->session()->get('session_fingerprint');

            if ($sessionFingerprint !== '' && !hash_equals($sessionFingerprint, $fingerprint)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                abort(419, __('Session expired. Please login again.'));
            }

            $request->session()->put('session_fingerprint', $fingerprint);

            $lastRotationAt = (int) $request->session()->get('session_rotated_at', 0);
            $rotateEverySeconds = 900;

            if (time() - $lastRotationAt >= $rotateEverySeconds) {
                $request->session()->migrate(true);
                $request->session()->put('session_rotated_at', time());
            }
        }

        return $next($request);
    }
}
