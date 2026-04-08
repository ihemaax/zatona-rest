<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    protected function applyOrderScope($query, ?int $branchFilterId = null)
    {
        $user = auth()->user();

        if (!$user) {
            if ($branchFilterId) {
                $query->where('branch_id', $branchFilterId);
            }
            return $query;
        }

        if ($user->canViewAllBranchesOrders()) {
            if ($branchFilterId) {
                $query->where('branch_id', $branchFilterId);
            }
            return $query;
        }

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
            return $query;
        }

        return $query->whereRaw('1 = 0');
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

    protected function resolveBranchFilter(Request $request, ?User $user): ?int
    {
        $branchId = (int) $request->query('branch_id', 0);
        if ($branchId <= 0) {
            return null;
        }

        if (!$user) {
            return null;
        }

        if ($user->canViewAllBranchesOrders()) {
            return Branch::whereKey($branchId)->exists() ? $branchId : null;
        }

        if ($user->branch_id && (int) $user->branch_id === $branchId) {
            return $branchId;
        }

        return null;
    }

    protected function branchFilterOptions(?User $user)
    {
        if (!$user) {
            return collect();
        }

        if ($user->canViewAllBranchesOrders()) {
            return Branch::query()->orderBy('name')->get(['id', 'name']);
        }

        if ($user->branch_id) {
            return Branch::query()
                ->whereKey($user->branch_id)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return collect();
    }

    protected function buildDashboardData(string $range = 'today', ?int $branchFilterId = null): array
    {
        $user = auth()->user();
        $ordersBase = Order::query();
        $this->applyOrderScope($ordersBase, $branchFilterId);
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

        $weeklyTrend = collect(range(6, 0))->map(function ($daysAgo) use ($range, $branchFilterId) {
            $date = Carbon::today()->subDays($daysAgo);

            $dailyQuery = Order::query();
            $this->applyOrderScope($dailyQuery, $branchFilterId);

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

        $this->applyOrderScope($topProducts, $branchFilterId);
        $this->applyDateRange($topProducts, $range, 'orders.created_at');
        $topProducts = $topProducts->get();

        $branchesStatsQuery = Branch::query()->orderBy('name');
        if ($branchFilterId) {
            $branchesStatsQuery->whereKey($branchFilterId);
        } elseif ($user && !$user->canViewAllBranchesOrders() && $user->branch_id) {
            $branchesStatsQuery->whereKey($user->branch_id);
        } elseif ($user && !$user->canViewAllBranchesOrders() && !$user->branch_id) {
            $branchesStatsQuery->whereRaw('1 = 0');
        }

        $branchesStats = $branchesStatsQuery
            ->withCount(['orders as orders_count' => function ($query) use ($range, $branchFilterId) {
                $this->applyOrderScope($query, $branchFilterId);
                $this->applyDateRange($query, $range);
            }])
            ->get();

        if (!$user && $branchFilterId) {
            $branchesStats = $branchesStats->where('id', $branchFilterId)->values();
        } elseif (!$user) {
            $branchesStats = $branchesStats->values();
        } elseif ($user->canViewAllBranchesOrders()) {
            $branchesStats = $branchesStats->values();
        } else {
            $branchesStats = $branchesStats->values();
        }

        return [
            'range' => $range,
            'selectedBranchId' => $branchFilterId,
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

    protected function buildEmptyDashboardData(string $range = 'today', ?int $branchFilterId = null): array
    {
        $weeklyTrend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'orders' => 0,
                'sales' => 0,
            ];
        })->values();

        return [
            'range' => $range,
            'selectedBranchId' => $branchFilterId,
            'ordersCount' => 0,
            'todayOrders' => 0,
            'newOrders' => 0,
            'pendingOrders' => 0,
            'deliveryOrders' => 0,
            'pickupOrders' => 0,
            'todaySales' => 0,
            'deliverySales' => 0,
            'pickupSales' => 0,
            'latestOrders' => collect(),
            'deliveryLatest' => collect(),
            'pickupLatest' => collect(),
            'branchesStats' => collect(),
            'notifications' => collect(),
            'statusBreakdown' => [
                'pending' => 0,
                'confirmed' => 0,
                'preparing' => 0,
                'out_for_delivery' => 0,
                'delivered' => 0,
                'cancelled' => 0,
            ],
            'weeklyTrend' => $weeklyTrend,
            'kpis' => [
                'prep_sla_minutes' => 0,
                'avg_delivery_minutes' => 0,
                'cancellation_rate' => 0,
                'completion_rate' => 0,
            ],
            'shiftSummary' => [
                'orders_count' => 0,
                'sales_total' => 0,
                'avg_order_value' => 0,
            ],
            'topProducts' => collect(),
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->canAccessAdminPanel(), 403, 'غير مسموح لك بالدخول إلى لوحة الإدارة.');

        if (!$user->canAccessDashboard()) {
            return redirect()->to($this->resolveStaffLandingUrl($user));
        }

        $range = $request->query('range', 'today');
        if (!in_array($range, ['today', '7d', '30d'], true)) {
            $range = 'today';
        }
        $selectedBranchId = $this->resolveBranchFilter($request, $user);
        $branchFilterOptions = $this->branchFilterOptions($user);

        return view('admin.dashboard', array_merge(
            $this->buildDashboardData($range, $selectedBranchId),
            [
                'dashboardBaseRoute' => 'admin.dashboard',
                'dashboardPollRoute' => 'admin.dashboard.poll',
                'dashboardExportRoute' => 'admin.dashboard.export-snapshot',
                'isDemoDashboard' => false,
                'branchFilterOptions' => $branchFilterOptions,
            ]
        ));
    }

    public function demo(Request $request)
    {
        $range = $request->query('range', 'today');
        if (!in_array($range, ['today', '7d', '30d'], true)) {
            $range = 'today';
        }
        $selectedBranchId = (int) $request->query('branch_id', 0);
        $selectedBranchId = $selectedBranchId > 0 ? $selectedBranchId : null;

        $now = now();

        $latestOrders = collect([
            (object) ['id' => 9001, 'order_number' => 'ORD-91310', 'customer_name' => 'Khaled Mostafa', 'order_type' => 'delivery', 'total' => 320.00, 'status' => 'out_for_delivery', 'is_seen_by_admin' => false, 'created_at' => $now->copy()->subMinutes(4), 'branch' => (object) ['name' => 'مدينة نصر']],
            (object) ['id' => 9002, 'order_number' => 'ORD-91311', 'customer_name' => 'Sara Emad', 'order_type' => 'pickup', 'total' => 190.00, 'status' => 'preparing', 'is_seen_by_admin' => false, 'created_at' => $now->copy()->subMinutes(9), 'branch' => (object) ['name' => 'المعادي']],
            (object) ['id' => 9003, 'order_number' => 'ORD-91312', 'customer_name' => 'Omar Hany', 'order_type' => 'delivery', 'total' => 275.00, 'status' => 'confirmed', 'is_seen_by_admin' => true, 'created_at' => $now->copy()->subMinutes(17), 'branch' => (object) ['name' => 'التجمع']],
            (object) ['id' => 9004, 'order_number' => 'ORD-91313', 'customer_name' => 'Nadine Fathy', 'order_type' => 'delivery', 'total' => 415.50, 'status' => 'delivered', 'is_seen_by_admin' => true, 'created_at' => $now->copy()->subMinutes(31), 'branch' => (object) ['name' => '6 أكتوبر']],
            (object) ['id' => 9005, 'order_number' => 'ORD-91314', 'customer_name' => 'Mariam Adel', 'order_type' => 'pickup', 'total' => 165.00, 'status' => 'pending', 'is_seen_by_admin' => false, 'created_at' => $now->copy()->subMinutes(42), 'branch' => (object) ['name' => 'حلوان']],
            (object) ['id' => 9006, 'order_number' => 'ORD-91315', 'customer_name' => 'Amr Magdy', 'order_type' => 'delivery', 'total' => 286.00, 'status' => 'preparing', 'is_seen_by_admin' => true, 'created_at' => $now->copy()->subMinutes(55), 'branch' => (object) ['name' => 'المقطم']],
        ]);

        $deliveryLatest = $latestOrders->where('order_type', 'delivery')->values();
        $pickupLatest = $latestOrders->where('order_type', 'pickup')->values();
        $notifications = $latestOrders->where('is_seen_by_admin', false)->values();

        $statusBreakdown = [
            'pending' => 21,
            'confirmed' => 31,
            'preparing' => 44,
            'out_for_delivery' => 38,
            'delivered' => 697,
            'cancelled' => 11,
        ];

        $weeklyTrend = collect(range(6, 0))->map(function ($daysAgo) use ($now) {
            $date = $now->copy()->subDays($daysAgo);
            $orders = [94, 101, 117, 123, 136, 128, 143][6 - $daysAgo];
            $sales = [16420.00, 17880.00, 20410.00, 21750.00, 24190.00, 22640.00, 25830.00][6 - $daysAgo];

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'orders' => $orders,
                'sales' => $sales,
            ];
        })->values();

        $branchesStats = collect([
            (object) ['name' => 'مدينة نصر', 'address' => 'شارع الطيران', 'orders_count' => 166],
            (object) ['name' => 'المعادي', 'address' => 'زهراء المعادي', 'orders_count' => 142],
            (object) ['name' => 'التجمع الخامس', 'address' => 'شارع التسعين', 'orders_count' => 174],
            (object) ['name' => '6 أكتوبر', 'address' => 'المحور المركزي', 'orders_count' => 133],
            (object) ['name' => 'حلوان', 'address' => 'شارع منصور', 'orders_count' => 111],
        ]);

        $topProducts = collect([
            (object) ['product_name' => 'زنجر سبايسي', 'total_quantity' => 268],
            (object) ['product_name' => 'تشيكن كرنش', 'total_quantity' => 241],
            (object) ['product_name' => 'كومبو فيليه', 'total_quantity' => 190],
            (object) ['product_name' => 'وجبة شاورما', 'total_quantity' => 177],
            (object) ['product_name' => 'بطاطس تشيزي', 'total_quantity' => 153],
        ]);

        return view('admin.dashboard', [
            'range' => $range,
            'selectedBranchId' => $selectedBranchId,
            'ordersCount' => 842,
            'todayOrders' => 842,
            'newOrders' => 67,
            'pendingOrders' => 21,
            'deliveryOrders' => 569,
            'pickupOrders' => 273,
            'todaySales' => 154320.50,
            'deliverySales' => 109740.25,
            'pickupSales' => 44580.25,
            'latestOrders' => $latestOrders,
            'deliveryLatest' => $deliveryLatest,
            'pickupLatest' => $pickupLatest,
            'branchesStats' => $branchesStats,
            'notifications' => $notifications,
            'statusBreakdown' => $statusBreakdown,
            'weeklyTrend' => $weeklyTrend,
            'kpis' => [
                'prep_sla_minutes' => 18.4,
                'avg_delivery_minutes' => 26.1,
                'cancellation_rate' => 1.31,
                'completion_rate' => 98.45,
            ],
            'shiftSummary' => [
                'orders_count' => 842,
                'sales_total' => 154320.50,
                'avg_order_value' => 183.28,
            ],
            'topProducts' => $topProducts,
            'dashboardBaseRoute' => 'admin.dashboard.demo',
            'dashboardPollRoute' => null,
            'dashboardExportRoute' => null,
            'isDemoDashboard' => true,
            'branchFilterOptions' => Branch::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function exportSnapshot(Request $request): StreamedResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->canAccessDashboard(), 403, 'هذه الصفحة متاحة للمدير والإدمن والسوبر أدمن فقط.');

        $range = $request->query('range', 'today');
        if (!in_array($range, ['today', '7d', '30d'], true)) {
            $range = 'today';
        }
        $selectedBranchId = $this->resolveBranchFilter($request, $user);

        $data = $this->buildDashboardData($range, $selectedBranchId);

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
        $user = auth()->user();
        abort_unless($user && $user->canAccessDashboard(), 403, 'هذه الصفحة متاحة للمدير والإدمن والسوبر أدمن فقط.');

        $range = $request->query('range', 'today');
        if (!in_array($range, ['today', '7d', '30d'], true)) {
            $range = 'today';
        }
        $selectedBranchId = $this->resolveBranchFilter($request, $user);

        $data = $this->buildDashboardData($range, $selectedBranchId);

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

    protected function resolveStaffLandingUrl(User $user): string
    {
        if ($user->role === User::ROLE_KITCHEN) {
            return route('admin.kitchen.index');
        }

        if ($user->role === User::ROLE_DELIVERY) {
            return route('admin.delivery.dashboard');
        }

        if ($user->hasPermission('use_cashier') && !empty($user->branch_id)) {
            return route('admin.cashier.pos', ['branch' => $user->branch_id]);
        }

        if ($user->hasPermission('use_cashier') && $user->hasPermission('manage_cashier')) {
            return route('admin.cashier.index');
        }

        return route('admin.orders.index');
    }
}
