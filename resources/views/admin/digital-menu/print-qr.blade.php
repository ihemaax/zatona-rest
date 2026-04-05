<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة QR - {{ $setting->title }}</title>
    <style>
        :root{
            --bg:#f6f1eb;
            --card:#fffdfa;
            --border:#e7ddd1;
            --text:#231f1b;
            --muted:#8a847a;
            --soft:#f8f4ee;
            --accent:#6f7f5f;
            --accent-2:#8d9d7c;
            --shadow:0 18px 40px rgba(35,31,27,.08);
        }

        *{
            box-sizing:border-box;
        }

        body{
            font-family:'Cairo', Tahoma, Arial, sans-serif;
            background:linear-gradient(180deg, #fcfaf7 0%, var(--bg) 100%);
            color:var(--text);
            margin:0;
            padding:28px;
        }

        .print-shell{
            max-width:760px;
            margin:0 auto;
        }

        .print-wrap{
            background:var(--card);
            border:1px solid var(--border);
            border-radius:28px;
            padding:30px 24px;
            text-align:center;
            box-shadow:var(--shadow);
        }

        .brand-badge{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:8px 14px;
            border-radius:999px;
            background:linear-gradient(135deg, rgba(111,127,95,.10) 0%, rgba(141,157,124,.16) 100%);
            border:1px solid rgba(111,127,95,.16);
            color:#5c6a4f;
            font-size:.78rem;
            font-weight:900;
            margin-bottom:14px;
        }

        .title{
            font-size:2rem;
            font-weight:900;
            color:var(--text);
            margin:0 0 8px;
            letter-spacing:-.02em;
        }

        .subtitle{
            color:var(--muted);
            margin:0 auto 20px;
            line-height:1.8;
            font-size:.97rem;
            font-weight:700;
            max-width:560px;
        }

        .qr-panel{
            display:inline-block;
            background:linear-gradient(180deg, #ffffff 0%, #fbf8f3 100%);
            border:1px solid var(--border);
            border-radius:26px;
            padding:18px;
            box-shadow:0 12px 28px rgba(35,31,27,.06);
            margin:8px 0 16px;
        }

        .qr-panel img{
            width:340px;
            height:340px;
            display:block;
            border-radius:18px;
            background:#fff;
        }

        .link-box{
            margin-top:6px;
            background:var(--soft);
            border:1px solid var(--border);
            border-radius:18px;
            padding:14px 16px;
        }

        .link-label{
            font-size:.76rem;
            color:var(--muted);
            font-weight:800;
            margin-bottom:6px;
        }

        .link{
            font-size:.94rem;
            word-break:break-all;
            color:#443b33;
            direction:ltr;
            text-align:center;
            font-weight:800;
            line-height:1.7;
        }

        .hint{
            margin-top:16px;
            color:var(--muted);
            font-size:.9rem;
            font-weight:700;
            line-height:1.7;
        }

        .footer-note{
            margin-top:18px;
            padding-top:18px;
            border-top:1px solid var(--border);
            color:#6f6a61;
            font-size:.82rem;
            font-weight:700;
            line-height:1.8;
        }

        @media (max-width: 767.98px){
            body{
                padding:14px;
            }

            .print-wrap{
                padding:22px 16px;
                border-radius:20px;
            }

            .title{
                font-size:1.4rem;
            }

            .subtitle{
                font-size:.9rem;
                margin-bottom:16px;
            }

            .qr-panel{
                width:100%;
                padding:14px;
                border-radius:20px;
            }

            .qr-panel img{
                width:100%;
                max-width:260px;
                height:auto;
                margin:0 auto;
            }

            .link{
                font-size:.82rem;
            }

            .hint,
            .footer-note{
                font-size:.8rem;
            }
        }

        @media print{
            body{
                background:#fff;
                padding:0;
            }

            .print-shell{
                max-width:none;
            }

            .print-wrap{
                border:none;
                box-shadow:none;
                border-radius:0;
                padding:0;
            }

            .qr-panel{
                box-shadow:none;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-shell">
        <div class="print-wrap">
            <div class="brand-badge">QR Code Menu</div>

            <h1 class="title">{{ $setting->title }}</h1>

            <p class="subtitle">
                {{ $setting->subtitle ?: 'امسح رمز الاستجابة السريعة لفتح المنيو الإلكتروني والاطلاع على الأصناف بسهولة.' }}
            </p>

            <div class="qr-panel">
                <img src="{{ route('admin.digital-menu.qr.image') }}" alt="QR Code">
            </div>

            <div class="link-box">
                <div class="link-label">رابط المنيو الإلكتروني</div>
                <div class="link">{{ $menuUrl }}</div>
            </div>

            <div class="hint">امسح الكود باستخدام كاميرا الهاتف لفتح المنيو مباشرة</div>

            <div class="footer-note">
                Scan to view the digital menu
            </div>
        </div>
    </div>
</body>
</html>