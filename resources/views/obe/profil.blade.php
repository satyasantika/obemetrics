@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Data Profil Lulusan"
                    subtitle="Kelola profil lulusan dan indikator capaian"
                    icon="bi bi-people-fill"
                    />
                <div class="card-body align-content-center">
                    <div class="row mb-2">
                        <div class="col">
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateProfil">
                                <i class="bi bi-plus-circle"></i> Tambah Profil Lulusan
                            </button>
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'profil_indikators']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm mt-1 float-end">
                                <i class="bi bi-upload"></i> Upload Banyak Indikator Profil
                            </a>
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'profils']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm mt-1 float-end me-2">
                                <i class="bi bi-upload"></i> Upload Banyak Profil
                            </a>
                        </div>
                    </div>
                    <hr>

                    {{-- daftar profil --}}

                    <div class="row g-3">
                        @forelse ($profils as $profil)
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                <div class="card-header bg-white border-bottom py-3">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div>
                                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis border border-primary-subtle">{{ $profil->kode }}</span>
                                            <h5 class="card-title mb-0 mt-2 text-dark">{{ $profil->nama }}</h5>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditProfil-{{ $profil->id }}">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <p class="card-text text-muted mb-3">
                                        {{ $profil->deskripsi }}
                                    </p>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="mb-0 fw-semibold">Indikator</h6>
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateProfilIndikator-{{ $profil->id }}">
                                            <i class="bi bi-plus-circle"></i> Tambah
                                        </button>
                                    </div>
                                    <ol class="list-group list-group-flush rounded-3 border overflow-hidden">
                                        @php
                                            $profilindikators = \App\Models\ProfilIndikator::where('profil_id',$profil->id)->get();
                                        @endphp
                                        @forelse ($profilindikators as $profilindikator)
                                        <li class="list-group-item d-flex align-items-center justify-content-between gap-2 py-2">
                                            <span class="small text-dark">{{ $profilindikator->nama }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" data-bs-toggle="modal" data-bs-target="#modalEditProfilIndikator-{{ $profilindikator->id }}" title="Edit indikator">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </li>
                                        @empty
                                        <li class="list-group-item text-muted small">Belum ada indikator untuk profil ini.</li>
                                        @endforelse
                                    </ol>
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

<div class="modal fade" id="modalCreateProfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal edit profil, tambah indikator, edit indikator dipindah ke luar container-fluid agar tidak terpengaruh parent -->
@foreach ($profils as $profil)
    <div class="modal fade" id="modalEditProfil-{{ $profil->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm me-auto" onclick="if(confirm('Yakin akan menghapus profil {{ $profil->kode }}: {{ $profil->nama }}?')){ document.getElementById('delete-profil-{{ $profil->id }}').submit(); }">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        @else
                            <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                        @endif
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-save"></i> Save</button>
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
        <div class="modal-dialog modal-lg">
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
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @php
        $profilindikators = \App\Models\ProfilIndikator::where('profil_id',$profil->id)->get();
    @endphp
    @foreach ($profilindikators as $profilindikator)
    <div class="modal fade" id="modalEditProfilIndikator-{{ $profilindikator->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm me-auto" onclick="if(confirm('Yakin akan menghapus indikator {{ $profilindikator->nama }}?')){ document.getElementById('delete-profilindikator-{{ $profilindikator->id }}').submit(); }">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        @else
                            <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                        @endif
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-save"></i> Save</button>
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
@endforeach

@endsection
