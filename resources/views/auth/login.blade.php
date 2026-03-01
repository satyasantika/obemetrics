@extends('layouts.app')
@section('hideNavbar', true)

@section('content')
@php
    $appName = config('app.name', 'obemetrics');
    $loginTitle = 'Selamat datang di pusat kendali OBE kampus Anda.';
    $loginSubtitle = 'Masuk untuk mengelola kurikulum, pemetaan CPL-CPMK, evaluasi asesmen, dan monitoring ketercapaian pembelajaran secara terintegrasi.';
    $helpText = 'comming soon';
@endphp

<style>
    .auth-modern-wrap {
        min-height: calc(100vh - 150px);
        display: flex;
        align-items: center;
    }

    .auth-modern-card {
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
    }

    .auth-modern-left {
        padding: 2.2rem;
        background: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, .20), transparent 35%),
                    radial-gradient(circle at 80% 80%, rgba(20, 184, 166, .20), transparent 35%),
                    #f8fafc;
        border-right: 1px solid #e5e7eb;
    }

    .auth-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .82rem;
        color: #0c4a6e;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        padding: .35rem .65rem;
        border-radius: 999px;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .auth-title {
        font-weight: 800;
        font-size: clamp(1.35rem, 2vw, 1.9rem);
        line-height: 1.3;
        color: #0f172a;
        margin-bottom: .6rem;
    }

    .auth-subtitle {
        color: #64748b;
        line-height: 1.7;
        margin-bottom: 1.3rem;
    }

    .auth-placeholder {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        background: #fff;
        color: #475569;
        padding: 1rem;
        font-size: .92rem;
        line-height: 1.65;
    }

    .auth-modern-right {
        padding: 2.2rem;
    }

    .auth-form-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: .35rem;
    }

    .auth-form-subtitle {
        color: #64748b;
        font-size: .93rem;
        margin-bottom: 1.2rem;
    }

    .auth-modern-right .form-label {
        font-weight: 700;
        color: #334155;
    }

    .auth-modern-right .form-control {
        border-radius: 12px;
        min-height: 46px;
    }

    .btn-login-modern {
        border: none;
        border-radius: 12px;
        min-height: 46px;
        font-weight: 700;
        background: linear-gradient(135deg, #3b82f6, #14b8a6);
        box-shadow: 0 10px 24px rgba(20, 184, 166, .25);
    }

    .auth-help {
        margin-top: .9rem;
        color: #64748b;
        font-size: .85rem;
    }

    .auth-copyright {
        margin-top: .85rem;
        text-align: center;
        color: #64748b;
        font-size: .8rem;
    }

    @media (max-width: 991px) {
        .auth-modern-left {
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }
    }
</style>

<div class="container auth-modern-wrap">
    <div class="row justify-content-center w-100">
        <div class="col-xl-10 col-lg-11">
            <div class="auth-modern-card">
                <div class="row g-0">
                    <div class="col-lg-6 auth-modern-left">
                        <span class="auth-badge"><i class="bi bi-shield-check"></i> Secure Access</span>
                        <div class="auth-title">{{ $appName }}</div>
                        <p class="auth-subtitle">
                            {{ $loginTitle }}
                            <br>
                            {{ $loginSubtitle }}
                        </p>

                        <div class="auth-placeholder">
                            <strong>Ringkasan manfaat platform</strong><br>
                            Sistem ini dirancang agar proses evaluasi OBE lebih cepat, terdokumentasi, dan konsisten lintas semester.
                            <br><br>
                            Rekomendasi isi:
                            <ul class="mb-0 mt-2">
                                <li>- Logo institusi: comming soon</li>
                                <li>- URL SOP login/reset akun: comming soon</li>
                                <li>- Link pusat bantuan internal: comming soon</li>
                            </ul>
                        </div>

                        <div class="auth-help">
                            <i class="bi bi-life-preserver me-1"></i>
                            Bantuan login: {{ $helpText }}
                        </div>
                    </div>

                    <div class="col-lg-6 auth-modern-right">
                        <div class="auth-form-title">Masuk ke akun Anda</div>
                        <div class="auth-form-subtitle">Gunakan username dan password yang telah terdaftar.</div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="username" class="form-label">{{ __('Username') }}</label>
                                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>
                                @error('username')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                @error('password')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-login-modern">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> {{ __('Login') }}
                                </button>
                            </div>

                            <div class="auth-copyright">
                                © {{ date('Y') }} {{ $appName }}. All rights reserved.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
