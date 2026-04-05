<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected function applyUserOrderScope($query)
    {
        $user = auth()->user();

        if (!$user) {
            return $query;
        }

        if ($user->isSuperAdmin() || $user->hasPermission('view_all_branches_orders')) {
            return $query;
        }

        if ($user->role === User::ROLE_DELIVERY) {
            $query->where('delivery_user_id', $user->id);

            return $query;
        }

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function index()
    {
        $query = Order::with('branch')->latest();
        $this->applyUserOrderScope($query);

        $orders = $query->paginate(20);

        return view('admin.orders.index', [
            'orders' => $orders,
            'pageType' => 'all',
        ]);
    }

    public function deliveryOrders()
    {
        $query = Order::with('branch')
            ->where('order_type', 'delivery')
            ->latest();

        $this->applyUserOrderScope($query);

        $orders = $query->paginate(20);

        return view('admin.orders.index', [
            'orders' => $orders,
            'pageType' => 'delivery',
        ]);
    }

    public function pickupOrders()
    {
        $query = Order::with('branch')
            ->where('order_type', 'pickup')
            ->latest();

        $this->applyUserOrderScope($query);

        $orders = $query->paginate(20);

        return view('admin.orders.index', [
            'orders' => $orders,
            'pageType' => 'pickup',
        ]);
    }

    public function show(Order $order)
    {
        $order->load('branch', 'items', 'deliveryUser');
        $user = auth()->user();

        if ($user->role === User::ROLE_DELIVERY) {
            abort_if((int) $order->delivery_user_id !== (int) $user->id, 403, 'ليس لديك صلاحية لعرض هذا الطلب.');
        }

        if (
            !$user->isSuperAdmin()
            && !$user->hasPermission('view_all_branches_orders')
            && $user->branch_id
            && (int) $order->branch_id !== (int) $user->branch_id
        ) {
            abort(403, 'ليس لديك صلاحية لعرض هذا الطلب.');
        }

        if (
            !$user->isSuperAdmin()
            && !$user->hasPermission('view_all_branches_orders')
            && !$user->branch_id
        ) {
            abort(403, 'ليس لديك صلاحية لعرض هذا الطلب.');
        }

        if (!$order->is_seen_by_admin) {
            $order->update(['is_seen_by_admin' => true]);
        }

        $deliveryUsers = User::query()
            ->where('user_type', User::TYPE_STAFF)
            ->where('role', User::ROLE_DELIVERY)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.orders.show', compact('order', 'deliveryUsers'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $user = auth()->user();

        if ($user->role === User::ROLE_DELIVERY) {
            abort_if((int) $order->delivery_user_id !== (int) $user->id, 403, 'ليس لديك صلاحية لتحديث هذا الطلب.');
        }

        if (
            !$user->isSuperAdmin()
            && !$user->hasPermission('view_all_branches_orders')
            && $user->branch_id
            && (int) $order->branch_id !== (int) $user->branch_id
        ) {
            abort(403, 'ليس لديك صلاحية لتحديث هذا الطلب.');
        }

        if (
            !$user->isSuperAdmin()
            && !$user->hasPermission('view_all_branches_orders')
            && !$user->branch_id
        ) {
            abort(403, 'ليس لديك صلاحية لتحديث هذا الطلب.');
        }

        $validated = $request->validate([
            'status' => 'required|string',
            'order_type' => 'nullable|in:delivery,pickup',
            'status_note' => 'nullable|string',
            'estimated_delivery_minutes' => 'nullable|integer|min:1|max:300',
        ]);

        $minutes = $validated['estimated_delivery_minutes'] ?? null;

        $order->update([
            'status' => $validated['status'],
            'order_type' => $validated['order_type'] ?? $order->order_type,
            'status_note' => $validated['status_note'] ?? null,
            'estimated_delivery_minutes' => $minutes,
            'estimated_delivery_at' => $minutes
                ? now()->addMinutes((int) $minutes)
                : $order->estimated_delivery_at,
        ]);

        return redirect()->back()->with('success', 'تم تحديث حالة الطلب بنجاح.');
    }

    public function assignDelivery(Request $request, Order $order)
    {
        $user = auth()->user();

        abort_unless(
            $user->isSuperAdmin() || $user->hasPermission('update_order_status'),
            403,
            'ليس لديك صلاحية لإسناد الطلب للدليفري.'
        );

        $validated = $request->validate([
            'delivery_user_id' => ['required', 'exists:users,id'],
        ]);

        $deliveryUser = User::query()
            ->where('id', $validated['delivery_user_id'])
            ->where('user_type', User::TYPE_STAFF)
            ->where('role', User::ROLE_DELIVERY)
            ->where('is_active', true)
            ->firstOrFail();

        $order->update([
            'order_type' => 'delivery',
            'delivery_user_id' => $deliveryUser->id,
            'status' => in_array($order->status, ['pending', 'confirmed', 'preparing'], true)
                ? 'out_for_delivery'
                : $order->status,
        ]);

        return redirect()->back()->with('success', 'تم إسناد الطلب للدليفري بنجاح.');
    }

    public function poll(Request $request): JsonResponse
    {
        $type = $request->get('type', 'all');

        $query = Order::with('branch')->latest();
        $countsBaseQuery = Order::query();

        $this->applyUserOrderScope($query);
        $this->applyUserOrderScope($countsBaseQuery);

        if ($type === 'delivery') {
            $query->where('order_type', 'delivery');
        } elseif ($type === 'pickup') {
            $query->where('order_type', 'pickup');
        }

        $orders = $query->take(20)->get();

        $allCountQuery = clone $countsBaseQuery;
        $deliveryCountQuery = clone $countsBaseQuery;
        $pickupCountQuery = clone $countsBaseQuery;
        $newCountQuery = clone $countsBaseQuery;
        $pendingCountQuery = clone $countsBaseQuery;

        return response()->json([
            'counts' => [
                'all' => $allCountQuery->count(),
                'delivery' => $deliveryCountQuery->where('order_type', 'delivery')->count(),
                'pickup' => $pickupCountQuery->where('order_type', 'pickup')->count(),
                'new' => $newCountQuery->where('is_seen_by_admin', false)->count(),
                'pending' => $pendingCountQuery->where('status', 'pending')->count(),
                'current_page_total' => $orders->count(),
            ],
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'order_type' => $order->order_type,
                    'branch_name' => $order->branch?->name,
                    'total' => (float) $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at?->format('Y-m-d h:i A'),
                    'is_seen_by_admin' => (bool) $order->is_seen_by_admin,
                    'show_url' => route('admin.orders.show', $order->id),
                ];
            })->values(),
        ]);
    }
}
