@extends('layouts.app')

@section('content')
@php
    $restaurantName = $setting->title ?? __('site.brand');
    $restaurantPhone = $setting->phone ?? null;
    $restaurantAddress = $setting->address ?? null;
    $restaurantSubtitle = $setting->subtitle ?? null;
    $coverImage = $setting->banner ?? null;
    $logoImage = $setting->logo ?? null;
@endphp

<style>
    .elite-home{
        max-width: 1240px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    .elite-hero-shell{
        margin-bottom: 20px;
    }

    .elite-hero-card{
        position: relative;
        overflow: hidden;
        border-radius: 0 0 30px 30px;
        background: var(--fb-card, #fffdf9);
        border: 1px solid var(--fb-border, #e7ddd1);
        box-shadow: var(--fb-shadow-lg, 0 24px 60px rgba(60, 52, 40, .14));
    }

    .elite-cover{
        position: relative;
        min-height: 360px;
        background:
            linear-gradient(180deg, rgba(20,20,18,.10), rgba(20,20,18,.30)),
            url('{{ $coverImage ? \App\Support\MediaUrl::fromPath( $coverImage) : "https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=1600&auto=format&fit=crop" }}') center/cover no-repeat;
    }

    .elite-cover::before{
        content:"";
        position:absolute;
        inset:0;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 26%),
            linear-gradient(180deg, rgba(20,20,18,.02), rgba(20,20,18,.18));
    }

    .elite-cover::after{
        content:"";
        position:absolute;
        inset:auto 0 0 0;
        height:140px;
        background:linear-gradient(to top, rgba(12,12,10,.24), transparent);
    }

    .elite-hero-content{
        position: relative;
        margin-top: -74px;
        padding: 0 24px 22px;
        z-index: 3;
    }

    .elite-identity-card{
        background: rgba(255,253,249,.96);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(233, 227, 216, .95);
        border-radius: 28px;
        box-shadow: 0 24px 60px rgba(60, 52, 40, .14);
        padding: 18px;
    }

    .elite-identity-top{
        display: grid;
        grid-template-columns: auto minmax(0,1fr);
        gap: 18px;
        align-items: center;
    }

    .elite-logo-frame{
        width: 126px;
        height: 126px;
        border-radius: 50%;
        padding: 5px;
        background: linear-gradient(135deg, #ffffff 0%, #efe9de 45%, #d8d1c4 100%);
        box-shadow:
            0 16px 34px rgba(0,0,0,.10),
            0 0 0 1px rgba(255,255,255,.9) inset;
        position: relative;
        flex: 0 0 auto;
    }

    .elite-logo-frame::after{
        content:"";
        position:absolute;
        inset: 8px;
        border-radius:50%;
        border:1px solid rgba(255,255,255,.85);
        pointer-events:none;
    }

    .elite-logo{
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background:
            url('{{ $logoImage ? \App\Support\MediaUrl::fromPath( $logoImage) : "https://via.placeholder.com/500x500?text=Logo" }}') center/cover no-repeat,
            #fff;
        border: 4px solid #fff;
    }

    .elite-brand-copy{
        min-width: 0;
    }

    .elite-brand-kicker{
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #f4f0e8;
        color: #746d63;
        font-size: .75rem;
        font-weight: 900;
        margin-bottom: 10px;
        letter-spacing: .02em;
    }

    .elite-brand-kicker .dot{
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }

    .elite-title{
        margin: 0 0 6px;
        font-size: 2rem;
        line-height: 1.15;
        font-weight: 900;
        color: var(--fb-text, #231f1b);
        letter-spacing: -.02em;
    }

    .elite-subtitle{
        margin: 0;
        color: var(--fb-muted, #7b6f63);
        font-size: .95rem;
        line-height: 1.8;
        font-weight: 800;
    }

    .elite-meta-row{
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding-top: 16px;
        margin-top: 16px;
        border-top: 1px solid #ece5da;
    }

    .elite-pill{
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 14px;
        border-radius: 999px;
        background: #f8f5ef;
        border: 1px solid #e7e0d4;
        color: #5f5a52;
        font-size: .84rem;
        font-weight: 900;
    }

    .elite-pill.success{
        background: #edf8ef;
        color: #1f7a40;
        border-color: #d7ebdc;
    }

    .elite-pill .dot{
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }

    .elite-layout{
        display: grid;
        grid-template-columns: 300px minmax(0,1fr);
        gap: 18px;
        align-items: start;
    }

    .elite-sidebar,
    .elite-main{
        display: grid;
        gap: 18px;
    }

    .elite-card{
        background: var(--fb-card, #fffdf9);
        border: 1px solid var(--fb-border, #e7ddd1);
        border-radius: 24px;
        box-shadow: var(--fb-shadow, 0 10px 24px rgba(60, 52, 40, .05));
        overflow: hidden;
    }

    .elite-card-body{
        padding: 18px;
    }

    .elite-card-title{
        margin: 0 0 14px;
        font-size: 1.02rem;
        font-weight: 900;
        color: var(--fb-text, #231f1b);
    }

    .elite-message{
        position: relative;
        border-radius: 20px;
        padding: 18px;
        background: linear-gradient(135deg, #7e8b70 0%, #a6b595 100%);
        color: #fff;
        box-shadow: 0 16px 34px rgba(111, 127, 95, .18);
        overflow: hidden;
    }

    .elite-message::after{
        content:"";
        position:absolute;
        top:-40px;
        right:-20px;
        width:120px;
        height:120px;
        border-radius:50%;
        background: rgba(255,255,255,.10);
    }

    .elite-message strong{
        position: relative;
        display:block;
        font-size: 1rem;
        line-height: 1.9;
        z-index: 2;
    }

    .elite-info-list{
        display: grid;
        gap: 10px;
    }

    .elite-info-item{
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #4d4a45;
        font-size: .92rem;
        line-height: 1.75;
        font-weight: 700;
    }

    .elite-info-icon{
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: #f3efe8;
        color: #6f7f5f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 1rem;
    }

    .elite-categories{
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }

    .elite-categories::-webkit-scrollbar{
        display:none;
    }

    .elite-cat{
        border: none;
        background: transparent;
        padding: 0;
        min-width: 92px;
        width: 92px;
        text-align: center;
        cursor: pointer;
        text-decoration: none;
    }

    .elite-cat-ring{
        width: 92px;
        height: 92px;
        border-radius: 50%;
        padding: 3px;
        background: linear-gradient(135deg, #ddd8cf 0%, #f3efe8 100%);
        margin: 0 auto 8px;
        transition: .2s ease;
        box-shadow: 0 10px 20px rgba(60, 52, 40, .05);
    }

    .elite-cat-inner{
        width: 100%;
        height: 100%;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #fff;
        background: #f2eee7;
    }

    .elite-cat-inner img{
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .elite-cat-label{
        color: #615d55;
        font-size: .8rem;
        font-weight: 900;
        line-height: 1.35;
    }

    .elite-cat:hover .elite-cat-ring{
        background: linear-gradient(135deg, #7d8d6d 0%, #b6c4a6 100%);
        transform: translateY(-2px);
        box-shadow: 0 14px 24px rgba(111,127,95,.16);
    }

    .elite-cat:hover .elite-cat-label{
        color: var(--fb-primary, #6f7f5f);
    }

    .elite-feed{
        display: grid;
        gap: 18px;
    }

    .elite-section{
        background: var(--fb-card, #fffdf9);
        border: 1px solid var(--fb-border, #e7ddd1);
        border-radius: 24px;
        box-shadow: var(--fb-shadow, 0 10px 24px rgba(60, 52, 40, .05));
        overflow: hidden;
    }

    .elite-section-head{
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding: 18px 18px 10px;
    }

    .elite-section-title{
        margin: 0;
        font-size: 1.06rem;
        font-weight: 900;
        color: var(--fb-text, #231f1b);
    }

    .elite-section-count{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 999px;
        background: #f3efe8;
        color: #7a7469;
        font-size: .76rem;
        font-weight: 900;
    }

    .elite-products{
        display: grid;
        gap: 14px;
        padding: 0 18px 18px;
    }

    .elite-product{
        position: relative;
        display: grid;
        grid-template-columns: 180px minmax(0,1fr);
        gap: 14px;
        align-items: stretch;
        background: linear-gradient(180deg, #fffdfa 0%, #fcf9f4 100%);
        border: 1px solid #ece5da;
        border-radius: 22px;
        overflow: hidden;
        transition: .22s ease;
        box-shadow: 0 10px 24px rgba(60, 52, 40, .05);
    }

    .elite-product:hover{
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(60, 52, 40, .10);
    }

    .elite-product-media{
        position: relative;
        height: 100%;
    }

    .elite-product-image{
        width: 100%;
        height: 100%;
        min-height: 188px;
        object-fit: cover;
        display: block;
        background: #f1ece4;
    }

    .elite-product-badge{
        position: absolute;
        top: 12px;
        inset-inline-start: 12px;
        background: rgba(25,25,22,.72);
        color: #fff;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: .68rem;
        font-weight: 900;
        backdrop-filter: blur(10px);
    }

    .elite-product-body{
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 16px 16px 16px 0;
    }

    .elite-product-top{
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .elite-product-name{
        margin: 0;
        font-size: 1.02rem;
        font-weight: 900;
        color: var(--fb-text, #231f1b);
        line-height: 1.5;
        letter-spacing: -.01em;
    }

    .elite-product-category{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #edf2e7;
        color: #6f7f5f;
        font-size: .68rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .elite-product-desc{
        margin: 0;
        color: #6f6a61;
        font-size: .84rem;
        line-height: 1.8;
        font-weight: 700;
    }

    .elite-product-bottom{
        margin-top: auto;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .elite-price{
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .elite-price-label{
        font-size: .72rem;
        color: #8a847a;
        font-weight: 800;
    }

    .elite-price-value{
        font-size: 1.08rem;
        font-weight: 900;
        color: var(--fb-text, #231f1b);
    }

    .elite-badge{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 999px;
        background: #f5f0e8;
        color: #6f7f5f;
        font-size: .75rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .elite-empty{
        background: var(--fb-card, #fffdf9);
        border: 1px solid var(--fb-border, #e7ddd1);
        border-radius: 22px;
        box-shadow: var(--fb-shadow, 0 10px 24px rgba(60, 52, 40, .05));
        padding: 28px 18px;
        text-align: center;
        color: #6f6a61;
        font-weight: 800;
    }

    @media (max-width: 991.98px){
        .elite-layout{
            grid-template-columns: 1fr;
        }

        .elite-sidebar{
            order: 2;
        }

        .elite-main{
            order: 1;
        }

        .elite-identity-top{
            grid-template-columns: auto minmax(0,1fr);
        }
    }

    @media (max-width: 767.98px){
        .elite-home{
            padding-bottom: 28px;
        }

        .elite-hero-card{
            border-radius: 0 0 22px 22px;
        }

        .elite-cover{
            min-height: 220px;
        }

        .elite-hero-content{
            margin-top: -34px;
            padding: 0 12px 14px;
        }

        .elite-identity-card{
            border-radius: 20px;
            padding: 14px;
        }

        .elite-identity-top{
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .elite-brand-main{
            gap: 12px;
            display: flex;
            align-items: center;
        }

        .elite-logo-frame{
            width: 86px;
            height: 86px;
            padding: 4px;
        }

        .elite-title{
            font-size: 1.12rem;
            line-height: 1.3;
        }

        .elite-subtitle{
            font-size: .82rem;
            line-height: 1.7;
        }

        .elite-brand-kicker{
            font-size: .68rem;
            padding: 6px 10px;
            margin-bottom: 8px;
        }

        .elite-meta-row{
            gap: 8px;
            padding-top: 14px;
            margin-top: 14px;
        }

        .elite-pill{
            font-size: .72rem;
            padding: 8px 10px;
        }

        .elite-card-body{
            padding: 14px;
        }

        .elite-cat,
        .elite-cat-ring{
            width: 74px;
            min-width: 74px;
            height: 74px;
        }

        .elite-cat-label{
            font-size: .74rem;
        }

        .elite-section-head{
            padding: 14px 14px 8px;
        }

        .elite-products{
            padding: 0 14px 14px;
            gap: 12px;
        }

        .elite-product{
            grid-template-columns: 82px minmax(0,1fr);
            gap: 10px;
            border-radius: 16px;
            padding: 8px;
        }

        .elite-product-media{
            border-radius: 12px;
            overflow: hidden;
        }

        .elite-product-image{
            min-height: auto;
            height: 82px;
            border-radius: 12px;
        }

        .elite-product-badge{
            top: auto;
            bottom: 6px;
            inset-inline-start: 6px;
            padding: 5px 8px;
            font-size: .58rem;
        }

        .elite-product-body{
            gap: 7px;
            padding: 0;
            justify-content: center;
        }

        .elite-product-name{
            font-size: .87rem;
            line-height: 1.45;
        }

        .elite-product-category{
            font-size: .60rem;
            padding: 5px 7px;
        }

        .elite-product-desc{
            font-size: .74rem;
            line-height: 1.65;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .elite-product-bottom{
            gap: 8px;
            align-items: center;
        }

        .elite-price-label{
            font-size: .66rem;
        }

        .elite-price-value{
            font-size: .88rem;
        }

        .elite-badge{
            padding: 7px 10px;
            font-size: .70rem;
            border-radius: 12px;
        }
    }

    @media (max-width: 390px){
        .elite-brand-main{
            align-items: flex-start;
        }

        .elite-logo-frame{
            width: 78px;
            height: 78px;
        }

        .elite-title{
            font-size: 1rem;
        }

        .elite-product{
            grid-template-columns: 74px minmax(0,1fr);
        }

        .elite-product-image{
            height: 74px;
        }
    }
</style>

<div class="elite-home">
    <section class="elite-hero-shell">
        <div class="elite-hero-card">
            <div class="elite-cover"></div>

            <div class="elite-hero-content">
                <div class="elite-identity-card">
                    <div class="elite-identity-top">
                        <div class="elite-logo-frame">
                            <div class="elite-logo"></div>
                        </div>

                        <div class="elite-brand-copy">
                            <div class="elite-brand-kicker">
                                <span class="dot"></span>
                                المنيو الإلكتروني الرسمي
                            </div>

                            <h1 class="elite-title">{{ $restaurantName }}</h1>

                            <p class="elite-subtitle">
                                @if($restaurantSubtitle)
                                    {{ $restaurantSubtitle }}
                                @elseif($restaurantPhone)
                                    {{ $restaurantPhone }}
                                @else
                                    منيو إلكتروني منظم وواضح يسهّل على العميل تصفح الأصناف بسرعة من خلال QR.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="elite-meta-row">
                        <div class="elite-pill success">
                            <span class="dot"></span>
                            منيو رقمي سريع وسهل التصفح
                        </div>

                        @if($restaurantPhone)
                            <div class="elite-pill">
                                {{ $restaurantPhone }}
                            </div>
                        @endif

                        @if($restaurantAddress)
                            <div class="elite-pill">
                                {{ $restaurantAddress }}
                            </div>
                        @endif

                        <div class="elite-pill">
                            {{ $categories->sum(fn($category) => $category->items->count()) }} صنف متاح
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="elite-layout" id="menu-area">
        <aside class="elite-sidebar">
            <div class="elite-card">
                <div class="elite-card-body">
                    <div class="elite-message">
                        <strong>اختر من الأصناف المتاحة واستعرض المنيو بسهولة من خلال تصميم واضح وسريع ومناسب تمامًا للعرض على الموبايل عبر QR.</strong>
                    </div>
                </div>
            </div>

            <div class="elite-card">
                <div class="elite-card-body">
                    <h3 class="elite-card-title">معلومات المطعم</h3>

                    <div class="elite-info-list">
                        <div class="elite-info-item">
                            <span class="elite-info-icon">🍽️</span>
                            <div>عرض منظم للأصناف مع تجربة تصفح مريحة وسهلة للعميل.</div>
                        </div>

                        @if($restaurantPhone)
                            <div class="elite-info-item">
                                <span class="elite-info-icon">📞</span>
                                <div>
                                    <a href="tel:{{ $restaurantPhone }}" style="color:inherit;text-decoration:none;">
                                        {{ $restaurantPhone }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($restaurantAddress)
                            <div class="elite-info-item">
                                <span class="elite-info-icon">📍</span>
                                <div>{{ $restaurantAddress }}</div>
                            </div>
                        @endif

                        <div class="elite-info-item">
                            <span class="elite-info-icon">📱</span>
                            <div>الواجهة مناسبة للعرض السريع بعد مسح رمز الـ QR مباشرة.</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="elite-main">
            <div class="elite-card">
                <div class="elite-card-body">
                    <h3 class="elite-card-title">التصنيفات</h3>

                    <div class="elite-categories">
                        @foreach($categories as $category)
                            @php
                                $firstItem = $category->items->first();
                                $categoryImage = $firstItem && $firstItem->image
                                    ? \App\Support\MediaUrl::fromPath( $firstItem->image)
                                    : 'https://via.placeholder.com/300x300?text=Food';
                            @endphp

                            <a href="#cat-{{ $category->id }}" class="elite-cat">
                                <div class="elite-cat-ring">
                                    <div class="elite-cat-inner">
                                        <img src="{{ $categoryImage }}" alt="{{ $category->name }}">
                                    </div>
                                </div>
                                <div class="elite-cat-label">{{ $category->name }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="elite-feed">
                @php
                    $allItemsCount = $categories->sum(fn($category) => $category->items->count());
                @endphp

                @if($allItemsCount > 0)
                    @foreach($categories as $category)
                        @if($category->items->count())
                            <section class="elite-section" id="cat-{{ $category->id }}">
                                <div class="elite-section-head">
                                    <h3 class="elite-section-title">{{ $category->name }}</h3>
                                    <span class="elite-section-count">{{ $category->items->count() }} صنف</span>
                                </div>

                                <div class="elite-products">
                                    @foreach($category->items as $item)
                                        <article class="elite-product">
                                            <div class="elite-product-media">
                                                <img
                                                    src="{{ $item->image ? \App\Support\MediaUrl::fromPath( $item->image) : 'https://via.placeholder.com/600x400?text=Food' }}"
                                                    alt="{{ $item->name }}"
                                                    class="elite-product-image"
                                                >

                                                @if($item->badge)
                                                    <span class="elite-product-badge">{{ $item->badge }}</span>
                                                @else
                                                    <span class="elite-product-badge">متاح</span>
                                                @endif
                                            </div>

                                            <div class="elite-product-body">
                                                <div class="elite-product-top">
                                                    <h4 class="elite-product-name">{{ $item->name }}</h4>
                                                    <span class="elite-product-category">{{ $category->name }}</span>
                                                </div>

                                                <p class="elite-product-desc">
                                                    @if(($setting->show_descriptions ?? true) && $item->description)
                                                        {{ $item->description }}
                                                    @else
                                                        صنف متاح ضمن المنيو الإلكتروني مع عرض واضح ومرتب للعميل.
                                                    @endif
                                                </p>

                                                <div class="elite-product-bottom">
                                                    @if($setting->show_prices ?? true)
                                                        <div class="elite-price">
                                                            <div class="elite-price-label">السعر</div>
                                                            <div class="elite-price-value">{{ number_format($item->price, 2) }} ج.م</div>
                                                        </div>
                                                    @endif

                                                    @if($item->badge)
                                                        <span class="elite-badge">{{ $item->badge }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    @endforeach
                @else
                    <div class="elite-empty">
                        لا توجد أصناف متاحة حالياً.
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>
@endsection