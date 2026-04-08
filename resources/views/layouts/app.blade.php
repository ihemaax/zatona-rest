@php
    $layoutSetting = $setting ?? null;
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-seo-meta :title="$title ?? __('site.brand')" :description="$metaDescription ?? null" :image="$metaImage ?? null" :site-name="$layoutSetting?->restaurant_name" :site-phone="$layoutSetting?->restaurant_phone" :site-address="$layoutSetting?->restaurant_address" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Instrument+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <x-analytics />

    @php
        $manifestPath = public_path('build/manifest.json');
        $hasFrontLayoutEntry = false;

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
            $hasFrontLayoutEntry = isset($manifest['resources/css/front-layout.css']);
        }
    @endphp

    @if($hasFrontLayoutEntry)
        @vite(['resources/css/front-layout.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/front-layout.css')) !!}</style>
    @endif

</head>
<body>

@php
    $newOrdersCount = $layoutNewOrdersCount ?? 0;
    $cartCount = count(session('cart', []));
    $currentRoute = Route::currentRouteName();
    $shouldBlockDevtools = request()->routeIs([
        'home',
        'products.show',
        'cart.*',
        'checkout.*',
        'order.success',
        'digital.menu.show',
        'my.orders*',
    ]);
@endphp

<nav class="navbar navbar-expand-lg main-navbar" id="mainNavbar">
    <div class="container py-2">
        <a class="navbar-brand brand-logo" href="{{ route('home') }}">
            @if(($layoutSetting?->logo))
                <img src="{{ \App\Support\MediaUrl::fromPath($layoutSetting->logo) }}" alt="{{ $layoutSetting->restaurant_name ?? __('site.brand') }}" class="brand-logo-image">
            @else
                <span class="brand-badge">ZZ</span>
            @endif
            <span>{{ $layoutSetting->restaurant_name ?? __('site.brand') }}</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarContent" aria-controls="mainNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbarContent">
            <div class="ms-auto me-auto mt-3 mt-lg-0"></div>

            <div class="nav-actions ms-lg-auto">
                <div class="lang-switch">
                    <a href="{{ route('locale.switch', 'ar') }}" class="{{ app()->getLocale() === 'ar' ? 'active' : '' }}">AR</a>
                    <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                </div>

                @auth
                    @if(auth()->user()->canAccessAdminPanel())
                        <a href="{{ route('admin.dashboard') }}" class="btn-soft">{{ __('site.admin_panel') }}</a>
                        <a href="{{ route('admin.settings.edit') }}" class="btn-soft">الإعدادات</a>
                        <a href="{{ route('admin.categories.index') }}" class="btn-soft">{{ __('site.categories') }}</a>
                        <a href="{{ route('admin.products.index') }}" class="btn-soft">{{ __('site.products') }}</a>
                        <a href="{{ route('admin.branches.index') }}" class="btn-soft">الفروع</a>
                        <a href="{{ route('admin.orders.index') }}" class="btn-soft position-relative">
                            الطلبات
                            @if($newOrdersCount > 0)
                                <span class="admin-new-badge">{{ $newOrdersCount }}</span>
                            @endif
                        </a>
                    @else
                        <a href="{{ route('cart.index') }}" class="btn-soft">
                            {{ __('site.cart') }}
                            <span class="badge text-bg-danger">{{ $cartCount }}</span>
                        </a>

                        @if(Route::has('my.orders'))
                            <a href="{{ route('my.orders') }}" class="btn-soft">
                                {{ __('site.my_orders') }}
                            </a>
                        @endif
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-brand">{{ __('site.logout') }}</button>
                    </form>
                @else
                    <a href="{{ route('cart.index') }}" class="btn-soft">
                        {{ __('site.cart') }}
                        <span class="badge text-bg-danger">{{ $cartCount }}</span>
                    </a>

                    <a href="{{ route('login') }}" class="btn-brand">{{ __('site.login') }}</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<div class="container page-container py-4">
    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<footer class="site-footer">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="footer-card">
                    <h5 class="footer-title">{{ $layoutSetting->restaurant_name ?? __('site.brand') }}</h5>
                    <p class="footer-text mb-3">
                        منصة طلبات أكل أونلاين بتجربة سهلة وسريعة مع تصميم مريح وسلس لكل الأجهزة.
                    </p>
                    <p class="mb-2"><strong>الهاتف:</strong> {{ $layoutSetting->restaurant_phone ?? '-' }}</p>
                    <p class="mb-0"><strong>العنوان:</strong> {{ $layoutSetting->restaurant_address ?? '-' }}</p>
                </div>
            </div>

            <div class="col-md-2">
                <div class="footer-card">
                    <h6 class="footer-title">روابط مهمة</h6>
                    <div class="footer-links">
                        <a href="{{ route('home') }}">الرئيسية</a>
                        <a href="{{ route('pages.about') }}">من نحن</a>
                        <a href="{{ route('pages.contact') }}">اتصل بنا</a>
                        <a href="{{ route('pages.faq') }}">الأسئلة الشائعة</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="footer-card">
                    <h6 class="footer-title">السياسات</h6>
                    <div class="footer-links">
                        <a href="{{ route('pages.privacy') }}">سياسة الخصوصية</a>

                        @auth
                            @if(!auth()->user()->canAccessAdminPanel() && Route::has('my.orders'))
                                <a href="{{ route('my.orders') }}">طلباتي</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}">تسجيل الدخول</a>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="footer-card">
                    <h6 class="footer-title">حالة المطعم</h6>
                    <p class="footer-text mb-3">متابعة حالة التشغيل وخدمة التوصيل حسب الإعدادات الحالية.</p>

                    <span class="footer-status {{ ($layoutSetting && $layoutSetting->is_open) ? 'open' : 'closed' }}">
                        <span class="footer-status-dot"></span>
                        {{ ($layoutSetting && $layoutSetting->is_open) ? 'المطعم مفتوح الآن' : 'المطعم مغلق الآن' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="small text-muted">
                © {{ date('Y') }} {{ $layoutSetting->restaurant_name ?? __('site.brand') }}. جميع الحقوق محفوظة.
            </div>

            <div class="small text-muted">
                Designed for a better restaurant ordering experience.
            </div>
        </div>
    </div>
</footer>

{{-- Mobile Bottom Bar --}}
<div class="mobile-bottom-bar mobile-only">
    @auth
        @if(auth()->user()->canAccessAdminPanel())
            <div class="mobile-bottom-item">
                <a href="{{ route('admin.dashboard') }}" class="mobile-bottom-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>الرئيسية</span>
                </a>
            </div>

            <div class="mobile-bottom-item">
                <a href="{{ route('admin.orders.index') }}" class="mobile-bottom-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="bi bi-bag-check-fill"></i>
                    <span>الطلبات</span>
                    @if($newOrdersCount > 0)
                        <span class="mobile-bottom-badge">{{ $newOrdersCount }}</span>
                    @endif
                </a>
            </div>

            <div class="mobile-bottom-item">
                <a href="{{ route('admin.products.index') }}" class="mobile-bottom-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>المنتجات</span>
                </a>
            </div>

            <div class="mobile-bottom-item">
                <a href="{{ route('admin.settings.edit') }}" class="mobile-bottom-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders2-vertical"></i>
                    <span>الإعدادات</span>
                </a>
            </div>

            <div class="mobile-bottom-item">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="mobile-bottom-link">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>خروج</span>
                    </button>
                </form>
            </div>
        @else
            <div class="mobile-bottom-item">
                <a href="{{ route('home') }}" class="mobile-bottom-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house-door-fill"></i>
                    <span>الرئيسية</span>
                </a>
            </div>

            <div class="mobile-bottom-item">
                <a href="{{ route('cart.index') }}" class="mobile-bottom-link is-cart {{ request()->routeIs('cart.*') ? 'active' : '' }}">
                    <i class="bi bi-bag-fill"></i>
                    <span>{{ __('site.cart') }}</span>
                    @if($cartCount > 0)
                        <span class="mobile-bottom-badge">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>

            @if(Route::has('my.orders'))
                <div class="mobile-bottom-item">
                    <a href="{{ route('my.orders') }}" class="mobile-bottom-link {{ request()->routeIs('my.orders') ? 'active' : '' }}">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span>طلباتي</span>
                    </a>
                </div>
            @endif

            <div class="mobile-bottom-item">
                <a href="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="mobile-bottom-link">
                    <i class="bi bi-translate"></i>
                    <span>{{ app()->getLocale() === 'ar' ? 'EN' : 'AR' }}</span>
                </a>
            </div>

            <div class="mobile-bottom-item">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="mobile-bottom-link">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>خروج</span>
                    </button>
                </form>
            </div>
        @endif
    @else
        <div class="mobile-bottom-item">
            <a href="{{ route('home') }}" class="mobile-bottom-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="bi bi-house-door-fill"></i>
                <span>الرئيسية</span>
            </a>
        </div>

        <div class="mobile-bottom-item">
            <a href="{{ route('cart.index') }}" class="mobile-bottom-link is-cart {{ request()->routeIs('cart.*') ? 'active' : '' }}">
                <i class="bi bi-bag-fill"></i>
                <span>{{ __('site.cart') }}</span>
                @if($cartCount > 0)
                    <span class="mobile-bottom-badge">{{ $cartCount }}</span>
                @endif
            </a>
        </div>

        <div class="mobile-bottom-item">
            <a href="{{ route('login') }}" class="mobile-bottom-link {{ request()->routeIs('login') ? 'active' : '' }}">
                <span class="mobile-bottom-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 13.75a5.25 5.25 0 1 0 0-10.5 5.25 5.25 0 0 0 0 10.5Zm0 2.25c-4.28 0-7.75 2.86-7.75 6.38 0 .2.16.37.37.37h14.76c.2 0 .37-.16.37-.37 0-3.52-3.47-6.38-7.75-6.38Z"/>
                    </svg>
                </span>
                <span>{{ __('site.login') }}</span>
            </a>
        </div>

        <div class="mobile-bottom-item">
            <a href="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="mobile-bottom-link">
                <span class="mobile-bottom-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4.5 5A1.5 1.5 0 0 0 3 6.5v10A1.5 1.5 0 0 0 4.5 18H11v-1.5H4.5v-10h9v5h1.5v-5A1.5 1.5 0 0 0 13.5 5h-9Zm10.93 5.5-.64 1.37h2.43l-1.79-3.87-1.8 3.87h.84Zm-4.87-2.1V9.9h2.64v1.03c-.35.75-.9 1.43-1.5 2.03l-.06.05-.08-.07a7.6 7.6 0 0 1-1.44-2.01h-1.1c.35.96.92 1.85 1.63 2.6l.1.1-.11.08c-.56.42-1.2.77-1.88 1.03v1.11c.95-.3 1.84-.78 2.61-1.4l.1-.08.1.08c.78.62 1.67 1.1 2.62 1.4v-1.11a6.9 6.9 0 0 1-1.9-1.04l-.1-.08.09-.1c.8-.82 1.44-1.8 1.8-2.84l.04-.12V8.4h-2.38V7h-1.18v1.4h-2.64Zm8.54 6.35h-1.72l-.7 1.5h-1.35l2.96-6.25h1.17l2.93 6.25h-1.35l-.67-1.5h-1.27Z"/>
                    </svg>
                </span>
                <span>{{ app()->getLocale() === 'ar' ? 'EN' : 'AR' }}</span>
            </a>
        </div>

        <div class="mobile-bottom-item">
            <a href="{{ route('pages.contact') }}" class="mobile-bottom-link mobile-bottom-widget {{ request()->routeIs('pages.contact') ? 'active' : '' }}">
                <span class="mobile-bottom-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2.25a9.75 9.75 0 0 0-9.75 9.75v4.53a2.22 2.22 0 0 0 2.22 2.22h1.28a1.5 1.5 0 0 0 1.5-1.5v-4.5a1.5 1.5 0 0 0-1.5-1.5H3.82a8.25 8.25 0 0 1 16.36 0h-1.93a1.5 1.5 0 0 0-1.5 1.5v4.5a1.5 1.5 0 0 0 1.5 1.5h.93A3.57 3.57 0 0 1 15.75 21h-2.62a1.5 1.5 0 0 0 0 3h2.62a6.57 6.57 0 0 0 6.56-6.56V12A9.75 9.75 0 0 0 12 2.25Z"/>
                    </svg>
                </span>
                <span>الدعم</span>
            </a>
        </div>
    @endauth
</div>

<script nonce="{{ $cspNonce }}" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>

@if($shouldBlockDevtools)
    <script nonce="{{ $cspNonce }}">
        (function () {
            const blockedCombinations = new Set(['i', 'j', 'c', 'u', 's']);

            document.addEventListener('contextmenu', function (event) {
                event.preventDefault();
            });

            document.addEventListener('keydown', function (event) {
                const key = (event.key || '').toLowerCase();
                const isF12 = event.key === 'F12';
                const isDevtoolsCombo = (event.ctrlKey || event.metaKey) && event.shiftKey && blockedCombinations.has(key);
                const isViewSource = (event.ctrlKey || event.metaKey) && key === 'u';
                const isSaveShortcut = (event.ctrlKey || event.metaKey) && key === 's';

                if (isF12 || isDevtoolsCombo || isViewSource || isSaveShortcut) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);
        })();
    </script>
@endif

@stack('scripts')
</body>
</html>
