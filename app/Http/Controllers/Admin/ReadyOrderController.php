<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;

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

        return $query;
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

    public function poll(): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->hasPermission('update_order_status') || $user->role === User::ROLE_CASHIER), 403);

        $base = Order::with('branch')
            ->where('status', 'ready_for_pickup')
            ->latest();

        $this->applyBranchScope($base);

        $orders = $base->take(60)->get();

        $deliveryOrders = $orders->where('order_type', 'delivery')->values();
        $pickupOrders = $orders->where('order_type', 'pickup')->values();

        return response()->json([
            'counts' => [
                'delivery' => $deliveryOrders->count(),
                'pickup' => $pickupOrders->count(),
            ],
            'delivery_orders' => $deliveryOrders->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'branch_name' => $order->branch?->name,
            ])->values(),
            'pickup_orders' => $pickupOrders->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'branch_name' => $order->branch?->name,
            ])->values(),
        ]);
    }
}
