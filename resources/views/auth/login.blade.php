@extends('layouts.app')
@section('hideNavbar', true)

@section('content')
@php
    $appName = 'SILOGY';
@endphp

<style>
    /* ── import font ── */
    @import url('https://fonts.bunny.net/css?family=Nunito:400,600,700,800,900&family=Nunito+Sans:400,600,700');

    /* ── tokens ── */
    :root {
        --ink:   #060d1a; --dark2: #0c1628;
        --g300:  #86efac; --g400:  #4ade80;
        --g500:  #22c55e; --g600:  #16a34a;
        --g700:  #15803d; --g800:  #166534; --g900: #14532d;
        --bd-d:  rgba(255,255,255,.09);
        --bd-d2: rgba(255,255,255,.14);
        --tw:    #f0f6ff; --tws: #8ba3c0; --twss: #546880;
        --text:  #182435; --muted: #6b7a8d;
        --border: #e8edf3; --light: #f5f7fb;
    }

    /* ── layout ── */
    html, body { height: 100%; }
    body { font-family: 'Nunito Sans', 'Nunito', sans-serif; background: var(--light); }

    .lp-wrap {
        min-height: 100vh;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    /* ── LEFT PANEL ── */
    .lp-left {
        position: relative; overflow: hidden;
        background: linear-gradient(162deg, var(--ink) 0%, var(--dark2) 50%, #091a10 100%);
        display: flex; flex-direction: column;
        padding: 3rem 3.5rem;
    }
    .lp-left::before {
        content: ''; position: absolute; inset: 0; pointer-events: none;
        background:
            radial-gradient(ellipse at 80% 100%, rgba(22,163,74,.22) 0%, transparent 55%),
            radial-gradient(ellipse at 20% 0%,   rgba(74,222,128,.07) 0%, transparent 45%);
    }
    .lp-left::after {
        content: ''; position: absolute; inset: 0; pointer-events: none;
        background-image:
            linear-gradient(rgba(74,222,128,.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(74,222,128,.035) 1px, transparent 1px);
        background-size: 52px 52px;
        mask-image: radial-gradient(ellipse at 40% 60%, black 20%, transparent 70%);
        -webkit-mask-image: radial-gradient(ellipse at 40% 60%, black 20%, transparent 70%);
    }
    .lp-left-inner {
        position: relative; z-index: 1;
        display: flex; flex-direction: column;
        height: 100%; justify-content: space-between;
    }

    /* brand area */
    .lp-logo {
        display: flex; align-items: center; gap: .75rem;
        color: var(--tw); font-family: 'Nunito', sans-serif;
        font-weight: 900; font-size: 1.05rem; letter-spacing: -.02em;
        margin-bottom: 3.5rem;
    }
    .lp-logo-mark {
        width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
        background: linear-gradient(145deg, var(--g700) 0%, var(--g500) 100%);
        display: grid; place-items: center; color: #fff; font-size: 1.1rem;
        box-shadow: 0 0 0 1px rgba(74,222,128,.3), 0 6px 20px rgba(22,163,74,.4);
    }
    .lp-logo-sub {
        display: block; font-size: .6rem; font-weight: 600;
        color: var(--tws); line-height: 1.2; margin-top: 2px;
    }

    /* hero copy */
    .lp-eyebrow {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .7rem; font-family: 'Nunito', sans-serif; font-weight: 900;
        text-transform: uppercase; letter-spacing: .1em;
        color: var(--g400);
        background: rgba(22,163,74,.12); border: 1px solid rgba(74,222,128,.25);
        border-radius: 999px; padding: .26rem .85rem;
        margin-bottom: 1.5rem;
    }
    .lp-h1 {
        font-family: 'Nunito', sans-serif;
        font-size: clamp(2.4rem, 3.8vw, 3.6rem); font-weight: 900;
        letter-spacing: -.045em; line-height: 1; color: var(--tw);
        margin-bottom: .6rem;
    }
    .lp-tagline {
        font-family: 'Nunito', sans-serif;
        font-size: clamp(.85rem, 1.1vw, 1rem); font-weight: 700;
        background: linear-gradient(100deg, var(--g300), var(--g400));
        -webkit-background-clip: text; background-clip: text; color: transparent;
        margin-bottom: 1.4rem;
    }
    .lp-desc {
        color: var(--tws); font-size: .9rem; line-height: 1.82;
        max-width: 360px; margin-bottom: 2.2rem;
    }

    /* benefit list */
    .lp-benefits { display: flex; flex-direction: column; gap: .65rem; margin-bottom: auto; }
    .lp-bi {
        display: flex; align-items: flex-start; gap: .75rem;
        background: rgba(255,255,255,.04); border: 1px solid var(--bd-d);
        border-radius: 12px; padding: .85rem 1rem;
    }
    .lp-bi-icon {
        width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
        background: rgba(22,163,74,.15); border: 1px solid rgba(74,222,128,.2);
        display: grid; place-items: center; color: var(--g400); font-size: .85rem;
    }
    .lp-bi-text { font-size: .84rem; color: var(--tw); line-height: 1.55; }
    .lp-bi-text span { color: var(--tws); display: block; font-size: .78rem; margin-top: .1rem; }

    /* footer copy */
    .lp-left-foot {
        margin-top: 2.5rem; padding-top: 1.5rem;
        border-top: 1px solid var(--bd-d);
        color: var(--twss); font-size: .77rem; line-height: 1.65;
    }
    .lp-left-foot a { color: var(--g500); transition: .14s; }
    .lp-left-foot a:hover { color: var(--g400); }

    /* ── RIGHT PANEL ── */
    .lp-right {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 3rem 2.5rem; background: #fff;
    }
    .lp-form-box { width: 100%; max-width: 380px; }

    .lp-form-eyebrow {
        font-size: .7rem; font-family: 'Nunito', sans-serif; font-weight: 900;
        text-transform: uppercase; letter-spacing: .12em;
        color: var(--g700); margin-bottom: 1.1rem;
        display: flex; align-items: center; gap: .5rem;
    }
    .lp-form-eyebrow::after {
        content: ''; flex: 1; height: 1px;
        background: linear-gradient(90deg, var(--border), transparent);
    }
    .lp-form-h {
        font-family: 'Nunito', sans-serif;
        font-size: 1.65rem; font-weight: 900; letter-spacing: -.03em;
        color: var(--text); margin-bottom: .35rem;
    }
    .lp-form-sub {
        color: var(--muted); font-size: .87rem; line-height: 1.6;
        margin-bottom: 1.8rem;
    }

    /* form controls */
    .lp-field { margin-bottom: 1.1rem; }
    .lp-label {
        display: block; font-family: 'Nunito', sans-serif;
        font-size: .8rem; font-weight: 800; color: var(--text);
        letter-spacing: .02em; margin-bottom: .45rem;
    }
    .lp-input {
        width: 100%; height: 46px;
        background: var(--light); border: 1.5px solid var(--border);
        border-radius: 11px; padding: 0 1rem;
        font-family: 'Nunito Sans', sans-serif; font-size: .93rem;
        color: var(--text); outline: none;
        transition: border-color .16s, box-shadow .16s, background .16s;
    }
    .lp-input:focus {
        background: #fff; border-color: var(--g600);
        box-shadow: 0 0 0 3px rgba(22,163,74,.12);
    }
    .lp-input.is-invalid { border-color: #ef4444; background: #fff7f7; }
    .lp-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
    .lp-invalid { color: #dc2626; font-size: .8rem; margin-top: .35rem; font-weight: 700; }

    /* password toggle */
    .lp-pw-wrap { position: relative; }
    .lp-pw-toggle {
        position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer;
        color: var(--muted); font-size: 1rem; padding: 0; line-height: 1;
        transition: color .14s;
    }
    .lp-pw-toggle:hover { color: var(--g700); }

    /* submit */
    .lp-btn {
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        width: 100%; height: 48px; border: none; border-radius: 12px;
        font-family: 'Nunito', sans-serif; font-weight: 900; font-size: .97rem;
        background: linear-gradient(135deg, var(--g700) 0%, var(--g500) 100%);
        color: #fff; cursor: pointer; margin-top: 1.5rem;
        box-shadow: 0 8px 28px rgba(22,163,74,.4), inset 0 1px 0 rgba(255,255,255,.18);
        transition: all .18s ease;
    }
    .lp-btn:hover { filter: brightness(1.1); transform: translateY(-2px); box-shadow: 0 14px 36px rgba(22,163,74,.5); }
    .lp-btn:active { transform: translateY(0); }

    /* footer */
    .lp-form-foot {
        margin-top: 1.6rem; text-align: center;
        color: var(--muted); font-size: .78rem; line-height: 1.7;
    }
    .lp-form-foot a { color: var(--g600); font-weight: 700; transition: .14s; }
    .lp-form-foot a:hover { color: var(--g500); }
    .lp-back {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .8rem; font-weight: 700; color: var(--muted);
        margin-bottom: 2rem; transition: color .14s;
    }
    .lp-back:hover { color: var(--g700); }

    /* ── RESPONSIVE ── */
    @media (max-width: 860px) {
        .lp-wrap { grid-template-columns: 1fr; }
        .lp-left { padding: 2.2rem; min-height: auto; }
        .lp-h1 { font-size: 2.2rem; }
        .lp-desc { max-width: 100%; }
        .lp-left-foot { margin-top: 1.5rem; }
        .lp-right { padding: 2.2rem; }
    }
    @media (max-width: 480px) {
        .lp-left { padding: 1.75rem; }
        .lp-right { padding: 1.75rem 1.25rem; }
    }
</style>

<div class="lp-wrap">

    {{-- ═══ LEFT: Branding ═══ --}}
    <div class="lp-left">
        <div class="lp-left-inner">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="lp-logo" style="text-decoration:none;">
                <span class="lp-logo-mark"><i class="bi bi-mortarboard-fill"></i></span>
                <span>
                    SILOGY
                    <span class="lp-logo-sub">Siliwangi Learning Outcomes &amp; Quality Analytics</span>
                </span>
            </a>

            {{-- Hero copy --}}
            <div>
                <span class="lp-eyebrow"><i class="bi bi-patch-check-fill"></i> Analytics-Driven OBE Platform</span>
                <h1 class="lp-h1">SILOGY</h1>
                <p class="lp-tagline">From learning data to academic quality.</p>
                <p class="lp-desc">
                    Paradigma mutu pembelajaran berbasis <em>Outcome-Based Education</em>
                    yang menjadikan data capaian sebagai dasar setiap keputusan akademik.
                </p>

                {{-- Benefits --}}
                <div class="lp-benefits">
                    <div class="lp-bi">
                        <span class="lp-bi-icon"><i class="bi bi-rulers"></i></span>
                        <div class="lp-bi-text">
                            Measurement
                            <span>Pengukuran terstruktur ketercapaian CPMK, Sub-CPMK, dan CPL</span>
                        </div>
                    </div>
                    <div class="lp-bi">
                        <span class="lp-bi-icon"><i class="bi bi-graph-up-arrow"></i></span>
                        <div class="lp-bi-text">
                            Analytics
                            <span>Analisis pola, tren, dan kesenjangan mutu capaian pembelajaran</span>
                        </div>
                    </div>
                    <div class="lp-bi">
                        <span class="lp-bi-icon"><i class="bi bi-arrow-clockwise"></i></span>
                        <div class="lp-bi-text">
                            Continuous Improvement
                            <span>Perbaikan pembelajaran, asesmen, dan kurikulum berbasis bukti</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="lp-left-foot">
                &copy; {{ date('Y') }} SILOGY &mdash; Universitas Siliwangi<br>
                Lembaga Penjaminan Mutu dan Pengembangan Pembelajaran &middot;
                <a href="https://lpmpp.unsil.ac.id" target="_blank">lpmpp.unsil.ac.id</a>
            </div>
        </div>
    </div>

    {{-- ═══ RIGHT: Form ═══ --}}
    <div class="lp-right">
        <div class="lp-form-box">

            @if (url()->previous() !== url()->current())
                <a href="{{ url()->previous() }}" class="lp-back" style="text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            @else
                <a href="{{ url('/') }}" class="lp-back" style="text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Beranda
                </a>
            @endif

            <p class="lp-form-eyebrow"><i class="bi bi-lock-fill"></i> Akses Sistem</p>
            <h2 class="lp-form-h">Selamat datang kembali</h2>
            <p class="lp-form-sub">
                Masuk dengan akun yang telah terdaftar untuk mengakses
                dashboard pengelolaan mutu akademik SILOGY.
            </p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Username --}}
                <div class="lp-field">
                    <label for="username" class="lp-label">Username</label>
                    <input
                        id="username" name="username" type="text"
                        class="lp-input @error('username') is-invalid @enderror"
                        value="{{ old('username') }}"
                        required autocomplete="username" autofocus
                        placeholder="Masukkan username Anda"
                    >
                    @error('username')
                        <p class="lp-invalid"><i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="lp-field">
                    <label for="password" class="lp-label">Password</label>
                    <div class="lp-pw-wrap">
                        <input
                            id="password" name="password" type="password"
                            class="lp-input @error('password') is-invalid @enderror"
                            required autocomplete="current-password"
                            placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                            style="padding-right:2.8rem;"
                        >
                        <button type="button" class="lp-pw-toggle" onclick="togglePw()" aria-label="Tampilkan password">
                            <i class="bi bi-eye" id="pwIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="lp-invalid"><i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="lp-btn">
                    <i class="bi bi-door-open"></i> Masuk ke SILOGY
                </button>
            </form>

            <div class="lp-form-foot">
                Kendala akun? Hubungi
                <a href="https://lpmpp.unsil.ac.id" target="_blank">tim LPMPP Unsil</a>
            </div>
        </div>
    </div>

</div>

<script>
function togglePw() {
    const pw = document.getElementById('password');
    const ic = document.getElementById('pwIcon');
    if (pw.type === 'password') {
        pw.type = 'text';
        ic.className = 'bi bi-eye-slash';
    } else {
        pw.type = 'password';
        ic.className = 'bi bi-eye';
    }
}
</script>
@endsection
