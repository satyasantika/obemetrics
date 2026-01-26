@extends('layouts.app')
@section('content')
@php
    $nama_kurikulum = \App\Models\Kurikulum::find($kurikulum)->nama;
@endphp
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard Profil Lulusan pada ').$nama_kurikulum }}</div>

                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('kurikulums.profils.create',$kurikulum) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-plus-circle"></i> profil</a>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        @php
                            $profils = \App\Models\Profil::where('kurikulum_id',$kurikulum)->get();
                        @endphp
                        @forelse ($profils as $profil)
                        <!-- Card -->
                        <div class="col mb-2">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">{{ $profil->nama }}</h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        {{ $profil->deskripsi }}
                                    </p>
                                    <h6>Indikator:</h6>
                                    <ul>
                                        @php
                                            $indikators = \App\Models\ProfilIndikator::where('profil_id',$profil->id)->get();
                                        @endphp
                                        @forelse ($indikators as $indikator)
                                        <li>{{ $indikator->nama }}</li>
                                        @empty

                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @empty

                        @endforelse

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
