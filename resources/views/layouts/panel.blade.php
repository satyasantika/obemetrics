<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} @stack('title')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        :root {
            --panel-bg: #f4f1ea;
            --panel-surface: rgba(255, 250, 244, 0.82);
            --panel-surface-strong: #fffaf3;
            --panel-border: rgba(96, 84, 68, 0.10);
            --panel-text: #43362b;
            --panel-muted: #847463;
            --panel-accent: #7b9b8b;
            --panel-accent-strong: #5f7d6f;
            --panel-accent-soft: rgba(123, 155, 139, 0.16);
            --panel-shadow: 0 18px 40px rgba(82, 63, 42, 0.08);
        }

        body.modern-panel-shell {
            font-family: 'Manrope', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(220, 205, 184, 0.55), transparent 28%),
                linear-gradient(180deg, #f7f2ea 0%, #f1ece3 100%);
            color: var(--panel-text);
        }

        .modern-panel-shell .wrapper,
        .modern-panel-shell .content-wrapper,
        .modern-panel-shell .main-footer,
        .modern-panel-shell .main-header {
            background: transparent;
        }

        .modern-panel-shell .main-header.app-topbar {
            border-bottom: 1px solid rgba(96, 84, 68, 0.08);
            background: linear-gradient(135deg, rgba(255, 251, 246, 0.86), rgba(248, 241, 230, 0.74));
            backdrop-filter: blur(16px);
            box-shadow: 0 10px 28px rgba(95, 78, 58, 0.06);
            min-height: 4rem;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }

        .modern-panel-shell .main-header .nav-link,
        .modern-panel-shell .main-header .dropdown-item,
        .modern-panel-shell .main-footer,
        .modern-panel-shell .main-footer a {
            color: var(--panel-text);
        }

        .modern-panel-shell .app-topbar .navbar-nav {
            gap: 0.35rem;
            align-items: center;
        }

        .modern-panel-shell .app-topbar .nav-link {
            border-radius: 999px;
            font-weight: 700;
            padding: 0.52rem 0.9rem;
            line-height: 1;
            transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        .modern-panel-shell .app-topbar .topbar-home-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
            padding: 0;
        }

        .modern-panel-shell .app-topbar .topbar-menu-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-left: 0.35rem;
            font-weight: 700;
            color: #6f6151;
            white-space: nowrap;
            max-width: min(50vw, 520px);
        }

        .modern-panel-shell .app-topbar .topbar-menu-indicator .divider {
            color: #b29f88;
            font-weight: 600;
        }

        .modern-panel-shell .app-topbar .topbar-menu-indicator .label {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modern-panel-shell .app-topbar .nav-link:hover,
        .modern-panel-shell .app-topbar .nav-link:focus-visible {
            background: rgba(123, 155, 139, 0.16);
            color: var(--panel-accent-strong);
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(95, 125, 111, 0.12);
            outline: none;
        }

        .modern-panel-shell .app-topbar .nav-link[data-widget="pushmenu"] {
            width: 2.35rem;
            height: 2.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            background: rgba(255, 255, 255, 0.58);
            border: 1px solid rgba(96, 84, 68, 0.1);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }

        .modern-panel-shell .app-topbar .dropdown-menu {
            border: 1px solid rgba(96, 84, 68, 0.1);
            border-radius: 0.9rem;
            padding: 0.45rem;
            background: rgba(255, 251, 246, 0.95);
            backdrop-filter: blur(14px);
            box-shadow: 0 14px 30px rgba(82, 63, 42, 0.12);
        }

        .modern-panel-shell .app-topbar .dropdown-item {
            border-radius: 0.6rem;
            font-weight: 600;
            padding: 0.48rem 0.7rem;
        }

        .modern-panel-shell .app-topbar .dropdown-item:hover,
        .modern-panel-shell .app-topbar .dropdown-item:focus {
            background: rgba(123, 155, 139, 0.14);
            color: var(--panel-accent-strong);
        }

        .modern-panel-shell .app-sidebar {
            background: linear-gradient(180deg, rgba(250, 244, 235, 0.96) 0%, rgba(244, 238, 229, 0.96) 100%);
            border-right: 1px solid var(--panel-border);
            box-shadow: 14px 0 36px rgba(84, 68, 50, 0.06);
        }

        .modern-panel-shell .app-sidebar .brand-link {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            margin: 1rem;
            width: calc(100% - 2rem);
            min-height: 5rem;
            box-sizing: border-box;
            padding: 1rem 1.05rem;
            border: 1px solid rgba(96, 84, 68, 0.08);
            border-radius: 1.2rem;
            background: linear-gradient(135deg, rgba(255, 250, 244, 0.92), rgba(247, 240, 229, 0.82));
            box-shadow: var(--panel-shadow);
        }

        .modern-panel-shell .app-sidebar .brand-copy {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
        }

        .modern-panel-shell .app-sidebar .brand-eyebrow {
            color: var(--panel-muted);
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            line-height: 1.3;
            margin-bottom: 0.2rem;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .modern-panel-shell .app-sidebar .brand-text {
            color: var(--panel-text);
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modern-panel-shell .app-sidebar .brand-mark {
            position: relative;
            flex: 0 0 auto;
            width: 3rem;
            height: 3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(123, 155, 139, 0.20), rgba(231, 212, 184, 0.66));
            color: var(--panel-accent-strong);
            border: 1px solid rgba(123, 155, 139, 0.16);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
            overflow: hidden;
        }

        .modern-panel-shell .app-sidebar .brand-mark > i:first-child {
            font-size: 1.45rem;
            opacity: 0.82;
        }

        .modern-panel-shell .app-sidebar .brand-mark .brand-mark-accent {
            position: absolute;
            right: 0.34rem;
            bottom: 0.34rem;
            font-size: 0.82rem;
            background: rgba(255, 250, 244, 0.88);
            border-radius: 999px;
            padding: 0.16rem;
            box-shadow: 0 4px 10px rgba(70, 92, 79, 0.12);
        }

        .modern-panel-shell .app-sidebar .sidebar {
            position: relative;
            z-index: 1;
            padding: 0 0.9rem 1.1rem;
        }

        .modern-panel-shell .nav-sidebar > .nav-item {
            margin-bottom: 0.28rem;
        }

        .modern-panel-shell .nav-sidebar .nav-header {
            padding: 1rem 0.9rem 0.45rem;
            font-size: 0.69rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--panel-muted);
        }

        .modern-panel-shell .nav-sidebar .nav-link {
            border-radius: 0.95rem;
            margin: 0;
            padding: 0.78rem 0.92rem;
            color: #5a4b3e;
            font-weight: 600;
            transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        .modern-panel-shell .nav-sidebar .nav-link .nav-icon {
            width: 1.8rem;
            margin-right: 0.35rem;
            color: #8a7b69;
            font-size: 0.95rem;
        }

        .modern-panel-shell .nav-sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.62);
            color: var(--panel-text);
            transform: translateX(2px);
            border-top-left-radius: 0.95rem;
            border-bottom-left-radius: 0.95rem;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .modern-panel-shell .nav-sidebar .nav-link:hover .nav-icon,
        .modern-panel-shell .nav-sidebar .nav-link.active .nav-icon {
            color: var(--panel-accent-strong);
        }

        .modern-panel-shell .nav-sidebar .nav-link.active {
            background: linear-gradient(135deg, rgba(123, 155, 139, 0.20), rgba(255, 255, 255, 0.88));
            color: var(--panel-text);
            box-shadow: 0 10px 24px rgba(105, 132, 118, 0.12);
            border-top-left-radius: 0.95rem;
            border-bottom-left-radius: 0.95rem;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .modern-panel-shell .nav-sidebar .nav-link.disabled,
        .modern-panel-shell .nav-sidebar .nav-link[aria-disabled="true"] {
            opacity: 0.5;
            background: transparent;
            box-shadow: none;
        }

        .modern-panel-shell .content-wrapper {
            min-height: calc(100vh - 9rem) !important;
            background: transparent;
        }

        .modern-panel-shell .content-wrapper .content {
            padding-top: 1rem !important;
        }

        .modern-panel-shell .content-wrapper .card {
            border: 1px solid rgba(96, 84, 68, 0.08);
            border-radius: 1.25rem;
            background: var(--panel-surface);
            backdrop-filter: blur(10px);
            box-shadow: var(--panel-shadow);
            overflow: hidden;
        }

        .modern-panel-shell .content-wrapper .card-header {
            background: rgba(255, 250, 244, 0.72);
            border-bottom: 1px solid rgba(96, 84, 68, 0.08);
        }

        .modern-panel-shell .main-footer.app-footer {
            border-top: 1px solid rgba(96, 84, 68, 0.06);
            background: linear-gradient(135deg, rgba(255, 251, 246, 0.82), rgba(248, 241, 230, 0.7));
            color: var(--panel-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.55rem 1.2rem;
            margin-left: 260px !important;
            margin-right: 1rem !important;
            margin-bottom: 1rem !important;
            margin-top: 0 !important;
            padding: 0.8rem 1rem;
            box-sizing: border-box;
            width: auto;
            border-radius: 1rem;
            box-shadow: 0 10px 24px rgba(95, 78, 58, 0.06);
            transition: margin-left 0.3s ease;
        }

        /* Footer adjust ketika sidebar di-collapse */
        .modern-panel-shell.sidebar-collapse .main-footer.app-footer,
        .modern-panel-shell.sidebar-mini.sidebar-collapse .main-footer.app-footer {
            margin-left: 88px !important;
            margin-right: 1rem !important;
            margin-bottom: 1rem !important;
        }

        .modern-panel-shell .app-footer .footer-copy,
        .modern-panel-shell .app-footer .footer-meta {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-width: 0;
            font-size: 0.88rem;
        }

        .modern-panel-shell .app-footer .footer-copy strong {
            color: var(--panel-text);
            font-weight: 800;
        }

        .modern-panel-shell .app-footer .footer-meta {
            margin-left: auto;
            color: #726252;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .modern-panel-shell .app-footer .footer-meta a {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            color: var(--panel-accent-strong);
            font-weight: 700;
            text-decoration: none;
            border-radius: 999px;
            padding: 0.2rem 0.55rem;
            background: rgba(123, 155, 139, 0.12);
        }

        .modern-panel-shell .app-footer .footer-meta a:hover {
            background: rgba(123, 155, 139, 0.18);
            color: #4f6e61;
        }

        @media (max-width: 767.98px) {
            .modern-panel-shell .app-topbar .nav-link {
                padding: 0.45rem 0.72rem;
            }

            .modern-panel-shell .app-topbar .topbar-home-link {
                width: 2.05rem;
                height: 2.05rem;
            }

            .modern-panel-shell .app-topbar .topbar-menu-indicator {
                max-width: 45vw;
                font-size: 0.82rem;
                margin-left: 0.25rem;
            }

            .modern-panel-shell .main-footer.app-footer {
                margin-left: 1rem !important;
                margin-right: 1rem !important;
                margin-bottom: 1rem !important;
                margin-top: 0.75rem !important;
                padding: 0.9rem 1rem;
                gap: 0.6rem 1rem;
                border-radius: 0.9rem;
            }

            /* Override sidebar-collapse footer margin di mobile */
            .modern-panel-shell.sidebar-collapse .main-footer.app-footer,
            .modern-panel-shell.sidebar-mini.sidebar-collapse .main-footer.app-footer {
                margin-left: 1rem !important;
            }

            .modern-panel-shell .app-footer .footer-copy,
            .modern-panel-shell .app-footer .footer-meta {
                width: 100%;
                justify-content: flex-start;
                font-size: 0.8rem;
                flex-direction: column;
                gap: 0.25rem;
            }

            .modern-panel-shell .app-footer .footer-copy strong {
                font-size: 0.85rem;
                display: block;
                line-height: 1.4;
            }

            .modern-panel-shell .app-footer .footer-copy span {
                font-size: 0.75rem;
                color: #9d8d79;
            }

            .modern-panel-shell .app-footer .footer-meta {
                margin-left: 0;
                margin-top: 0.6rem;
            }

            .modern-panel-shell .app-footer .footer-meta a {
                font-size: 0.78rem;
                padding: 0.18rem 0.5rem;
                gap: 0.2rem;
            }

            .modern-panel-shell .app-footer .footer-meta a i {
                font-size: 0.75rem;
            }
        }

        /* ── Desktop collapsed (sidebar-mini + sidebar-collapse) ── */
        .modern-panel-shell.sidebar-mini.sidebar-collapse .app-sidebar .brand-link {
            justify-content: flex-start;
            align-items: center;
            padding: 0.38rem;
            width: calc(100% - 0.6rem);
            min-height: 3.35rem;
            margin: 0.2rem auto 0.45rem;
            gap: 0.35rem;
            border-radius: 0.95rem;
            overflow: hidden;
        }

        .modern-panel-shell.sidebar-mini.sidebar-collapse .app-sidebar .brand-copy {
            display: flex !important;
            justify-content: center;
            min-width: 0;
            width: calc(100% - 2.35rem);
            max-width: calc(100% - 2.35rem);
            height: 2.05rem;
            line-height: 1;
            overflow: hidden;
        }

        .modern-panel-shell.sidebar-mini.sidebar-collapse .app-sidebar .brand-mark {
            width: 2.05rem;
            height: 2.05rem;
            border-radius: 0.6rem;
        }

        .modern-panel-shell.sidebar-mini.sidebar-collapse .app-sidebar .brand-mark > i:first-child {
            font-size: 1rem;
        }

        .modern-panel-shell.sidebar-mini.sidebar-collapse .app-sidebar .brand-mark .brand-mark-accent {
            font-size: 0.52rem;
            right: 0.1rem;
            bottom: 0.1rem;
            padding: 0.08rem;
        }

        .modern-panel-shell.sidebar-mini.sidebar-collapse .app-sidebar .brand-eyebrow {
            display: none;
        }

        .modern-panel-shell.sidebar-mini.sidebar-collapse .app-sidebar .brand-copy .brand-text {
            display: block;
            width: 100%;
            max-width: 100%;
            text-align: left;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-decoration: none;
        }

        @media (max-width: 991.98px) {
            .modern-panel-shell .app-sidebar .brand-link {
                margin: 0.75rem;
                width: calc(100% - 1.5rem);
            }

            .modern-panel-shell .app-sidebar .sidebar {
                padding: 0 0.6rem 1rem;
            }

            .modern-panel-shell .app-sidebar .brand-eyebrow {
                font-size: 0.54rem;
            }
        }
    </style>
    @if (request()->routeIs('settings.import.*'))
        <style>
            .content-wrapper .card {
                border: 0;
                border-radius: 1rem;
                box-shadow: 0 .125rem .75rem rgba(0, 0, 0, .08);
                overflow: hidden;
            }

            .content-wrapper .card-header {
                background: var(--bs-light);
                border-bottom: 1px solid var(--bs-border-color);
            }

            .content-wrapper .card-body {
                background: var(--bs-light-bg-subtle);
            }

            .content-wrapper .table-responsive {
                border: 1px solid var(--bs-border-color);
                border-radius: .75rem;
                background: var(--bs-body-bg);
                box-shadow: 0 .125rem .5rem rgba(0, 0, 0, .05);
            }

            .content-wrapper .table {
                margin-bottom: 0;
            }

            .content-wrapper .table thead th {
                background: var(--bs-light);
                font-size: .775rem;
                text-transform: uppercase;
                letter-spacing: .03em;
                color: var(--bs-secondary-color);
                vertical-align: middle;
            }

            .content-wrapper .btn {
                border-radius: 9999px;
                font-weight: 600;
                box-shadow: 0 .1rem .4rem rgba(0, 0, 0, .08);
            }

            .content-wrapper .badge {
                border-radius: 9999px;
            }

            .content-wrapper input.form-control,
            .content-wrapper select.form-select,
            .content-wrapper select.form-control,
            .content-wrapper textarea.form-control {
                border-radius: .6rem;
                min-height: calc(1.5em + .75rem + 2px);
            }

            .content-wrapper .dataTables_wrapper .dataTables_length select,
            .content-wrapper .dataTables_wrapper .dataTables_filter input {
                border-radius: .6rem;
                border: 1px solid var(--bs-border-color);
                padding: .2rem .5rem;
            }
        </style>
    @endif
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed modern-panel-shell">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light px-3 app-topbar">
        @php
            $currentRouteName = optional(request()->route())->getName();
            $menuLabel = $currentRouteName
                ? str_replace('.', ' / ', ucwords(str_replace(['-', '_'], ' ', $currentRouteName)))
                : 'Dashboard';
        @endphp
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-flex align-items-center">
                <a href="{{ auth()->check() ? route('home') : url('/') }}" class="nav-link topbar-home-link" title="Home" aria-label="Home">
                    <i class="fas fa-home"></i>
                </a>
                <span class="topbar-menu-indicator" title="{{ $menuLabel }}">
                    <span class="divider">/</span>
                    <span class="label" id="topbar-menu-label">{{ $menuLabel }}</span>
                </span>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            @auth
                <li class="nav-item dropdown">
                    <a class="nav-link" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                        <i class="far fa-user"></i> {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        @impersonating($guard = null)
                            <a class="dropdown-item" href="{{ route('impersonate.leave') }}">Back to Admin</a>
                        @endImpersonating
                        <a class="dropdown-item" href="{{ route('password.change') }}">Ubah Password</a>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form-panel').submit();">
                            Logout
                        </a>
                        <form id="logout-form-panel" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            @endauth
        </ul>
    </nav>

    @include('layouts.partials.sidebar')

    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                @include('layouts.alert')
                @yield('content')
            </div>
        </section>

    </div>
    <footer class="main-footer app-footer">
        <div class="footer-copy">
            <strong>&copy; {{ now()->year }} {{ config('app.name', 'OBEmetrics') }}.</strong>
            <span>All rights reserved.</span>
        </div>
        <div class="footer-meta">
            <span>LPMPP Universitas Siliwangi</span>
            <a href="https://lpmpp.unsil.ac.id" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-globe"></i>
                lpmpp.unsil.ac.id
            </a>
        </div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    (function () {
        var labelEl = document.getElementById('topbar-menu-label');
        if (!labelEl) {
            return;
        }

        var activeLink = document.querySelector('.main-sidebar .nav-sidebar .nav-link.active');
        if (!activeLink) {
            return;
        }

        var sourceEl = activeLink.querySelector('p') || activeLink;
        var text = (sourceEl.textContent || '').replace(/\s+/g, ' ').trim();
        if (!text) {
            return;
        }

        labelEl.textContent = text;
        if (labelEl.parentElement) {
            labelEl.parentElement.setAttribute('title', text);
        }
    })();
</script>
@stack('scripts')
</body>
</html>
