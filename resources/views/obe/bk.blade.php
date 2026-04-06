@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Data Bahan Kajian (BK)"
                    subtitle="Kelola bahan kajian pendukung CPL"
                    icon="bi bi-journals"
                    />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-2">
                        <div class="col">
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateBk">
                                <i class="bi bi-plus-circle"></i> Tambah Bahan Kajian
                            </button>
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'bks']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm mt-1 float-end"><i class="bi bi-upload"></i> Upload Banyak BK</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="table-responsive rounded-3 border bg-white shadow-sm">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-uppercase small text-muted fw-semibold" style="width: 280px;">BK</th>
                                        <th class="text-uppercase small text-muted fw-semibold">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($bks as $bk)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <strong class="me-2">{{ $bk->kode }}</strong>
                                            {{-- Edit BK --}}
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" data-bs-toggle="modal" data-bs-target="#modalEditBk-{{ $bk->id }}" title="Edit BK">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                        <td style="text-align: justify">
                                            {{ $bk->nama }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data Bahan Kajian untuk kurikulum ini.</span>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCreateBk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden">
            <form action="{{ route('kurikulums.bks.store', $kurikulum) }}" method="post">
                @csrf
                <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                <div class="modal-header bg-light-subtle border-bottom">
                    <h5 class="modal-title">Tambah Data Bahan Kajian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Nama Bahan Kajian <span class="text-danger">(*)</span></label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Kode Bahan Kajian <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="6" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light-subtle border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($bks as $bk)
<div class="modal fade" id="modalEditBk-{{ $bk->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden">
            <form action="{{ route('kurikulums.bks.update',[$kurikulum->id,$bk->id]) }}" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                <div class="modal-header bg-light-subtle border-bottom">
                    <h5 class="modal-title">Edit BK: {{ $bk->kode }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Nama Bahan Kajian <span class="text-danger">(*)</span></label>
                            <input type="text" name="nama" class="form-control" value="{{ $bk->nama }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Kode Bahan Kajian <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" value="{{ $bk->kode }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="6" class="form-control">{{ $bk->deskripsi }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light-subtle border-top">
                    @php
                        $canDeleteBk = !$bk->joinCplBks()->exists() && !$bk->joinCplMks()->exists();
                    @endphp
                    @if ($canDeleteBk)
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm me-auto" onclick="if(confirm('Yakin akan menghapus BK {{ $bk->kode }}?')){ document.getElementById('delete-bk-{{ $bk->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
                    @else
                        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            @if ($canDeleteBk)
                <form id="delete-bk-{{ $bk->id }}" action="{{ route('kurikulums.bks.destroy',[$kurikulum->id,$bk->id]) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
            @endif
        </div>
    </div>
</div>
@endforeach


@endsection
