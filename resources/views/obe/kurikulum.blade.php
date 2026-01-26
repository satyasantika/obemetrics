@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Data Profil Lulusan Program Studi {{ $kurikulum->prodi->nama }} untuk <strong>{{ $kurikulum->nama }}</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col">
                            <a href="{{ route('kurikulums.profils.create',$kurikulum) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambah Profil</a>
                        </div>
                    </div>
                    <hr>
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
                                    <h6>Indikator:</h6>
                                    <ul>
                                        @php
                                            $indikators = \App\Models\ProfilIndikator::where('profil_id',$profil->id)->get();
                                        @endphp
                                        @forelse ($indikators as $indikator)
                                        <li>{{ $indikator->nama }}</li>
                                        @empty
                                        <span class="bg-danger text-white">Belum ada indikator untuk profil ini.</span>
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
