<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    protected function canAccessKitchen(User $user): bool
    {
        return $user->role === User::ROLE_KITCHEN
            || $user->isSuperAdmin()
            || $user->isOwner()
            || $user->role === User::ROLE_MANAGER;
    }

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

    protected function kitchenQueueQuery()
    {
        $query = Order::query()
            ->with(['branch', 'items.product'])
            ->whereIn('status', ['confirmed', 'preparing'])
            ->latest();

        return $this->applyBranchScope($query);
    }

    protected function canManageOrder(Order $order): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->hasPermission('view_all_branches_orders')) {
            return true;
        }

        return $user->branch_id && (int) $user->branch_id === (int) $order->branch_id;
    }

    public function index()
    {
        $user = auth()->user();
        abort_unless($user && $this->canAccessKitchen($user), 403);

        return view('admin.kitchen.index', [
            'orders' => $this->kitchenQueueQuery()->paginate(30),
        ]);
    }

    public function poll(): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && $this->canAccessKitchen($user), 403);

        $orders = $this->kitchenQueueQuery()->take(50)->get();

        return response()->json([
            'orders' => $orders->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'order_type' => $order->order_type,
                    'status' => $order->status,
                    'status_note' => $order->status_note,
                    'branch_name' => $order->branch?->name,
                    'created_at' => $order->created_at?->format('Y-m-d h:i A'),
                    'items' => $order->items->map(fn ($item) => [
                        'name' => $item->product?->name ?? 'منتج',
                        'quantity' => (int) $item->quantity,
                    ])->values(),
                ];
            })->values(),
        ]);
    }

    public function start(Order $order): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user && $this->canAccessKitchen($user), 403);
        abort_unless($this->canManageOrder($order), 403, 'ليس لديك صلاحية تعديل هذا الطلب.');

        if (!in_array($order->status, ['confirmed', 'preparing'], true)) {
            return back()->with('error', 'هذا الطلب غير متاح حاليًا في شاشة المطبخ.');
        }

        if ($order->status === 'confirmed') {
            $order->update([
                'status' => 'preparing',
                'status_note' => 'بدأ التحضير في المطبخ',
            ]);
        }

        return back()->with('success', 'تم تحويل الطلب إلى قيد التحضير.');
    }

    public function ready(Order $order): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user && $this->canAccessKitchen($user), 403);
        abort_unless($this->canManageOrder($order), 403, 'ليس لديك صلاحية تعديل هذا الطلب.');

        if (!in_array($order->status, ['confirmed', 'preparing'], true)) {
            return back()->with('error', 'هذا الطلب غير متاح حاليًا في شاشة المطبخ.');
        }

        if ($order->order_type === 'delivery') {
            $order->update([
                'status' => 'ready_for_pickup',
                'status_note' => 'تم إنهاء التحضير بالمطبخ وجاهز لإسناد الدليفري',
            ]);

            return back()->with('success', 'الطلب جاهز وتم تحويله لشاشة الطلبات الجاهزة لإسناد الدليفري.');
        }

        $order->update([
            'status' => 'ready_for_pickup',
            'status_note' => 'تم إنهاء التحضير بالمطبخ وجاهز للاستلام من الفرع',
        ]);

        return back()->with('success', 'تم تجهيز الطلب وأصبح جاهزًا للاستلام.');
    }
}
