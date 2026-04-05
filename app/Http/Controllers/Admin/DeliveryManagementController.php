<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class DeliveryManagementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->hasPermission('manage_delivery')), 403);

        $deliveryUsers = User::query()
            ->where('user_type', User::TYPE_STAFF)
            ->where('role', User::ROLE_DELIVERY)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (User $deliveryUser) {
                $base = Order::query()->where('delivery_user_id', $deliveryUser->id);

                return [
                    'user' => $deliveryUser,
                    'assigned_total' => (clone $base)->count(),
                    'active_total' => (clone $base)->whereIn('status', ['confirmed', 'preparing', 'out_for_delivery'])->count(),
                    'delivered_total' => (clone $base)->where('status', 'delivered')->count(),
                    'cancelled_total' => (clone $base)->where('status', 'cancelled')->count(),
                    'latest_orders' => (clone $base)->latest()->take(5)->get(),
                ];
            });

        return view('admin.delivery.management', [
            'deliveryUsers' => $deliveryUsers,
        ]);
    }
}
