@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <div class="card">
                <x-obe.header
                    title="Data Capaian Pembelajaran Mata Kuliah (CPMK)"
                    subtitle="Kelola CPMK pada mata kuliah terpilih"
                       icon="bi bi-list-check" />
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateCpmk"><i class="bi bi-plus-circle"></i> Tambah CPMK</button>
                            <a href="{{ route('settings.import.mk-master', ['mk' => $mk->id, 'target' => 'cpmks']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm mt-1 float-end"><i class="bi bi-upload"></i> Tambah Banyak CPMK</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                @forelse ($cpmks as $cpmk)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <strong>{{ $cpmk->kode }}</strong>
                                            {{-- Edit CPMK --}}
                                            <button type="button" class="btn btn-sm btn-white text-primary" data-bs-toggle="modal" data-bs-target="#modalEditCpmk-{{ $cpmk->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                        <td style="text-align: justify">
                                            {{ $cpmk->nama }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data CPMK untuk mata kuliah ini.</span>
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

<div class="modal fade" id="modalCreateCpmk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('mks.cpmks.store', $mk) }}" method="post">
                @csrf
                <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah CPMK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label"><strong>Kode</strong> CPMK <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label"><strong>Nama</strong> CPMK <span class="text-danger">(*)</span></label>
                            <textarea name="nama" rows="3" class="form-control" required></textarea>
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
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($cpmks as $cpmk)
<div class="modal fade" id="modalEditCpmk-{{ $cpmk->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('mks.cpmks.update',[$mk->id,$cpmk->id]) }}" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit CPMK: {{ $cpmk->kode }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label"><strong>Kode</strong> CPMK <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" value="{{ $cpmk->kode }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label"><strong>Nama</strong> CPMK <span class="text-danger">(*)</span></label>
                            <textarea name="nama" rows="3" class="form-control" required>{{ $cpmk->nama }}</textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="8" class="form-control">{{ $cpmk->deskripsi }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @php
                        $canDeleteCpmk = !$cpmk->joinCplCpmks()->exists() && !$cpmk->subcpmks()->exists();
                    @endphp
                    @if ($canDeleteCpmk)
                        <button type="button" class="btn btn-outline-danger btn-sm me-auto" onclick="if(confirm('Yakin akan menghapus CPMK {{ $cpmk->kode }}?')){ document.getElementById('delete-cpmk-{{ $cpmk->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
                    @else
                        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            @if ($canDeleteCpmk)
                <form id="delete-cpmk-{{ $cpmk->id }}" action="{{ route('mks.cpmks.destroy',[$mk->id,$cpmk->id]) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
            @endif
        </div>
    </div>
</div>
@endforeach


@endsection
