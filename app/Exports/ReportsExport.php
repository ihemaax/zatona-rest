<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportsExport implements FromArray, WithHeadings
{
    protected array $rows = [];

    public function __construct(
        protected array $summary,
        protected Collection $topProducts,
        protected Collection $branchesPerformance,
        protected Collection $delayedOrders,
        protected Collection $latestOrders,
        protected string $fromDate,
        protected string $toDate,
        protected ?string $branchName = null
    ) {
        $this->buildRows();
    }

    public function headings(): array
    {
        return ['القسم', 'العنصر', 'القيمة 1', 'القيمة 2', 'القيمة 3', 'القيمة 4'];
    }

    public function array(): array
    {
        return $this->rows;
    }

    protected function buildRows(): void
    {
        $this->rows[] = ['ملخص التقرير', 'من تاريخ', $this->fromDate, 'إلى تاريخ', $this->toDate, ''];
        $this->rows[] = ['ملخص التقرير', 'الفرع', $this->branchName ?: 'كل الفروع', '', '', ''];
        $this->rows[] = ['', '', '', '', '', ''];

        $this->rows[] = ['ملخص عام', 'إجمالي المبيعات', $this->summary['totalSales'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'مبيعات اليوم', $this->summary['todaySales'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'مبيعات أمس', $this->summary['yesterdaySales'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'فرق المبيعات', $this->summary['salesDiff'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'إجمالي الطلبات', $this->summary['ordersCount'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'Pending', $this->summary['pendingOrders'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'Confirmed', $this->summary['confirmedOrders'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'Preparing', $this->summary['preparingOrders'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'Out for delivery', $this->summary['outForDeliveryOrders'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'Delivered', $this->summary['deliveredOrders'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'Cancelled', $this->summary['cancelledOrders'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'Delivery orders', $this->summary['deliveryOrders'], '', '', ''];
        $this->rows[] = ['ملخص عام', 'Pickup orders', $this->summary['pickupOrders'], '', '', ''];

        $this->rows[] = ['', '', '', '', '', ''];
        $this->rows[] = ['أفضل المنتجات', 'اسم المنتج', 'إجمالي الكمية', 'إجمالي الإيراد', '', ''];

        foreach ($this->topProducts as $product) {
            $this->rows[] = [
                'أفضل المنتجات',
                $product->product_name,
                $product->total_quantity,
                $product->total_revenue,
                '',
                '',
            ];
        }

        $this->rows[] = ['', '', '', '', '', ''];
        $this->rows[] = ['أداء الفروع', 'الفرع', 'عدد الطلبات', 'إجمالي المبيعات', 'Pending', 'Delivered / Cancelled'];

        foreach ($this->branchesPerformance as $branch) {
            $this->rows[] = [
                'أداء الفروع',
                $branch->name,
                $branch->orders_count,
                $branch->sales_total,
                $branch->pending_count,
                $branch->delivered_count . ' / ' . $branch->cancelled_count,
            ];
        }

        $this->rows[] = ['', '', '', '', '', ''];
        $this->rows[] = ['الطلبات المتأخرة', 'رقم الطلب', 'العميل', 'الفرع', 'الحالة', 'التأخير بالدقائق'];

        foreach ($this->delayedOrders as $order) {
            $this->rows[] = [
                'الطلبات المتأخرة',
                $order->order_number,
                $order->customer_name,
                $order->branch?->name ?? '-',
                $order->status,
                $order->delay_minutes,
            ];
        }

        $this->rows[] = ['', '', '', '', '', ''];
        $this->rows[] = ['آخر الطلبات', 'رقم الطلب', 'العميل', 'الفرع', 'الحالة', 'الإجمالي'];

        foreach ($this->latestOrders as $order) {
            $this->rows[] = [
                'آخر الطلبات',
                $order->order_number,
                $order->customer_name,
                $order->branch?->name ?? '-',
                $order->status,
                $order->total,
            ];
        }
    }
}