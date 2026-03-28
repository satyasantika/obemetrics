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
    @include('layouts.partials.panel.styles')
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
@include('layouts.partials.panel.scripts')
@stack('scripts')
</body>
</html>
