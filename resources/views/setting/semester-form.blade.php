@extends('layouts.setting-form')

@push('header')
    {{ $semester->id ? 'Edit' : 'Tambah' }} {{ $header }}
    <a href="{{ route('semesters.index') }}" class="btn btn-primary btn-sm float-end">kembali</a>
@endpush

@push('body')

<form id="formAction" action="{{ $semester->id ? route('semesters.update',$semester->id) : route('semesters.store') }}" method="post">
    @csrf
    @if ($semester->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- kode semester --}}
        <div class="row mb-3">
            <label for="kode" class="col-md-4 col-form-label text-md-end">Kode Semester</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $semester->kode }}" name="kode" class="form-control" id="kode" required autofocus>
            </div>
        </div>
        {{-- nama --}}
        <div class="row mb-3">
            <label for="nama" class="col-md-4 col-form-label text-md-end">Nama Semester</label>
            <div class="col-md-8">
                <textarea name="nama" rows="3" class="form-control" id="nama" required>{{ $semester->nama }}</textarea>
            </div>
        </div>
        {{-- deskripsi --}}
        <div class="row mb-3">
            <label for="deskripsi" class="col-md-4 col-form-label text-md-end">Deskripsi</label>
            <div class="col-md-8">
                <textarea name="deskripsi" rows="3" class="form-control" id="deskripsi">{{ $semester->deskripsi }}</textarea>
            </div>
        </div>

        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
            <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('semesters.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </div>
</form>

@if ($semester->id)
<div class="col">
    <form id="delete-form" action="{{ route('semesters.destroy',$semester->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <hr>
        <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $semester->nama }}?');">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>
@endif

@endpush
