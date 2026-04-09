<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'obemetrics') }}</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --g400: #4ade80;
            --g500: #22c55e;
            --g600: #16a34a;
            --g700: #15803d;
            --g800: #166534;
            --g900: #14532d;
            --hero: #0b1120;
            --hero2: #0f1a2e;
            --card-dark: rgba(255,255,255,.05);
            --border-dark: rgba(255,255,255,.1);
            --text-w: #f1f5f9;
            --text-ws: #94a3b8;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Nunito', sans-serif; color: var(--text); background: var(--surface); overflow-x: hidden; }
        a { text-decoration: none; }
        img { display: block; max-width: 100%; }

        /* ── NAVBAR ── */
        .navbar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(11,17,32,.85);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border-dark);
        }
        .navbar-inner {
            width: min(1200px, 92%);
            margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 0;
        }
        .brand {
            display: flex; align-items: center; gap: .65rem;
            color: var(--text-w); font-weight: 800; font-size: 1.1rem;
        }
        .brand-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg, var(--g600), var(--g400));
            display: grid; place-items: center; color: #fff; font-size: 1.1rem;
            box-shadow: 0 4px 14px rgba(22,163,74,.45);
        }
        .nav-links { display: flex; align-items: center; gap: .5rem; }
        .nav-btn {
            padding: .5rem 1.1rem; border-radius: 8px; font-weight: 700; font-size: .88rem;
            display: inline-flex; align-items: center; gap: .4rem; transition: .18s;
        }
        .nav-btn-ghost {
            color: var(--text-ws); border: 1px solid var(--border-dark); background: transparent;
        }
        .nav-btn-ghost:hover { color: var(--text-w); border-color: rgba(255,255,255,.25); }
        .nav-btn-primary {
            background: linear-gradient(135deg, var(--g600), var(--g500));
            color: #fff; border: none;
            box-shadow: 0 4px 14px rgba(22,163,74,.35);
        }
        .nav-btn-primary:hover { filter: brightness(1.08); transform: translateY(-1px); }

        /* ── HERO ── */
        .hero-wrap {
            background: linear-gradient(160deg, var(--hero) 0%, var(--hero2) 60%, #0d2418 100%);
            position: relative; overflow: hidden;
        }
        .hero-wrap::before {
            content: '';
            position: absolute; inset: 0; pointer-events: none;
            background:
                radial-gradient(ellipse at 15% 40%, rgba(22,163,74,.18) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 20%, rgba(74,222,128,.10) 0%, transparent 45%);
        }
        .hero-inner {
            position: relative;
            width: min(1200px, 92%);
            margin: 0 auto;
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 3rem; align-items: center;
            padding: 5rem 0 4.5rem;
        }
        .hero-tag {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(22,163,74,.15); border: 1px solid rgba(74,222,128,.3);
            color: var(--g400); font-size: .8rem; font-weight: 700;
            border-radius: 999px; padding: .32rem .85rem; margin-bottom: 1.3rem;
            letter-spacing: .04em; text-transform: uppercase;
        }
        .hero-h1 {
            font-size: clamp(2rem, 3.2vw, 3.2rem);
            font-weight: 900; line-height: 1.18;
            color: var(--text-w);
        }
        .hero-h1 em {
            font-style: normal;
            background: linear-gradient(90deg, var(--g400), var(--g500));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-sub {
            margin: 1.1rem 0 2rem;
            color: var(--text-ws); font-size: 1rem; line-height: 1.75;
        }
        .hero-cta { display: flex; flex-wrap: wrap; gap: .75rem; }
        .hero-btn-main {
            padding: .75rem 1.6rem; border-radius: 10px; font-weight: 800; font-size: .95rem;
            background: linear-gradient(135deg, var(--g600), var(--g500)); color: #fff; border: none;
            display: inline-flex; align-items: center; gap: .5rem; transition: .18s;
            box-shadow: 0 8px 24px rgba(22,163,74,.4);
        }
        .hero-btn-main:hover { transform: translateY(-2px); filter: brightness(1.08); }
        .hero-btn-ghost {
            padding: .75rem 1.6rem; border-radius: 10px; font-weight: 700; font-size: .95rem;
            border: 1px solid rgba(255,255,255,.2); color: var(--text-w); background: transparent;
            display: inline-flex; align-items: center; gap: .5rem; transition: .18s;
        }
        .hero-btn-ghost:hover { background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.35); }
        .hero-tabs {
            display: flex; gap: .5rem; margin-top: 2rem; flex-wrap: wrap;
        }
        .hero-tab {
            padding: .4rem 1rem; border-radius: 999px; font-size: .82rem; font-weight: 700;
            border: 1px solid rgba(255,255,255,.15); color: var(--text-ws); cursor: default;
            letter-spacing: .04em; text-transform: uppercase;
        }
        .hero-tab.active {
            background: rgba(22,163,74,.2); border-color: rgba(74,222,128,.4); color: var(--g400);
        }
        .hero-right { display: flex; flex-direction: column; gap: 1rem; }
        .hero-stat-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem;
        }
        .hero-stat {
            background: var(--card-dark); border: 1px solid var(--border-dark);
            border-radius: 14px; padding: 1.1rem .85rem; text-align: center;
            backdrop-filter: blur(6px);
        }
        .hero-stat .val {
            font-size: 1.6rem; font-weight: 900; color: var(--g400); line-height: 1;
        }
        .hero-stat .lbl {
            color: var(--text-ws); font-size: .75rem; margin-top: .3rem;
        }
        .hero-visual {
            background: rgba(255,255,255,.04); border: 1px solid var(--border-dark);
            border-radius: 16px; padding: 1.75rem;
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; gap: .6rem; text-align: center;
            min-height: 180px; color: var(--text-ws);
        }
        .hero-visual i { font-size: 3rem; opacity: .35; color: var(--g400); }
        .hero-visual small { font-size: .78rem; opacity: .6; }
        .hero-contact {
            color: var(--text-ws); font-size: .82rem;
            display: flex; align-items: center; gap: .5rem; margin-top: .25rem;
        }
        .hero-contact i { color: var(--g500); }

        /* ── STATS BAR ── */
        .stats-bar {
            background: var(--surface-soft); border-bottom: 1px solid var(--border);
        }
        .stats-bar-inner {
            width: min(1200px, 92%); margin: 0 auto;
            display: flex; flex-wrap: wrap; align-items: center;
            gap: 0; padding: 1.4rem 0;
        }
        .stat-item {
            flex: 1; min-width: 160px;
            display: flex; align-items: center; gap: .85rem;
            padding: .4rem 1.5rem;
            border-right: 1px solid var(--border);
        }
        .stat-item:first-child { padding-left: 0; }
        .stat-item:last-child { border-right: none; }
        .stat-icon {
            width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
            display: grid; place-items: center;
            background: linear-gradient(135deg, rgba(22,163,74,.12), rgba(74,222,128,.08));
            color: var(--g600); font-size: 1.15rem;
            border: 1px solid rgba(22,163,74,.2);
        }
        .stat-val { font-size: 1.3rem; font-weight: 900; color: var(--g700); line-height: 1; }
        .stat-lbl { font-size: .78rem; color: var(--muted); margin-top: .15rem; }

        /* ── SECTION HELPERS ── */
        .light-wrap { background: var(--surface); }
        .section {
            width: min(1200px, 92%); margin: 0 auto;
            padding: 4rem 0;
        }
        .eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
            color: var(--g700); background: rgba(22,163,74,.08);
            border: 1px solid rgba(22,163,74,.2);
            border-radius: 999px; padding: .28rem .8rem; margin-bottom: .75rem;
        }
        .sec-title {
            font-size: clamp(1.4rem, 2.2vw, 1.9rem); font-weight: 900;
            color: var(--text); margin-bottom: .5rem;
        }
        .sec-sub {
            color: var(--muted); font-size: .97rem; line-height: 1.72; max-width: 580px;
        }
        .sec-head { margin-bottom: 2rem; }

        /* ── FEATURE GRID ── */
        .feat-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.1rem;
        }
        .feat-card {
            border: 1px solid var(--border); border-radius: 18px;
            padding: 1.5rem 1.35rem; transition: .2s; background: var(--surface);
        }
        .feat-card:hover {
            border-color: rgba(22,163,74,.35);
            box-shadow: 0 10px 32px rgba(22,163,74,.1);
            transform: translateY(-3px);
        }
        .feat-icon {
            width: 46px; height: 46px; border-radius: 12px; font-size: 1.3rem;
            display: grid; place-items: center;
            background: linear-gradient(135deg, rgba(22,163,74,.12), rgba(74,222,128,.07));
            color: var(--g600); border: 1px solid rgba(22,163,74,.2);
            margin-bottom: 1rem;
        }
        .feat-card h3 { font-size: 1rem; font-weight: 800; margin-bottom: .4rem; }
        .feat-card p { color: var(--muted); font-size: .9rem; line-height: 1.65; }

        /* ── NEWS ── */
        .news-section { background: var(--surface-soft); }
        .news-inner { width: min(1200px, 92%); margin: 0 auto; padding: 4rem 0; }
        .news-grid {
            display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem;
        }
        .news-featured {
            background: var(--surface); border: 1px solid var(--border); border-radius: 18px;
            overflow: hidden;
        }
        .news-featured-img {
            background: linear-gradient(135deg, #0b1120, #0d2418);
            height: 180px; display: flex; align-items: center; justify-content: center;
            color: rgba(74,222,128,.3); font-size: 3.5rem;
        }
        .news-featured-body { padding: 1.5rem; }
        .news-tag {
            display: inline-block; font-size: .72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: var(--g700); background: rgba(22,163,74,.1);
            border: 1px solid rgba(22,163,74,.2); border-radius: 999px;
            padding: .2rem .65rem; margin-bottom: .75rem;
        }
        .news-tag-blue { color: #0369a1; background: #f0f9ff; border-color: #bae6fd; }
        .news-featured-body h3 { font-size: 1.15rem; font-weight: 800; margin-bottom: .5rem; }
        .news-featured-body p { color: var(--muted); font-size: .92rem; line-height: 1.65; }
        .news-meta {
            display: flex; align-items: center; gap: .5rem; margin-top: 1rem;
            color: var(--muted); font-size: .82rem;
        }
        .news-list { display: flex; flex-direction: column; gap: .85rem; }
        .news-item {
            background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
            padding: 1rem 1.1rem; display: flex; gap: 1rem; align-items: flex-start;
            transition: .18s;
        }
        .news-item:hover { border-color: rgba(22,163,74,.3); transform: translateX(3px); }
        .news-item-icon {
            width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
            display: grid; place-items: center; font-size: .95rem;
            background: rgba(22,163,74,.08); color: var(--g600);
            border: 1px solid rgba(22,163,74,.15);
        }
        .news-item-body h4 { font-size: .9rem; font-weight: 700; margin-bottom: .25rem; }
        .news-item-body p { color: var(--muted); font-size: .82rem; line-height: 1.55; }
        .news-item-date { color: var(--muted); font-size: .76rem; margin-top: .3rem; }

        /* ── BENEFIT ── */
        .benefit-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;
        }
        .benefit-list { list-style: none; margin-top: 1.25rem; display: flex; flex-direction: column; gap: .7rem; }
        .benefit-list li {
            display: flex; align-items: flex-start; gap: .7rem;
            color: var(--muted); font-size: .95rem; line-height: 1.6;
        }
        .benefit-list li i { color: var(--g600); flex-shrink: 0; margin-top: .2rem; font-size: 1rem; }
        .benefit-cta {
            background: linear-gradient(150deg, var(--g800) 0%, var(--g600) 100%);
            border-radius: 20px; padding: 2.5rem 2rem; color: #fff;
            display: flex; flex-direction: column; gap: 1.1rem;
            position: relative; overflow: hidden;
        }
        .benefit-cta::before {
            content: '';
            position: absolute; top: -40%; right: -20%;
            width: 240px; height: 240px; border-radius: 50%;
            background: rgba(255,255,255,.05); pointer-events: none;
        }
        .benefit-cta h3 { font-size: 1.4rem; font-weight: 900; }
        .benefit-cta p { font-size: .93rem; opacity: .82; line-height: 1.65; }
        .cta-btn-white {
            align-self: flex-start; background: #fff; color: var(--g700);
            font-weight: 800; font-size: .93rem; border: none; border-radius: 10px;
            padding: .72rem 1.5rem; display: inline-flex; align-items: center; gap: .5rem;
            transition: .18s;
        }
        .cta-btn-white:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.15); }
        .cta-note { font-size: .8rem; opacity: .65; }

        /* ── CTA BAND ── */
        .cta-band {
            background: linear-gradient(160deg, var(--hero) 0%, #0d2418 100%);
            position: relative; overflow: hidden;
        }
        .cta-band::before {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background: radial-gradient(ellipse at 50% 50%, rgba(22,163,74,.15), transparent 65%);
        }
        .cta-band-inner {
            position: relative; width: min(1200px, 92%); margin: 0 auto;
            padding: 4rem 0; text-align: center;
        }
        .cta-band-inner h2 { font-size: clamp(1.5rem, 2.5vw, 2.2rem); font-weight: 900; color: var(--text-w); margin-bottom: .6rem; }
        .cta-band-inner p { color: var(--text-ws); font-size: .97rem; max-width: 500px; margin: 0 auto 2rem; line-height: 1.7; }
        .cta-band-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: .75rem; }

        /* ── FOOTER ── */
        .footer-wrap {
            background: var(--hero);
            border-top: 1px solid var(--border-dark);
        }
        .footer-inner {
            width: min(1200px, 92%); margin: 0 auto;
            padding: 3rem 0 2rem;
            display: grid; grid-template-columns: 1.6fr 1fr 1fr 1.2fr; gap: 2.5rem;
        }
        .footer-brand { color: var(--text-ws); }
        .footer-brand .brand { margin-bottom: .8rem; }
        .footer-brand p { font-size: .88rem; line-height: 1.65; }
        .footer-col h4 { color: var(--text-w); font-size: .88rem; font-weight: 800; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: .06em; }
        .footer-links { list-style: none; display: flex; flex-direction: column; gap: .55rem; }
        .footer-links a { color: var(--text-ws); font-size: .87rem; transition: .15s; }
        .footer-links a:hover { color: var(--g400); }
        .footer-contact { list-style: none; display: flex; flex-direction: column; gap: .6rem; }
        .footer-contact li { display: flex; align-items: flex-start; gap: .55rem; color: var(--text-ws); font-size: .87rem; line-height: 1.5; }
        .footer-contact li i { color: var(--g500); flex-shrink: 0; margin-top: .1rem; }
        .footer-bottom {
            width: min(1200px, 92%); margin: 0 auto;
            padding: 1.25rem 0; border-top: 1px solid var(--border-dark);
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .5rem;
        }
        .footer-bottom span { color: var(--text-ws); font-size: .83rem; }
        .footer-bottom a { color: var(--g400); font-size: .83rem; }
        .footer-bottom a:hover { text-decoration: underline; }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .footer-inner { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 860px) {
            .hero-inner { grid-template-columns: 1fr; gap: 2rem; padding: 3.5rem 0 3rem; }
            .news-grid { grid-template-columns: 1fr; }
            .benefit-grid { grid-template-columns: 1fr; }
            .feat-grid { grid-template-columns: 1fr 1fr; }
            .stats-bar-inner { gap: .5rem; }
            .stat-item { min-width: 140px; padding: .4rem 1rem; }
        }
        @media (max-width: 600px) {
            .feat-grid { grid-template-columns: 1fr; }
            .footer-inner { grid-template-columns: 1fr; gap: 1.5rem; }
            .hero-stat-grid { grid-template-columns: repeat(3, 1fr); }
            .navbar-inner { gap: .5rem; }
        }
    </style>
</head>
<body>
@php
    $brandName    = config('app.name', 'obemetrics');
    $supportEmail = '[Isi email helpdesk resmi]';
    try {
        $totalProdi = \App\Models\Prodi::count();
        $totalMk    = \App\Models\Mk::count();
        $avgRaw     = \App\Models\Nilai::whereNotNull('nilai')->avg('nilai');
        if ($avgRaw === null) $avgRaw = \App\Models\KontrakMk::whereNotNull('nilai_angka')->avg('nilai_angka');
    } catch (\Throwable $e) {
        $totalProdi = 0; $totalMk = 0; $avgRaw = null;
    }
    $avgCapaian = $avgRaw !== null ? number_format((float)$avgRaw, 1, ',', '.') . '%' : '—';
@endphp

{{-- ── NAVBAR ── --}}
<nav class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
            {{ $brandName }}
        </a>
        <div class="nav-links">
            @if (Route::has('login'))
                @auth
                    <a class="nav-btn nav-btn-primary" href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                @else
                    <a class="nav-btn nav-btn-ghost" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Masuk</a>
                    @if (Route::has('register'))
                        <a class="nav-btn nav-btn-primary" href="{{ route('register') }}"><i class="bi bi-person-plus"></i> Daftar</a>
                    @endif
                @endauth
            @endif
        </div>
    </div>
</nav>

{{-- ── HERO ── --}}
<div class="hero-wrap">
    <div class="hero-inner">
        <div class="hero-left">
            <span class="hero-tag"><i class="bi bi-patch-check-fill"></i> Outcome-Based Education Platform</span>
            <h1 class="hero-h1">
                <em>{{ $brandName }}</em><br>
                Learning and Quality<br>Assurance System
            </h1>
            <p class="hero-sub">
                Dapatkan kemudahan untuk mewujudkan standar mutu akademik yang unggul, inovatif,
                dan terintegrasi dalam satu platform berbasis data nyata.
            </p>
            <div class="hero-cta">
                @auth
                    <a class="hero-btn-main" href="{{ route('home') }}"><i class="bi bi-rocket-takeoff"></i> Masuk ke Dashboard</a>
                @else
                    @if (Route::has('login'))
                        <a class="hero-btn-main" href="{{ route('login') }}"><i class="bi bi-door-open"></i> MASUK</a>
                    @endif
                    <a class="hero-btn-ghost" href="#fitur"><i class="bi bi-grid-1x2"></i> Lihat Fitur</a>
                @endauth
            </div>
            <div class="hero-tabs">
                <span class="hero-tab active">Penjaminan Mutu</span>
                <span class="hero-tab">Pengembangan Pembelajaran</span>
            </div>
            <div class="hero-contact" style="margin-top:1.25rem;">
                <i class="bi bi-headset"></i>
                Butuh bantuan? <strong style="color:#94a3b8;">{{ $supportEmail }}</strong>
            </div>
        </div>
        <div class="hero-right">
            <div class="hero-stat-grid">
                <div class="hero-stat">
                    <div class="val">{{ number_format($totalProdi, 0, ',', '.') }}</div>
                    <div class="lbl">Program Studi</div>
                </div>
                <div class="hero-stat">
                    <div class="val">{{ number_format($totalMk, 0, ',', '.') }}</div>
                    <div class="lbl">Mata Kuliah</div>
                </div>
                <div class="hero-stat">
                    <div class="val">{{ $avgCapaian }}</div>
                    <div class="lbl">Rata-rata Capaian</div>
                </div>
            </div>
            <div class="hero-visual">
                <i class="bi bi-image"></i>
                <div style="font-size:.88rem;">
                    <strong style="color:#94a3b8;">Placeholder Visual Hero</strong><br>
                    Ganti dengan foto mahasiswa / preview dashboard.
                </div>
                <small>Rekomendasi: <code>public/images/welcome-hero.png</code> (1200x800 px)</small>
            </div>
        </div>
    </div>
</div>

{{-- ── STATS BAR ── --}}
<div class="stats-bar">
    <div class="stats-bar-inner">
        <div class="stat-item">
            <span class="stat-icon"><i class="bi bi-building"></i></span>
            <div>
                <div class="stat-val">{{ number_format($totalProdi, 0, ',', '.') }}</div>
                <div class="stat-lbl">Program Studi Aktif</div>
            </div>
        </div>
        <div class="stat-item">
            <span class="stat-icon"><i class="bi bi-journal-text"></i></span>
            <div>
                <div class="stat-val">{{ number_format($totalMk, 0, ',', '.') }}</div>
                <div class="stat-lbl">Mata Kuliah Terdaftar</div>
            </div>
        </div>
        <div class="stat-item">
            <span class="stat-icon"><i class="bi bi-graph-up-arrow"></i></span>
            <div>
                <div class="stat-val">{{ $avgCapaian }}</div>
                <div class="stat-lbl">Rata-rata Capaian CPL</div>
            </div>
        </div>
        <div class="stat-item">
            <span class="stat-icon"><i class="bi bi-shield-check"></i></span>
            <div>
                <div class="stat-val">OBE</div>
                <div class="stat-lbl">Berbasis Standar Nasional</div>
            </div>
        </div>
    </div>
</div>

{{-- ── FITUR ── --}}
<div class="light-wrap">
    <div id="fitur" class="section">
        <div class="sec-head">
            <span class="eyebrow"><i class="bi bi-grid-1x2-fill"></i> Fitur Utama</span>
            <h2 class="sec-title">Satu Platform, Semua Kebutuhan OBE</h2>
            <p class="sec-sub">Rancangan fitur yang mendukung siklus penuh penjaminan mutu pembelajaran berbasis capaian.</p>
        </div>
        <div class="feat-grid">
            <div class="feat-card">
                <div class="feat-icon"><i class="bi bi-diagram-3-fill"></i></div>
                <h3>Pemetaan CPL–CPMK</h3>
                <p>Mapping capaian pembelajaran lintas kurikulum dan mata kuliah secara konsisten dan terdokumentasi.</p>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
                <h3>Analitik Evaluasi</h3>
                <p>Pantau indikator ketercapaian dan evaluasi hasil asesmen dengan tampilan visual yang mudah dipahami.</p>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="bi bi-shield-check"></i></div>
                <h3>Akses Berbasis Peran</h3>
                <p>Kontrol akses per pengguna, peran, dan izin agar pengelolaan data lebih aman dan terstruktur.</p>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="bi bi-book-fill"></i></div>
                <h3>Manajemen Kurikulum</h3>
                <p>Kelola data kurikulum, bahan kajian, dan profil lulusan dalam satu tempat yang mudah diakses.</p>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="bi bi-calculator-fill"></i></div>
                <h3>Pengisian Nilai</h3>
                <p>Proses pengisian nilai mahasiswa per komponen penugasan dengan kalkulasi otomatis bobot capaian.</p>
            </div>
            <div class="feat-card">
                <div class="feat-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                <h3>Laporan dan Ekspor</h3>
                <p>Generate laporan capaian CPL secara otomatis siap untuk kebutuhan akreditasi dan evaluasi institusi.</p>
            </div>
        </div>
    </div>
</div>

{{-- ── NEWS ── --}}
<div class="news-section">
    <div class="news-inner">
        <div class="sec-head">
            <span class="eyebrow"><i class="bi bi-newspaper"></i> Informasi</span>
            <h2 class="sec-title">Informasi Terbaru dan Pengumuman</h2>
            <p class="sec-sub">Update terkini seputar akademik, agenda evaluasi, dan pengumuman penting dari unit terkait.</p>
        </div>
        <div class="news-grid">
            <div class="news-featured">
                <div class="news-featured-img"><i class="bi bi-megaphone-fill"></i></div>
                <div class="news-featured-body">
                    <span class="news-tag">Artikel Utama</span>
                    <h3>[Judul Pengumuman atau Artikel Utama]</h3>
                    <p>[Isi ringkasan pengumuman utama, misalnya deadline pengumpulan borang, rilis versi baru, atau kebijakan akademik terbaru.]</p>
                    <div class="news-meta">
                        <i class="bi bi-person-circle"></i> Admin &nbsp;&middot;&nbsp;
                        <i class="bi bi-calendar3"></i> {{ date('d M Y') }}
                    </div>
                </div>
            </div>
            <div class="news-list">
                <div class="news-item">
                    <span class="news-item-icon"><i class="bi bi-calendar-event-fill"></i></span>
                    <div class="news-item-body">
                        <div><span class="news-tag news-tag-blue">Agenda</span></div>
                        <h4>[Agenda Akademik 1]</h4>
                        <p>[Deskripsi singkat agenda, tanggal, dan lokasi atau tautan bergabung.]</p>
                        <div class="news-item-date"><i class="bi bi-clock"></i> [DD-MM-YYYY]</div>
                    </div>
                </div>
                <div class="news-item">
                    <span class="news-item-icon"><i class="bi bi-journal-code"></i></span>
                    <div class="news-item-body">
                        <div><span class="news-tag">Panduan</span></div>
                        <h4>[Panduan atau Dokumen Terbaru]</h4>
                        <p>[Deskripsi dokumen SOP atau video tutorial yang baru diterbitkan.]</p>
                        <div class="news-item-date"><i class="bi bi-clock"></i> [DD-MM-YYYY]</div>
                    </div>
                </div>
                <div class="news-item">
                    <span class="news-item-icon"><i class="bi bi-tools"></i></span>
                    <div class="news-item-body">
                        <div><span class="news-tag">Update</span></div>
                        <h4>[Update Fitur atau Pemeliharaan Sistem]</h4>
                        <p>[Informasi singkat pembaruan fitur atau jadwal maintenance sistem.]</p>
                        <div class="news-item-date"><i class="bi bi-clock"></i> [DD-MM-YYYY]</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── BENEFIT ── --}}
<div class="light-wrap">
    <div class="section">
        <div class="benefit-grid">
            <div>
                <span class="eyebrow"><i class="bi bi-stars"></i> Mengapa {{ $brandName }}?</span>
                <h2 class="sec-title">Meningkatkan Mutu Pendidikan<br>Secara Berkelanjutan</h2>
                <p class="sec-sub">{{ $brandName }} dirancang untuk membantu institusi pendidikan mencapai dan mempertahankan standar mutu yang tinggi sesuai visi dan misi lembaga.</p>
                <ul class="benefit-list">
                    <li><i class="bi bi-check-circle-fill"></i> Peningkatan akuntabilitas dan transparansi pengelolaan data akademik</li>
                    <li><i class="bi bi-check-circle-fill"></i> Efisiensi proses penjaminan mutu yang terstruktur dan terdokumentasi</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pemantauan kinerja program studi secara real-time</li>
                    <li><i class="bi bi-check-circle-fill"></i> Kesesuaian dengan standar nasional OBE dan akreditasi</li>
                    <li><i class="bi bi-check-circle-fill"></i> Mendukung pengambilan keputusan berbasis data yang sahih</li>
                    <li><i class="bi bi-check-circle-fill"></i> Integrasi antar unit: prodi, dosen, mahasiswa, dan pimpinan</li>
                </ul>
            </div>
            <div class="benefit-cta">
                <h3>Siap Memulai?</h3>
                <p>Bergabunglah dengan sistem penjaminan mutu pendidikan yang terintegrasi dan modern bersama {{ $brandName }}.</p>
                @auth
                    <a class="cta-btn-white" href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Buka Dashboard</a>
                @else
                    @if (Route::has('login'))
                        <a class="cta-btn-white" href="{{ route('login') }}"><i class="bi bi-door-open"></i> Masuk ke Sistem</a>
                    @endif
                @endauth
                <span class="cta-note">Butuh bantuan? Hubungi tim IT atau LPMPP.</span>
            </div>
        </div>
    </div>
</div>

{{-- ── CTA BAND ── --}}
<div class="cta-band">
    <div class="cta-band-inner">
        <h2>Mulai Perjalanan Mutu Anda Hari Ini</h2>
        <p>Platform terintegrasi untuk penjaminan mutu akademik yang unggul, inovatif, dan berbasis data nyata.</p>
        <div class="cta-band-actions">
            @auth
                <a class="hero-btn-main" href="{{ route('home') }}"><i class="bi bi-rocket-takeoff"></i> Masuk ke Dashboard</a>
            @else
                @if (Route::has('login'))
                    <a class="hero-btn-main" href="{{ route('login') }}"><i class="bi bi-door-open"></i> Masuk ke Sistem</a>
                @endif
                <a class="hero-btn-ghost" href="#fitur"><i class="bi bi-info-circle"></i> Pelajari Fitur</a>
            @endauth
        </div>
    </div>
</div>

{{-- ── FOOTER ── --}}
<div class="footer-wrap">
    <div class="footer-inner">
        <div class="footer-brand">
            <a class="brand" href="{{ url('/') }}" style="margin-bottom:.85rem;">
                <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
                {{ $brandName }}
            </a>
            <p>Sistem informasi berbasis OBE untuk penjaminan mutu pembelajaran di lingkungan Universitas Siliwangi.</p>
        </div>
        <div class="footer-col">
            <h4>Fitur</h4>
            <ul class="footer-links">
                <li><a href="#">Pemetaan CPL-CPMK</a></li>
                <li><a href="#">Analitik Evaluasi</a></li>
                <li><a href="#">Manajemen Kurikulum</a></li>
                <li><a href="#">Pengisian Nilai</a></li>
                <li><a href="#">Laporan dan Ekspor</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Menu</h4>
            <ul class="footer-links">
                @if (Route::has('login'))
                    <li><a href="{{ route('login') }}">Masuk</a></li>
                    @if (Route::has('register'))
                        <li><a href="{{ route('register') }}">Daftar</a></li>
                    @endif
                @endif
                <li><a href="https://lpmpp.unsil.ac.id" target="_blank">LPMPP Unsil</a></li>
                <li><a href="https://unsil.ac.id" target="_blank">Universitas Siliwangi</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Kontak</h4>
            <ul class="footer-contact">
                <li><i class="bi bi-geo-alt-fill"></i> Universitas Siliwangi, Tasikmalaya, Jawa Barat</li>
                <li><i class="bi bi-envelope-fill"></i> {{ $supportEmail }}</li>
                <li><i class="bi bi-globe"></i> <a href="https://lpmpp.unsil.ac.id" target="_blank" style="color:var(--g400);">lpmpp.unsil.ac.id</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>
            &copy; {{ date('Y') }} {{ $brandName }} &mdash; Lembaga Penjaminan Mutu dan Pengembangan Pembelajaran,
            Universitas Siliwangi
        </span>
        <a href="https://lpmpp.unsil.ac.id" target="_blank"><i class="bi bi-globe"></i> lpmpp.unsil.ac.id</a>
    </div>
</div>

</body>
</html>
