<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('authenticated_at', now()->timestamp);

        $user = auth()->user();

        if ($user && $user->canAccessAdminPanel()) {
            return $this->redirectToStaffLanding($user);
        }

        return redirect()->intended(route('home', absolute: false));
    }

    protected function redirectToStaffLanding(User $user): RedirectResponse
    {
        if ($user->canAccessDashboard()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === User::ROLE_KITCHEN) {
            return redirect()->route('admin.kitchen.index');
        }

        if ($user->role === User::ROLE_DELIVERY) {
            return redirect()->route('admin.delivery.dashboard');
        }

        if ($user->hasPermission('use_cashier')) {
            if ($user->hasPermission('manage_cashier')) {
                return redirect()->route('admin.cashier.index');
            }

            if (!empty($user->branch_id)) {
                return redirect()->route('admin.cashier.pos', ['branch' => $user->branch_id]);
            }
        }

        if ($user->hasPermission('view_orders')) {
            return redirect()->route('admin.orders.index');
        }

        return redirect()->route('home');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
