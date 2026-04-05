<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $setting->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body{
            font-family:Tahoma, Arial, sans-serif;
            background:#f7f9fc;
            color:#111827;
        }

        .menu-hero{
            background:linear-gradient(135deg,#111827 0%, #7f1d1d 45%, #dc2626 100%);
            color:#fff;
            padding:48px 20px;
            border-radius:0 0 30px 30px;
            margin-bottom:28px;
        }

        .menu-logo{
            width:84px;
            height:84px;
            object-fit:cover;
            border-radius:20px;
            border:3px solid rgba(255,255,255,.16);
            background:#fff;
        }

        .menu-banner{
            width:100%;
            height:260px;
            object-fit:cover;
            border-radius:24px;
            margin-top:18px;
        }

        .category-bar{
            display:flex;
            gap:10px;
            overflow:auto;
            padding-bottom:6px;
            margin-bottom:24px;
            scrollbar-width:none;
        }

        .category-bar::-webkit-scrollbar{ display:none; }

        .category-pill{
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:999px;
            padding:10px 16px;
            font-weight:800;
            white-space:nowrap;
        }

        .menu-section{
            margin-bottom:30px;
        }

        .menu-section-title{
            font-size:1.3rem;
            font-weight:900;
            margin-bottom:14px;
        }

        .menu-grid{
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:18px;
        }

        .menu-card{
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:24px;
            overflow:hidden;
            box-shadow:0 12px 24px rgba(15,23,42,.06);
            height:100%;
        }

        .menu-card img{
            width:100%;
            height:210px;
            object-fit:cover;
        }

        .menu-card-body{
            padding:16px;
        }

        .menu-card-title{
            font-weight:900;
            font-size:1.1rem;
            margin-bottom:6px;
        }

        .menu-card-desc{
            color:#6b7280;
            font-size:.93rem;
            margin-bottom:10px;
        }

        .menu-card-bottom{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
            flex-wrap:wrap;
        }

        .menu-price{
            font-weight:900;
            color:#b91c1c;
            font-size:1rem;
        }

        .menu-badge{
            background:#111827;
            color:#fff;
            font-size:.78rem;
            font-weight:800;
            border-radius:999px;
            padding:6px 10px;
        }

        @media (max-width: 991px){
            .menu-grid{
                grid-template-columns:repeat(2,minmax(0,1fr));
            }
        }

        @media (max-width: 767px){
            .menu-grid{
                grid-template-columns:1fr;
            }

            .menu-banner{
                height:180px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="menu-hero">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                @if($setting->logo)
                    <img src="{{ asset('storage/' . $setting->logo) }}" class="menu-logo" alt="{{ $setting->title }}">
                @endif

                <div>
                    <h1 class="fw-bold mb-2">{{ $setting->title }}</h1>
                    @if($setting->subtitle)
                        <p class="mb-1">{{ $setting->subtitle }}</p>
                    @endif
                    @if($setting->phone)
                        <div>{{ $setting->phone }}</div>
                    @endif
                    @if($setting->address)
                        <div>{{ $setting->address }}</div>
                    @endif
                </div>
            </div>

            @if($setting->banner)
                <img src="{{ asset('storage/' . $setting->banner) }}" class="menu-banner" alt="{{ $setting->title }}">
            @endif
        </div>

        <div class="category-bar">
            @foreach($categories as $category)
                <a href="#cat-{{ $category->id }}" class="category-pill">{{ $category->name }}</a>
            @endforeach
        </div>

        @foreach($categories as $category)
            <div class="menu-section" id="cat-{{ $category->id }}">
                <div class="menu-section-title">{{ $category->name }}</div>

                <div class="menu-grid">
                    @foreach($category->items as $item)
                        <div class="menu-card">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
                            @endif

                            <div class="menu-card-body">
                                <div class="menu-card-title">{{ $item->name }}</div>

                                @if($setting->show_descriptions && $item->description)
                                    <div class="menu-card-desc">{{ $item->description }}</div>
                                @endif

                                <div class="menu-card-bottom">
                                    @if($setting->show_prices)
                                        <div class="menu-price">{{ number_format($item->price, 2) }} ج.م</div>
                                    @endif

                                    @if($item->badge)
                                        <span class="menu-badge">{{ $item->badge }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>