<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Demo | {{ $demoData['brand']['name'] }}</title>
    <meta name="robots" content="noindex,nofollow">
    <style>
        :root{--bg:#0b1020;--card:#121a31;--text:#e8ecff;--muted:#9da7cc;--brand:#5b8cff;--ok:#36c78b}
        *{box-sizing:border-box} body{margin:0;font-family:Tahoma,Arial,sans-serif;background:linear-gradient(180deg,#080d1a,#0f1530);color:var(--text)}
        .wrap{max-width:1120px;margin:0 auto;padding:28px 18px 42px}
        .hero{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;background:var(--card);padding:22px;border-radius:16px;border:1px solid #202b51}
        .hero h1{margin:0 0 8px;font-size:30px}
        .muted{color:var(--muted)}
        .cta a{display:inline-block;text-decoration:none;color:#fff;background:var(--brand);padding:10px 14px;border-radius:10px;font-weight:700}
        .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:14px}
        .card{background:var(--card);border:1px solid #202b51;border-radius:14px;padding:16px}
        .kpi{font-size:28px;font-weight:700;color:#fff}
        .label{font-size:13px;color:var(--muted)}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th,td{padding:10px;border-bottom:1px solid #22305f;font-size:14px;text-align:right}
        th{color:#cfd8ff;font-weight:700}
        .status{font-size:12px;padding:4px 8px;border-radius:999px;background:#1f2c57}
        .status.ok{background:rgba(54,199,139,.2);color:#9ff0cb}
        ul{margin:8px 0 0;padding-right:18px} li{margin:8px 0;color:#d5dcff}
        .foot{margin-top:16px;text-align:center;color:#90a0d5;font-size:13px}
        @media (max-width:860px){.grid{grid-template-columns:1fr}.hero{flex-direction:column}}
    </style>
</head>
<body>
<div class="wrap">
    <section class="hero">
        <div>
            <h1>{{ $demoData['brand']['name'] }}</h1>
            <div class="muted">{{ $demoData['brand']['tagline'] }}</div>
            <div class="muted" style="margin-top:8px">للتواصل: {{ $demoData['brand']['phone'] }} — {{ $demoData['brand']['email'] }}</div>
        </div>
        <div class="cta">
            <a href="{{ $demoData['brand']['website'] }}" target="_blank" rel="noopener noreferrer">زيارة لوحة النظام</a>
        </div>
    </section>

    <section class="grid">
        <div class="card"><div class="kpi">{{ number_format($demoData['summary']['monthly_orders']) }}</div><div class="label">عدد الطلبات الشهرية</div></div>
        <div class="card"><div class="kpi">{{ number_format($demoData['summary']['monthly_sales']) }} ج.م</div><div class="label">إجمالي المبيعات الشهرية</div></div>
        <div class="card"><div class="kpi">{{ $demoData['summary']['avg_order_time_minutes'] }} دقيقة</div><div class="label">متوسط زمن تنفيذ الطلب</div></div>
        <div class="card"><div class="kpi">{{ $demoData['summary']['cancellation_rate'] }}%</div><div class="label">معدل الإلغاء</div></div>
        <div class="card"><div class="kpi">{{ $demoData['summary']['active_branches'] }}</div><div class="label">الفروع النشطة</div></div>
        <div class="card"><div class="kpi">{{ $demoData['summary']['active_staff'] }}</div><div class="label">الموظفون النشطون</div></div>
    </section>

    <section class="grid">
        <div class="card" style="grid-column:span 2">
            <h3 style="margin-top:0">بيانات تشغيل الفروع (عينة حقيقية للعرض)</h3>
            <table>
                <thead><tr><th>الفرع</th><th>الطلبات</th><th>المبيعات</th><th>SLA</th></tr></thead>
                <tbody>
                @foreach($demoData['branches'] as $branch)
                    <tr>
                        <td>{{ $branch['name'] }}</td>
                        <td>{{ number_format($branch['orders']) }}</td>
                        <td>{{ number_format($branch['sales']) }} ج.م</td>
                        <td><span class="status ok">{{ $branch['sla'] }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card">
            <h3 style="margin-top:0">أهم المزايا</h3>
            <ul>
                @foreach($demoData['features'] as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="card" style="margin-top:12px">
        <h3 style="margin-top:0">آخر الطلبات (Demo Data)</h3>
        <table>
            <thead><tr><th>رقم الطلب</th><th>العميل</th><th>النوع</th><th>الإجمالي</th><th>الحالة</th></tr></thead>
            <tbody>
            @foreach($demoData['recent_orders'] as $order)
                <tr>
                    <td>{{ $order['number'] }}</td>
                    <td>{{ $order['customer'] }}</td>
                    <td>{{ $order['type'] }}</td>
                    <td>{{ number_format($order['total'], 2) }} ج.م</td>
                    <td><span class="status">{{ $order['status'] }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <div class="foot">Standalone Sales Demo Page — منفصلة بالكامل عن لوحة التشغيل الداخلية.</div>
</div>
</body>
</html>
