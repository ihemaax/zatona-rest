<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class ReadyOrderController extends Controller
{
    protected function applyBranchScope($query)
    {
        $user = auth()->user();

        if (!$user || $user->isSuperAdmin() || $user->hasPermission('view_all_branches_orders')) {
            return $query;
        }

        if ($user->branch_id) {
            return $query->where('branch_id', $user->branch_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function index()
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->hasPermission('update_order_status') || $user->role === User::ROLE_CASHIER), 403);

        $base = Order::with('branch')->where('status', 'ready_for_pickup')->latest();
        $this->applyBranchScope($base);

        $deliveryOrders = (clone $base)->where('order_type', 'delivery')->get();
        $pickupOrders = (clone $base)->where('order_type', 'pickup')->get();

        $deliveryUsers = User::query()
            ->where('user_type', User::TYPE_STAFF)
            ->where('role', User::ROLE_DELIVERY)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.orders.ready', compact('deliveryOrders', 'pickupOrders', 'deliveryUsers'));
    }
}
