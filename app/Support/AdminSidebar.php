<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class AdminSidebar
{
    public static function build(?User $adminUser, bool $isDemoDashboard, int $newOrdersCount): array
    {
        $isDeliveryUser = $adminUser?->role === User::ROLE_DELIVERY;
        $isKitchenUser = $adminUser?->role === User::ROLE_KITCHEN;
        $canViewDashboard = $isDemoDashboard || $adminUser?->canAccessDashboard();

        $hasAdminPermission = static function (string $permission) use ($isDemoDashboard, $adminUser): bool {
            return $isDemoDashboard || $adminUser?->isSuperAdmin() || $adminUser?->hasPermission($permission);
        };

        $demoOrAdminUrl = static function (string $demoPath, string $adminUrl) use ($isDemoDashboard): string {
            return $isDemoDashboard
                ? route('admin.demo.module', ['path' => $demoPath])
                : $adminUrl;
        };

        $dashboardLinks = [];
        if ($isDeliveryUser) {
            $dashboardLinks[] = [
                'label' => 'طلباتي (الدليفري)',
                'url' => $demoOrAdminUrl('delivery-dashboard', url('/admin/delivery-dashboard')),
                'active' => request()->is('admin/delivery-dashboard') || request()->is('delivery-dashboard'),
            ];
        } elseif ($isKitchenUser) {
            if ($adminUser?->isSuperAdmin() || $adminUser?->isOwner() || $adminUser?->role === User::ROLE_MANAGER || $adminUser?->role === User::ROLE_KITCHEN) {
                $dashboardLinks[] = [
                    'label' => 'شاشة المطبخ',
                    'url' => $demoOrAdminUrl('kitchen', route('admin.kitchen.index')),
                    'active' => request()->routeIs('admin.kitchen.*'),
                ];
            }
        } else {
            if ($canViewDashboard) {
                $dashboardLinks[] = [
                    'label' => 'الرئيسية',
                    'url' => route($isDemoDashboard ? 'admin.dashboard.demo' : 'admin.dashboard'),
                    'active' => request()->routeIs('admin.dashboard') || request()->routeIs('admin.dashboard.demo'),
                ];
            }

            $dashboardLinks[] = [
                'label' => 'جميع الطلبات',
                'url' => $demoOrAdminUrl('orders', route('admin.orders.index')),
                'active' => request()->routeIs('admin.orders.index') || request()->routeIs('admin.orders.show'),
                'badge' => $newOrdersCount,
                'badge_id' => 'sidebarNewOrdersCount',
            ];
            $dashboardLinks[] = [
                'label' => 'طلبات التوصيل',
                'url' => $demoOrAdminUrl('orders-delivery', route('admin.orders.delivery')),
                'active' => request()->routeIs('admin.orders.delivery'),
            ];
            $dashboardLinks[] = [
                'label' => 'طلبات الاستلام',
                'url' => $demoOrAdminUrl('orders-pickup', route('admin.orders.pickup')),
                'active' => request()->routeIs('admin.orders.pickup'),
            ];

            if ($adminUser?->hasPermission('use_cashier') && $adminUser?->branch_id) {
                $dashboardLinks[] = [
                    'label' => 'شاشة الكاشير',
                    'url' => route('admin.cashier.pos', $adminUser->branch_id),
                    'active' => request()->routeIs('admin.cashier.pos') || request()->routeIs('admin.cashier.invoice'),
                ];
            }

            $dashboardLinks[] = [
                'label' => 'شاشة المطبخ',
                'url' => $demoOrAdminUrl('kitchen', route('admin.kitchen.index')),
                'active' => request()->routeIs('admin.kitchen.*'),
            ];
            $dashboardLinks[] = [
                'label' => 'الطلبات الجاهزة',
                'url' => $demoOrAdminUrl('ready-orders', route('admin.orders.ready')),
                'active' => request()->routeIs('admin.orders.ready'),
            ];

            if ($hasAdminPermission('manage_delivery')) {
                $dashboardLinks[] = [
                    'label' => 'متابعة الدليفري',
                    'url' => $demoOrAdminUrl('delivery-management', route('admin.delivery.management')),
                    'active' => request()->routeIs('admin.delivery.management'),
                ];
            }
        }

        $operationsLinks = [];
        if (!$isDeliveryUser && !$isKitchenUser) {
            if ($hasAdminPermission('manage_branches')) {
                $operationsLinks[] = ['label' => 'الفروع', 'url' => $demoOrAdminUrl('branches', route('admin.branches.index')), 'active' => request()->routeIs('admin.branches.*')];
            }
            if ($hasAdminPermission('manage_categories')) {
                $operationsLinks[] = ['label' => 'الأقسام', 'url' => $demoOrAdminUrl('categories', route('admin.categories.index')), 'active' => request()->routeIs('admin.categories.*')];
            }
            if ($hasAdminPermission('manage_products')) {
                $operationsLinks[] = ['label' => 'المنتجات', 'url' => $demoOrAdminUrl('products', route('admin.products.index')), 'active' => request()->routeIs('admin.products.*')];
            }
            if ($hasAdminPermission('manage_cashier')) {
                $operationsLinks[] = ['label' => 'إدارة الكاشير', 'url' => route('admin.cashier.index'), 'active' => request()->routeIs('admin.cashier.index')];
            }

            $operationsLinks[] = ['label' => 'كوبونات الخصم', 'url' => $demoOrAdminUrl('coupons', url('/admin/coupons')), 'active' => request()->is('admin/coupons*')];

            if ($hasAdminPermission('manage_settings')) {
                $operationsLinks[] = [
                    'label' => 'العروض',
                    'url' => $demoOrAdminUrl('offers', Route::has('admin.offers.index') ? route('admin.offers.index') : url('/admin/offers')),
                    'active' => request()->routeIs('admin.offers.*'),
                ];
                $operationsLinks[] = ['label' => 'الإعدادات', 'url' => $demoOrAdminUrl('settings', route('admin.settings.edit')), 'active' => request()->routeIs('admin.settings.*')];
            }

            if ($hasAdminPermission('manage_staff')) {
                $operationsLinks[] = ['label' => 'الموظفون', 'url' => $demoOrAdminUrl('staff', route('admin.staff.index')), 'active' => request()->routeIs('admin.staff.*')];
            }
            if ($hasAdminPermission('view_reports')) {
                $operationsLinks[] = ['label' => 'التقارير', 'url' => $demoOrAdminUrl('reports', route('admin.reports.index')), 'active' => request()->routeIs('admin.reports.*')];
            }
            if ($hasAdminPermission('view_audit_logs')) {
                $operationsLinks[] = ['label' => 'Audit Logs', 'url' => route('admin.audit-logs.index'), 'active' => request()->routeIs('admin.audit-logs.*')];
            }
            if ($hasAdminPermission('view_customer_leads')) {
                $operationsLinks[] = ['label' => 'بيانات العملاء', 'url' => route('admin.customer-leads.index'), 'active' => request()->routeIs('admin.customer-leads.*')];
            }
        }

        $digitalMenuLinks = [];
        if (!$isDeliveryUser && !$isKitchenUser && $hasAdminPermission('manage_digital_menu') && Route::has('admin.digital-menu.settings')) {
            $digitalMenuLinks[] = ['label' => 'الإعدادات', 'url' => $demoOrAdminUrl('digital-menu/settings', route('admin.digital-menu.settings')), 'active' => request()->routeIs('admin.digital-menu.settings')];
            if (Route::has('admin.digital-menu.categories')) {
                $digitalMenuLinks[] = ['label' => 'الأقسام', 'url' => $demoOrAdminUrl('digital-menu/categories', route('admin.digital-menu.categories')), 'active' => request()->routeIs('admin.digital-menu.categories')];
            }
            if (Route::has('admin.digital-menu.items')) {
                $digitalMenuLinks[] = ['label' => 'المنتجات', 'url' => $demoOrAdminUrl('digital-menu/items', route('admin.digital-menu.items')), 'active' => request()->routeIs('admin.digital-menu.items')];
            }
            if (Route::has('admin.digital-menu.qr')) {
                $digitalMenuLinks[] = ['label' => 'QR والروابط', 'url' => $demoOrAdminUrl('digital-menu/qr', route('admin.digital-menu.qr')), 'active' => request()->routeIs('admin.digital-menu.qr*')];
            }
        }

        $adsLinks = [];
        if (!$isDeliveryUser && !$isKitchenUser && Route::has('admin.popup-campaign.edit')) {
            $adsLinks[] = ['label' => 'الإعلان المنبثق', 'url' => $demoOrAdminUrl('popup-campaign', route('admin.popup-campaign.edit')), 'active' => request()->routeIs('admin.popup-campaign.*')];
        }

        return [
            'is_delivery_user' => $isDeliveryUser,
            'is_kitchen_user' => $isKitchenUser,
            'dashboard_group_open' => self::anyActive($dashboardLinks),
            'operations_group_open' => self::anyActive($operationsLinks),
            'digital_menu_group_open' => self::anyActive($digitalMenuLinks),
            'ads_group_open' => self::anyActive($adsLinks),
            'dashboard_links' => $dashboardLinks,
            'operations_links' => $operationsLinks,
            'digital_menu_links' => $digitalMenuLinks,
            'ads_links' => $adsLinks,
            'demo_or_admin_url' => $demoOrAdminUrl,
        ];
    }

    private static function anyActive(array $links): bool
    {
        foreach ($links as $link) {
            if (!empty($link['active'])) {
                return true;
            }
        }

        return false;
    }
}
