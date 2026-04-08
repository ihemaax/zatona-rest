<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Admin Dashboard</title>
    <meta name="robots" content="noindex,nofollow">
    <style>
        :root{--bg:#0a0f1f;--card:#111a35;--line:#233261;--txt:#edf2ff;--muted:#9eabd2;--brand:#5f8fff}
        *{box-sizing:border-box}body{margin:0;background:linear-gradient(180deg,#090d1b,#0f1733);font-family:Tahoma,Arial,sans-serif;color:var(--txt)}
        .wrap{max-width:1200px;margin:0 auto;padding:24px 16px 40px}
        .head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;background:var(--card);border:1px solid var(--line);border-radius:16px;padding:18px}
        .head h1{margin:0 0 6px;font-size:29px}
        .muted{color:var(--muted)}
        .pill{display:inline-block;background:#1b2b59;border:1px solid #2d3f75;color:#cfe0ff;padding:6px 10px;border-radius:999px;font-size:12px}
        .grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:12px}
        .card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:14px}
        .kpi{font-size:30px;font-weight:700}
        .label{font-size:13px;color:var(--muted)}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border-bottom:1px solid #22325f;font-size:14px;text-align:right}
        th{color:#c8d6ff}
        .section{margin-top:12px}
        .status{font-size:12px;border-radius:999px;padding:4px 8px;background:#25396f;color:#d9e5ff}
        @media(max-width:980px){.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:640px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <div>
            <h1>لوحة تشغيل المطاعم - نسخة ديمو</h1>
            <div class="muted">صفحة مستقلة للعرض التجاري (Sales Demo) غير مرتبطة بتشغيل النظام الحقيقي.</div>
            <div class="muted" style="margin-top:8px">آخر تحديث: {{ $demoUpdatedAt }}</div>
        </div>
        <span class="pill">Standalone Demo</span>
    </div>

    <div class="grid">
        <div class="card"><div class="kpi">{{ number_format($cards['orders_count']) }}</div><div class="label">طلبات اليوم</div></div>
        <div class="card"><div class="kpi">{{ number_format($cards['new_orders']) }}</div><div class="label">طلبات جديدة</div></div>
        <div class="card"><div class="kpi">{{ number_format($cards['pending_orders']) }}</div><div class="label">بانتظار التأكيد</div></div>
        <div class="card"><div class="kpi">{{ number_format($cards['branches_count']) }}</div><div class="label">الفروع النشطة</div></div>
        <div class="card"><div class="kpi">{{ number_format($cards['today_sales'],2) }} ج.م</div><div class="label">مبيعات اليوم</div></div>
        <div class="card"><div class="kpi">{{ number_format($cards['delivery_sales'],2) }} ج.م</div><div class="label">مبيعات التوصيل</div></div>
        <div class="card"><div class="kpi">{{ number_format($cards['pickup_sales'],2) }} ج.م</div><div class="label">مبيعات الاستلام</div></div>
        <div class="card"><div class="kpi">{{ number_format($cards['avg_order_value'],2) }} ج.م</div><div class="label">متوسط الطلب</div></div>
    </div>

    <div class="section card">
        <h3 style="margin-top:0">أداء الفروع (بيانات عرض واقعية)</h3>
        <table>
            <thead><tr><th>الفرع</th><th>الطلبات</th><th>المبيعات</th><th>SLA</th></tr></thead>
            <tbody>
            @foreach($branches as $branch)
                <tr>
                    <td>{{ $branch['name'] }}</td>
                    <td>{{ number_format($branch['orders']) }}</td>
                    <td>{{ number_format($branch['sales'],2) }} ج.م</td>
                    <td><span class="status">{{ $branch['sla'] }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="section card">
        <h3 style="margin-top:0">آخر الطلبات</h3>
        <table>
            <thead><tr><th>رقم الطلب</th><th>العميل</th><th>النوع</th><th>الإجمالي</th><th>الحالة</th></tr></thead>
            <tbody>
            @foreach($latestOrders as $order)
                <tr>
                    <td>{{ $order['number'] }}</td>
                    <td>{{ $order['customer'] }}</td>
                    <td>{{ $order['type'] }}</td>
                    <td>{{ number_format($order['total'],2) }} ج.م</td>
                    <td><span class="status">{{ $order['status'] }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
