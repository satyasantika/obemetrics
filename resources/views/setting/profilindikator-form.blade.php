@extends('layouts.setting-form')

@push('header')
    {{ $profilindikator->id ? 'Edit' : 'Tambah' }} Data Indikator Profil Lulusan
    <a href="{{ route('kurikulums.profils.index', $profil->kurikulum) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')

<div class="card-body">
    {{-- identitas kurikulum --}}
    <div class="row">
        <div class="col-md-3">Profil</div>
        <div class="col"><strong>{{ $profil->nama }}</strong></div>
    </div>
    <div class="row">
        <div class="col-md-3">Kurikulum</div>
        <div class="col"><strong>{{ $profil->kurikulum->nama }}</strong></div>
    </div>
    <div class="row">
        <div class="col-md-3">Program Studi</div>
        <div class="col"><strong>{{ $profil->kurikulum->prodi->jenjang }} {{ $profil->kurikulum->prodi->nama }}</strong></div>
    </div>
    <hr>
    {{-- form PROFIL INDIKATOR --}}
    <form id="formAction" action="{{ $profilindikator->id ? route('profils.profilindikators.update',[$profil->id,$profilindikator->id]) : route('profils.profilindikators.store', $profil) }}" method="post">
        @csrf
        @if ($profilindikator->id)
            @method('PUT')
        @endif
        <input type="hidden" name="profil_id" value="{{ $profil->id }}">

        {{-- nama --}}
        <div class="row mb-3">
            <div class="col">
                <label for="nama" class="form-label">Nama Indikator <span class="text-danger">(*)</span></label>
                <input type="text" placeholder="" value="{{ $profilindikator->nama }}" name="nama" class="form-control" id="nama" required autofocus>
            </div>
        </div>
        {{-- deksripsi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" rows="12" class="form-control" id="deskripsi">{{ $profilindikator->deskripsi }}</textarea>
            </div>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('kurikulums.profils.index', $profil->kurikulum) }}" class="btn btn-outline-secondary btn-sm float-end"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($profilindikator->id)
<form id="delete-form" action="{{ route('profils.profilindikators.destroy',[$profil->id,$profilindikator->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus indikator: {{ $profilindikator->nama }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>

@endpush
