@extends('layouts.setting-form')

@push('header')
    {{ $evaluasi->id ? 'Edit' : 'Tambah' }} {{ $header }}
    <a href="{{ route('evaluasis.index') }}" class="btn btn-primary btn-sm float-end">kembali</a>
@endpush

@push('body')

<form id="formAction" action="{{ $evaluasi->id ? route('evaluasis.update',$evaluasi->id) : route('evaluasis.store') }}" method="post">
    @csrf
    @if ($evaluasi->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- kode --}}
        <div class="row mb-3">
            <label for="kode" class="col-md-4 col-form-label text-md-end">Kode Evaluasi</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $evaluasi->kode }}" name="kode" class="form-control" id="kode" required autofocus>
            </div>
        </div>
        {{-- nama --}}
        <div class="row mb-3">
            <label for="nama" class="col-md-4 col-form-label text-md-end">Nama Evaluasi</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $evaluasi->nama }}" name="nama" class="form-control" id="nama" required>
            </div>
        </div>
        {{-- kategori --}}
        <div class="row mb-3">
            <label for="kategori" class="col-md-4 col-form-label text-md-end">Kategori</label>
            <div class="col-md-8">
                <select name="kategori" id="kategori" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Pengetahuan/Kognitif" @selected($evaluasi->kategori == 'Pengetahuan/Kognitif')>Pengetahuan/Kognitif</option>
                    <option value="Hasil Proyek" @selected($evaluasi->kategori == 'Hasil Proyek')>Hasil Proyek</option>
                    <option value="Aktivitas Partisipatif" @selected($evaluasi->kategori == 'Aktivitas Partisipatif')>Aktivitas Partisipatif</option>
                </select>
            </div>
        </div>
        {{-- deskripsi --}}
        <div class="row mb-3">
            <label for="deskripsi" class="col-md-4 col-form-label text-md-end">Deskripsi</label>
            <div class="col-md-8">
                <textarea name="deskripsi" rows="3" class="form-control" id="deskripsi">{{ $evaluasi->deskripsi }}</textarea>
            </div>
        </div>

        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
            <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('evaluasis.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </div>
</form>

@if ($evaluasi->id)
<div class="col">
    <form id="delete-form" action="{{ route('evaluasis.destroy',$evaluasi->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <hr>
        <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $evaluasi->nama }}?');">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>
@endif

@endpush
