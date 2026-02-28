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
    @if (request()->routeIs('setting.import.*'))
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
                        <a class="dropdown-item" href="{{ route('mypassword.change') }}">Ubah Password</a>
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

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ auth()->check() ? route('home') : url('/') }}" class="brand-link">
            <img src="{{ asset('images/logo-unsil.png') }}"
                 alt="Logo UNSIL"
                 class="brand-image img-circle elevation-2"
                 style="opacity: .95; width: 28px; height: 28px; object-fit: cover;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
            <span class="brand-image img-circle elevation-2 d-none align-items-center justify-content-center"
                  style="opacity:.95; width:28px; height:28px;">
                <i class="fas fa-university" style="font-size:.9rem;"></i>
            </span>
            <span class="brand-text font-weight-light">{{ config('app.name', 'Laravel') }}</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                @php
                    $isProdiSidebarMode = request()->routeIs(
                        'ruang.prodi',
                        'mks.users.*',
                        'kurikulums.profils.*',
                        'kurikulums.cpls.*',
                        'kurikulums.bks.*',
                        'kurikulums.mks.*',
                        'kurikulums.joinprofilcpls.*',
                        'kurikulums.joincplbks.*',
                        'kurikulums.joincplmks.*',
                        'kurikulums.rencana-asesmen',
                        'kurikulums.analisis-asesmen',
                        'kurikulums.spyderweb-cpl',
                        'kurikulums.laporan-mahasiswa',
                        'setting.import.kurikulum-master'
                    );

                    $isMkSidebarMode = request()->routeIs(
                        'ruang.dosen',
                        'mks.cpmks.*',
                        'mks.subcpmks.*',
                        'mks.joincplcpmks.*',
                        'mks.penugasans.*',
                        'mks.joinsubcpmkpenugasans.*',
                        'mks.nilais.*',
                        'setting.import.nilais*',
                        'mks.workclouds.*',
                        'mks.achievements.*',
                        'mks.ketercapaians.*',
                        'mks.spyderweb',
                        'setting.import.mk-master*'
                    );

                    $selectedKurikulum = null;
                    if ($isProdiSidebarMode && auth()->check()) {
                        $routeKurikulum = request()->route('kurikulum');
                        $routeMk = request()->route('mk');
                        $routeMks = request()->route('mks');

                        if ($routeKurikulum instanceof \App\Models\Kurikulum) {
                            $selectedKurikulum = $routeKurikulum;
                        } elseif (!empty($routeKurikulum)) {
                            $selectedKurikulum = \App\Models\Kurikulum::find((int) $routeKurikulum);
                        } elseif ($routeMk instanceof \App\Models\Mk) {
                            $selectedKurikulum = \App\Models\Kurikulum::find((int) $routeMk->kurikulum_id);
                        } elseif (!empty($routeMk)) {
                            $mkModel = \App\Models\Mk::find((int) $routeMk);
                            $selectedKurikulum = $mkModel ? \App\Models\Kurikulum::find((int) $mkModel->kurikulum_id) : null;
                        } elseif ($routeMks instanceof \App\Models\Mk) {
                            $selectedKurikulum = \App\Models\Kurikulum::find((int) $routeMks->kurikulum_id);
                        } elseif (!empty($routeMks)) {
                            $mkModel = \App\Models\Mk::find((int) $routeMks);
                            $selectedKurikulum = $mkModel ? \App\Models\Kurikulum::find((int) $mkModel->kurikulum_id) : null;
                        } else {
                            $selectedKurikulum = \App\Models\Kurikulum::find((int) session('selected_kurikulum_id'));
                        }

                        if ($selectedKurikulum) {
                            $hasPimpinanAccess = \App\Models\JoinProdiUser::query()
                                ->where('user_id', auth()->id())
                                ->where('prodi_id', $selectedKurikulum->prodi_id)
                                ->where('status_pimpinan', true)
                                ->exists();

                            if (!$hasPimpinanAccess) {
                                $selectedKurikulum = null;
                            }
                        }
                    }

                    $isImportMaster = request()->routeIs('setting.import.kurikulum-master') && request()->query('target') === 'kurikulum_bundle';
                    $isImportJoinMaster = request()->routeIs('setting.import.kurikulum-master') && request()->query('target') === 'join_kurikulum_bundle';

                    $profilExists = false;
                    $cplExists = false;
                    $bkExists = false;
                    $mkExists = false;
                    $joinProfilCplExists = false;
                    $joinCplBkExists = false;
                    $joinCplMkExists = false;

                    $dataComplete = false;
                    $joinProfilCplBKExists = false;
                    $mustImportJoinMaster = false;
                    $reportsReady = false;

                    if ($selectedKurikulum) {
                        session(['selected_kurikulum_id' => $selectedKurikulum->id]);

                        $profilExists = $selectedKurikulum->profils()->exists();
                        $cplExists = $selectedKurikulum->cpls()->exists();
                        $bkExists = $selectedKurikulum->bks()->exists();
                        $mkExists = $selectedKurikulum->mks()->exists();

                        $joinProfilCplExists = $selectedKurikulum->joinProfilCpls()->exists();
                        $joinCplBkExists = $selectedKurikulum->joinCplBks()->exists();
                        $joinCplMkExists = $selectedKurikulum->joinCplMks()->exists();

                        $dataComplete = $profilExists && $cplExists && $bkExists && $mkExists;
                        $joinProfilCplBKExists = $joinProfilCplExists && $joinCplBkExists;
                        $mustImportJoinMaster = $dataComplete && !$joinProfilCplBKExists;
                        $reportsReady = $joinCplMkExists;
                    }

                    $selectedMk = null;
                    $dosenMkMenus = collect();
                    $isMkImportMaster = false;

                    $mkDataComplete = false;
                    $mkSiapMenilai = false;
                    $mkPenilaianComplete = false;
                    $mkHasKontrakAccess = false;

                    if ($isMkSidebarMode && auth()->check()) {
                        $routeMk = request()->route('mk');
                        if ($routeMk instanceof \App\Models\Mk) {
                            $selectedMk = $routeMk;
                        } elseif (!empty($routeMk)) {
                            $selectedMk = \App\Models\Mk::find((int) $routeMk);
                        }

                        $dosenMkMenus = auth()->user()->joinMkUsers()
                            ->with('mk')
                            ->get()
                            ->pluck('mk')
                            ->filter()
                            ->unique('id')
                            ->sortBy('kode')
                            ->values();

                        $isMkImportMaster = request()->routeIs('setting.import.mk-master*') && request()->query('target') === 'mk_bundle';

                        if ($selectedMk) {
                            $mkHasKontrakAccess = \App\Models\KontrakMk::query()
                                ->where('mk_id', $selectedMk->id)
                                ->where('user_id', auth()->id())
                                ->exists();

                            $cpmkExists = $selectedMk->cpmks()->exists();
                            $joinCplCpmkExists = $selectedMk->joinCplCpmks()->exists();
                            $subcpmkExists = $selectedMk->joinCplCpmks()->whereHas('subcpmks')->exists();

                            $penugasanExists = $selectedMk->penugasans()->exists();
                            $joinSubcpmkPenugasanExists = $selectedMk->joinsubcpmkpenugasans()->exists();
                            $nilaiExists = $selectedMk->nilais()->exists();

                            $mkDataComplete = $cpmkExists && $joinCplCpmkExists && $subcpmkExists;
                            $mkSiapMenilai = $mkDataComplete && $penugasanExists && $joinSubcpmkPenugasanExists;
                            $mkPenilaianComplete = $mkDataComplete && $penugasanExists && $joinSubcpmkPenugasanExists && $nilaiExists;
                        }
                    }
                @endphp

                @if ($isProdiSidebarMode && auth()->check())
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('ruang.prodi') }}" class="nav-link {{ request()->routeIs('ruang.prodi') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-check-circle"></i>
                                <p>Pilih Kurikulum</p>
                            </a>
                        </li>

                        @if($selectedKurikulum)
                            <li class="nav-header">{{ $selectedKurikulum->kode }} - {{ $selectedKurikulum->nama }}</li>

                            <li class="nav-header">DATA MASTER</li>
                            <li class="nav-item">
                                <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $selectedKurikulum->id, 'target' => 'kurikulum_bundle', 'return_url' => url()->current()]) }}"
                                   class="nav-link {{ $isImportMaster ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-upload"></i>
                                    <p>Upload Data Master</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ $dataComplete ? route('kurikulums.profils.index', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.profils.*') ? 'active' : '' }} {{ $dataComplete ? '' : 'disabled' }}"
                                   @if(!$dataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-user-graduate"></i>
                                    <p>Profil Lulusan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $dataComplete ? route('kurikulums.cpls.index', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.cpls.*') ? 'active' : '' }} {{ $dataComplete ? '' : 'disabled' }}"
                                   @if(!$dataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-bullseye"></i>
                                    <p>CPL</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $dataComplete ? route('kurikulums.bks.index', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.bks.*') ? 'active' : '' }} {{ $dataComplete ? '' : 'disabled' }}"
                                   @if(!$dataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-book"></i>
                                    <p>BK</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $dataComplete ? route('kurikulums.mks.index', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.mks.*') ? 'active' : '' }} {{ $dataComplete ? '' : 'disabled' }}"
                                   @if(!$dataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-bookmark"></i>
                                    <p>MK</p>
                                </a>
                            </li>

                            <li class="nav-header">INTERAKSI</li>
                            <li class="nav-item">
                                <a href="{{ $dataComplete ? route('setting.import.kurikulum-master', ['kurikulum' => $selectedKurikulum->id, 'target' => 'join_kurikulum_bundle', 'return_url' => url()->current()]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ $isImportJoinMaster ? 'active' : '' }} {{ $dataComplete ? '' : 'disabled' }}"
                                   @if(!$dataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-file-upload"></i>
                                    <p>Upload Data Interaksi</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ $joinProfilCplBKExists ? route('kurikulums.joinprofilcpls.index', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.joinprofilcpls.*') ? 'active' : '' }} {{ $joinProfilCplBKExists ? '' : 'disabled' }}"
                                   @if(!$joinProfilCplBKExists) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-link"></i>
                                    <p>Interaksi Profil >< CPL</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $joinProfilCplBKExists ? route('kurikulums.joincplbks.index', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.joincplbks.*') ? 'active' : '' }} {{ $joinProfilCplBKExists ? '' : 'disabled' }}"
                                   @if(!$joinProfilCplBKExists) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-link"></i>
                                    <p>Interaksi CPL >< BK</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $joinProfilCplBKExists ? route('kurikulums.joincplmks.index', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.joincplmks.*') ? 'active' : '' }} {{ $joinProfilCplBKExists ? '' : 'disabled' }}"
                                   @if(!$joinProfilCplBKExists) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-sliders-h"></i>
                                    <p>Bobot CPL tiap MK</p>
                                </a>
                            </li>

                            <li class="nav-header">LAPORAN</li>
                            <li class="nav-item">
                                <a href="{{ $reportsReady ? route('kurikulums.rencana-asesmen', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.rencana-asesmen') ? 'active' : '' }} {{ $reportsReady ? '' : 'disabled' }}"
                                   @if(!$reportsReady) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-project-diagram"></i>
                                    <p>Rencana Asesmen CPL</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $reportsReady ? route('kurikulums.analisis-asesmen', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.analisis-asesmen') ? 'active' : '' }} {{ $reportsReady ? '' : 'disabled' }}"
                                   @if(!$reportsReady) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <p>Analisis Asesmen CPL</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $reportsReady ? route('kurikulums.spyderweb-cpl', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.spyderweb-cpl') ? 'active' : '' }} {{ $reportsReady ? '' : 'disabled' }}"
                                   @if(!$reportsReady) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-bullseye"></i>
                                    <p>Grafik Spyderweb CPL</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $reportsReady ? route('kurikulums.laporan-mahasiswa', [$selectedKurikulum->id]) : 'javascript:void(0)' }}"
                                   class="nav-link {{ request()->routeIs('kurikulums.laporan-mahasiswa') ? 'active' : '' }} {{ $reportsReady ? '' : 'disabled' }}"
                                   @if(!$reportsReady) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-address-card"></i>
                                    <p>Resume Mahasiswa</p>
                                </a>
                            </li>

                            @if(!$dataComplete)
                                <li class="nav-header text-warning">Lengkapi data master terlebih dahulu (Profil, CPL, BK, MK).</li>
                            @elseif($mustImportJoinMaster)
                                <li class="nav-header text-warning">Upload data interaksi terlebih dahulu.</li>
                            @elseif(!$joinCplMkExists)
                                <li class="nav-header text-warning">Lengkapi pembobotan CPL tiap MK terlebih dahulu.</li>
                            @endif
                        @else
                            <li class="nav-header text-warning">Pilih kurikulum dulu di halaman Ruang Prodi.</li>
                        @endif
                    </ul>
                @elseif ($isMkSidebarMode && auth()->check())
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('ruang.dosen') }}" class="nav-link {{ request()->routeIs('ruang.dosen') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chalkboard-teacher"></i>
                                <p>Pilih Mata Kuliah</p>
                            </a>
                        </li>

                        <li class="nav-header">MENU MK</li>

                        @if($selectedMk)
                            <li class="nav-header">{{ $selectedMk->kode }} - {{ \Illuminate\Support\Str::limit($selectedMk->nama, 26) }}</li>

                            <li class="nav-header">DATA</li>
                            <li class="nav-item">
                                <a href="{{ route('setting.import.mk-master', ['mk' => $selectedMk->id, 'target' => 'mk_bundle', 'return_url' => url()->current()]) }}" class="nav-link {{ $isMkImportMaster ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-upload"></i>
                                    <p>Import Data Master</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $mkDataComplete ? route('mks.cpmks.index', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.cpmks.*') ? 'active' : '' }} {{ $mkDataComplete ? '' : 'disabled' }}" @if(!$mkDataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-sliders-h"></i>
                                    <p>CPMK</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $mkDataComplete ? route('mks.joincplcpmks.index', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.joincplcpmks.*') ? 'active' : '' }} {{ $mkDataComplete ? '' : 'disabled' }}" @if(!$mkDataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-link"></i>
                                    <p>Set CPL >< CPMK</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $mkDataComplete ? route('mks.subcpmks.index', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.subcpmks.*') ? 'active' : '' }} {{ $mkDataComplete ? '' : 'disabled' }}" @if(!$mkDataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-list"></i>
                                    <p>SubCPMK</p>
                                </a>
                            </li>

                            <li class="nav-header">TUGAS & PENILAIAN</li>
                            <li class="nav-item">
                                <a href="{{ $mkDataComplete ? route('mks.penugasans.index', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.penugasans.*') ? 'active' : '' }} {{ $mkDataComplete ? '' : 'disabled' }}" @if(!$mkDataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>Tagihan Tugas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $mkDataComplete ? route('mks.joinsubcpmkpenugasans.index', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.joinsubcpmkpenugasans.*') ? 'active' : '' }} {{ $mkDataComplete ? '' : 'disabled' }}" @if(!$mkDataComplete) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-project-diagram"></i>
                                    <p>Set SubCPMK >< Tugas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ ($mkSiapMenilai && $mkHasKontrakAccess) ? route('mks.nilais.index', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.nilais.*', 'setting.import.nilais*') ? 'active' : '' }} {{ ($mkSiapMenilai && $mkHasKontrakAccess) ? '' : 'disabled' }}" @if(!($mkSiapMenilai && $mkHasKontrakAccess)) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-clipboard-check"></i>
                                    <p>Pengisian Nilai</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ ($mkPenilaianComplete && $mkHasKontrakAccess) ? route('mks.workclouds.index', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.workclouds.*') ? 'active' : '' }} {{ ($mkPenilaianComplete && $mkHasKontrakAccess) ? '' : 'disabled' }}" @if(!($mkPenilaianComplete && $mkHasKontrakAccess)) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-cloud-upload-alt"></i>
                                    <p>Portofolio Penilaian</p>
                                </a>
                            </li>

                            <li class="nav-header">LAPORAN</li>
                            <li class="nav-item">
                                <a href="{{ ($mkPenilaianComplete && $mkHasKontrakAccess) ? route('mks.achievements.index', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.achievements.*') ? 'active' : '' }} {{ ($mkPenilaianComplete && $mkHasKontrakAccess) ? '' : 'disabled' }}" @if(!($mkPenilaianComplete && $mkHasKontrakAccess)) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <p>Evaluasi CPL v1</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ ($mkPenilaianComplete && $mkHasKontrakAccess) ? route('mks.ketercapaians.index', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.ketercapaians.*') ? 'active' : '' }} {{ ($mkPenilaianComplete && $mkHasKontrakAccess) ? '' : 'disabled' }}" @if(!($mkPenilaianComplete && $mkHasKontrakAccess)) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-chart-area"></i>
                                    <p>Evaluasi CPL v2</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ ($mkPenilaianComplete && $mkHasKontrakAccess) ? route('mks.spyderweb', [$selectedMk->id]) : 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('mks.spyderweb') ? 'active' : '' }} {{ ($mkPenilaianComplete && $mkHasKontrakAccess) ? '' : 'disabled' }}" @if(!($mkPenilaianComplete && $mkHasKontrakAccess)) aria-disabled="true" tabindex="-1" @endif>
                                    <i class="nav-icon fas fa-bullseye"></i>
                                    <p>Jaring Laba-laba</p>
                                </a>
                            </li>

                            @if(!$mkHasKontrakAccess)
                                <li class="nav-header text-warning">Anda belum tercatat pada kontrak mata kuliah ini, menu penilaian dinonaktifkan.</li>
                            @endif
                        @else
                            <li class="nav-header text-warning">Pilih mata kuliah dari halaman Ruang Dosen.</li>
                        @endif
                    </ul>
                @else
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ auth()->check() ? route('home') : url('/') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    @can('access admin dashboard')
                        <li class="nav-header">ADMIN</li>

                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Manajemen User</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*', 'permissions.*', 'rolepermissions.*', 'userroles.*', 'userpermissions.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>Role & Permission</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('prodis.index') }}" class="nav-link {{ request()->routeIs('prodis.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-book-reader"></i>
                                <p>Prodi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('semesters.index') }}" class="nav-link {{ request()->routeIs('semesters.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Semester</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('evaluasis.index') }}" class="nav-link {{ request()->routeIs('evaluasis.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clipboard-check"></i>
                                <p>Evaluasi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('mahasiswas.index') }}" class="nav-link {{ request()->routeIs('mahasiswas.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-graduate"></i>
                                <p>Mahasiswa</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('kontrakmks.index') }}" class="nav-link {{ request()->routeIs('kontrakmks.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-signature"></i>
                                <p>Kontrak Mata Kuliah</p>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('setting.import.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('setting.import.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-import"></i>
                                <p>
                                    Bulk Import
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('setting.import.admin-master') }}" class="nav-link {{ request()->routeIs('setting.import.admin-master*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Admin Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.import.users') }}" class="nav-link {{ request()->routeIs('setting.import.users*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Users</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.import.mahasiswas') }}" class="nav-link {{ request()->routeIs('setting.import.mahasiswas*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Mahasiswa</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.import.joinprodiusers') }}" class="nav-link {{ request()->routeIs('setting.import.joinprodiusers*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Join Prodi User</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.import.kontrakmks') }}" class="nav-link {{ request()->routeIs('setting.import.kontrakmks*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kontrak MK</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                    @can('access prodi dashboard')
                        <li class="nav-header">PRODI</li>
                        <li class="nav-item">
                            <a href="{{ route('ruang.prodi') }}" class="nav-link {{ request()->routeIs('ruang.prodi') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-sitemap"></i>
                                <p>Pilih Kurikulum</p>
                            </a>
                        </li>
                    @endcan

                    @can('access dosen dashboard')
                        <li class="nav-header">DOSEN</li>
                        <li class="nav-item">
                            <a href="{{ route('ruang.dosen') }}" class="nav-link {{ request()->routeIs('ruang.dosen') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chalkboard-teacher"></i>
                                <p>Pilih Mata Kuliah</p>
                            </a>
                        </li>
                    @endcan
                </ul>
                @endif
            </nav>
        </div>
    </aside>

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
            Env: <span class="fw-semibold text-uppercase">{{ config('app.env') }}</span>
            &middot; Laravel {{ app()->version() }}
            &middot; PHP {{ PHP_VERSION }}
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
