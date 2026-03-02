<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'obemetrics') }} | Welcome</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand-primary: #3b82f6;
            --brand-secondary: #14b8a6;
            --text-main: #0f172a;
            --text-soft: #64748b;
            --surface: #ffffff;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Nunito', sans-serif;
            color: var(--text-main);
            background: radial-gradient(circle at 10% 10%, rgba(59, 130, 246, 0.15), transparent 30%),
                        radial-gradient(circle at 90% 90%, rgba(20, 184, 166, 0.18), transparent 35%),
                        #f8fafc;
        }

        .container {
            width: min(1120px, 92%);
            margin: 0 auto;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-weight: 800;
            text-decoration: none;
            color: var(--text-main);
        }

        .brand-badge {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            color: #fff;
            font-weight: 800;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }

        .nav-actions {
            display: flex;
            gap: .75rem;
        }

        .btn {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: .65rem 1rem;
            text-decoration: none;
            font-weight: 700;
            transition: .2s ease;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
        }

        .btn-light {
            background: #fff;
            color: var(--text-main);
        }

        .btn-light:hover {
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .btn-primary {
            border: none;
            color: #fff;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            box-shadow: 0 10px 24px rgba(20, 184, 166, .25);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            filter: brightness(.98);
        }

        .hero {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 1.5rem;
            align-items: stretch;
            margin: 1rem 0 2.5rem;
        }

        .hero-main,
        .hero-side {
            background: rgba(255, 255, 255, .8);
            border: 1px solid rgba(226, 232, 240, .9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(15, 23, 42, .07);
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .85rem;
            font-weight: 700;
            color: #0369a1;
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            border-radius: 999px;
            padding: .35rem .7rem;
            margin-bottom: 1rem;
        }

        h1 {
            margin: 0;
            line-height: 1.2;
            font-size: clamp(1.8rem, 2.8vw, 2.8rem);
        }

        .lead {
            margin: 1rem 0 1.6rem;
            color: var(--text-soft);
            font-size: 1rem;
            line-height: 1.75;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .7rem;
        }

        .hero-meta {
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px dashed var(--border);
            color: var(--text-soft);
            font-size: .9rem;
        }

        .placeholder-box {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 1.25rem;
            background: #f8fafc;
            color: #475569;
            font-size: .95rem;
            line-height: 1.7;
        }

        .kpis {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }

        .kpi {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: .9rem;
            background: #fff;
        }

        .kpi .value {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: .2rem;
        }

        .kpi .label {
            color: var(--text-soft);
            font-size: .82rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1rem;
        }

        .feature-card h3 {
            margin: .75rem 0 .45rem;
            font-size: 1rem;
        }

        .feature-card p {
            margin: 0;
            color: var(--text-soft);
            line-height: 1.7;
            font-size: .92rem;
        }

        .insight-section {
            margin-bottom: 2rem;
            background: rgba(255, 255, 255, .85);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 8px 30px rgba(15, 23, 42, .05);
        }

        .insight-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: .6rem;
            margin-bottom: 1rem;
        }

        .insight-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .insight-subtitle {
            margin: .35rem 0 0;
            color: var(--text-soft);
            font-size: .92rem;
        }

        .insight-badge {
            border: 1px solid #bae6fd;
            background: #f0f9ff;
            color: #0369a1;
            font-size: .78rem;
            font-weight: 700;
            border-radius: 999px;
            padding: .3rem .65rem;
        }

        .insight-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .insight-card {
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
            padding: 1rem;
        }

        .insight-card h4 {
            margin: .5rem 0;
            font-size: .98rem;
        }

        .insight-card p,
        .insight-card li {
            color: var(--text-soft);
            font-size: .9rem;
            line-height: 1.65;
        }

        .insight-card ul {
            margin: .6rem 0 0;
            padding-left: 1rem;
        }

        footer {
            padding-bottom: 2rem;
            color: #64748b;
            font-size: .9rem;
            text-align: center;
        }

        @media (max-width: 980px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .insight-grid {
                grid-template-columns: 1fr;
            }

            .kpis {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
@php
    $brandName = config('app.name', 'obemetrics');
    $tagline = 'Satu platform terintegrasi untuk memantau, mengevaluasi, dan meningkatkan ketercapaian Outcome-Based Education (OBE).';
    $description = 'obemetrics membantu program studi, dosen, dan admin mengelola data CPL-CPMK, asesmen, serta pelaporan capaian pembelajaran secara terstruktur, transparan, dan berbasis data real-time.';
    $supportContact = '[Isi email/nomor helpdesk resmi]';

    try {
        $totalProdi = \App\Models\Prodi::count();
        $totalMk = \App\Models\Mk::count();

        $avgCapaianRaw = \App\Models\Nilai::whereNotNull('nilai')->avg('nilai');
        if ($avgCapaianRaw === null) {
            $avgCapaianRaw = \App\Models\KontrakMk::whereNotNull('nilai_angka')->avg('nilai_angka');
        }
    } catch (\Throwable $e) {
        $totalProdi = 0;
        $totalMk = 0;
        $avgCapaianRaw = null;
    }

    $avgCapaian = $avgCapaianRaw !== null
        ? number_format((float) $avgCapaianRaw, 1, ',', '.') . '%'
        : 'Belum ada data';
@endphp

<div class="container">
    <header class="navbar">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand-badge">O</span>
            <span>{{ $brandName }}</span>
        </a>

        @if (Route::has('login'))
            <nav class="nav-actions">
                @auth
                    <a class="btn btn-light" href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                @else
                    <a class="btn btn-light" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                    @if (Route::has('register'))
                        <a class="btn btn-primary" href="{{ route('register') }}"><i class="bi bi-person-plus"></i> Daftar</a>
                    @endif
                @endauth
            </nav>
        @endif
    </header>

    <section class="hero">
        <article class="hero-main">
            <span class="hero-label"><i class="bi bi-stars"></i> Platform Outcome-Based Education</span>
            <h1>Kelola data OBE lebih cepat, rapi, dan terukur bersama {{ $brandName }}</h1>
            <p class="lead">
                {{ $tagline }}
                <br>
                {{ $description }}
            </p>

            <div class="hero-actions">
                @auth
                    <a class="btn btn-primary" href="{{ route('home') }}"><i class="bi bi-rocket-takeoff"></i> Masuk ke Dashboard</a>
                @else
                    @if (Route::has('login'))
                        <a class="btn btn-primary" href="{{ route('login') }}"><i class="bi bi-door-open"></i> Mulai Sekarang</a>
                    @endif
                    <a class="btn btn-light" href="#fitur"><i class="bi bi-grid"></i> Lihat Fitur</a>
                @endauth
            </div>

            <div class="hero-meta">
                <strong>Kontak Support:</strong> {{ $supportContact }}
            </div>
        </article>

        <aside class="hero-side">
            <div class="placeholder-box">
                <strong>Placeholder visual hero</strong><br>
                Ganti area ini dengan ilustrasi/logo institusi/preview dashboard.
                <br><br>
                Rekomendasi isi:
                <ul>
                    <li>- Path gambar: <code>public/images/welcome-hero.png</code></li>
                    <li>- Ukuran disarankan: 1200x800 px</li>
                    <li>- Format: PNG/WebP</li>
                </ul>
            </div>

            <div class="kpis">
                <div class="kpi">
                    <div class="value">{{ number_format($totalProdi, 0, ',', '.') }}</div>
                    <div class="label">Program Studi</div>
                </div>
                <div class="kpi">
                    <div class="value">{{ number_format($totalMk, 0, ',', '.') }}</div>
                    <div class="label">Mata Kuliah</div>
                </div>
                <div class="kpi">
                    <div class="value">{{ $avgCapaian }}</div>
                    <div class="label">Rata-rata Capaian</div>
                </div>
            </div>
        </aside>
    </section>

    <section id="fitur" class="feature-grid">
        <div class="feature-card">
            <i class="bi bi-diagram-3"></i>
            <h3>Pemetaan CPL-CPMK</h3>
            <p>Mapping capaian pembelajaran lintas kurikulum dan mata kuliah secara konsisten dan terdokumentasi.</p>
        </div>
        <div class="feature-card">
            <i class="bi bi-bar-chart-line"></i>
            <h3>Analitik Evaluasi</h3>
            <p>Pantau indikator ketercapaian dan evaluasi hasil asesmen dengan tampilan yang mudah dipahami.</p>
        </div>
        <div class="feature-card">
            <i class="bi bi-shield-check"></i>
            <h3>Akses Berbasis Peran</h3>
            <p>Kontrol akses per pengguna, role, dan permission agar pengelolaan data lebih aman dan terstruktur.</p>
        </div>
    </section>

    <section class="insight-section">
        <div class="insight-head">
            <div>
                <h3 class="insight-title">Ruang Informasi & Pengembangan</h3>
                <p class="insight-subtitle">Gunakan bagian ini untuk mengisi update penting agar halaman welcome lebih informatif.</p>
            </div>
            <span class="insight-badge">Placeholder Siap Pakai</span>
        </div>

        <div class="insight-grid">
            <article class="insight-card">
                <i class="bi bi-megaphone"></i>
                <h4>Pengumuman Utama</h4>
                <p>[Isi pengumuman rilis versi terbaru, maintenance, atau deadline evaluasi semester berjalan.]</p>
                <ul>
                    <li>Tanggal: [DD-MM-YYYY]</li>
                    <li>Status: [Aktif/Upcoming/Selesai]</li>
                    <li>Penanggung jawab: [Nama Unit]</li>
                </ul>
            </article>

            <article class="insight-card">
                <i class="bi bi-calendar-event"></i>
                <h4>Agenda Akademik</h4>
                <p>[Isi daftar agenda penting terkait asesmen, monitoring CPL, dan validasi data kurikulum.]</p>
                <ul>
                    <li>Agenda 1: [Nama agenda + tanggal]</li>
                    <li>Agenda 2: [Nama agenda + tanggal]</li>
                    <li>Agenda 3: [Nama agenda + tanggal]</li>
                </ul>
            </article>

            <article class="insight-card">
                <i class="bi bi-journal-code"></i>
                <h4>Dokumen & Panduan</h4>
                <p>[Tambahkan link SOP, video tutorial, atau panduan singkat penggunaan fitur inti obemetrics.]</p>
                <ul>
                    <li>Panduan Admin: [URL/Path dokumen]</li>
                    <li>Panduan Prodi: [URL/Path dokumen]</li>
                    <li>Panduan Dosen: [URL/Path dokumen]</li>
                </ul>
            </article>
        </div>
    </section>

    <footer>
        © {{ date('Y') }} {{ $brandName }}. All rights reserved.<br>
                                Lembaga Penjaminan Mutu dan Pengembangan Pembelajaran<br>Universitas Siliwangi
                                <br>
                                <a href="https://lpmpp.unsil.ac.id" target="_blank">
                                    <i class="bi bi-globe me-1"></i>
                                    lpmpp.unsil.ac.id
                                </a>
    </footer>
</div>
</body>
</html>
