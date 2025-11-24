@extends('layouts.setting-form')

@push('header')
    {{ $kurikulum->id ? 'Edit' : 'Tambah' }} {{ $header }}
    <a href="{{ route('kurikulums.index') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')

<div class="card-body">
    <form id="formAction" action="{{ $kurikulum->id ? route('kurikulums.update',$kurikulum->id) : route('kurikulums.store') }}" method="post">
        @csrf
        @if ($kurikulum->id)
            @method('PUT')
        @endif
        <input type="hidden" name="prodi_id" value="{{ $prodi_id }}">
        {{-- nama --}}
        <div class="row mb-3 p-2">
            <label for="nama" class="col-md-4 col-form-label text-md-end">Nama Kurikulum</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $kurikulum->nama }}" name="nama" class="form-control" id="nama" required autofocus>
            </div>
        </div>
        {{-- kode kurikulum --}}
        <div class="row mb-3 p-2">
            <label for="kode" class="col-md-4 col-form-label text-md-end">Kode Kurikulum</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $kurikulum->kode }}" name="kode" class="form-control" id="kode" required autofocus>
            </div>
        </div>
        {{-- deksripsi --}}
        <div class="row mb-3 p-2">
            <label for="deskripsi" class="col-md-4 col-form-label text-md-end">Deskripsi</label>
            <div class="col-md-8">
                <textarea name="deskripsi" rows="3" class="form-control" id="deskripsi" required>{{ $kurikulum->deskripsi }}</textarea>
            </div>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('kurikulums.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>
@if ($kurikulum->id)
<div class="col">
    <form id="delete-form" action="{{ route('kurikulums.destroy',$kurikulum->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <hr>
        <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $kurikulum->name }}?');">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>
@endif


@endpush
