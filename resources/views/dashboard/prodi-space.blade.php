@extends('layouts.panel')

@push('title')
    Ruang Prodi
@endpush

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-success-subtle text-success-emphasis border-0 border-bottom border-success-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-diagram-3-fill"></i>
            <span>Manajemen Kurikulum OBE</span>
        </div>
        @if(!empty($selectedKurikulum))
            <span class="badge bg-success text-white">Aktif: {{ $selectedKurikulum->kode }}</span>
        @endif
    </div>
    <div class="card-body">
        @forelse (($managedProdis ?? collect()) as $prodi)
            <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <span class="h6 mb-0">Program Studi {{ $prodi->jenjang }} {{ $prodi->nama }}</span>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCreateKurikulum-{{ $prodi->id }}">
                        <i class="bi bi-plus-circle"></i> Tambah Kurikulum
                    </button>
                </div>

                <div class="row g-3">
                    @forelse ($prodi->kurikulums as $kurikulum)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="border rounded-3 p-3 bg-white h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $kurikulum->kode }}</span>
                                    @if(($selectedKurikulum->id ?? null) === $kurikulum->id)
                                        <span class="badge bg-success">Aktif</span>
                                    @endif
                                </div>
                                <div class="fw-semibold">{{ $kurikulum->nama }}</div>
                                <small class="text-muted d-block mt-1">Target capaian: {{ $kurikulum->target_capaian_lulusan ?? 100 }}%</small>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap mt-3">
                                @php
                                    $canBulkImportMaster =
                                        !$kurikulum->profils()->exists() &&
                                        !$kurikulum->cpls()->exists() &&
                                        !$kurikulum->bks()->exists();
                                        // !$kurikulum->mks()->exists();
                                @endphp
                                @if ($canBulkImportMaster)
                                    <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'kurikulum_bundle', 'return_url' => url()->full()]) }}" class="btn btn-outline-success btn-sm rounded-pill">
                                        <i class="bi bi-upload"></i> Detail Selengkapnya
                                    </a>
                                @else
                                    <a href="{{ route('kurikulums.profils.index',[$kurikulum->id]) }}" class="btn btn-outline-success btn-sm rounded-pill">
                                        <i class="bi bi-eye"></i> Detail Selengkapnya
                                    </a>
                                @endif
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEditKurikulum-{{ $kurikulum->id }}">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                        <div class="col-12">
                            <div class="text-muted">Tidak ada kurikulum pada program studi ini.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="modal fade" id="modalCreateKurikulum-{{ $prodi->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('prodis.kurikulums.store', $prodi->id) }}" method="post">
                            @csrf
                            <input type="hidden" name="prodi_id" value="{{ $prodi->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title">Tambah Kurikulum - {{ $prodi->jenjang }} {{ $prodi->nama }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col">
                                        <label for="nama-create-{{ $prodi->id }}" class="form-label">Nama Kurikulum <span class="text-danger">(*)</span></label>
                                        <input type="text" name="nama" class="form-control" id="nama-create-{{ $prodi->id }}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="kode-create-{{ $prodi->id }}" class="form-label">Kode Kurikulum <span class="text-danger">(*)</span></label>
                                        <input type="text" name="kode" class="form-control" id="kode-create-{{ $prodi->id }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="target-create-{{ $prodi->id }}" class="form-label">Target Capaian Lulusan (%) <span class="text-danger">(*)</span></label>
                                        <input type="number" step="1" min="0" max="100" name="target_capaian_lulusan" class="form-control" id="target-create-{{ $prodi->id }}" value="100" required>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col">
                                        <label for="deskripsi-create-{{ $prodi->id }}" class="form-label">Deskripsi</label>
                                        <textarea name="deskripsi" rows="5" class="form-control" id="deskripsi-create-{{ $prodi->id }}"></textarea>
                                    </div>
                                </div>
                                <span class="text-danger">(*) Wajib diisi.</span>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary btn-sm rounded-pill" type="button" data-bs-dismiss="modal">Close</button>
                                <button class="btn btn-outline-success btn-sm rounded-pill" type="submit"><i class="bi bi-save"></i> Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @foreach ($prodi->kurikulums as $kurikulumModal)
                <div class="modal fade" id="modalEditKurikulum-{{ $kurikulumModal->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form action="{{ route('prodis.kurikulums.update',[$prodi->id,$kurikulumModal->id]) }}" method="post">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="prodi_id" value="{{ $prodi->id }}">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Kurikulum - {{ $kurikulumModal->nama }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col">
                                            <label for="nama-edit-{{ $kurikulumModal->id }}" class="form-label">Nama Kurikulum <span class="text-danger">(*)</span></label>
                                            <input type="text" name="nama" class="form-control" id="nama-edit-{{ $kurikulumModal->id }}" value="{{ $kurikulumModal->nama }}" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="kode-edit-{{ $kurikulumModal->id }}" class="form-label">Kode Kurikulum <span class="text-danger">(*)</span></label>
                                            <input type="text" name="kode" class="form-control" id="kode-edit-{{ $kurikulumModal->id }}" value="{{ $kurikulumModal->kode }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="target-edit-{{ $kurikulumModal->id }}" class="form-label">Target Capaian Lulusan (%) <span class="text-danger">(*)</span></label>
                                            <input type="number" step="1" min="0" max="100" name="target_capaian_lulusan" class="form-control" id="target-edit-{{ $kurikulumModal->id }}" value="{{ $kurikulumModal->target_capaian_lulusan ?? 100 }}" required>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col">
                                            <label for="deskripsi-edit-{{ $kurikulumModal->id }}" class="form-label">Deskripsi</label>
                                            <textarea name="deskripsi" rows="5" class="form-control" id="deskripsi-edit-{{ $kurikulumModal->id }}">{{ $kurikulumModal->deskripsi }}</textarea>
                                        </div>
                                    </div>
                                    <span class="text-danger">(*) Wajib diisi.</span>
                                </div>
                                <div class="modal-footer">
                                    @php
                                        $canDeleteKurikulum =
                                            !$kurikulumModal->profils()->exists() &&
                                            !$kurikulumModal->cpls()->exists() &&
                                            !$kurikulumModal->bks()->exists() &&
                                            !$kurikulumModal->mks()->exists() &&
                                            !$kurikulumModal->joinProfilCpls()->exists() &&
                                            !$kurikulumModal->joinCplBks()->exists() &&
                                            !$kurikulumModal->joinCplMks()->exists() &&
                                            !$kurikulumModal->joinMkUsers()->exists();
                                    @endphp
                                    @if ($canDeleteKurikulum)
                                        <button class="btn btn-outline-danger btn-sm me-auto" type="button" onclick="if(confirm('Yakin akan menghapus {{ $kurikulumModal->kode }}: {{ $kurikulumModal->nama }}?')){ document.getElementById('delete-kurikulum-{{ $kurikulumModal->id }}').submit(); }">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    @else
                                        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                                    @endif
                                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
                                    <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
                                </div>
                            </form>
                            @if ($canDeleteKurikulum)
                                <form id="delete-kurikulum-{{ $kurikulumModal->id }}" action="{{ route('prodis.kurikulums.destroy',[$prodi->id,$kurikulumModal->id]) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

        @empty
            <div class="alert alert-warning mb-0">Anda belum terdaftar pada program studi manapun.</div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Keep Bootstrap modals at document root to avoid stacking/overflow issues.
    const kurikulumModals = document.querySelectorAll('[id^="modalCreateKurikulum-"], [id^="modalEditKurikulum-"]');
    kurikulumModals.forEach(function (modalEl) {
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
    });
});
</script>
@endpush
