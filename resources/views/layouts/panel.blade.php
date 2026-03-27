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

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
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
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light px-3">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ auth()->check() ? route('home') : url('/') }}" class="nav-link">Home</a>
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

    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            LPMPP Universitas Siliwangi
                                <a href="https://lpmpp.unsil.ac.id" target="_blank">
                                    <i class="bi bi-globe me-1"></i>
                                    lpmpp.unsil.ac.id
                                </a>
        </div>
        <strong>
            &copy; {{ now()->year }} {{ config('app.name', 'OBEmetrics') }}.
        </strong>
        All rights reserved.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>
