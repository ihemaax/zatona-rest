<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerSubscriptionAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isOwner()) {
            return $next($request);
        }

        abort(403, 'غير مصرح لك بالوصول إلى صفحة إدارة الاشتراك.');
    }
}
