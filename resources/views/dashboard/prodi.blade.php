@push('title')
    Dashboard Program Studi
@endpush
<div class="row">
    <div class="col">
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="mb-0">Manajemen Kurikulum OBE</h5>
                <small class="text-muted">Kelola kurikulum dan lanjutkan ke detail pemetaan CPL</small>
            </div>
            <div class="card-body">
                {{-- Program Studi --}}
                @forelse (auth()->user()->joinProdiUsers->pluck('prodi') as $prodi)
                    <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <span class="h6 mb-0">Program Studi {{ $prodi->jenjang }} {{ $prodi->nama }}</span>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateKurikulum-{{ $prodi->id }}">
                            <i class="bi bi-plus-circle"></i> Tambah Kurikulum
                        </button>
                    </div>

                    <ol class="mb-2">
                        @forelse (auth()->user()->joinProdiUsers->pluck('prodi.kurikulums')->flatten() as $kurikulum)
                        <li class="mb-2">
                            <div class="border rounded-2 p-2 p-md-3 bg-white">
                                <div class="fw-semibold">{{ $kurikulum->nama }}</div>
                                <div class="mt-1 d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditKurikulum-{{ $kurikulum->id }}">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <a href="{{ route('kurikulums.profils.index',[$kurikulum->id]) }}" class="btn btn-link btn-sm p-0 text-decoration-none">
                                        <i class="bi bi-eye"></i> Selengkapnya
                                    </a>
                                </div>
                            </div>
                        </li>
                        @empty
                            <div class="text-muted">Tidak ada kurikulum pada program studi ini.</div>
                        @endforelse
                    </ol>
                    </div>

                    <div class="modal fade" id="modalCreateKurikulum-{{ $prodi->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
                                        <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @foreach ($prodi->kurikulums as $kurikulumModal)
                        <div class="modal fade" id="modalEditKurikulum-{{ $kurikulumModal->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                    Anda belum terdaftar pada program studi manapun.
                @endforelse
            </div>
        </div>
    </div>
</div>
