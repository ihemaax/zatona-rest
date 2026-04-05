<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->canAccessAdminPanel()) {
            abort(403, 'غير مسموح لك بالدخول إلى لوحة الإدارة.');
        }

        return $next($request);
    }
}