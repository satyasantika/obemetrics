@extends('layouts.setting-form')

@push('header')
    {{ $kurikulum->id ? 'Edit' : 'Tambah' }} {{ $header }}
    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')

<div class="card-body">
    {{-- identitas kurikulum --}}
    @if ($kurikulum->id)
        <div class="row">
            <div class="col-md-3">Kurikulum</div>
            <div class="col"><strong>{{ $kurikulum->nama }}</strong></div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-3">Program Studi</div>
        <div class="col"><strong>{{ $prodi->jenjang }} {{ $prodi->nama }}</strong></div>
    </div>
    <hr>
    {{-- form KURIKULUM --}}
    <form id="formAction" action="{{ $kurikulum->id ? route('prodis.kurikulums.update',[$prodi->id,$kurikulum->id]) : route('prodis.kurikulums.store', $prodi) }}" method="post">
        @csrf
        @if ($kurikulum->id)
            @method('PUT')
        @endif
        <input type="hidden" name="prodi_id" value="{{ $prodi->id }}">

        {{-- nama --}}
        <div class="row mb-3">
            <div class="col">
                <label for="nama" class="form-label">Nama Kurikulum <span class="text-danger">(*)</span></label>
                <input type="text" placeholder="" value="{{ $kurikulum->nama }}" name="nama" class="form-control" id="nama" required autofocus>
            </div>
        </div>
        {{-- kode kurikulum --}}
        <div class="row mb-3">
            <div class="col">
                <label for="kode" class="form-label">Kode Kurikulum <span class="text-danger">(*)</span></label>
                <input type="text" placeholder="" value="{{ $kurikulum->kode }}" name="kode" class="form-control" id="kode">
            </div>
        </div>
        {{-- deksripsi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" rows="8" class="form-control" id="deskripsi">{{ $kurikulum->deskripsi }}</textarea>
            </div>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm float-end"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($kurikulum->id)
<form id="delete-form" action="{{ route('prodis.kurikulums.destroy',[$prodi->id,$kurikulum->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus  {{ $kurikulum->kode }}: {{ $kurikulum->nama }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>
@endpush
