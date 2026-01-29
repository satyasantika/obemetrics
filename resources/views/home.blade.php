@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
                        $firstActive = $admin ? 'admin' : ($prodi ? 'prodi' : ($dosen ? 'dosen' : ''));
                    @endphp

                    <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
                        @if($admin)
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link {{ $firstActive == 'admin' ? 'active' : '' }}"
                                    id="admin-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#admin"
                                    type="button" role="tab"
                                    aria-controls="admin"
                                    aria-selected="{{ $firstActive == 'admin' ? 'true' : 'false' }}">
                                    Ruang Admin
                                </button>
                            </li>
                        @endif
                        @if($prodi)
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link {{ $firstActive == 'prodi' ? 'active' : '' }}"
                                    id="prodi-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#prodi"
                                    type="button" role="tab"
                                    aria-controls="prodi"
                                    aria-selected="{{ $firstActive == 'prodi' ? 'true' : 'false' }}">
                                    Ruang Prodi
                                </button>
                            </li>
                        @endif
                        @if($dosen)
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link {{ $firstActive == 'dosen' ? 'active' : '' }}"
                                    id="dosen-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#dosen"
                                    type="button" role="tab"
                                    aria-controls="dosen"
                                    aria-selected="{{ $firstActive == 'dosen' ? 'true' : 'false' }}">
                                    Ruang Dosen
                                </button>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content mt-3" id="dashboardTabsContent">
                        @if($admin)
                            <div class="tab-pane fade {{ $firstActive == 'admin' ? 'show active' : '' }}" id="admin" role="tabpanel" aria-labelledby="admin-tab">
                                @includeWhen($admin, 'dashboard.admin')
                            </div>
                        @endif
                        @if($prodi)
                            <div class="tab-pane fade {{ $firstActive == 'prodi' ? 'show active' : '' }}" id="prodi" role="tabpanel" aria-labelledby="prodi-tab">
                                @includeWhen($prodi, 'dashboard.prodi')
                            </div>
                        @endif
                        @if($dosen)
                            <div class="tab-pane fade {{ $firstActive == 'dosen' ? 'show active' : '' }}" id="dosen" role="tabpanel" aria-labelledby="dosen-tab">
                                @includeWhen($dosen, 'dashboard.dosen')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
