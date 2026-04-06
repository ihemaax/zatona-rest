<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'لوحة الإدارة' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @php
        $manifestPath = public_path('build/manifest.json');
        $hasAdminLayoutEntry = false;

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
            $hasAdminLayoutEntry = isset($manifest['resources/css/admin-layout.css']);
        }
    @endphp

    @if($hasAdminLayoutEntry)
        @vite(['resources/css/admin-layout.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/admin-layout.css')) !!}</style>
        @vite(['resources/js/app.js'])
    @endif
</head>
<body>

@php
    $adminUser = auth()->user();
    $isDemoDashboard = ($isDemoDashboard ?? false)
        || request()->routeIs('admin.dashboard.demo')
        || request()->routeIs('admin.demo.module');
    $dashboardHomeRoute = $isDemoDashboard ? 'admin.dashboard.demo' : 'admin.dashboard';
    $hasAdminPermission = function (string $permission) use ($isDemoDashboard, $adminUser) {
        return $isDemoDashboard || $adminUser?->isSuperAdmin() || $adminUser?->hasPermission($permission);
    };
    $demoOrAdminUrl = function (string $demoPath, string $adminUrl) use ($isDemoDashboard) {
        return $isDemoDashboard
            ? route('admin.demo.module', ['path' => $demoPath])
            : $adminUrl;
    };
    $isDeliveryUser = $adminUser?->role === \App\Models\User::ROLE_DELIVERY;
    $isKitchenUser = $adminUser?->role === \App\Models\User::ROLE_KITCHEN;
    $newOrdersCount = $layoutAdminNewOrdersCount ?? 0;

    $dashboardGroupOpen =
        request()->routeIs('admin.dashboard') ||
        request()->routeIs('admin.dashboard.demo') ||
        request()->routeIs('admin.delivery.dashboard') ||
        request()->routeIs('admin.delivery.management') ||
        request()->routeIs('delivery.orders.*') ||
        request()->routeIs('admin.orders.index') ||
        request()->routeIs('admin.orders.show') ||
        request()->routeIs('admin.orders.delivery') ||
        request()->routeIs('admin.orders.pickup') ||
        request()->routeIs('admin.kitchen.*') ||
        request()->routeIs('admin.orders.ready');

    $operationsGroupOpen =
        request()->routeIs('admin.branches.*') ||
        request()->routeIs('admin.categories.*') ||
        request()->routeIs('admin.products.*') ||
        request()->routeIs('admin.coupons.*') ||
        request()->routeIs('admin.settings.*') ||
        request()->routeIs('admin.staff.*') ||
        request()->routeIs('admin.reports.*');

    $digitalMenuGroupOpen =
        request()->routeIs('admin.digital-menu.settings') ||
        request()->routeIs('admin.digital-menu.categories') ||
        request()->routeIs('admin.digital-menu.items') ||
        request()->routeIs('admin.digital-menu.qr*');

    $adsGroupOpen =
        request()->routeIs('admin.popup-campaign.*');
@endphp

<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sb-scroll">
            <div class="sb-mobile-head">
                <div>
                    <div class="sb-brand-title">لوحة الإدارة</div>
                    <div class="sb-brand-sub">إدارة الطلبات والتشغيل</div>
                </div>

                <button class="sb-close" type="button" id="sidebarCloseBtn" aria-label="إغلاق القائمة">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="sb-brand">
                <img
                    src="https://imgg.io/images/2026/04/04/991cb410fcfc5d1d62d88526434044c8.png"
                    alt="Logo"
                    class="sb-brand-logo-image"
                >

                <div>
                    <h2 class="sb-brand-title">لوحة الإدارة</h2>
                    <p class="sb-brand-sub">تشغيل ومتابعة الطلبات والفروع</p>
                </div>
            </div>

            <div class="sb-group {{ $dashboardGroupOpen ? 'active' : '' }}" data-group>
                <button type="button" class="sb-group-toggle" data-group-toggle>
                    <svg class="sb-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                        <path d="M3 10.5L12 3l9 7.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5.25 9.75V20a.75.75 0 00.75.75h4.5v-6h3v6H18a.75.75 0 00.75-.75V9.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="sb-link-text">لوحة التحكم</span>
                    <svg class="sb-arrow" fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div class="sb-submenu">
                    @if($isDeliveryUser)
                        <a href="{{ $demoOrAdminUrl('delivery-dashboard', url('/admin/delivery-dashboard')) }}" class="sb-sublink {{ request()->is('admin/delivery-dashboard') || request()->is('delivery-dashboard') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>طلباتي (الدليفري)</span>
                        </a>
                    @elseif($isKitchenUser)
                        @if($adminUser?->isSuperAdmin() || $adminUser?->isOwner() || $adminUser?->role === \App\Models\User::ROLE_MANAGER || $adminUser?->role === \App\Models\User::ROLE_KITCHEN)
                            <a href="{{ $demoOrAdminUrl('kitchen', route('admin.kitchen.index')) }}" class="sb-sublink {{ request()->routeIs('admin.kitchen.*') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>شاشة المطبخ</span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route($dashboardHomeRoute) }}" class="sb-sublink {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.dashboard.demo') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الرئيسية</span>
                        </a>

                            <a href="{{ $demoOrAdminUrl('orders', route('admin.orders.index')) }}" class="sb-sublink {{ request()->routeIs('admin.orders.index') || request()->routeIs('admin.orders.show') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>جميع الطلبات</span>
                                <span class="sb-badge" id="sidebarNewOrdersCount">{{ $newOrdersCount }}</span>
                            </a>

                            <a href="{{ $demoOrAdminUrl('orders-delivery', route('admin.orders.delivery')) }}" class="sb-sublink {{ request()->routeIs('admin.orders.delivery') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>طلبات التوصيل</span>
                            </a>

                        <a href="{{ $demoOrAdminUrl('orders-pickup', route('admin.orders.pickup')) }}" class="sb-sublink {{ request()->routeIs('admin.orders.pickup') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>طلبات الاستلام</span>
                        </a>

                        <a href="{{ $demoOrAdminUrl('kitchen', route('admin.kitchen.index')) }}" class="sb-sublink {{ request()->routeIs('admin.kitchen.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>شاشة المطبخ</span>
                        </a>

                        <a href="{{ $demoOrAdminUrl('ready-orders', route('admin.orders.ready')) }}" class="sb-sublink {{ request()->routeIs('admin.orders.ready') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الطلبات الجاهزة</span>
                        </a>

                        @if($hasAdminPermission('manage_delivery'))
                            <a href="{{ $demoOrAdminUrl('delivery-management', route('admin.delivery.management')) }}" class="sb-sublink {{ request()->routeIs('admin.delivery.management') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>متابعة الدليفري</span>
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            @unless($isDeliveryUser || $isKitchenUser)
            <div class="sb-group {{ $operationsGroupOpen ? 'active' : '' }}" data-group>
                <button type="button" class="sb-group-toggle" data-group-toggle>
                    <svg class="sb-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                        <path d="M12 20s6-4.8 6-10a6 6 0 10-12 0c0 5.2 6 10 6 10z" stroke="currentColor" stroke-linejoin="round"/>
                        <circle cx="12" cy="10" r="2.25" stroke="currentColor"/>
                    </svg>
                    <span class="sb-link-text">التشغيل والإدارة</span>
                    <svg class="sb-arrow" fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div class="sb-submenu">
                    @if($hasAdminPermission('manage_branches'))
                        <a href="{{ $demoOrAdminUrl('branches', route('admin.branches.index')) }}" class="sb-sublink {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الفروع</span>
                        </a>
                    @endif

                    @if($hasAdminPermission('manage_categories'))
                        <a href="{{ $demoOrAdminUrl('categories', route('admin.categories.index')) }}" class="sb-sublink {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الأقسام</span>
                        </a>
                    @endif

                    @if($hasAdminPermission('manage_products'))
                        <a href="{{ $demoOrAdminUrl('products', route('admin.products.index')) }}" class="sb-sublink {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>المنتجات</span>
                        </a>
                    @endif

                    <a href="{{ $demoOrAdminUrl('coupons', url('/admin/coupons')) }}" class="sb-sublink {{ request()->is('admin/coupons*') ? 'active' : '' }}">
                        <span class="sb-sublink-dot"></span>
                        <span>كوبونات الخصم</span>
                    </a>

                    @if($hasAdminPermission('manage_settings'))
                        <a href="{{ $demoOrAdminUrl('settings', route('admin.settings.edit')) }}" class="sb-sublink {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الإعدادات</span>
                        </a>
                    @endif

                    @if($hasAdminPermission('manage_staff'))
                        <a href="{{ $demoOrAdminUrl('staff', route('admin.staff.index')) }}" class="sb-sublink {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الموظفون</span>
                        </a>
                    @endif

                    @if($hasAdminPermission('view_reports'))
                        <a href="{{ $demoOrAdminUrl('reports', route('admin.reports.index')) }}" class="sb-sublink {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>التقارير</span>
                        </a>
                    @endif
                </div>
            </div>
            @endunless

            @unless($isDeliveryUser || $isKitchenUser)
            @if($hasAdminPermission('manage_digital_menu') && Route::has('admin.digital-menu.settings'))
                <div class="sb-group {{ $digitalMenuGroupOpen ? 'active' : '' }}" data-group>
                    <button type="button" class="sb-group-toggle" data-group-toggle>
                        <svg class="sb-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                            <rect x="4.75" y="4.75" width="14.5" height="14.5" rx="2" stroke="currentColor"/>
                            <path d="M8 9h8M8 12h8M8 15h5" stroke="currentColor" stroke-linecap="round"/>
                        </svg>
                        <span class="sb-link-text">المنيو الإلكتروني</span>
                        <svg class="sb-arrow" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="sb-submenu">
                        <a href="{{ $demoOrAdminUrl('digital-menu/settings', route('admin.digital-menu.settings')) }}" class="sb-sublink {{ request()->routeIs('admin.digital-menu.settings') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الإعدادات</span>
                        </a>

                        @if(Route::has('admin.digital-menu.categories'))
                            <a href="{{ $demoOrAdminUrl('digital-menu/categories', route('admin.digital-menu.categories')) }}" class="sb-sublink {{ request()->routeIs('admin.digital-menu.categories') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>الأقسام</span>
                            </a>
                        @endif

                        @if(Route::has('admin.digital-menu.items'))
                            <a href="{{ $demoOrAdminUrl('digital-menu/items', route('admin.digital-menu.items')) }}" class="sb-sublink {{ request()->routeIs('admin.digital-menu.items') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>المنتجات</span>
                            </a>
                        @endif

                        @if(Route::has('admin.digital-menu.qr'))
                            <a href="{{ $demoOrAdminUrl('digital-menu/qr', route('admin.digital-menu.qr')) }}" class="sb-sublink {{ request()->routeIs('admin.digital-menu.qr*') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>QR والروابط</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if(Route::has('admin.popup-campaign.edit'))
                <div class="sb-group {{ $adsGroupOpen ? 'active' : '' }}" data-group>
                    <button type="button" class="sb-group-toggle" data-group-toggle>
                        <svg class="sb-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                            <rect x="4.75" y="5.75" width="14.5" height="12.5" rx="2" stroke="currentColor"/>
                            <path d="M8 10h8M8 13h5" stroke="currentColor" stroke-linecap="round"/>
                        </svg>
                        <span class="sb-link-text">الإعلانات</span>
                        <svg class="sb-arrow" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="sb-submenu">
                        <a href="{{ $demoOrAdminUrl('popup-campaign', route('admin.popup-campaign.edit')) }}" class="sb-sublink {{ request()->routeIs('admin.popup-campaign.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الإعلان المنبثق</span>
                        </a>
                    </div>
                </div>
            @endif
            @endunless
        </div>

        <div class="sb-footer">
            @if($isDemoDashboard)
                <div class="sb-logout" style="cursor:default;justify-content:center;">
                    <span>وضع العرض التجريبي</span>
                </div>
            @else
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sb-logout">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                            <path d="M10 17l-5-5 5-5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5 12h9" stroke="currentColor" stroke-linecap="round"/>
                            <path d="M14 5.75h2.25A1.75 1.75 0 0118 7.5v9a1.75 1.75 0 01-1.75 1.75H14" stroke="currentColor" stroke-linecap="round"/>
                        </svg>
                        <span>تسجيل الخروج</span>
                    </button>
                </form>
            @endif
        </div>
    </aside>

    <div class="admin-backdrop" id="adminBackdrop"></div>

    <main class="admin-main">
        <div class="admin-topbar">
            <div class="topbar-start">
                <button class="mobile-menu-btn" type="button" id="mobileMenuBtn" aria-label="فتح القائمة">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-linecap="round"/>
                        <line x1="3" y1="12" x2="21" y2="12" stroke="currentColor" stroke-linecap="round"/>
                        <line x1="3" y1="18" x2="21" y2="18" stroke="currentColor" stroke-linecap="round"/>
                    </svg>
                </button>

                <div>
                    <h1 class="admin-topbar-title">{{ $pageTitle ?? 'لوحة الإدارة' }}</h1>
                    <p class="admin-topbar-subtitle">{{ $pageSubtitle ?? 'إدارة الطلبات والتشغيل اليومي بشكل منظم وواضح.' }}</p>
                </div>
            </div>

            <div class="topbar-status">
                <span class="status-dot"></span>
                النظام يعمل بشكل طبيعي
            </div>
        </div>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger mb-4">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

@if(!request()->is('admin/ai-assistant'))
    <button type="button" class="admin-ai-fab" id="adminAiFab" aria-label="فتح المساعد الذكي">
        <span class="admin-ai-fab-badge">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.9">
                <path d="M12 3l1.9 3.86L18 8.75l-2.95 2.88.7 4.07L12 13.95 8.25 15.7l.7-4.07L6 8.75l4.1-1.89L12 3z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span>المساعد الذكي</span>
    </button>

    <div class="admin-ai-overlay" id="adminAiOverlay"></div>

    <div class="admin-ai-popup" id="adminAiPopup" aria-hidden="true">
        <div class="admin-ai-popup-head">
            <div class="admin-ai-popup-info">
                <h3 class="admin-ai-popup-title">المساعد الذكي</h3>
            </div>

            <div class="admin-ai-popup-actions">
                <button type="button" class="admin-ai-icon-btn" id="adminAiMinimize" aria-label="تصغير">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M6 12h12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <a href="{{ $demoOrAdminUrl('ai-assistant', url('/admin/ai-assistant')) }}" class="admin-ai-icon-btn" title="فتح في صفحة كاملة">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                        <path d="M14 5h5v5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 14L19 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 13v4a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                <button type="button" class="admin-ai-icon-btn" id="adminAiClose" aria-label="إغلاق">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="admin-ai-popup-body">
            <iframe
                id="adminAiFrame"
                class="admin-ai-frame"
                src="about:blank"
                data-src="{{ $demoOrAdminUrl('ai-assistant', url('/admin/ai-assistant?embed=1')) }}"
                loading="lazy"
                referrerpolicy="same-origin"
            ></iframe>
            <div class="admin-ai-fallback" id="adminAiFallback">
                <div class="admin-ai-fallback-card">
                    <p class="admin-ai-fallback-title">تعذر تحميل نافذة المساعد داخل الودجت</p>
                    <p class="admin-ai-fallback-text">استخدم فتح الصفحة الكاملة للمساعد وسيعمل بشكل طبيعي.</p>
                    <a href="{{ $demoOrAdminUrl('ai-assistant', url('/admin/ai-assistant')) }}" class="btn-admin-soft">فتح صفحة المساعد الكاملة</a>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-ai-toast" id="adminAiToast">
        <div class="admin-ai-toast-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.9">
                <path d="M12 3l1.9 3.86L18 8.75l-2.95 2.88.7 4.07L12 13.95 8.25 15.7l.7-4.07L6 8.75l4.1-1.89L12 3z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div>
            <p class="admin-ai-toast-title">وصل رد جديد من المساعد</p>
            <p class="admin-ai-toast-text">اضغط لفتح المحادثة ومتابعة الرد الأخير.</p>
        </div>
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminBackdrop');
    const menuBtn = document.getElementById('mobileMenuBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');
    const BP = 992;

    const aiFab = document.getElementById('adminAiFab');
    const aiOverlay = document.getElementById('adminAiOverlay');
    const aiPopup = document.getElementById('adminAiPopup');
    const aiFrame = document.getElementById('adminAiFrame');
    const aiClose = document.getElementById('adminAiClose');
    const aiMinimize = document.getElementById('adminAiMinimize');
    const aiToast = document.getElementById('adminAiToast');
    const aiFallback = document.getElementById('adminAiFallback');

    const AI_STORAGE_KEY = 'admin_ai_widget_state_v1';
    const AI_NOTIFY_KEY = 'admin_ai_unread_v1';
    const currentStaffRole = @json(auth()->user()?->role);
    const isReadyOrdersPage = @json(request()->routeIs('admin.orders.ready'));
    const readyOrdersPollUrl = @json(route('admin.orders.ready.poll', absolute: false));
    const isDemoDashboardMode = @json($isDemoDashboard);

    let toastTimer = null;
    let audioCtx = null;
    let aiLoadGuardTimer = null;

    const isMobile = () => window.innerWidth < BP;

    const openSidebar = () => {
        if (!isMobile()) return;
        sidebar?.classList.add('show');
        backdrop?.classList.add('show');
        body.classList.add('sidebar-open');
    };

    const closeSidebar = () => {
        sidebar?.classList.remove('show');
        backdrop?.classList.remove('show');
        body.classList.remove('sidebar-open');
    };

    function getAiState() {
        try {
            return JSON.parse(localStorage.getItem(AI_STORAGE_KEY) || '{}');
        } catch (e) {
            return {};
        }
    }

    function setAiState(nextState = {}) {
        const current = getAiState();
        localStorage.setItem(AI_STORAGE_KEY, JSON.stringify({
            ...current,
            ...nextState
        }));
    }

    function setUnreadAiNotification(value) {
        localStorage.setItem(AI_NOTIFY_KEY, value ? '1' : '0');
        if (value) {
            aiFab?.classList.add('has-notification');
        } else {
            aiFab?.classList.remove('has-notification');
        }
    }

    function readUnreadAiNotification() {
        return localStorage.getItem(AI_NOTIFY_KEY) === '1';
    }

    function lazyLoadAiFrame() {
        if (aiFrame && (!aiFrame.getAttribute('src') || aiFrame.getAttribute('src') === 'about:blank')) {
            aiFallback?.classList.remove('show');
            aiFrame.style.display = 'block';
            aiFrame.setAttribute('src', aiFrame.dataset.src || '/admin/ai-assistant?embed=1');

            if (aiLoadGuardTimer) {
                clearTimeout(aiLoadGuardTimer);
            }

            aiLoadGuardTimer = setTimeout(() => {
                if (aiFallback?.classList.contains('show')) return;
                showAiFallback();
            }, 5000);
        }
    }

    function showAiFallback() {
        aiFrame.style.display = 'none';
        aiFallback?.classList.add('show');
    }

    function inspectAiFrame() {
        if (!aiFrame) return;

        try {
            const frameDoc = aiFrame.contentDocument;
            const textLength = (frameDoc?.body?.innerText || '').trim().length;
            const hasRenderableNodes = (frameDoc?.body?.children?.length || 0) > 0;

            if (!frameDoc || (!hasRenderableNodes && textLength === 0)) {
                showAiFallback();
            } else {
                aiFallback?.classList.remove('show');
                aiFrame.style.display = 'block';
            }
        } catch (e) {
            showAiFallback();
        } finally {
            if (aiLoadGuardTimer) {
                clearTimeout(aiLoadGuardTimer);
                aiLoadGuardTimer = null;
            }
        }
    }

    function openAiPopup() {
        closeSidebar();
        lazyLoadAiFrame();

        aiPopup?.classList.add('show');
        aiOverlay?.classList.add('show');
        aiPopup?.classList.remove('minimized');
        aiPopup?.setAttribute('aria-hidden', 'false');

        setAiState({
            open: true,
            minimized: false
        });

        setUnreadAiNotification(false);
        hideAiToast();
    }

    function closeAiPopup() {
        aiPopup?.classList.remove('show');
        aiPopup?.classList.remove('minimized');
        aiOverlay?.classList.remove('show');
        aiPopup?.setAttribute('aria-hidden', 'true');

        setAiState({
            open: false,
            minimized: false
        });
    }

    function minimizeAiPopup() {
        aiPopup?.classList.add('show');
        aiPopup?.classList.add('minimized');
        aiOverlay?.classList.remove('show');
        aiPopup?.setAttribute('aria-hidden', 'false');

        lazyLoadAiFrame();

        setAiState({
            open: true,
            minimized: true
        });
    }

    function restoreAiPopup() {
        aiPopup?.classList.add('show');
        aiPopup?.classList.remove('minimized');
        aiOverlay?.classList.add('show');
        aiPopup?.setAttribute('aria-hidden', 'false');

        setAiState({
            open: true,
            minimized: false
        });

        setUnreadAiNotification(false);
        hideAiToast();
    }

    function showAiToast() {
        if (!aiToast) return;

        aiToast.classList.add('show');

        if (toastTimer) {
            clearTimeout(toastTimer);
        }

        toastTimer = setTimeout(() => {
            hideAiToast();
        }, 5000);
    }

    function hideAiToast() {
        aiToast?.classList.remove('show');
    }

    function beepAiNotification() {
        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return;

            if (!audioCtx) {
                audioCtx = new AudioContextClass();
            }

            const oscillator = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            oscillator.type = 'sine';
            oscillator.frequency.value = 880;
            gain.gain.value = 0.03;

            oscillator.connect(gain);
            gain.connect(audioCtx.destination);

            oscillator.start();

            setTimeout(() => {
                oscillator.stop();
            }, 120);
        } catch (e) {}
    }

    function showCashierReadyToast(text) {
        const popup = document.createElement('div');
        popup.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;background:#166534;color:#fff;padding:12px 16px;border-radius:12px;box-shadow:0 10px 28px rgba(0,0,0,.2);font-weight:800;font-size:.85rem;';
        popup.textContent = text;
        document.body.appendChild(popup);

        setTimeout(() => {
            popup.style.opacity = '0';
            popup.style.transition = 'opacity .35s ease';
        }, 2600);

        setTimeout(() => popup.remove(), 3100);
    }

    function enhanceMobileTables() {
        if (window.innerWidth > 767) return;

        document.querySelectorAll('.admin-table-wrap table').forEach((table) => {
            const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
            if (!headers.length) return;

            table.classList.add('table-mobile-stack');

            table.querySelectorAll('tbody tr').forEach((row) => {
                Array.from(row.children).forEach((cell, index) => {
                    if (cell.tagName !== 'TD') return;
                    if (!cell.getAttribute('data-label')) {
                        cell.setAttribute('data-label', headers[index] || 'بيانات');
                    }
                });
            });

        });
    }

    menuBtn?.addEventListener('click', () => {
        sidebar?.classList.contains('show') ? closeSidebar() : openSidebar();
    });

    closeBtn?.addEventListener('click', closeSidebar);
    backdrop?.addEventListener('click', closeSidebar);

    sidebar?.querySelectorAll('a.sb-sublink').forEach(link => {
        link.addEventListener('click', () => {
            if (isMobile()) closeSidebar();
        });
    });

    const groups = sidebar?.querySelectorAll('[data-group]');
    groups?.forEach(group => {
        const toggle = group.querySelector('[data-group-toggle]');
        toggle?.addEventListener('click', function () {
            group.classList.toggle('active');
        });
    });

    aiFab?.addEventListener('click', () => {
        const isShown = aiPopup?.classList.contains('show');
        const isMinimized = aiPopup?.classList.contains('minimized');

        if (!isShown) {
            openAiPopup();
            return;
        }

        if (isMinimized) {
            restoreAiPopup();
            return;
        }

        minimizeAiPopup();
    });

    aiClose?.addEventListener('click', closeAiPopup);
    aiOverlay?.addEventListener('click', closeAiPopup);
    aiMinimize?.addEventListener('click', minimizeAiPopup);
    aiToast?.addEventListener('click', openAiPopup);
    aiFrame?.addEventListener('load', inspectAiFrame);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSidebar();

            if (aiPopup?.classList.contains('show') && !aiPopup?.classList.contains('minimized')) {
                minimizeAiPopup();
            }
        }
    });

    window.addEventListener('resize', () => {
        if (!isMobile()) closeSidebar();
        enhanceMobileTables();
    });

    window.addEventListener('message', function (event) {
        if (!event.data || typeof event.data !== 'object') return;
        if (event.data.source !== 'admin-ai-assistant') return;

        if (event.data.type === 'assistant-replied') {
            const popupVisible = aiPopup?.classList.contains('show') && !aiPopup?.classList.contains('minimized');

            if (!popupVisible) {
                setUnreadAiNotification(true);
                showAiToast();
                beepAiNotification();
            }
        }

        if (event.data.type === 'assistant-focus') {
            openAiPopup();
        }
    });

    const savedState = getAiState();

    if (readUnreadAiNotification()) {
        aiFab?.classList.add('has-notification');
    }

    if (savedState.open) {
        lazyLoadAiFrame();

        if (savedState.minimized) {
            aiPopup?.classList.add('show', 'minimized');
            aiPopup?.setAttribute('aria-hidden', 'false');
        } else {
            aiPopup?.classList.add('show');
            aiOverlay?.classList.add('show');
            aiPopup?.setAttribute('aria-hidden', 'false');
        }
    }

    enhanceMobileTables();

    if (isDemoDashboardMode) {
        document.addEventListener('click', function (event) {
            const link = event.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

            const targetUrl = new URL(href, window.location.origin);
            if (!targetUrl.pathname.startsWith('/admin/')) return;

            event.preventDefault();

            if (targetUrl.pathname === '/admin/dashboard') {
                window.location.href = '{{ route('admin.dashboard.demo') }}';
                return;
            }

            window.location.href = `/demo${targetUrl.pathname}${targetUrl.search}`;
        });
    }

    if (window.innerWidth <= 767) {
        const contentRoot = document.querySelector('.admin-content');
        if (contentRoot) {
            const mobileTablesObserver = new MutationObserver(() => enhanceMobileTables());
            mobileTablesObserver.observe(contentRoot, { childList: true, subtree: true });
        }
    }

    if (currentStaffRole === 'cashier' && !isReadyOrdersPage) {
        let seenReadyOrderIds = new Set();
        let readyPollInitialized = false;

        const notifyCashierReadyOrders = async () => {
            try {
                const response = await fetch(readyOrdersPollUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });

                if (!response.ok) return;

                const data = await response.json();
                const allReadyOrders = [
                    ...(data.delivery_orders || []),
                    ...(data.pickup_orders || []),
                ];

                const currentIds = new Set(
                    allReadyOrders.map((order) => Number(order.id)).filter(Boolean)
                );

                if (readyPollInitialized) {
                    const newReadyCount = allReadyOrders.filter((order) => !seenReadyOrderIds.has(Number(order.id))).length;

                    if (newReadyCount > 0) {
                        showCashierReadyToast(`تم تجهيز ${newReadyCount} طلب جديد وجاهز للاستلام.`);
                        beepAiNotification();
                    }
                }

                seenReadyOrderIds = currentIds;
                readyPollInitialized = true;
            } catch (_) {
                // تجاهل أخطاء الشبكة المؤقتة
            }
        };

        notifyCashierReadyOrders();
        setInterval(notifyCashierReadyOrders, 3000);

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                notifyCashierReadyOrders();
            }
        });
    }
});
</script>

@stack('scripts')
</body>
</html>
