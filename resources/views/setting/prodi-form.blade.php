@extends('layouts.setting-form')

@push('header')
    {{ $prodi->id ? 'Edit' : 'Tambah' }} {{ $header }}
    <a href="{{ route('prodis.index') }}" class="btn btn-primary btn-sm float-end">kembali</a>
@endpush

@push('body')

<form id="formAction" action="{{ $prodi->id ? route('prodis.update',$prodi->id) : route('prodis.store') }}" method="post">
    @csrf
    @if ($prodi->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- kode UNSIL --}}
        <div class="row mb-3 bg-secondary text-white p-2">
            <label for="kode_unsil" class="col-md-4 col-form-label text-md-end">Kode Prodi (lokal)</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->kode_unsil }}" name="kode_unsil" class="form-control" id="kode_unsil" required autofocus>
            </div>
        </div>
        {{-- nama --}}
        <div class="row mb-3 bg-secondary text-white p-2">
            <label for="nama" class="col-md-4 col-form-label text-md-end">Nama Prodi</label>
            <div class="col-md-8">
                <textarea name="nama" rows="3" class="form-control" id="nama" required>{{ $prodi->nama }}</textarea>
            </div>
        </div>
        {{-- singkat --}}
        <div class="row mb-3 bg-secondary text-white p-2">
            <label for="singkat" class="col-md-4 col-form-label text-md-end">Singkatan</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->singkat }}" name="singkat" class="form-control" id="singkat" >
            </div>
        </div>
        {{-- mapel --}}
        <div class="row mb-3 bg-secondary text-white p-2">
            <label for="mapel" class="col-md-4 col-form-label text-md-end">Nama Mapel</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->mapel }}" name="mapel" class="form-control" id="mapel" >
            </div>
        </div>
        <hr>
        {{-- pt --}}
        <div class="row mb-3">
            <label for="pt" class="col-md-4 col-form-label text-md-end">Perguruan Tinggi</label>
            <div class="col-md-8">
                <textarea name="pt" rows="3" class="form-control" id="pt" required>{{ $prodi->pt }}</textarea>
            </div>
        </div>
        {{-- fakultas --}}
        <div class="row mb-3">
            <label for="fakultas" class="col-md-4 col-form-label text-md-end" required>Fakultas</label>
            <div class="col-md-8">
                <textarea name="fakultas" rows="3" class="form-control" id="fakultas">{{ $prodi->fakultas }}</textarea>
            </div>
        </div>
        {{-- kode Prodi --}}
        <div class="row mb-3">
            <label for="kode_prodi" class="col-md-4 col-form-label text-md-end">Kode Prodi di PDDIKTI</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->kode_prodi }}" name="kode_prodi" class="form-control" id="kode_prodi" >
            </div>
        </div>
        {{-- visi_misi --}}
        <div class="row mb-3">
            <label for="visi_misi" class="col-md-4 col-form-label text-md-end">Visi Keilmuan</label>
            <div class="col-md-8">
                <textarea name="visi_misi" rows="6" class="form-control" id="visi_misi">{{ $prodi->visi_misi }}</textarea>
            </div>
        </div>
        {{-- jenjang --}}
        <div class="row mb-3">
            <label for="jenjang" class="col-md-4 col-form-label text-md-end">Jenjang</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->jenjang }}" name="jenjang" class="form-control" id="jenjang" >
            </div>
        </div>
        {{-- gelar lulusan --}}
        <div class="row mb-3">
            <label for="gelar_lulusan" class="col-md-4 col-form-label text-md-end">Gelar Lulusan</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->gelar_lulusan }}" name="gelar_lulusan" class="form-control" id="gelar_lulusan" >
            </div>
        </div>
        {{-- alamat --}}
        <div class="row mb-3">
            <label for="alamat" class="col-md-4 col-form-label text-md-end">Alamat Kantor</label>
            <div class="col-md-8">
                <textarea name="alamat" rows="3" class="form-control" id="alamat">{{ $prodi->alamat }}</textarea>
            </div>
        </div>
        {{-- no_telepon --}}
        <div class="row mb-3">
            <label for="no_telepon" class="col-md-4 col-form-label text-md-end">Nomor Telepon</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->no_telepon }}" name="no_telepon" class="form-control" id="no_telepon" >
            </div>
        </div>
        {{-- email --}}
        <div class="row mb-3">
            <label for="email" class="col-md-4 col-form-label text-md-end">Alamat e-mail</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->email }}" name="email" class="form-control" id="email" >
            </div>
        </div>
        {{-- website --}}
        <div class="row mb-3">
            <label for="website" class="col-md-4 col-form-label text-md-end">Website</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->website }}" name="website" class="form-control" id="website" >
            </div>
        </div>
        {{-- tahun pendirian --}}
        <div class="row mb-3">
            <label for="tahun_pendirian" class="col-md-4 col-form-label text-md-end">Tahun Pendirian</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->tahun_pendirian }}" name="tahun_pendirian" class="form-control" id="tahun_pendirian" >
            </div>
        </div>
        {{-- sk pendirian --}}
        <div class="row mb-3">
            <label for="sk_pendirian" class="col-md-4 col-form-label text-md-end">SK Izin/SK Pendirian</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->sk_pendirian }}" name="sk_pendirian" class="form-control" id="sk_pendirian" >
            </div>
        </div>
        {{-- tahun akreditasi --}}
        <div class="row mb-3">
            <label for="tahun_akreditasi" class="col-md-4 col-form-label text-md-end">Tahun SK Akreditasi BAN PT atau LAM (SK terakhir)</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->tahun_akreditasi }}" name="tahun_akreditasi" class="form-control" id="tahun_akreditasi" >
            </div>
        </div>
        {{-- sk akreditasi --}}
        <div class="row mb-3">
            <label for="sk_akreditasi" class="col-md-4 col-form-label text-md-end">Nomor SK Akreditasi BAN PT atau LAM (SK terakhir)</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->sk_akreditasi }}" name="sk_akreditasi" class="form-control" id="sk_akreditasi" >
            </div>
        </div>
        {{-- tahun internasional --}}
        <div class="row mb-3">
            <label for="tahun_internasional" class="col-md-4 col-form-label text-md-end">Tahun SK Akreditasi/Sertifikat Internasional</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->tahun_internasional }}" name="tahun_internasional" class="form-control" id="tahun_internasional" >
            </div>
        </div>
        {{-- sk internasional --}}
        <div class="row mb-3">
            <label for="sk_internasional" class="col-md-4 col-form-label text-md-end">SK Akreditasi/Sertifikat Internasional</label>
            <div class="col-md-8">
                <input type="text" placeholder="" value="{{ $prodi->sk_internasional }}" name="sk_internasional" class="form-control" id="sk_internasional" >
            </div>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
            <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('prodis.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </div>
</form>

@if ($prodi->id)
<form id="formReset" action="{{ route('users.resetpassword',$prodi->id) }}" method="POST" class="d-none">
    @csrf
</form>
@endif
@if ($prodi->id)
<div class="col">
    <form id="delete-form" action="{{ route('prodis.destroy',$prodi->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <hr>
        <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $prodi->name }}?');">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>
@endif

@endpush
