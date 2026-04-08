<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SalesDemoController extends Controller
{
    public function index(): View
    {
        $demoData = [
            'brand' => [
                'name' => 'Za3tr Kitchen ERP',
                'tagline' => 'نظام مطاعم متكامل لإدارة الطلبات والتشغيل من شاشة واحدة',
                'website' => 'https://panel.za3tr-zatona.cloud',
                'phone' => '+20 100 555 8899',
                'email' => 'sales@za3tr-zatona.cloud',
            ],
            'summary' => [
                'monthly_orders' => 18340,
                'monthly_sales' => 2946800,
                'avg_order_time_minutes' => 24,
                'cancellation_rate' => 2.3,
                'active_branches' => 12,
                'active_staff' => 96,
            ],
            'branches' => [
                ['name' => 'مدينة نصر', 'orders' => 2180, 'sales' => 364500, 'sla' => '22 دقيقة'],
                ['name' => 'المعادي', 'orders' => 1940, 'sales' => 329200, 'sla' => '24 دقيقة'],
                ['name' => 'التجمع الخامس', 'orders' => 2330, 'sales' => 402900, 'sla' => '21 دقيقة'],
                ['name' => '6 أكتوبر', 'orders' => 1685, 'sales' => 286700, 'sla' => '26 دقيقة'],
            ],
            'recent_orders' => [
                ['number' => 'ORD-91273', 'customer' => 'Ahmed Salah', 'type' => 'Delivery', 'total' => 420.00, 'status' => 'Delivered'],
                ['number' => 'ORD-91274', 'customer' => 'Mona Nabil', 'type' => 'Pickup', 'total' => 185.00, 'status' => 'Preparing'],
                ['number' => 'ORD-91275', 'customer' => 'Youssef Adel', 'type' => 'Delivery', 'total' => 310.00, 'status' => 'Out for Delivery'],
                ['number' => 'ORD-91276', 'customer' => 'Nour Hany', 'type' => 'Delivery', 'total' => 255.00, 'status' => 'Confirmed'],
            ],
            'features' => [
                'لوحة تشغيل لحظية للطلبات والتوصيل والمطبخ.',
                'تقارير مبيعات يومية/أسبوعية/شهرية مع تصدير Excel و PDF.',
                'نظام صلاحيات كامل للمديرين والموظفين والفروع.',
                'POS كاشير للفروع مع متابعة مخزون وخيارات المنتجات.',
                'منيو إلكتروني + QR + كوبونات + حملات Popup.',
            ],
        ];

        return view('demo.sales-standalone', compact('demoData'));
    }
}
