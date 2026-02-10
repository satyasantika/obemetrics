@extends('layouts.setting-form')

@push('header')
    {{ $penugasan->id ? 'Edit' : 'Tambah' }} Data Penugasan Mata Kuliah
    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')

<div class="card-body">
    {{-- identitas mk --}}
    <div class="row">
        <div class="col-md-3">Mata Kuliah</div>
        <div class="col"><strong>{{ $mk->nama }}</strong></div>
    </div>
    <div class="row">
        <div class="col-md-3">Kurikulum</div>
        <div class="col"><strong>{{ $mk->kurikulum->nama }}</strong></div>
    </div>
    <div class="row">
        <div class="col-md-3">Program Studi</div>
        <div class="col"><strong>{{ $mk->kurikulum->prodi->jenjang }} {{ $mk->kurikulum->prodi->nama }}</strong></div>
    </div>
    <hr>
    {{-- form penugasan --}}
    <form id="formAction" action="{{ $penugasan->id ? route('mks.penugasans.update',[$mk->id,$penugasan->id]) : route('mks.penugasans.store', $mk->id) }}" method="post">
        @csrf
        @if ($penugasan->id)
            @method('PUT')
        @endif
        <input type="hidden" name="mk_id" value="{{ $mk->id }}">

        {{-- nama tugas --}}
        <div class="row mb-3">
            <div class="col">
                <label for="nama" class="form-label">Nama Tugas <span class="text-danger">(*)</span></label>
                <textarea name="nama" rows="3" class="form-control" id="nama" required autofocus>{{ $penugasan->nama }}</textarea>
            </div>
        </div>
        <div class="row mb-3">
            {{-- kode --}}
            <div class="col">
                <label for="kode" class="form-label">Kode</label>
                <input type="text" name="kode" class="form-control" id="kode" value="{{ $penugasan->kode }}">
            </div>
            {{-- bobot --}}
            <div class="col">
                <label for="bobot" class="form-label">Bobot (%) <span class="text-danger">(*)</span></label>
                <input type="number" step="1" name="bobot" class="form-control" id="bobot" value="{{ $penugasan->bobot }}" required>
            </div>
        </div>
        {{-- bentuk evaluasi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="evaluasi_id" class="form-label">Bentuk Evaluasi <span class="text-danger">(*)</span></label>
                <select
                    name="evaluasi_id"
                    id="evaluasi_id"
                    class="form-select"
                    required
                >
                    <option value="">-Pilih Evaluasi-</option>
                    @foreach ($evaluasis as $evaluasi)
                        <option value="{{ $evaluasi->id }}"
                            @selected($penugasan->evaluasi_id == $evaluasi->id)>
                            {{ $evaluasi->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- deskripsi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" rows="8" class="form-control" id="deskripsi">{{ $penugasan->deskripsi }}</textarea>
            </div>
        </div>
        <hr>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('mks.penugasans.index', $mk->id) }}" class="btn btn-outline-secondary btn-sm float-end"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($penugasan->id)
<form id="delete-form" action="{{ route('mks.penugasans.destroy',[$mk->id,$penugasan->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus  {{ $mk->kode }}: {{ $penugasan->nama }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>
@endpush
