@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Data Profil Lulusan</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas kurikulum --}}
                    <div class="row">
                        <div class="col-md-3">Nama Kurikulum</div>
                        <div class="col"><strong>{{ $kurikulum->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Program Studi</div>
                        <div class="col"><strong>{{ $kurikulum->prodi->jenjang }} {{ $kurikulum->prodi->nama }}</strong></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col">
                            @include('layouts.menu-kurikulum',$kurikulum)
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('kurikulums.profils.create',$kurikulum) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-plus-circle"></i> Tambah Profil Lulusan
                            </a>
                        </div>
                    </div>

                    {{-- daftar profil --}}

                    <div class="row">
                        @forelse ($profils as $profil)
                        <!-- Card -->
                        <div class="col-md-6 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="card-title mb-0">
                                        {{ $profil->nama }}
                                        {{-- Edit Profil --}}
                                        <a href="{{ route('kurikulums.profils.edit',[$kurikulum->id,$profil->id]) }}" class="btn btn-sm btn-primary float-end">
                                            <i class="bi bi-pencil-square"></i> Edit Profil
                                        </a>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        {{ $profil->deskripsi }}
                                    </p>
                                    <hr>
                                    {{-- indikator profil lulusan: --}}
                                    <h6>
                                        <strong>Indikator:</strong>
                                    </h6>
                                    <ol>
                                        @php
                                            $profilindikators = \App\Models\ProfilIndikator::where('profil_id',$profil->id)->get();
                                        @endphp
                                        @forelse ($profilindikators as $profilindikator)
                                        <li>
                                            {{ $profilindikator->nama }}
                                            <a href="{{ route('profils.profilindikators.edit',[$profil->id,$profilindikator->id]) }}" class="btn btn-sm btn-white text-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </li>
                                        @empty
                                        <span class="bg-danger text-white">Belum ada indikator untuk profil ini.</span>
                                        @endforelse
                                    </ol>
                                    <a href="{{ route('profils.profilindikators.create',$profil) }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-plus-circle"></i> Tambah Indikator
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col">
                            <span class="bg-warning text-dark p-2">Belum ada data profil lulusan untuk kurikulum ini.</span>
                        </div>
                        @endforelse

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
