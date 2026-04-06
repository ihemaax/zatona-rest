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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <x-analytics />

        @vite(['resources/css/front-layout.css', 'resources/js/app.js'])

</head>
<body>

@php
    $newOrdersCount = $layoutNewOrdersCount ?? 0;
    $cartCount = count(session('cart', []));
    $currentRoute = Route::currentRouteName();
@endphp

<nav class="navbar navbar-expand-lg main-navbar" id="mainNavbar">
    <div class="container py-2">
        <a class="navbar-brand brand-logo" href="{{ route('home') }}">
            <span>{{ __('site.brand') }}</span>
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
                    @if(auth()->user()->is_admin)
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

                    <a href="{{ route('login') }}" class="btn-soft">{{ __('site.login') }}</a>
                    <a href="{{ route('register') }}" class="btn-brand">{{ __('site.register') }}</a>
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
                            @if(!auth()->user()->is_admin && Route::has('my.orders'))
                                <a href="{{ route('my.orders') }}">طلباتي</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}">تسجيل الدخول</a>
                            <a href="{{ route('register') }}">إنشاء حساب</a>
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
        @if(auth()->user()->is_admin)
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
                <i class="bi bi-person-fill"></i>
                <span>{{ __('site.login') }}</span>
            </a>
        </div>

        <div class="mobile-bottom-item">
            <a href="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="mobile-bottom-link">
                <i class="bi bi-translate"></i>
                <span>{{ app()->getLocale() === 'ar' ? 'EN' : 'AR' }}</span>
            </a>
        </div>

        <div class="mobile-bottom-item">
            <a href="{{ route('register') }}" class="mobile-bottom-link {{ request()->routeIs('register') ? 'active' : '' }}">
                <i class="bi bi-person-plus-fill"></i>
                <span>{{ __('site.register') }}</span>
            </a>
        </div>
    @endauth
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.getElementById('mainNavbar');
    if (!navbar) return;

    let lastScrollY = window.scrollY;
    let ticking = false;
    const isMobile = () => window.innerWidth <= 767.98;

    function handleNavbar() {
        if (isMobile()) {
            navbar.classList.remove('nav-hidden');
            ticking = false;
            return;
        }

        const currentScrollY = window.scrollY;

        if (currentScrollY <= 10) {
            navbar.classList.remove('nav-hidden');
        } else if (currentScrollY > lastScrollY) {
            navbar.classList.add('nav-hidden');
        } else {
            navbar.classList.remove('nav-hidden');
        }

        lastScrollY = currentScrollY;
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            requestAnimationFrame(handleNavbar);
            ticking = true;
        }
    }, { passive: true });

    window.addEventListener('resize', function () {
        if (isMobile()) {
            navbar.classList.remove('nav-hidden');
        }
    });
});
</script>
@stack('scripts')
</body>
</html>