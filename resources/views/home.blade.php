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
            <hr>
            <div class="h2">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Dashboard Admin</span>
            </div>
                @includeWhen($admin, 'dashboard.admin')
            </div>
            @endif
            @if($prodi)
                <hr>
                <div class="h2">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>Dashboard Program Studi</span>
                </div>
                @includeWhen($prodi, 'dashboard.prodi')
            @endif
            @if($dosen)
                <hr>
                <div class="h2">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Dashboard Dosen</span>
                </div>
                @includeWhen($dosen, 'dashboard.dosen')
            @endif
        </div>
    </div>
</div>
@endsection
