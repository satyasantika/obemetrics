@extends('layouts.setting-form')

@push('header')
    {{ $kurikulum->id ? 'Edit' : 'Tambah' }} Data Mata Kuliah
    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
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
    {{-- form BK --}}
    <form id="formAction" action="{{ $mk->id ? route('kurikulums.mks.update',[$kurikulum->id,$mk->id]) : route('kurikulums.mks.store', $kurikulum) }}" method="post">
        @csrf
        @if ($mk->id)
            @method('PUT')
        @endif
        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">

        <div class="row mb-3">
            {{-- semester --}}
            <div class="col">
                <label for="semester" class="form-label">Semester <span class="text-danger">(*)</span></label>
                <select name="semester" class="form-select col" id="semester">
                    <option value="">- Pilih Semester -</option>
                    @for ($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" @selected($mk->semester == $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            {{-- kode kurikulum --}}
            <div class="col">
                <label for="kode" class="form-label"><strong>Kode</strong> Mata Kuliah <span class="text-danger">(*)</span></label>
                <input type="text" placeholder="" value="{{ $mk->kode }}" name="kode" class="form-control" id="kode">
            </div>
        </div>
        {{-- nama --}}
        <div class="row mb-3">
            <div class="col">
                <label for="nama" class="form-label"><strong>Nama</strong> Mata Kuliah <span class="text-danger">(*)</span></label>
                <input type="text" placeholder="" value="{{ $mk->nama }}" name="nama" class="form-control" id="nama" required autofocus>
            </div>
        </div>
        <div class="row mb-3">
            {{-- sks_teori --}}
            <div class="col">
            <label for="sks_teori" class="form-label">SKS Teori</label>
            <input type="number" min="0" max="6" placeholder="" value="{{ $mk->sks_teori ?? 0 }}" name="sks_teori" class="form-control" id="sks_teori">
            </div>
            {{-- sks_praktik --}}
            <div class="col">
            <label for="sks_praktik" class="form-label">SKS Praktikum</label>
            <input type="number" min="0" max="6" placeholder="" value="{{ $mk->sks_praktik ?? 0 }}" name="sks_praktik" class="form-control" id="sks_praktik">
            </div>
            {{-- sks_lapangan --}}
            <div class="col">
            <label for="sks_lapangan" class="form-label">SKS Lapangan</label>
            <input type="number" min="0" max="6" placeholder="" value="{{ $mk->sks_lapangan ?? 0 }}" name="sks_lapangan" class="form-control" id="sks_lapangan">
            </div>
        </div>
        {{-- deskripsi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" rows="8" class="form-control" id="deskripsi">{{ $mk->deskripsi }}</textarea>
            </div>
        </div>
        <hr>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('kurikulums.mks.index', $kurikulum) }}" class="btn btn-outline-secondary btn-sm float-end"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($mk->id)
<form id="delete-form" action="{{ route('kurikulums.mks.destroy',[$kurikulum->id,$mk->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus  {{ $mk->kode }}: {{ $mk->nama }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>
@endpush
