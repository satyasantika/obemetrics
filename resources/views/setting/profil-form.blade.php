@extends('layouts.setting-form')

@push('header')
    {{ $profil->id ? 'Edit' : 'Tambah' }} Data Profil Lulusan
    <a href="{{ route('kurikulums.profils.index', $kurikulum) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')

<div class="card-body">
    {{-- identitas kurikulum --}}
    <div class="row">
        <div class="col-md-3">Kurikulum</div>
        <div class="col"><strong>{{ $kurikulum->nama }}</strong></div>
    </div>
    <div class="row">
        <div class="col-md-3">Program Studi</div>
        <div class="col"><strong>{{ $kurikulum->prodi->jenjang }} {{ $kurikulum->prodi->nama }}</strong></div>
    </div>
    <hr>
    {{-- form PROFIL --}}
    <form id="formAction" action="{{ $profil->id ? route('kurikulums.profils.update',[$kurikulum->id,$profil->id]) : route('kurikulums.profils.store', $kurikulum) }}" method="post">
        @csrf
        @if ($profil->id)
            @method('PUT')
        @endif
        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">

        {{-- nama --}}
        <div class="row mb-3">
            <label for="nama" class="form-label">Nama Profil <span class="text-danger">(*)</span></label>
            <div class="col">
                <input type="text" placeholder="" value="{{ $profil->nama }}" name="nama" class="form-control" id="nama" required autofocus>
            </div>
        </div>
        {{-- deksripsi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" rows="8" class="form-control" id="deskripsi">{{ $profil->deskripsi }}</textarea>
            </div>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('kurikulums.profils.index', $kurikulum) }}" class="btn btn-outline-secondary btn-sm float-end"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($profil->id)
<form id="delete-form" action="{{ route('kurikulums.profils.destroy',[$kurikulum->id,$profil->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $profil->nama }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>
@endpush
