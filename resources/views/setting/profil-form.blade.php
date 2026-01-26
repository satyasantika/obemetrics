@extends('layouts.setting-form')

@push('header')
    {{ $profil->id ? 'Edit' : 'Tambah' }} Data Profil Lulusan untuk <strong>{{ $kurikulum->nama }}</strong>
    <a href="{{ route('kurikulums.profils.index', $kurikulum) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')

<div class="card-body">
    <form id="formAction" action="{{ $profil->id ? route('kurikulums.profils.update',[$kurikulum->id,$profil->id]) : route('kurikulums.profils.store', $kurikulum) }}" method="post">
        @csrf
        @if ($profil->id)
            @method('PUT')
        @endif
        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">

        {{-- nama --}}
        <div class="row mb-3 p-2">
            <label for="nama" class="col-md-4 col-form-label text-md-end">Nama Profil <span class="text-danger">(*)</span></label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $profil->nama }}" name="nama" class="form-control" id="nama" required autofocus>
            </div>
        </div>
        {{-- deksripsi --}}
        <div class="row mb-3 p-2">
            <label for="deskripsi" class="col-md-4 col-form-label text-md-end">Deskripsi</label>
            <div class="col-md-8">
                <textarea name="deskripsi" rows="3" class="form-control" id="deskripsi">{{ $profil->deskripsi }}</textarea>
            </div>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('kurikulums.profils.index', $kurikulum) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($profil->id)
<form id="delete-form" action="{{ route('kurikulums.profils.destroy',[$kurikulum->id,$profil->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $profil->name }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>
@endpush
