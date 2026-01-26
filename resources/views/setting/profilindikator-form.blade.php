@extends('layouts.setting-form')

@push('header')
    {{ $profilindikator->id ? 'Edit' : 'Tambah' }} Data Indikator Profil Lulusan untuk <strong>{{ $profil->nama }}</strong>
    <a href="{{ route('kurikulums.profils.index', $profil->kurikulum) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')

<div class="card-body">
    <form id="formAction" action="{{ $profilindikator->id ? route('profils.profilindikators.update',[$profil->id,$profilindikator->id]) : route('profils.profilindikators.store', $profil) }}" method="post">
        @csrf
        @if ($profilindikator->id)
            @method('PUT')
        @endif
        <input type="hidden" name="profil_id" value="{{ $profil->id }}">

        {{-- nama --}}
        <div class="row mb-3 p-2">
            <label for="nama" class="col-md-4 col-form-label text-md-end">Nama Indikator <span class="text-danger">(*)</span></label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $profilindikator->nama }}" name="nama" class="form-control" id="nama" required autofocus>
            </div>
        </div>
        {{-- deksripsi --}}
        <div class="row mb-3 p-2">
            <label for="deskripsi" class="col-md-4 col-form-label text-md-end">Deskripsi</label>
            <div class="col-md-8">
                <textarea name="deskripsi" rows="3" class="form-control" id="deskripsi">{{ $profilindikator->deskripsi }}</textarea>
            </div>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('kurikulums.profils.index', $profil->kurikulum) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($profilindikator->id)
<form id="delete-form" action="{{ route('profils.profilindikators.destroy',[$profil->id,$profilindikator->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $profilindikator->name }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>

@endpush
