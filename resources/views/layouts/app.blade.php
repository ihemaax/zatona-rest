<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-seo-meta :title="$title ?? __('site.brand')" :description="$metaDescription ?? null" :image="$metaImage ?? null" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <x-analytics />

    <style>
        :root{
            --fb-bg: #f6f3ee;
            --fb-card: #fffdf9;
            --fb-border: #e9e3d8;
            --fb-text: #222222;
            --fb-muted: #6f6a61;

            --fb-primary: #6f7f5f;
            --fb-primary-dark: #5c6a4f;
            --fb-soft: #eef2e8;

            --fb-success: #4e7a57;
            --fb-danger: #c7685d;

            --fb-shadow: 0 8px 24px rgba(60, 52, 40, .08);
            --fb-shadow-lg: 0 16px 40px rgba(60, 52, 40, .12);

            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-sm: 12px;

            --mobile-bar-h: 84px;
            --mobile-safe-gap: 18px;
        }

        *{ box-sizing:border-box; }

        html, body{
            margin:0;
            padding:0;
            min-height:100%;
            overflow-x:hidden;
        }

        body{
            font-family:'Cairo', Tahoma, Arial, sans-serif !important;
            background: linear-gradient(180deg, #f7f4ef 0%, #fbf9f5 52%, #f2eee7 100%);
            color: var(--fb-text);
        }

        a{
            text-decoration:none;
        }

        img{
            max-width:100%;
        }

        .main-navbar{
            position: sticky;
            top: 0;
            z-index: 1100;
            background: rgba(255, 253, 249, .88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(233, 227, 216, .95);
            box-shadow: 0 4px 18px rgba(60, 52, 40, .05);
            transition: transform .28s ease;
        }

        .main-navbar.nav-hidden{
            transform: translateY(-110%);
        }

        .brand-logo{
            display:flex;
            align-items:center;
            gap:12px;
            color: var(--fb-text) !important;
            font-size: 1.05rem;
            font-weight: 900;
            white-space: nowrap;
        }

        .brand-badge{
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
            color: #fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight: 900;
            box-shadow: 0 12px 24px rgba(111, 127, 95, .18);
            flex-shrink: 0;
        }

        .navbar-toggler{
            border: 1px solid var(--fb-border);
            border-radius: 12px;
            background: #fff;
            padding: 8px 10px;
        }

        .navbar-toggler:focus{
            box-shadow: 0 0 0 .2rem rgba(111, 127, 95, .14);
        }

        .navbar-toggler-icon{
            width: 1.2em;
            height: 1.2em;
        }

        .nav-actions{
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            align-items:center;
        }

        .btn-soft{
            border: 1px solid var(--fb-border);
            background: #fff;
            color: var(--fb-text);
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 800;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            transition: .18s ease;
            min-height: 44px;
        }

        .btn-soft:hover{
            background: #f6f2eb;
            color: var(--fb-text);
            border-color: #ddd4c8;
        }

        .btn-brand{
            background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 900;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            min-height: 44px;
            box-shadow: 0 10px 22px rgba(111, 127, 95, .18);
            transition: .18s ease;
        }

        .btn-brand:hover{
            color:#fff;
            opacity:.97;
        }

        .lang-switch{
            display:flex;
            gap:6px;
            padding:5px;
            border:1px solid var(--fb-border);
            border-radius:14px;
            background:#fff;
        }

        .lang-switch a{
            min-width:46px;
            text-align:center;
            padding:8px 12px;
            border-radius:10px;
            color:var(--fb-text);
            font-weight:900;
            transition:.18s ease;
        }

        .lang-switch a.active{
            background: var(--fb-soft);
            color: var(--fb-primary);
        }

        .admin-new-badge{
            min-width:22px;
            height:22px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border-radius:999px;
            font-size:12px;
            font-weight:900;
            background: var(--fb-primary);
            color:#fff;
        }

        .page-container{
            min-height: calc(100vh - 78px);
        }

        .alert{
            border:none;
            border-radius: 16px !important;
            box-shadow: var(--fb-shadow);
            padding: 14px 16px;
            font-weight: 800;
        }

        .alert-success{
            background:#edf8ef;
            color:#166534;
        }

        .alert-danger{
            background:#fff1f1;
            color:#991b1b;
        }

        .alert ul{
            padding-inline-start: 18px;
        }

        .site-footer{
            margin-top: 44px;
            border-top: 1px solid var(--fb-border);
            background: rgba(255, 253, 249, .96);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
        }

        .footer-card{
            background: var(--fb-card);
            border: 1px solid var(--fb-border);
            border-radius: 20px;
            box-shadow: var(--fb-shadow);
            padding: 18px;
            height: 100%;
        }

        .footer-title{
            font-weight: 900;
            margin-bottom: 14px;
            color: var(--fb-text);
            font-size: 1rem;
        }

        .footer-text{
            color: var(--fb-muted);
            line-height: 1.8;
            font-weight: 700;
            font-size: .92rem;
        }

        .footer-links{
            display:flex;
            flex-direction:column;
            gap:10px;
        }

        .footer-links a{
            color: var(--fb-muted);
            font-weight: 800;
            transition: .18s ease;
        }

        .footer-links a:hover{
            color: var(--fb-primary);
        }

        .footer-status{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:9px 14px;
            border-radius:999px;
            font-size:.84rem;
            font-weight:900;
        }

        .footer-status.open{
            background:#edf8ef;
            color:#1f7a40;
        }

        .footer-status.closed{
            background:#fff1f1;
            color:#b42318;
        }

        .footer-status-dot{
            width:8px;
            height:8px;
            border-radius:50%;
            background: currentColor;
            display:inline-block;
        }

        .footer-bottom{
            border-top: 1px solid #ece5da;
            margin-top: 24px;
            padding-top: 18px;
        }

        .text-muted{
            color: var(--fb-muted) !important;
        }

        .badge.text-bg-danger{
            background-color: #d46d60 !important;
            border-radius: 999px;
            font-weight: 900;
            padding: .42em .58em;
        }

        .container{
            position: relative;
        }

        .mobile-bottom-bar{
            display:none;
        }

        .mobile-only{
            display:none !important;
        }

        .desktop-only{
            display:inherit !important;
        }

        @media (max-width: 991.98px){
            .nav-actions{
                width:100%;
                margin-top:14px;
                display:flex;
                flex-direction:column;
                align-items:stretch;
            }

            .nav-actions .btn-soft,
            .nav-actions .btn-brand{
                width:100%;
                justify-content:center;
                text-align:center;
            }

            .lang-switch{
                width:100%;
                justify-content:center;
            }

            .footer-card{
                padding: 16px;
            }
        }

        @media (max-width: 767.98px){
            body{
                padding-bottom: calc(var(--mobile-bar-h) + env(safe-area-inset-bottom, 0px) + 14px);
            }

            .container{
                padding-left:14px !important;
                padding-right:14px !important;
            }

            .brand-logo{
                font-size: .98rem;
            }

            .brand-badge{
                width: 42px;
                height: 42px;
                border-radius: 14px;
                font-size: .95rem;
            }

            .btn-soft,
            .btn-brand{
                font-size: .88rem;
                min-height: 42px;
                padding: 10px 12px;
            }

            .page-container{
                padding-top: 16px !important;
                padding-bottom: 20px !important;
                min-height: auto;
            }

            .site-footer{
                margin-top: 34px;
            }

            .footer-bottom{
                text-align: center;
            }

            .footer-text{
                font-size: .88rem;
            }

            .mobile-only{
                display:flex !important;
            }

            .desktop-only{
                display:none !important;
            }

            .main-navbar{
                position: sticky;
                top: 0;
            }

            .main-navbar .navbar-toggler{
                display:none !important;
            }

            .main-navbar .navbar-collapse{
                display:none !important;
            }

            .mobile-bottom-bar{
                position: fixed;
                left: 12px;
                right: 12px;
                bottom: calc(10px + env(safe-area-inset-bottom, 0px));
                z-index: 1200;
                display:flex;
                align-items:center;
                justify-content:space-between;
                gap:8px;
                padding: 10px 8px;
                background: rgba(255, 253, 249, .94);
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                border: 1px solid rgba(233, 227, 216, .95);
                border-radius: 24px;
                box-shadow: 0 18px 35px rgba(60, 52, 40, .14);
            }

            .mobile-bottom-item{
                flex:1 1 0;
                min-width:0;
                position:relative;
            }

            .mobile-bottom-link{
                border:none;
                background:transparent;
                width:100%;
                min-height:64px;
                border-radius:18px;
                color: var(--fb-muted);
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:center;
                gap:4px;
                padding:8px 6px;
                font-weight:900;
                font-size:.70rem;
                line-height:1.1;
                transition:.18s ease;
                position:relative;
            }

            .mobile-bottom-link i{
                font-size:1.22rem;
                line-height:1;
            }

            .mobile-bottom-link.active{
                background: linear-gradient(180deg, #f2f6ed 0%, #e9f0e2 100%);
                color: var(--fb-primary-dark);
                box-shadow: inset 0 0 0 1px rgba(111, 127, 95, .10);
            }

            .mobile-bottom-link:hover{
                color: var(--fb-primary-dark);
            }

            .mobile-bottom-badge{
                position:absolute;
                top:6px;
                inset-inline-end:18px;
                min-width:20px;
                height:20px;
                padding:0 5px;
                border-radius:999px;
                background: var(--fb-danger);
                color:#fff;
                display:inline-flex;
                align-items:center;
                justify-content:center;
                font-size:11px;
                font-weight:900;
                box-shadow: 0 8px 16px rgba(199, 104, 93, .25);
            }

            .mobile-bottom-link.is-cart i{
                transform: translateY(-1px);
            }
        }

        @media (max-width: 390px){
            .brand-logo span:last-child{
                max-width: 120px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .mobile-bottom-link{
                font-size:.64rem;
            }

            .mobile-bottom-link i{
                font-size:1.12rem;
            }
        }
    </style>
</head>
<body>

@php
    $newOrdersCount = 0;
    $layoutSetting = $setting ?? (\App\Models\Setting::query()->first());
    $cartCount = count(session('cart', []));

    if(auth()->check() && auth()->user()->is_admin) {
        $newOrdersCount = \App\Models\Order::where('is_seen_by_admin', false)->count();
    }

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