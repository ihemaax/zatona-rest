<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class DeliveryDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        abort_unless($user && $user->role === User::ROLE_DELIVERY, 403, 'هذه الصفحة مخصصة للدليفري فقط.');

        $base = Order::with('branch')
            ->where('delivery_user_id', $user->id)
            ->latest();

        $activeOrders = (clone $base)
            ->whereIn('status', ['confirmed', 'preparing', 'out_for_delivery'])
            ->paginate(12, ['*'], 'active_page');

        $completedOrders = (clone $base)
            ->whereIn('status', ['delivered', 'cancelled'])
            ->paginate(12, ['*'], 'completed_page');

        $countsBase = Order::query()->where('delivery_user_id', $user->id);

        $stats = [
            'assigned_total' => (clone $countsBase)->count(),
            'active_total' => (clone $countsBase)->whereIn('status', ['confirmed', 'preparing', 'out_for_delivery'])->count(),
            'completed_total' => (clone $countsBase)->where('status', 'delivered')->count(),
            'cancelled_total' => (clone $countsBase)->where('status', 'cancelled')->count(),
        ];

        return view('admin.delivery.dashboard', [
            'activeOrders' => $activeOrders,
            'completedOrders' => $completedOrders,
            'stats' => $stats,
        ]);
    }
}
        abort_unless($user && $user->role === 'delivery', 403, 'هذه الصفحة مخصصة للدليفري فقط.');

        $orders = Order::with('branch')
            ->where('delivery_user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('admin.delivery.dashboard', [
            'orders' => $orders,
        ]);
    }
}

