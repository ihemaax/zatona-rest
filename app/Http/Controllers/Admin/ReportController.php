<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportsExport;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected function currentUser()
    {
        return auth()->user();
    }

    protected function canViewAllBranches(): bool
    {
        $user = $this->currentUser();

        return $user->isSuperAdmin() || $user->hasPermission('view_all_branches_orders');
    }

    protected function selectedBranchId(Request $request): ?int
    {
        $user = $this->currentUser();

        if ($this->canViewAllBranches()) {
            $branchId = $request->filled('branch_id') ? (int) $request->branch_id : null;
            return $branchId > 0 ? $branchId : null;
        }

        return $user->branch_id ? (int) $user->branch_id : null;
    }

    protected function selectedFromDate(Request $request): string
    {
        return $request->filled('from_date')
            ? $request->from_date
            : now()->startOfMonth()->toDateString();
    }

    protected function selectedToDate(Request $request): string
    {
        return $request->filled('to_date')
            ? $request->to_date
            : now()->toDateString();
    }

    protected function baseOrdersQuery(?int $branchId = null, ?string $fromDate = null, ?string $toDate = null)
    {
        $query = Order::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query;
    }

    protected function applyOrderItemsScope($query, ?int $branchId = null, ?string $fromDate = null, ?string $toDate = null)
    {
        $query->whereExists(function ($sub) use ($branchId, $fromDate, $toDate) {
            $sub->select(DB::raw(1))
                ->from('orders')
                ->whereColumn('orders.id', 'order_items.order_id');

            if ($branchId) {
                $sub->where('orders.branch_id', $branchId);
            }

            if ($fromDate) {
                $sub->whereDate('orders.created_at', '>=', $fromDate);
            }

            if ($toDate) {
                $sub->whereDate('orders.created_at', '<=', $toDate);
            }
        });

        return $query;
    }

    protected function getReportData(Request $request): array
    {
        $selectedBranchId = $this->selectedBranchId($request);
        $fromDate = $this->selectedFromDate($request);
        $toDate = $this->selectedToDate($request);

        if ($fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $base = $this->baseOrdersQuery($selectedBranchId, $fromDate, $toDate);

        $ordersCount = (clone $base)->count();
        $totalSales = (float) (clone $base)->sum('total');
        $pendingOrders = (clone $base)->where('status', 'pending')->count();
        $confirmedOrders = (clone $base)->where('status', 'confirmed')->count();
        $preparingOrders = (clone $base)->where('status', 'preparing')->count();
        $outForDeliveryOrders = (clone $base)->where('status', 'out_for_delivery')->count();
        $deliveredOrders = (clone $base)->where('status', 'delivered')->count();
        $cancelledOrders = (clone $base)->where('status', 'cancelled')->count();
        $deliveryOrders = (clone $base)->where('order_type', 'delivery')->count();
        $pickupOrders = (clone $base)->where('order_type', 'pickup')->count();

        $todaySales = (float) $this->baseOrdersQuery(
            $selectedBranchId,
            now()->toDateString(),
            now()->toDateString()
        )->sum('total');

        $yesterdaySales = (float) $this->baseOrdersQuery(
            $selectedBranchId,
            now()->subDay()->toDateString(),
            now()->subDay()->toDateString()
        )->sum('total');

        $salesDiff = $todaySales - $yesterdaySales;

        $topProductsQuery = DB::table('order_items')
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->limit(10);

        $this->applyOrderItemsScope($topProductsQuery, $selectedBranchId, $fromDate, $toDate);
        $topProducts = $topProductsQuery->get();

        $latestOrders = (clone $base)
            ->with('branch')
            ->latest()
            ->take(10)
            ->get();

        $delayedOrders = $this->baseOrdersQuery($selectedBranchId, $fromDate, $toDate)
            ->with('branch')
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'out_for_delivery'])
            ->where('created_at', '<=', now()->subMinutes(30))
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($order) {
                $order->delay_minutes = now()->diffInMinutes($order->created_at);
                return $order;
            });

        $branchesPerformance = collect();

        if ($this->canViewAllBranches()) {
            $branchesPerformance = Branch::orderBy('name')
                ->get()
                ->map(function ($branch) use ($fromDate, $toDate) {
                    $branchBase = Order::query()
                        ->where('branch_id', $branch->id)
                        ->whereDate('created_at', '>=', $fromDate)
                        ->whereDate('created_at', '<=', $toDate);

                    return (object) [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'orders_count' => (clone $branchBase)->count(),
                        'sales_total' => (float) (clone $branchBase)->sum('total'),
                        'pending_count' => (clone $branchBase)->where('status', 'pending')->count(),
                        'delivered_count' => (clone $branchBase)->where('status', 'delivered')->count(),
                        'cancelled_count' => (clone $branchBase)->where('status', 'cancelled')->count(),
                    ];
                })
                ->sortByDesc('sales_total')
                ->values();
        }

        $selectedBranch = $selectedBranchId
            ? Branch::find($selectedBranchId)
            : null;

        $branches = $this->canViewAllBranches()
            ? Branch::orderBy('name')->get()
            : collect();

        return compact(
            'ordersCount',
            'totalSales',
            'todaySales',
            'yesterdaySales',
            'salesDiff',
            'pendingOrders',
            'confirmedOrders',
            'preparingOrders',
            'outForDeliveryOrders',
            'deliveredOrders',
            'cancelledOrders',
            'deliveryOrders',
            'pickupOrders',
            'topProducts',
            'latestOrders',
            'delayedOrders',
            'branchesPerformance',
            'branches',
            'selectedBranchId',
            'selectedBranch',
            'fromDate',
            'toDate'
        );
    }

    public function index(Request $request)
    {
        return view('admin.reports.index', $this->getReportData($request));
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);

        $summary = [
            'totalSales' => $data['totalSales'],
            'todaySales' => $data['todaySales'],
            'yesterdaySales' => $data['yesterdaySales'],
            'salesDiff' => $data['salesDiff'],
            'ordersCount' => $data['ordersCount'],
            'pendingOrders' => $data['pendingOrders'],
            'confirmedOrders' => $data['confirmedOrders'],
            'preparingOrders' => $data['preparingOrders'],
            'outForDeliveryOrders' => $data['outForDeliveryOrders'],
            'deliveredOrders' => $data['deliveredOrders'],
            'cancelledOrders' => $data['cancelledOrders'],
            'deliveryOrders' => $data['deliveryOrders'],
            'pickupOrders' => $data['pickupOrders'],
        ];

        $fileName = 'reports_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ReportsExport(
            summary: $summary,
            topProducts: $data['topProducts'],
            branchesPerformance: $data['branchesPerformance'],
            delayedOrders: $data['delayedOrders'],
            latestOrders: $data['latestOrders'],
            fromDate: $data['fromDate'],
            toDate: $data['toDate'],
            branchName: $data['selectedBranch']?->name
        ), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);

        $pdf = Pdf::loadView('admin.reports.pdf', $data)
    ->setPaper('a4', 'portrait');

return $pdf->download('reports_' . now()->format('Ymd_His') . '.pdf');
    }
}