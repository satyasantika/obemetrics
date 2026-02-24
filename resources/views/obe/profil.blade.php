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
                    @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])
                    <hr>
                    {{-- menu kurikulum --}}
                    @include('components.menu-kurikulum',['kurikulum' => $kurikulum])
                    <hr>
                    <div class="row mb-2">
                        <div class="col">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateProfil">
                                <i class="bi bi-plus-circle"></i> Tambah Profil Lulusan
                            </button>
                            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'profil_indikators']) }}" class="btn btn-sm btn-success mt-1 float-end">
                                <i class="bi bi-upload"></i> Upload Banyak Indikator Profil
                            </a>
                            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'profils']) }}" class="btn btn-sm btn-success mt-1 float-end me-2">
                                <i class="bi bi-upload"></i> Upload Banyak Profil
                            </a>
                        </div>
                    </div>
                    <hr>

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
                                        <button type="button" class="btn btn-sm btn-primary float-end" data-bs-toggle="modal" data-bs-target="#modalEditProfil-{{ $profil->id }}">
                                            <i class="bi bi-pencil-square"></i> Edit Profil
                                        </button>
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
                                            <button type="button" class="btn btn-sm btn-white text-primary" data-bs-toggle="modal" data-bs-target="#modalEditProfilIndikator-{{ $profilindikator->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </li>
                                        @empty
                                        <span class="bg-danger text-white">Belum ada indikator untuk profil ini.</span>
                                        @endforelse
                                    </ol>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalCreateProfilIndikator-{{ $profil->id }}">
                                        <i class="bi bi-plus-circle"></i> Tambah Indikator
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modalEditProfil-{{ $profil->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <form action="{{ route('kurikulums.profils.update',[$kurikulum->id,$profil->id]) }}" method="post">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Profil: {{ $profil->nama }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Nama Profil <span class="text-danger">(*)</span></label>
                                                    <input type="text" name="nama" class="form-control" value="{{ $profil->nama }}" required>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Kode Profil <span class="text-danger">(*)</span></label>
                                                    <input type="text" name="kode" class="form-control" value="{{ $profil->kode }}" required>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Deskripsi</label>
                                                    <textarea name="deskripsi" rows="8" class="form-control">{{ $profil->deskripsi }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            @php
                                                $canDeleteProfil = !in_array((string) $profil->id, $nonDeletableProfilIds ?? [], true);
                                            @endphp
                                            @if ($canDeleteProfil)
                                                <button type="button" class="btn btn-outline-danger btn-sm me-auto" onclick="if(confirm('Yakin akan menghapus profil {{ $profil->kode }}: {{ $profil->nama }}?')){ document.getElementById('delete-profil-{{ $profil->id }}').submit(); }">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            @else
                                                <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                                            @endif
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                                        </div>
                                    </form>
                                    @if ($canDeleteProfil)
                                        <form id="delete-profil-{{ $profil->id }}" action="{{ route('kurikulums.profils.destroy',[$kurikulum->id,$profil->id]) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modalCreateProfilIndikator-{{ $profil->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <form action="{{ route('profils.profilindikators.store',$profil) }}" method="post">
                                        @csrf
                                        <input type="hidden" name="profil_id" value="{{ $profil->id }}">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tambah Indikator - {{ $profil->nama }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Nama Indikator <span class="text-danger">(*)</span></label>
                                                    <input type="text" name="nama" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Deskripsi</label>
                                                    <textarea name="deskripsi" rows="10" class="form-control"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @foreach ($profilindikators as $profilindikator)
                        <div class="modal fade" id="modalEditProfilIndikator-{{ $profilindikator->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <form action="{{ route('profils.profilindikators.update',[$profil->id,$profilindikator->id]) }}" method="post">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="profil_id" value="{{ $profil->id }}">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Indikator - {{ $profil->nama }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Nama Indikator <span class="text-danger">(*)</span></label>
                                                    <input type="text" name="nama" class="form-control" value="{{ $profilindikator->nama }}" required>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Deskripsi</label>
                                                    <textarea name="deskripsi" rows="10" class="form-control">{{ $profilindikator->deskripsi }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            @php
                                                $canDeleteProfilIndikator = !in_array((string) $profilindikator->id, $nonDeletableProfilIndikatorIds ?? [], true);
                                            @endphp
                                            @if ($canDeleteProfilIndikator)
                                                <button type="button" class="btn btn-outline-danger btn-sm me-auto" onclick="if(confirm('Yakin akan menghapus indikator {{ $profilindikator->nama }}?')){ document.getElementById('delete-profilindikator-{{ $profilindikator->id }}').submit(); }">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            @else
                                                <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                                            @endif
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                                        </div>
                                    </form>
                                    @if ($canDeleteProfilIndikator)
                                        <form id="delete-profilindikator-{{ $profilindikator->id }}" action="{{ route('profils.profilindikators.destroy',[$profil->id,$profilindikator->id]) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
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

<div class="modal fade" id="modalCreateProfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('kurikulums.profils.store',$kurikulum) }}" method="post">
                @csrf
                <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Profil Lulusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Nama Profil <span class="text-danger">(*)</span></label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Kode Profil <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="8" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
