@extends('layouts.setting-form')

@push('header')
    {{ $mk->id ? 'Edit' : 'Tambah' }} Data Capaian Pembelajaran Mata Kuliah (CPMK)
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
    {{-- form BK --}}
    <form id="formAction" action="{{ $cpmk->id ? route('mks.cpmks.update',[$mk->id,$cpmk->id]) : route('mks.cpmks.store', $mk) }}" method="post">
        @csrf
        @if ($cpmk->id)
            @method('PUT')
        @endif
        <input type="hidden" name="mk_id" value="{{ $mk->id }}">

        {{-- kode cpmk --}}
        <div class="row mb-3">
            <div class="col">
                <label for="kode" class="form-label"><strong>Kode</strong> CPMK <span class="text-danger">(*)</span></label>
                <input type="text" placeholder="" value="{{ $cpmk->kode }}" name="kode" class="form-control" id="kode">
            </div>
        </div>
        {{-- nama --}}
        <div class="row mb-3">
            <div class="col">
                <label for="nama" class="form-label"><strong>Nama</strong> CPMK <span class="text-danger">(*)</span></label>
                <textarea name="nama" rows="3" class="form-control" id="nama" required>{{ $cpmk->nama }}</textarea>
            </div>
        </div>
        {{-- deskripsi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" rows="8" class="form-control" id="deskripsi">{{ $cpmk->deskripsi }}</textarea>
            </div>
        </div>
        <hr>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('mks.cpmks.index', $mk) }}" class="btn btn-outline-secondary btn-sm float-end"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($cpmk->id)
<form id="delete-form" action="{{ route('mks.cpmks.destroy',[$mk->id,$cpmk->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $mk->name }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>
@endpush
