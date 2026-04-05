<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير</title>
    <style>
        @page {
            margin: 20px 18px;
        }

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color:#111;
            direction: rtl;
            text-align: right;
            line-height: 1.7;
        }

        h1,h2,h3{
            margin:0 0 10px;
            font-weight: bold;
        }

        .mb-20{ margin-bottom:20px; }
        .mb-10{ margin-bottom:10px; }

        .summary-box{
            border:1px solid #ddd;
            padding:12px;
            margin-bottom:15px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-bottom:18px;
            direction: rtl;
        }

        th, td{
            border:1px solid #ddd;
            padding:8px;
            font-size:11px;
            text-align:right;
            vertical-align:middle;
        }

        th{
            background:#f3f4f6;
            font-weight:bold;
        }

        .small{
            color:#666;
            font-size:11px;
        }

        .section-title{
            margin:18px 0 8px;
            font-size:14px;
            font-weight:bold;
        }
    </style>
</head>
<body>
    <div class="mb-20">
        <h1>تقرير النظام</h1>
        <div class="small">
            @if(isset($selectedBranch) && $selectedBranch)
                الفرع: {{ $selectedBranch->name }}
            @else
                الفرع: كل الفروع
            @endif
            — من {{ $fromDate }} إلى {{ $toDate }}
        </div>
    </div>

    <div class="summary-box">
        <h3 class="mb-10">ملخص عام</h3>

        <table>
            <tr>
                <th>إجمالي المبيعات</th>
                <th>مبيعات اليوم</th>
                <th>مبيعات أمس</th>
                <th>فرق المبيعات</th>
            </tr>
            <tr>
                <td>{{ number_format($totalSales, 2) }}</td>
                <td>{{ number_format($todaySales, 2) }}</td>
                <td>{{ number_format($yesterdaySales, 2) }}</td>
                <td>{{ number_format($salesDiff, 2) }}</td>
            </tr>
        </table>

        <table>
            <tr>
                <th>إجمالي الطلبات</th>
                <th>قيد الانتظار</th>
                <th>مؤكد</th>
                <th>جارٍ التحضير</th>
                <th>خرج للتوصيل</th>
                <th>تم التسليم</th>
                <th>ملغي</th>
            </tr>
            <tr>
                <td>{{ $ordersCount }}</td>
                <td>{{ $pendingOrders }}</td>
                <td>{{ $confirmedOrders }}</td>
                <td>{{ $preparingOrders }}</td>
                <td>{{ $outForDeliveryOrders }}</td>
                <td>{{ $deliveredOrders }}</td>
                <td>{{ $cancelledOrders }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">أفضل المنتجات</div>
    <table>
        <thead>
            <tr>
                <th>المنتج</th>
                <th>إجمالي الكمية</th>
                <th>إجمالي الإيراد</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProducts as $product)
                <tr>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->total_quantity }}</td>
                    <td>{{ number_format($product->total_revenue, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">لا توجد بيانات</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(isset($branchesPerformance) && $branchesPerformance->count())
        <div class="section-title">أداء الفروع</div>
        <table>
            <thead>
                <tr>
                    <th>الفرع</th>
                    <th>عدد الطلبات</th>
                    <th>إجمالي المبيعات</th>
                    <th>قيد الانتظار</th>
                    <th>تم التسليم</th>
                    <th>ملغي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branchesPerformance as $branch)
                    <tr>
                        <td>{{ $branch->name }}</td>
                        <td>{{ $branch->orders_count }}</td>
                        <td>{{ number_format($branch->sales_total, 2) }}</td>
                        <td>{{ $branch->pending_count }}</td>
                        <td>{{ $branch->delivered_count }}</td>
                        <td>{{ $branch->cancelled_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">الطلبات المتأخرة</div>
    <table>
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>العميل</th>
                <th>الفرع</th>
                <th>الحالة</th>
                <th>التأخير بالدقائق</th>
            </tr>
        </thead>
        <tbody>
            @forelse($delayedOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->branch?->name ?? '-' }}</td>
                    <td>{{ $order->status }}</td>
                    <td>{{ $order->delay_minutes }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد طلبات متأخرة</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">آخر الطلبات</div>
    <table>
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>العميل</th>
                <th>الفرع</th>
                <th>الحالة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @forelse($latestOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->branch?->name ?? '-' }}</td>
                    <td>{{ $order->status }}</td>
                    <td>{{ number_format($order->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد طلبات</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>