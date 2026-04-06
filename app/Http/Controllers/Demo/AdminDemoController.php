<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDemoController extends Controller
{
    public function show(Request $request, ?string $path = null)
    {
        $normalizedPath = trim((string) $path, '/');

        if ($normalizedPath === '' || $normalizedPath === 'dashboard') {
            return redirect()->route('admin.dashboard.demo');
        }

        $section = explode('/', $normalizedPath)[0];

        $sectionTitles = [
            'orders' => 'إدارة الطلبات',
            'orders-delivery' => 'طلبات التوصيل',
            'orders-pickup' => 'طلبات الاستلام',
            'kitchen' => 'شاشة المطبخ',
            'ready-orders' => 'الطلبات الجاهزة',
            'delivery-management' => 'متابعة الدليفري',
            'branches' => 'الفروع',
            'categories' => 'الأقسام',
            'products' => 'المنتجات',
            'settings' => 'الإعدادات',
            'staff' => 'الموظفون',
            'reports' => 'التقارير',
            'digital-menu' => 'المنيو الإلكتروني',
            'popup-campaign' => 'الإعلانات',
            'coupons' => 'الكوبونات',
            'ai-assistant' => 'المساعد الذكي',
        ];

        $sectionTitle = $sectionTitles[$section] ?? 'وحدة النظام';

        return view('demo.admin.module', [
            'isDemoDashboard' => true,
            'pageTitle' => "ديمو: {$sectionTitle}",
            'pageSubtitle' => 'عرض تجريبي كامل للواجهة بدون أي بيانات تشغيل فعلية',
            'demoSectionTitle' => $sectionTitle,
            'demoSectionPath' => $normalizedPath,
        ]);
    }
}
