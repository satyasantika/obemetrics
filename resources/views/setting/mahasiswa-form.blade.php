@extends('layouts.setting-form')

@push('header')
    {{ $mahasiswa->id ? 'Edit' : 'Tambah' }} {{ $header }}
@endpush

@push('body')

<form id="formAction" action="{{ $mahasiswa->id ? route('mahasiswas.update',$mahasiswa->id) : route('mahasiswas.store') }}" method="post">
    @csrf
    @if ($mahasiswa->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- Nama Lengkap --}}
        <div class="row mb-3">
            <label for="nama" class="col-md-4 col-form-label text-md-end">Nama Lengkap <span class="text-danger">(*)</span></label>
            <div class="col-md-8">
                <input type="text" placeholder="Nama Lengkap" value="{{ $mahasiswa->nama }}" name="nama" class="form-control" id="nama" required autofocus>
            </div>
        </div>
        {{-- NIM --}}
        <div class="row mb-3">
            <label for="nim" class="col-md-4 col-form-label text-md-end">NIM <span class="text-danger">(*)</span></label>
            <div class="col-md-8">
                <input type="text" placeholder="nim" value="{{ $mahasiswa->nim }}" name="nim" class="form-control" id="nim" required>
            </div>
        </div>
        {{-- Angkatan --}}
        <div class="row mb-3">
            <label for="angkatan" class="col-md-4 col-form-label text-md-end">Angkatan</label>
            <div class="col-md-8">
                <select name="angkatan" id="angkatan" class="form-control">
                    @for ($year = date('Y'); $year >= date('Y') - 10; $year--)
                        <option value="{{ $year }}" @selected($mahasiswa->angkatan == $year)>{{ $year }}</option>
                    @endfor
                </select>
            </div>
        </div>
        {{-- Program Studi --}}
        <div class="row mb-3">
            <label for="prodi_id" class="col-md-4 col-form-label text-md-end">Program Studi</label>
            <div class="col-md-8">
                <select name="prodi_id" id="prodi_id" class="form-control">
                    @forelse ($prodis as $prodi)
                        <option value="{{ $prodi->id }}" @selected($mahasiswa->prodi_id == $prodi->id)>{{ $prodi->jenjang }} - {{ $prodi->nama }}</option>
                    @empty
                        <option value="">-Belum ada data Program Studi-</option>
                    @endforelse
                </select>
            </div>
        </div>
        {{-- Email --}}
        <div class="row mb-3">
            <label for="email" class="col-md-4 col-form-label text-md-end">Alamat Email</label>
            <div class="col-md-8">
                <input type="email" placeholder="email" value="{{ $mahasiswa->email }}" name="email" class="form-control" id="email">
            </div>
        </div>
        {{-- Phone --}}
        <div class="row mb-3">
            <label for="phone" class="col-md-4 col-form-label text-md-end">no. WA aktif</label>
            <div class="col-md-8">
                <input type="text" placeholder="phone" value="{{ $mahasiswa->phone }}" name="phone" class="form-control" id="phone">
            </div>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('mahasiswas.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </div>
</form>

@if ($mahasiswa->id)
    <form id="delete-form" action="{{ route('mahasiswas.destroy',$mahasiswa->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $mahasiswa->name }}?');">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@endif

@endpush
