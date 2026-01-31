@extends('layouts.setting-form')

@push('header')
    {{ $metode->id ? 'Edit' : 'Tambah' }} {{ $header }}
    <a href="{{ route('metodes.index') }}" class="btn btn-primary btn-sm float-end">kembali</a>
@endpush

@push('body')

<form id="formAction" action="{{ $metode->id ? route('metodes.update',$metode->id) : route('metodes.store') }}" method="post">
    @csrf
    @if ($metode->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- nama --}}
        <div class="row mb-3">
            <label for="nama" class="col-md-4 col-form-label text-md-end">Nama Metode</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $metode->nama }}" name="nama" class="form-control" id="nama" required autofocus>
            </div>
        </div>
        {{-- deskripsi --}}
        <div class="row mb-3">
            <label for="deskripsi" class="col-md-4 col-form-label text-md-end">Deskripsi</label>
            <div class="col-md-8">
                <textarea name="deskripsi" rows="3" class="form-control" id="deskripsi">{{ $metode->deskripsi }}</textarea>
            </div>
        </div>

        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
            <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('metodes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </div>
</form>

@if ($metode->id)
<div class="col">
    <form id="delete-form" action="{{ route('metodes.destroy',$metode->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <hr>
        <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $metode->nama }}?');">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>
@endif

@endpush
