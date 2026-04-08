<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionAbsoluteTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $maxAgeSeconds = (int) config('session.absolute_timeout_seconds', 5 * 60 * 60);
        $authenticatedAt = (int) $request->session()->get('authenticated_at', 0);

        if ($authenticatedAt <= 0) {
            $request->session()->put('authenticated_at', now()->timestamp);
            return $next($request);
        }

        if ((now()->timestamp - $authenticatedAt) > $maxAgeSeconds) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'انتهت الجلسة لأسباب أمنية، سجل دخولك مرة أخرى.');
        }

        return $next($request);
    }
}
