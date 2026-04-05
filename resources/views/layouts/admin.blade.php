<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'لوحة الإدارة' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/js/app.js'])

    <style>
        :root{
            --admin-sidebar-width: 286px;

            --admin-bg: #f3efe8;
            --admin-surface: #fffdf9;
            --admin-surface-2: #f8f4ee;
            --admin-border: #e7dfd3;

            --admin-text: #231f1b;
            --admin-text-soft: #6f6a61;
            --admin-text-faint: #8c867c;

            --admin-primary: #6f7f5f;
            --admin-primary-dark: #5c6a4f;
            --admin-primary-soft: #eef2e8;

            --admin-danger: #b95c5c;
            --admin-danger-soft: #fff1f1;

            --admin-warning: #9d7a44;
            --admin-warning-soft: #f7f0e2;

            --admin-info: #5d7a9a;
            --admin-info-soft: #eaf1f8;

            --admin-success: #4f7458;
            --admin-success-soft: #edf8ef;

            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-sm: 12px;

            --shadow-sm: 0 8px 20px rgba(35,31,27,.05);
            --shadow-md: 0 18px 40px rgba(35,31,27,.08);

            --font: 'Cairo', Tahoma, Arial, sans-serif;
        }

        *, *::before, *::after{ box-sizing:border-box; }
        html, body{ margin:0; padding:0; min-height:100%; overflow-x:hidden; }

        body{
            font-family: var(--font);
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.55), transparent 20%),
                linear-gradient(180deg, #f5f1eb 0%, #f1ece5 100%);
            color: var(--admin-text);
        }

        body.sidebar-open{
            overflow: hidden;
        }

        a{
            color: inherit;
            text-decoration: none;
        }

        .admin-shell{
            display:flex;
            min-height:100vh;
        }

        .admin-sidebar{
            width: var(--admin-sidebar-width);
            position: fixed;
            top: 0;
            bottom: 0;
            {{ app()->getLocale() === 'ar' ? 'right:0;' : 'left:0;' }}
            z-index: 1100;
            display:flex;
            flex-direction:column;
            background: linear-gradient(180deg, #f7f3ed 0%, #f2ede6 100%);
            border-inline-end: 1px solid var(--admin-border);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
            transition: transform .28s cubic-bezier(.4,0,.2,1);
        }

        .admin-sidebar::before{
            content:'';
            position:absolute;
            top:0;
            left:0;
            right:0;
            height:4px;
            background: linear-gradient(90deg, var(--admin-primary-dark), var(--admin-primary));
        }

        .sb-scroll{
            flex:1;
            overflow-y:auto;
            padding: 0 14px 18px;
            scrollbar-width: thin;
            scrollbar-color: #d4cabd transparent;
        }

        .sb-scroll::-webkit-scrollbar{
            width: 6px;
        }

        .sb-scroll::-webkit-scrollbar-thumb{
            background: #d4cabd;
            border-radius: 999px;
        }

        .sb-mobile-head{
            display:none;
            align-items:center;
            justify-content:space-between;
            padding:16px 8px 14px;
            border-bottom:1px solid var(--admin-border);
            margin-bottom:10px;
        }

        .sb-brand{
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:10px;
            padding:24px 8px 18px;
            margin-bottom:8px;
        }

        .sb-brand-logo-image{
            max-width:150px;
            width:100%;
            height:auto;
            display:block;
            object-fit:contain;
            filter: drop-shadow(0 8px 18px rgba(111,127,95,.10));
        }

        .sb-brand-title{
            margin:0;
            font-size:1.03rem;
            font-weight:900;
            color:var(--admin-text);
            line-height:1.15;
            text-align:center;
        }

        .sb-brand-sub{
            margin:4px 0 0;
            font-size:.75rem;
            font-weight:700;
            color:var(--admin-text-soft);
            text-align:center;
        }

        .sb-close{
            width:38px;
            height:38px;
            border:none;
            border-radius:12px;
            background:#f3eee7;
            color:var(--admin-text);
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            box-shadow: inset 0 0 0 1px var(--admin-border);
            transition:.2s ease;
        }

        .sb-close:hover{
            background:#ece5db;
        }

        .sb-footer{
            padding:13px;
            border-top:1px solid var(--admin-border);
            background: rgba(255,255,255,.22);
        }

        .sb-logout{
            width:100%;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            padding:11px 14px;
            border:none;
            border-radius:15px;
            background:#f7e9e9;
            color:#975d5d;
            font-size:.88rem;
            font-weight:800;
            font-family:var(--font);
            cursor:pointer;
            transition:.2s ease;
            box-shadow: inset 0 0 0 1px #ecd3d3;
        }

        .sb-logout:hover{
            background:#f1dfdf;
            color:#874d4d;
        }

        .sb-logout svg{
            width:16px;
            height:16px;
            stroke:currentColor;
        }

        .admin-backdrop{
            position:fixed;
            inset:0;
            background:rgba(20,27,35,.26);
            z-index:1050;
            opacity:0;
            visibility:hidden;
            transition:.25s ease;
        }

        .admin-backdrop.show{
            opacity:1;
            visibility:visible;
        }

        .admin-main{
            flex:1;
            min-width:0;
            {{ app()->getLocale() === 'ar' ? 'margin-right:var(--admin-sidebar-width);' : 'margin-left:var(--admin-sidebar-width);' }}
            display:flex;
            flex-direction:column;
            min-height:100vh;
        }

        .admin-topbar{
            position:sticky;
            top:0;
            z-index:900;
            padding:16px 24px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            background:rgba(245,241,235,.84);
            backdrop-filter:blur(18px);
            -webkit-backdrop-filter:blur(18px);
            border-bottom:1px solid #e4dbcf;
        }

        .topbar-start{
            display:flex;
            align-items:center;
            gap:12px;
            min-width:0;
        }

        .mobile-menu-btn{
            display:none;
            width:42px;
            height:42px;
            border:none;
            border-radius:13px;
            background:#f1ece5;
            color:var(--admin-text);
            align-items:center;
            justify-content:center;
            cursor:pointer;
            flex-shrink:0;
            box-shadow: inset 0 0 0 1px #e3d9cc;
        }

        .mobile-menu-btn:hover{
            background:#ebe4da;
        }

        .mobile-menu-btn svg{
            width:19px;
            height:19px;
            stroke:currentColor;
        }

        .admin-topbar-title{
            margin:0;
            font-size:1.18rem;
            font-weight:900;
            color:var(--admin-text);
            line-height:1.2;
            letter-spacing:-.2px;
        }

        .admin-topbar-subtitle{
            margin:4px 0 0;
            font-size:.8rem;
            font-weight:700;
            color:var(--admin-text-faint);
        }

        .topbar-status{
            display:flex;
            align-items:center;
            gap:8px;
            flex-shrink:0;
            background:#f3eee7;
            color:#6b7a5f;
            border-radius:999px;
            padding:8px 14px;
            font-size:.78rem;
            font-weight:800;
            box-shadow: inset 0 0 0 1px #e5ddd1;
        }

        .status-dot{
            width:8px;
            height:8px;
            border-radius:50%;
            background:#6b9a77;
            animation: adminpulse 2.3s ease infinite;
            flex-shrink:0;
        }

        @keyframes adminpulse{
            0%,100%{ opacity:1; transform:scale(1); }
            50%{ opacity:.55; transform:scale(.82); }
        }

        .admin-content{
            padding:24px;
            flex:1;
            overflow-x:hidden;
        }

        .admin-card{
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
        }

        .section-title{
            font-size:1.05rem;
            font-weight:900;
            color:var(--admin-text);
            margin-bottom:4px;
            letter-spacing:-.15px;
        }

        .section-subtitle{
            font-size:.84rem;
            font-weight:700;
            color:var(--admin-text-faint);
            margin-bottom:16px;
        }

        .filter-pills{
            display:flex;
            flex-wrap:wrap;
            gap:8px;
        }

        .filter-pill{
            background:#f3eee7;
            border:1px solid #e3d9cc;
            color:#665f57;
            border-radius:999px;
            padding:8px 15px;
            font-size:.82rem;
            font-weight:800;
            font-family:var(--font);
            white-space:nowrap;
            transition:.18s ease;
        }

        .filter-pill:hover,
        .filter-pill.active{
            background:var(--admin-primary);
            border-color:var(--admin-primary);
            color:#fff;
        }

        .btn-admin{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:7px;
            background:linear-gradient(135deg, var(--admin-primary-dark), var(--admin-primary));
            color:#fff;
            border:none;
            border-radius:15px;
            padding:10px 18px;
            font-size:.85rem;
            font-weight:800;
            font-family:var(--font);
            cursor:pointer;
            transition:.18s ease;
            box-shadow:0 10px 20px rgba(111,127,95,.18);
        }

        .btn-admin:hover{
            color:#fff;
            opacity:.97;
        }

        .btn-admin-soft{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:7px;
            background:#f3eee7;
            color:#443b33;
            border:1px solid #e3d9cc;
            border-radius:15px;
            padding:10px 18px;
            font-size:.85rem;
            font-weight:800;
            font-family:var(--font);
            cursor:pointer;
            transition:.18s ease;
        }

        .btn-admin-soft:hover{
            color:#302821;
            background:#ebe4da;
        }

        .table{
            --bs-table-bg: transparent;
            margin-bottom:0;
        }

        .table thead th{
            background:#f7f2eb;
            color:#7b7268;
            font-size:.73rem;
            font-weight:900;
            letter-spacing:.04em;
            text-transform:uppercase;
            border-bottom:1px solid #e5ddd1;
            padding:14px 16px;
            white-space:nowrap;
        }

        .table tbody td{
            vertical-align:middle;
            border-color:#eee6dc;
            padding:14px 16px;
            font-size:.87rem;
            color:#554d45;
            font-weight:700;
            background:#fffdfa;
        }

        .table tbody tr:hover td{
            background:#fbf7f2;
        }

        .admin-table-wrap{
            overflow:auto;
            border-radius:18px;
            border:1px solid #ebe3d7;
            background:#fffdfa;
        }

        .form-control,
        .form-select,
        textarea.form-control{
            font-family:var(--font);
            border-radius:13px;
            padding:10px 13px;
            border:1px solid #ddd4c8;
            background:#faf7f2;
            font-size:.9rem;
            color:#2f2923;
            transition:.18s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus{
            border-color:#c6d2ba;
            box-shadow:0 0 0 3px rgba(111,127,95,.14);
            background:#fff;
            color:#231f1b;
            outline:none;
        }

        .form-control::placeholder,
        textarea.form-control::placeholder{
            color:#948a7f;
        }

        .form-label{
            color:#655d54;
            font-weight:800;
            margin-bottom:7px;
        }

        .alert{
            border:none;
            border-radius:18px !important;
            font-size:.87rem;
            font-weight:700;
            padding:14px 18px !important;
        }

        .alert-success{
            background:var(--admin-success-soft);
            color:var(--admin-success);
            border-inline-start:4px solid #7da087 !important;
        }

        .alert-danger{
            background:#f8e7e7;
            color:#935e62;
            border-inline-start:4px solid #c9878c !important;
        }

        .status-chip{
            display:inline-flex;
            align-items:center;
            gap:5px;
            padding:4px 10px;
            border-radius:999px;
            font-size:.73rem;
            font-weight:800;
            white-space:nowrap;
        }

        .status-chip::before{
            content:'';
            width:6px;
            height:6px;
            border-radius:50%;
            background:currentColor;
            flex-shrink:0;
        }

        .status-pending{ background:var(--admin-warning-soft); color:var(--admin-warning); }
        .status-confirmed{ background:var(--admin-info-soft); color:var(--admin-info); }
        .status-preparing{ background:#efe8f7; color:#7f6797; }
        .status-delivery{ background:#e7f1ee; color:#557b73; }
        .status-delivered{ background:var(--admin-success-soft); color:var(--admin-success); }
        .status-cancelled{ background:#f8e7e7; color:#9a5d63; }

        .order-type-chip{
            display:inline-flex;
            align-items:center;
            padding:4px 10px;
            border-radius:999px;
            font-size:.73rem;
            font-weight:800;
            white-space:nowrap;
        }

        .order-type-delivery{ background:var(--admin-info-soft); color:var(--admin-info); }
        .order-type-pickup{ background:var(--admin-success-soft); color:var(--admin-success); }

        .text-muted{
            color:#8c867c !important;
        }

        .new-order-dot{
            width:8px;
            height:8px;
            border-radius:50%;
            background:#c77878;
            display:inline-block;
            flex-shrink:0;
            box-shadow:0 0 0 4px rgba(199,120,120,.12);
        }

        .sb-group{
            margin-bottom:10px;
        }

        .sb-group-toggle{
            width:100%;
            display:flex;
            align-items:center;
            gap:11px;
            padding:12px 13px;
            border:none;
            border-radius:15px;
            color:var(--admin-text-soft);
            font-size:.88rem;
            font-weight:800;
            background:transparent;
            text-align:inherit;
            position:relative;
            transition:.18s ease;
            font-family:var(--font);
            cursor:pointer;
        }

        .sb-group-toggle:hover{
            background: rgba(255,255,255,.65);
            color: var(--admin-text);
        }

        .sb-group.active > .sb-group-toggle{
            background: linear-gradient(180deg, #fffdfa 0%, #f7f2eb 100%);
            color: var(--admin-text);
            box-shadow:
                inset 0 0 0 1px #e5ddd1,
                0 8px 16px rgba(35,31,27,.04);
        }

        .sb-group.active > .sb-group-toggle::before{
            content:'';
            position:absolute;
            top:50%;
            transform:translateY(-50%);
            {{ app()->getLocale() === 'ar' ? 'right:-14px;' : 'left:-14px;' }}
            width:4px;
            height:58%;
            border-radius:999px;
            background: var(--admin-primary);
        }

        .sb-link-icon{
            width:18px;
            height:18px;
            flex-shrink:0;
            stroke:currentColor;
            opacity:.9;
        }

        .sb-link-text{
            min-width:0;
            flex:1;
            text-align:start;
        }

        .sb-arrow{
            width:16px;
            height:16px;
            flex-shrink:0;
            transition:transform .22s ease;
            {{ app()->getLocale() === 'ar' ? 'margin-right:auto;' : 'margin-left:auto;' }}
        }

        .sb-group.active .sb-arrow{
            transform:rotate(180deg);
        }

        .sb-submenu{
            display:grid;
            gap:5px;
            max-height:0;
            overflow:hidden;
            opacity:0;
            margin-top:0;
            padding:0 6px 0 0;
            transition:max-height .28s ease, opacity .2s ease, margin .2s ease, padding .2s ease;
        }

        .sb-group.active .sb-submenu{
            max-height:900px;
            opacity:1;
            margin-top:6px;
            padding-bottom:2px;
        }

        .sb-sublink{
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px 12px;
            border-radius:13px;
            color:#746d64;
            font-size:.82rem;
            font-weight:800;
            transition:.18s ease;
            position:relative;
        }

        .sb-sublink:hover{
            background:rgba(255,255,255,.58);
            color:var(--admin-text);
        }

        .sb-sublink.active{
            background:#f3eee7;
            color:var(--admin-text);
            box-shadow: inset 0 0 0 1px #e5ddd1;
        }

        .sb-sublink-dot{
            width:7px;
            height:7px;
            border-radius:50%;
            background:#cdbfaa;
            flex-shrink:0;
        }

        .sb-sublink.active .sb-sublink-dot{
            background:var(--admin-primary);
            box-shadow:0 0 0 4px rgba(111,127,95,.10);
        }

        .sb-badge{
            margin-inline-start:auto;
            min-width:22px;
            height:22px;
            border-radius:999px;
            background: var(--admin-danger);
            color:#fff;
            font-size:.7rem;
            font-weight:900;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0 7px;
            box-shadow:0 8px 14px rgba(185,92,92,.18);
        }

        .admin-ai-fab{
            position:fixed;
            bottom:22px;
            {{ app()->getLocale() === 'ar' ? 'left:22px;' : 'right:22px;' }}
            z-index:1600;
            border:none;
            display:flex;
            align-items:center;
            gap:10px;
            height:60px;
            padding:0 18px 0 14px;
            border-radius:999px;
            background:linear-gradient(135deg, #5c6a4f 0%, #6f7f5f 100%);
            color:#fff;
            box-shadow:0 20px 38px rgba(111,127,95,.28);
            font-family:var(--font);
            font-weight:900;
            font-size:.9rem;
            cursor:pointer;
            transition:transform .22s ease, box-shadow .22s ease, opacity .22s ease;
        }

        .admin-ai-fab:hover{
            transform:translateY(-3px) scale(1.01);
            box-shadow:0 24px 44px rgba(111,127,95,.34);
        }

        .admin-ai-fab:active{
            transform:scale(.98);
        }

        .admin-ai-fab-badge{
            width:32px;
            height:32px;
            border-radius:50%;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            background:rgba(255,255,255,.16);
            box-shadow:inset 0 0 0 1px rgba(255,255,255,.14);
            flex-shrink:0;
        }

        .admin-ai-fab svg{
            width:20px;
            height:20px;
            stroke:currentColor;
        }

        .admin-ai-fab.has-notification::after{
            content:'';
            position:absolute;
            top:7px;
            {{ app()->getLocale() === 'ar' ? 'right:9px;' : 'left:9px;' }}
            width:11px;
            height:11px;
            border-radius:50%;
            background:#d96060;
            box-shadow:0 0 0 5px rgba(217,96,96,.16);
            animation:aiFabPulse 1.7s ease infinite;
        }

        @keyframes aiFabPulse{
            0%,100%{ transform:scale(1); opacity:1; }
            50%{ transform:scale(.8); opacity:.6; }
        }

        .admin-ai-overlay{
            position:fixed;
            inset:0;
            background:rgba(24,21,18,.30);
            backdrop-filter:blur(4px);
            -webkit-backdrop-filter:blur(4px);
            z-index:1650;
            opacity:0;
            visibility:hidden;
            transition:opacity .24s ease, visibility .24s ease;
        }

        .admin-ai-overlay.show{
            opacity:1;
            visibility:visible;
        }

        .admin-ai-popup{
            position:fixed;
            bottom:92px;
            {{ app()->getLocale() === 'ar' ? 'left:22px;' : 'right:22px;' }}
            width:min(430px, calc(100vw - 24px));
            height:min(720px, calc(100vh - 118px));
            background:linear-gradient(180deg,#fffdf9 0%, #f7f2eb 100%);
            border:1px solid var(--admin-border);
            border-radius:28px;
            box-shadow:0 30px 70px rgba(35,31,27,.18);
            z-index:1700;
            overflow:hidden;
            display:flex;
            flex-direction:column;
            opacity:0;
            visibility:hidden;
            transform:translateY(16px) scale(.96);
            transform-origin:bottom {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};
            transition:
                opacity .26s ease,
                visibility .26s ease,
                transform .30s cubic-bezier(.2,.8,.2,1);
        }

        .admin-ai-popup.show{
            opacity:1;
            visibility:visible;
            transform:translateY(0) scale(1);
        }

        .admin-ai-popup.minimized{
            height:72px;
        }

        .admin-ai-popup-head{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            padding:14px 14px 12px 16px;
            border-bottom:1px solid #e8dfd3;
            background:rgba(255,255,255,.76);
            backdrop-filter:blur(14px);
            -webkit-backdrop-filter:blur(14px);
            flex-shrink:0;
        }

        .admin-ai-popup-info{
            min-width:0;
        }

        .admin-ai-popup-title{
            margin:0;
            font-size:1rem;
            font-weight:900;
            color:var(--admin-text);
            line-height:1.2;
        }

        .admin-ai-popup-subtitle{
            margin:4px 0 0;
            font-size:.76rem;
            font-weight:700;
            color:var(--admin-text-faint);
            line-height:1.55;
        }

        .admin-ai-popup-actions{
            display:flex;
            align-items:center;
            gap:8px;
            flex-shrink:0;
        }

        .admin-ai-icon-btn{
            width:40px;
            height:40px;
            border:none;
            border-radius:14px;
            background:#f3eee7;
            color:var(--admin-text);
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            box-shadow:inset 0 0 0 1px #e5ddd1;
            transition:.18s ease;
        }

        .admin-ai-icon-btn:hover{
            background:#ebe4da;
        }

        .admin-ai-icon-btn svg{
            width:18px;
            height:18px;
            stroke:currentColor;
        }

        .admin-ai-popup-body{
            flex:1;
            min-height:0;
            background:#fffdfa;
        }

        .admin-ai-popup.minimized .admin-ai-popup-body{
            display:none;
        }

        .admin-ai-frame{
            width:100%;
            height:100%;
            border:0;
            display:block;
            background:#fff;
        }

        .admin-ai-toast{
            position:fixed;
            bottom:96px;
            {{ app()->getLocale() === 'ar' ? 'left:22px;' : 'right:22px;' }}
            z-index:1750;
            min-width:260px;
            max-width:min(360px, calc(100vw - 24px));
            background:#fffdfa;
            border:1px solid #e6ddd1;
            border-radius:18px;
            box-shadow:0 22px 46px rgba(35,31,27,.14);
            padding:14px 16px;
            display:flex;
            align-items:flex-start;
            gap:12px;
            opacity:0;
            visibility:hidden;
            transform:translateY(12px);
            transition:.24s ease;
        }

        .admin-ai-toast.show{
            opacity:1;
            visibility:visible;
            transform:translateY(0);
        }

        .admin-ai-toast-icon{
            width:38px;
            height:38px;
            border-radius:12px;
            background:linear-gradient(135deg, #5c6a4f 0%, #6f7f5f 100%);
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            box-shadow:0 12px 20px rgba(111,127,95,.20);
        }

        .admin-ai-toast-icon svg{
            width:18px;
            height:18px;
            stroke:currentColor;
        }

        .admin-ai-toast-title{
            margin:0;
            font-size:.88rem;
            font-weight:900;
            color:var(--admin-text);
            line-height:1.3;
        }

        .admin-ai-toast-text{
            margin:4px 0 0;
            font-size:.78rem;
            font-weight:700;
            color:var(--admin-text-faint);
            line-height:1.65;
        }

        @media (max-width: 991.98px){
            .mobile-menu-btn{ display:flex; }
            .sb-mobile-head{ display:flex; }

            .sb-brand{
                padding:18px 8px 14px;
            }

            .sb-brand-logo-image{
                max-width:128px;
            }

            .admin-sidebar{
                width:min(84vw, 292px);
                transform:translateX({{ app()->getLocale() === 'ar' ? '110%' : '-110%' }});
            }

            .admin-sidebar.show{
                transform:translateX(0);
            }

            .admin-main{
                margin-left:0 !important;
                margin-right:0 !important;
            }

            .admin-topbar{
                padding:13px 16px;
            }

            .admin-content{
                padding:14px;
            }

            .topbar-status{
                display:none;
            }
        }

        @media (max-width: 767.98px){
            .admin-topbar{
                padding:12px 13px;
            }

            .admin-content{
                padding:12px;
            }

            .admin-topbar-title{
                font-size:1rem;
            }

            .btn-admin,
            .btn-admin-soft{
                width:100%;
            }

            .filter-pills{
                overflow-x:auto;
                flex-wrap:nowrap;
                scrollbar-width:none;
                padding-bottom:4px;
            }

            .filter-pills::-webkit-scrollbar{
                display:none;
            }

            .admin-table-wrap table,
            .table{
                min-width:650px;
            }

            .row{
                --bs-gutter-x:.75rem;
                --bs-gutter-y:.75rem;
            }

            .admin-ai-fab{
                bottom:16px;
                {{ app()->getLocale() === 'ar' ? 'left:16px;' : 'right:16px;' }}
                height:56px;
                padding:0 16px 0 13px;
                font-size:.86rem;
            }

            .admin-ai-popup{
                bottom:82px;
                {{ app()->getLocale() === 'ar' ? 'left:8px;' : 'right:8px;' }}
                width:calc(100vw - 16px);
                height:calc(100vh - 96px);
                border-radius:24px;
            }

            .admin-ai-popup-head{
                padding:12px;
            }

            .admin-ai-popup-subtitle{
                display:none;
            }

            .admin-ai-toast{
                bottom:84px;
                {{ app()->getLocale() === 'ar' ? 'left:8px;' : 'right:8px;' }}
                max-width:calc(100vw - 16px);
            }
        }

        img, table, input, select, textarea, button{
            max-width:100%;
        }

        .admin-card,
        .dashboard-stat-card,
        .dashboard-section-card,
        .dashboard-table-card{
            min-width:0;
        }

        .admin-table-wrap,
        .dashboard-mobile-scroll{
            overflow-x:auto;
            -webkit-overflow-scrolling:touch;
        }

        .admin-table-wrap table,
        .dashboard-mobile-scroll table,
        .table{
            min-width:700px;
        }

        .row{
            --bs-gutter-x:1rem;
        }
    </style>
</head>
<body>

@php
    $adminUser = auth()->user();
    $isDeliveryUser = $adminUser?->role === \App\Models\User::ROLE_DELIVERY;
    $newOrdersCount = \App\Models\Order::where('is_seen_by_admin', false)->count();

    if ($adminUser && !$adminUser->isSuperAdmin() && !$adminUser->hasPermission('view_all_branches_orders') && $adminUser->branch_id) {
        $newOrdersCount = \App\Models\Order::where('is_seen_by_admin', false)
            ->where('branch_id', $adminUser->branch_id)
            ->count();
    }

    $dashboardGroupOpen =
        request()->routeIs('admin.dashboard') ||
        request()->routeIs('admin.delivery.dashboard') ||
        request()->routeIs('admin.delivery.management') ||
        request()->routeIs('delivery.orders.*') ||
        request()->routeIs('admin.orders.index') ||
        request()->routeIs('admin.orders.show') ||
        request()->routeIs('admin.orders.delivery') ||
        request()->routeIs('admin.orders.pickup');

    $operationsGroupOpen =
        request()->routeIs('admin.branches.*') ||
        request()->routeIs('admin.categories.*') ||
        request()->routeIs('admin.products.*') ||
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
                        <a href="{{ url('/admin/delivery-dashboard') }}" class="sb-sublink {{ request()->is('admin/delivery-dashboard') || request()->is('delivery-dashboard') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>طلباتي (الدليفري)</span>
                        </a>
                    @else
                    @if($adminUser?->hasPermission('view_orders') || $adminUser?->isSuperAdmin())
                        @if($isDeliveryUser)
                        @if($adminUser?->role === \App\Models\User::ROLE_DELIVERY)
                            <a href="{{ url('/admin/delivery-dashboard') }}" class="sb-sublink {{ request()->is('admin/delivery-dashboard') || request()->is('delivery-dashboard') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>طلباتي (الدليفري)</span>
                            </a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="sb-sublink {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>الرئيسية</span>
                            </a>

                            <a href="{{ url('/delivery') }}" class="sb-sublink {{ request()->is('delivery*') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>صفحة الدليفري</span>
                            </a>
                            @if(Route::has('admin.delivery.dashboard'))
                                <a href="{{ route('admin.delivery.dashboard') }}" class="sb-sublink {{ request()->routeIs('admin.delivery.dashboard') ? 'active' : '' }}">
                                    <span class="sb-sublink-dot"></span>
                                    <span>طلباتي (الدليفري)</span>
                                </a>
                            @endif

                            @if(Route::has('delivery.orders.index'))
                                <a href="{{ route('delivery.orders.index') }}" class="sb-sublink {{ request()->routeIs('delivery.orders.*') ? 'active' : '' }}">
                                    <span class="sb-sublink-dot"></span>
                                    <span>صفحة الدليفري</span>
                                </a>
                            @endif
                        @endif

                        <a href="{{ route('admin.dashboard') }}" class="sb-sublink {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الرئيسية</span>
                        </a>

                            <a href="{{ route('admin.orders.index') }}" class="sb-sublink {{ request()->routeIs('admin.orders.index') || request()->routeIs('admin.orders.show') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>جميع الطلبات</span>
                                <span class="sb-badge" id="sidebarNewOrdersCount">{{ $newOrdersCount }}</span>
                            </a>

                            <a href="{{ route('admin.orders.delivery') }}" class="sb-sublink {{ request()->routeIs('admin.orders.delivery') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>طلبات التوصيل</span>
                            </a>

                        <a href="{{ route('admin.orders.pickup') }}" class="sb-sublink {{ request()->routeIs('admin.orders.pickup') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>طلبات الاستلام</span>
                        </a>

                        <a href="{{ route('admin.delivery.management') }}" class="sb-sublink {{ request()->routeIs('admin.delivery.management') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>متابعة الدليفري</span>
                        </a>
                            <a href="{{ route('admin.orders.pickup') }}" class="sb-sublink {{ request()->routeIs('admin.orders.pickup') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>طلبات الاستلام</span>
                            </a>

                            <a href="{{ route('admin.delivery.management') }}" class="sb-sublink {{ request()->routeIs('admin.delivery.management') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>متابعة الدليفري</span>
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            @unless($isDeliveryUser)
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
                    @if($adminUser?->hasPermission('manage_branches') || $adminUser?->isSuperAdmin())
                        <a href="{{ route('admin.branches.index') }}" class="sb-sublink {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الفروع</span>
                        </a>
                    @endif

                    @if($adminUser?->hasPermission('manage_categories') || $adminUser?->isSuperAdmin())
                        <a href="{{ route('admin.categories.index') }}" class="sb-sublink {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الأقسام</span>
                        </a>
                    @endif

                    @if($adminUser?->hasPermission('manage_products') || $adminUser?->isSuperAdmin())
                        <a href="{{ route('admin.products.index') }}" class="sb-sublink {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>المنتجات</span>
                        </a>
                    @endif

                    @if($adminUser?->hasPermission('manage_settings') || $adminUser?->isSuperAdmin())
                        <a href="{{ route('admin.settings.edit') }}" class="sb-sublink {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الإعدادات</span>
                        </a>
                    @endif

                    @if($adminUser?->hasPermission('manage_staff') || $adminUser?->isSuperAdmin())
                        <a href="{{ route('admin.staff.index') }}" class="sb-sublink {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الموظفون</span>
                        </a>
                    @endif

                    @auth
                        @if(auth()->user()->hasPermission('view_reports') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.reports.index') }}" class="sb-sublink {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>التقارير</span>
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
            @endunless

            @unless($isDeliveryUser)
            @if(($adminUser?->hasPermission('manage_digital_menu') || $adminUser?->isSuperAdmin()) && Route::has('admin.digital-menu.settings'))
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
                        <a href="{{ route('admin.digital-menu.settings') }}" class="sb-sublink {{ request()->routeIs('admin.digital-menu.settings') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الإعدادات</span>
                        </a>

                        @if(Route::has('admin.digital-menu.categories'))
                            <a href="{{ route('admin.digital-menu.categories') }}" class="sb-sublink {{ request()->routeIs('admin.digital-menu.categories') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>الأقسام</span>
                            </a>
                        @endif

                        @if(Route::has('admin.digital-menu.items'))
                            <a href="{{ route('admin.digital-menu.items') }}" class="sb-sublink {{ request()->routeIs('admin.digital-menu.items') ? 'active' : '' }}">
                                <span class="sb-sublink-dot"></span>
                                <span>المنتجات</span>
                            </a>
                        @endif

                        @if(Route::has('admin.digital-menu.qr'))
                            <a href="{{ route('admin.digital-menu.qr') }}" class="sb-sublink {{ request()->routeIs('admin.digital-menu.qr*') ? 'active' : '' }}">
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
                        <a href="{{ route('admin.popup-campaign.edit') }}" class="sb-sublink {{ request()->routeIs('admin.popup-campaign.*') ? 'active' : '' }}">
                            <span class="sb-sublink-dot"></span>
                            <span>الإعلان المنبثق</span>
                        </a>
                    </div>
                </div>
            @endif
            @endunless
        </div>

        <div class="sb-footer">
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

                <a href="{{ url('/admin/ai-assistant') }}" class="admin-ai-icon-btn" title="فتح في صفحة كاملة">
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
                data-src="{{ url('/admin/ai-assistant?embed=1') }}"
                loading="lazy"
                referrerpolicy="same-origin"
            ></iframe>
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

    const AI_STORAGE_KEY = 'admin_ai_widget_state_v1';
    const AI_NOTIFY_KEY = 'admin_ai_unread_v1';

    let toastTimer = null;
    let audioCtx = null;

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
            aiFrame.setAttribute('src', aiFrame.dataset.src || '/admin/ai-assistant?embed=1');
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
});
</script>

@stack('scripts')
</body>
</html>
