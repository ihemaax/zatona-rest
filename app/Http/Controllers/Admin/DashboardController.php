<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            $query->where(function ($branchScopedQuery) use ($user) {
                $branchScopedQuery
                    ->where('branch_id', $user->branch_id)
                    ->orWhereNull('branch_id');
            });
        }

        return $query;
    }

    protected function applyDateRange($query, string $range, string $column = 'created_at'): void
    {
        if ($range === 'today') {
            $query->whereDate($column, today());
            return;
        }

        if (in_array($range, ['7d', '30d'], true)) {
            $days = (int) str_replace('d', '', $range);
            $query->whereDate($column, '>=', today()->subDays($days - 1));
        }
    }

    protected function buildDashboardData(string $range = 'today'): array
    {
        $user = auth()->user();

        $ordersBase = Order::query();
        $this->applyOrderScope($ordersBase);
        $this->applyDateRange($ordersBase, $range);

        $ordersCount = (clone $ordersBase)->count();
        $newOrders = (clone $ordersBase)->where('is_seen_by_admin', false)->count();
        $pendingOrders = (clone $ordersBase)->where('status', 'pending')->count();
        $deliveryOrders = (clone $ordersBase)->where('order_type', 'delivery')->count();
        $pickupOrders = (clone $ordersBase)->where('order_type', 'pickup')->count();

        $totalSales = (float) (clone $ordersBase)->sum('total');
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

        $weeklyTrend = collect(range(6, 0))->map(function ($daysAgo) use ($range) {
            $date = Carbon::today()->subDays($daysAgo);

            $dailyQuery = Order::query();
            $this->applyOrderScope($dailyQuery);

            if ($range === '30d') {
                $dailyQuery->whereDate('created_at', '>=', today()->subDays(29));
            }

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'orders' => (int) (clone $dailyQuery)->whereDate('created_at', $date)->count(),
                'sales' => (float) (clone $dailyQuery)->whereDate('created_at', $date)->sum('total'),
            ];
        })->values();

        $cancelled = $statusBreakdown['cancelled'];
        $delivered = $statusBreakdown['delivered'];
        $completedOrCancelled = max(1, $delivered + $cancelled);

        $avgPrepMinutes = (float) (clone $ordersBase)
            ->whereNotNull('out_for_delivery_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, out_for_delivery_at)) as avg_minutes')
            ->value('avg_minutes');

        $avgDeliveryMinutes = (float) (clone $ordersBase)
            ->whereNotNull('delivered_at')
            ->whereNotNull('out_for_delivery_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, out_for_delivery_at, delivered_at)) as avg_minutes')
            ->value('avg_minutes');

        $kpis = [
            'prep_sla_minutes' => round($avgPrepMinutes ?: 0, 1),
            'avg_delivery_minutes' => round($avgDeliveryMinutes ?: 0, 1),
            'cancellation_rate' => round(($cancelled / max(1, $ordersCount)) * 100, 2),
            'completion_rate' => round(($delivered / $completedOrCancelled) * 100, 2),
        ];

        $avgOrderValue = $ordersCount > 0 ? round($totalSales / $ordersCount, 2) : 0;
        $shiftSummary = [
            'orders_count' => $ordersCount,
            'sales_total' => round($totalSales, 2),
            'avg_order_value' => $avgOrderValue,
        ];

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as total_quantity')
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_quantity')
            ->limit(5);

        $this->applyOrderScope($topProducts);
        $this->applyDateRange($topProducts, $range, 'orders.created_at');
        $topProducts = $topProducts->get();

        if (!$user) {
            $branchesStats = Branch::withCount('orders')->orderBy('name')->get();
        } elseif ($user->isSuperAdmin() || $user->hasPermission('view_all_branches_orders')) {
            $branchesStats = Branch::withCount('orders')->orderBy('name')->get();
        } elseif ($user->branch_id) {
            $branchesStats = Branch::where('id', $user->branch_id)->withCount('orders')->orderBy('name')->get();
        } else {
            $branchesStats = collect();
        }

        return [
            'range' => $range,
            'ordersCount' => $ordersCount,
            'todayOrders' => $ordersCount,
            'newOrders' => $newOrders,
            'pendingOrders' => $pendingOrders,
            'deliveryOrders' => $deliveryOrders,
            'pickupOrders' => $pickupOrders,
            'todaySales' => $totalSales,
            'deliverySales' => $deliverySales,
            'pickupSales' => $pickupSales,
            'latestOrders' => $latestOrders,
            'deliveryLatest' => $deliveryLatest,
            'pickupLatest' => $pickupLatest,
            'branchesStats' => $branchesStats,
            'notifications' => $notifications,
            'statusBreakdown' => $statusBreakdown,
            'weeklyTrend' => $weeklyTrend,
            'kpis' => $kpis,
            'shiftSummary' => $shiftSummary,
            'topProducts' => $topProducts,
        ];
    }

    public function index(Request $request)
    {
        $range = $request->query('range', 'today');
        if (!in_array($range, ['today', '7d', '30d'], true)) {
            $range = 'today';
        }

        return view('admin.dashboard', array_merge(
            $this->buildDashboardData($range),
            [
                'dashboardBaseRoute' => 'admin.dashboard',
                'dashboardPollRoute' => 'admin.dashboard.poll',
                'dashboardExportRoute' => 'admin.dashboard.export-snapshot',
                'isDemoDashboard' => false,
            ]
        ));
    }

    public function demo(Request $request)
    {
        $range = $request->query('range', 'today');
        if (!in_array($range, ['today', '7d', '30d'], true)) {
            $range = 'today';
        }

        return view('admin.dashboard', array_merge(
            $this->buildDashboardData($range),
            [
                'dashboardBaseRoute' => 'admin.dashboard.demo',
                'dashboardPollRoute' => null,
                'dashboardExportRoute' => null,
                'isDemoDashboard' => true,
            ]
        ));
    }

    public function exportSnapshot(Request $request): StreamedResponse
    {
        $range = $request->query('range', 'today');
        if (!in_array($range, ['today', '7d', '30d'], true)) {
            $range = 'today';
        }

        $data = $this->buildDashboardData($range);

        $filename = 'dashboard-snapshot-' . $range . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['metric', 'value']);
            fputcsv($handle, ['range', $data['range']]);
            fputcsv($handle, ['orders_count', $data['ordersCount']]);
            fputcsv($handle, ['new_orders', $data['newOrders']]);
            fputcsv($handle, ['pending_orders', $data['pendingOrders']]);
            fputcsv($handle, ['delivery_orders', $data['deliveryOrders']]);
            fputcsv($handle, ['pickup_orders', $data['pickupOrders']]);
            fputcsv($handle, ['sales_total', $data['todaySales']]);
            fputcsv($handle, ['prep_sla_minutes', $data['kpis']['prep_sla_minutes']]);
            fputcsv($handle, ['avg_delivery_minutes', $data['kpis']['avg_delivery_minutes']]);
            fputcsv($handle, ['cancellation_rate', $data['kpis']['cancellation_rate']]);
            fputcsv($handle, ['completion_rate', $data['kpis']['completion_rate']]);
            fputcsv($handle, ['avg_order_value', $data['shiftSummary']['avg_order_value']]);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function poll(Request $request): JsonResponse
    {
        $range = $request->query('range', 'today');
        if (!in_array($range, ['today', '7d', '30d'], true)) {
            $range = 'today';
        }

        $data = $this->buildDashboardData($range);

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
                'kpis' => $data['kpis'],
                'shift_summary' => $data['shiftSummary'],
            ],
            'range' => $data['range'],
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
            'top_products' => collect($data['topProducts'])->map(function ($row) {
                return [
                    'product_name' => $row->product_name,
                    'total_quantity' => (int) $row->total_quantity,
                ];
            })->values(),
        ]);
    }
}
