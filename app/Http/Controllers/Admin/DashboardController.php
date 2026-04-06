<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected function applyOrderScope($query)
    {
        $user = auth()->user();

        if (!$user) {
            return $query;
        }

        if ($user->isSuperAdmin() || $user->hasPermission('view_all_branches_orders')) {
            return $query;
        }

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    protected function buildDashboardData(): array
    {
        $user = auth()->user();

        $ordersBase = Order::query();
        $this->applyOrderScope($ordersBase);

        $ordersCount = (clone $ordersBase)->count();
        $todayOrders = (clone $ordersBase)->whereDate('created_at', today())->count();
        $newOrders = (clone $ordersBase)->where('is_seen_by_admin', false)->count();
        $pendingOrders = (clone $ordersBase)->where('status', 'pending')->count();
        $deliveryOrders = (clone $ordersBase)->where('order_type', 'delivery')->count();
        $pickupOrders = (clone $ordersBase)->where('order_type', 'pickup')->count();

        $todaySales = (float) (clone $ordersBase)->whereDate('created_at', today())->sum('total');
        $deliverySales = (float) (clone $ordersBase)->where('order_type', 'delivery')->sum('total');
        $pickupSales = (float) (clone $ordersBase)->where('order_type', 'pickup')->sum('total');

        $latestOrders = (clone $ordersBase)->with('branch')->latest()->take(10)->get();
        $deliveryLatest = (clone $ordersBase)->with('branch')->where('order_type', 'delivery')->latest()->take(6)->get();
        $pickupLatest = (clone $ordersBase)->with('branch')->where('order_type', 'pickup')->latest()->take(6)->get();
        $notifications = (clone $ordersBase)->with('branch')->where('is_seen_by_admin', false)->latest()->take(6)->get();

        $statusBreakdown = [
            'pending' => (clone $ordersBase)->where('status', 'pending')->count(),
            'confirmed' => (clone $ordersBase)->where('status', 'confirmed')->count(),
            'preparing' => (clone $ordersBase)->where('status', 'preparing')->count(),
            'out_for_delivery' => (clone $ordersBase)->where('status', 'out_for_delivery')->count(),
            'delivered' => (clone $ordersBase)->where('status', 'delivered')->count(),
            'cancelled' => (clone $ordersBase)->where('status', 'cancelled')->count(),
        ];

        $weeklyTrend = collect(range(6, 0))->map(function ($daysAgo) use ($ordersBase) {
            $date = Carbon::today()->subDays($daysAgo);
            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'orders' => (int) (clone $ordersBase)->whereDate('created_at', $date)->count(),
                'sales' => (float) (clone $ordersBase)->whereDate('created_at', $date)->sum('total'),
            ];
        })->values();

        if ($user->isSuperAdmin() || $user->hasPermission('view_all_branches_orders')) {
            $branchesStats = Branch::withCount('orders')->orderBy('name')->get();
        } elseif ($user->branch_id) {
            $branchesStats = Branch::where('id', $user->branch_id)->withCount('orders')->orderBy('name')->get();
        } else {
            $branchesStats = collect();
        }

        return [
            'ordersCount' => $ordersCount,
            'todayOrders' => $todayOrders,
            'newOrders' => $newOrders,
            'pendingOrders' => $pendingOrders,
            'deliveryOrders' => $deliveryOrders,
            'pickupOrders' => $pickupOrders,
            'todaySales' => $todaySales,
            'deliverySales' => $deliverySales,
            'pickupSales' => $pickupSales,
            'latestOrders' => $latestOrders,
            'deliveryLatest' => $deliveryLatest,
            'pickupLatest' => $pickupLatest,
            'branchesStats' => $branchesStats,
            'notifications' => $notifications,
            'statusBreakdown' => $statusBreakdown,
            'weeklyTrend' => $weeklyTrend,
        ];
    }

    public function index()
    {
        return view('admin.dashboard', $this->buildDashboardData());
    }

    public function poll(): JsonResponse
    {
        $data = $this->buildDashboardData();

        return response()->json([
            'cards' => [
                'orders_count' => $data['ordersCount'],
                'today_orders' => $data['todayOrders'],
                'new_orders' => $data['newOrders'],
                'pending_orders' => $data['pendingOrders'],
                'delivery_orders' => $data['deliveryOrders'],
                'pickup_orders' => $data['pickupOrders'],
                'today_sales' => $data['todaySales'],
                'delivery_sales' => $data['deliverySales'],
                'pickup_sales' => $data['pickupSales'],
                'branches_count' => $data['branchesStats']->count(),
                'status_breakdown' => $data['statusBreakdown'],
            ],
            'new_orders_count' => $data['newOrders'],
            'notifications' => $data['notifications']->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'order_type' => $order->order_type,
                    'branch_name' => $order->branch?->name,
                    'created_at' => $order->created_at?->format('Y-m-d h:i A'),
                    'show_url' => route('admin.orders.show', $order->id),
                ];
            })->values(),

            'latest_orders' => $data['latestOrders']->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'order_type' => $order->order_type,
                    'branch_name' => $order->branch?->name,
                    'total' => (float) $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at?->format('Y-m-d h:i A'),
                    'is_seen_by_admin' => (bool) $order->is_seen_by_admin,
                    'show_url' => route('admin.orders.show', $order->id),
                ];
            })->values(),

            'delivery_latest' => $data['deliveryLatest']->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'status' => $order->status,
                    'branch_name' => $order->branch?->name,
                    'is_seen_by_admin' => (bool) $order->is_seen_by_admin,
                    'show_url' => route('admin.orders.show', $order->id),
                ];
            })->values(),

            'pickup_latest' => $data['pickupLatest']->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'branch_name' => $order->branch?->name,
                    'is_seen_by_admin' => (bool) $order->is_seen_by_admin,
                    'show_url' => route('admin.orders.show', $order->id),
                ];
            })->values(),

            'weekly_trend' => $data['weeklyTrend'],

            'branches_stats' => collect($data['branchesStats'])->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'orders_count' => $branch->orders_count,
                ];
            })->values(),
        ]);
    }
}
