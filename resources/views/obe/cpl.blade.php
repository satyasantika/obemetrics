@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Data Capaian Pembelajaran Lulusan (CPL)"
                    subtitle="Kelola CPL pada kurikulum aktif"
                    icon="bi bi-bullseye"
                    />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-2">
                        <div class="col">
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateCpl">
                                <i class="bi bi-plus-circle"></i> Tambah CPL
                            </button>
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'cpls']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm mt-1 float-end">
                                <i class="bi bi-upload"></i> Upload Banyak CPL
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="table-responsive rounded-3 border bg-white shadow-sm">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-uppercase small text-muted fw-semibold" style="width: 280px;">CPL</th>
                                        <th class="text-uppercase small text-muted fw-semibold">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                 @forelse ($cpls as $cpl)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <strong class="me-2">{{ $cpl->kode }}</strong>
                                            {{-- Edit CPL --}}
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" data-bs-toggle="modal" data-bs-target="#modalEditCpl-{{ $cpl->id }}" title="Edit CPL">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <div class="mt-2">
                                                <span class="badge rounded-pill bg-{{ $cpl->cakupan == 'Universitas' ? 'success' : '' }}{{ $cpl->cakupan == 'Fakultas' ? 'primary' : '' }}{{ $cpl->cakupan == 'Program Studi' ? 'dark' : '' }}">{{ $cpl->cakupan }}</span>
                                            </div>
                                        </td>
                                        <td style="text-align: justify">
                                            {{ $cpl->nama }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data CPL untuk kurikulum ini.</span>
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

<div class="modal fade" id="modalCreateCpl" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden">
            <form action="{{ route('kurikulums.cpls.store', $kurikulum) }}" method="post">
                @csrf
                <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                <div class="modal-header bg-light-subtle border-bottom">
                    <h5 class="modal-title">Tambah Data CPL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Kode CPL <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Nama CPL <span class="text-danger">(*)</span></label>
                            <textarea name="nama" rows="6" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="form-label col-md-4">Cakupan CPL <span class="text-danger">(*)</span></label>
                        <div class="col">
                            <select name="cakupan" class="form-select" required>
                                <option value="">- Pilih Cakupan -</option>
                                <option value="Universitas">Universitas</option>
                                <option value="Fakultas">Fakultas</option>
                                <option value="Program Studi">Program Studi</option>
                            </select>
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

@foreach ($cpls as $cpl)
<div class="modal fade" id="modalEditCpl-{{ $cpl->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden">
            <form action="{{ route('kurikulums.cpls.update',[$kurikulum->id,$cpl->id]) }}" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                <div class="modal-header bg-light-subtle border-bottom">
                    <h5 class="modal-title">Edit CPL: {{ $cpl->kode }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Kode CPL <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" value="{{ $cpl->kode }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Nama CPL <span class="text-danger">(*)</span></label>
                            <textarea name="nama" rows="6" class="form-control" required>{{ $cpl->nama }}</textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="form-label col-md-4">Cakupan CPL <span class="text-danger">(*)</span></label>
                        <div class="col">
                            <select name="cakupan" class="form-select" required>
                                <option value="">- Pilih Cakupan -</option>
                                <option value="Universitas" @selected($cpl->cakupan == 'Universitas')>Universitas</option>
                                <option value="Fakultas" @selected($cpl->cakupan == 'Fakultas')>Fakultas</option>
                                <option value="Program Studi" @selected($cpl->cakupan == 'Program Studi')>Program Studi</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light-subtle border-top">
                    @php
                        $canDeleteCpl = !$cpl->profilCpls()->exists() && !$cpl->joinCplBks()->exists();
                    @endphp
                    @if ($canDeleteCpl)
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm me-auto" onclick="if(confirm('Yakin akan menghapus CPL {{ $cpl->kode }}?')){ document.getElementById('delete-cpl-{{ $cpl->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
                    @else
                        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            @if ($canDeleteCpl)
                <form id="delete-cpl-{{ $cpl->id }}" action="{{ route('kurikulums.cpls.destroy',[$kurikulum->id,$cpl->id]) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
            @endif
        </div>
    </div>
</div>
@endforeach


@endsection
