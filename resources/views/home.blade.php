@extends('layouts.panel')

@section('content')
<div class="card">
    <div class="card-header">
        {{ __('Dashboard') }}
    </div>

    <div class="card-body">
        <h3>Selamat datang, {{ auth()->user()->name }}</h3>

        @php
            $admin = auth()->user()->can('access admin dashboard');
            $prodi = auth()->user()->can('access prodi dashboard');
            $dosen = auth()->user()->can('access dosen dashboard');
        @endphp

        <div class="d-flex flex-column gap-3 mt-3">
            @if($admin)
                {{-- <div class="card border-0 shadow-sm"> --}}
                    <hr>
                    <div class="h2">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>Dashboard Admin</span>
                    </div>
                    {{-- <div class="card-body"> --}}
                        @includeWhen($admin, 'dashboard.admin')
                    {{-- </div> --}}
                </div>
            @endif
            @if($prodi)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success-subtle text-success-emphasis border-0 border-bottom border-success-subtle fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-diagram-3-fill"></i>
                        <span>Dashboard Prodi</span>
                    </div>
                    <div class="card-body">
                        @includeWhen($prodi, 'dashboard.prodi')
                    </div>
                </div>
            @endif
            @if($dosen)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info-subtle text-info-emphasis border-0 border-bottom border-info-subtle fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-mortarboard-fill"></i>
                        <span>Dashboard Dosen</span>
                    </div>
                    <div class="card-body">
                        @includeWhen($dosen, 'dashboard.dosen')
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
