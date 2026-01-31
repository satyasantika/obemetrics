@extends('layouts.setting-form')

@push('header')
    {{ $mk->id ? 'Edit' : 'Tambah' }} Data Aktivitas Perkuliahan (Pertemuan)
    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')

<div class="card-body">
    {{-- identitas mk --}}
    <div class="row">
        <div class="col-md-3">Mata Kuliah</div>
        <div class="col"><strong>{{ $mk->nama }}</strong></div>
    </div>
    <div class="row">
        <div class="col-md-3">Kurikulum</div>
        <div class="col"><strong>{{ $mk->kurikulum->nama }}</strong></div>
    </div>
    <div class="row">
        <div class="col-md-3">Program Studi</div>
        <div class="col"><strong>{{ $mk->kurikulum->prodi->jenjang }} {{ $mk->kurikulum->prodi->nama }}</strong></div>
    </div>
    <hr>
    {{-- form Pertemuan --}}
    <form id="formAction" action="{{ $pertemuan->id ? route('mks.pertemuans.update',[$mk->id,$pertemuan->id]) : route('mks.pertemuans.store', $mk) }}" method="post">
        @csrf
        @if ($pertemuan->id)
            @method('PUT')
        @endif
        <input type="hidden" name="mk_id" value="{{ $mk->id }}">
        {{-- <input type="hidden" name="semester_id" value="{{ $pertemuan->semester->id }}"> --}}


        {{-- pertemuan ke --}}
        <div class="row mb-3">
            <div class="col">
                <label for="ke" class="form-label">Pertemuan ke-<span class="text-danger">(*)</span></label>
                <select name="ke" id="ke" class="form-control" required>
                    <option value="">Pilih Pertemuan</option>
                    <option value="1" @selected($pertemuan->ke == '1')>Pertemuan ke-1</option>
                    <option value="2" @selected($pertemuan->ke == '2')>Pertemuan ke-2</option>
                    <option value="3" @selected($pertemuan->ke == '3')>Pertemuan ke-3</option>
                    <option value="4" @selected($pertemuan->ke == '4')>Pertemuan ke-4</option>
                    <option value="5" @selected($pertemuan->ke == '5')>Pertemuan ke-5</option>
                    <option value="6" @selected($pertemuan->ke == '6')>Pertemuan ke-6</option>
                    <option value="7" @selected($pertemuan->ke == '7')>Pertemuan ke-7</option>
                    <option value="8" @selected($pertemuan->ke == '8')>Pertemuan ke-8</option>
                    <option value="9" @selected($pertemuan->ke == '9')>Pertemuan ke-9</option>
                    <option value="10" @selected($pertemuan->ke == '10')>Pertemuan ke-10</option>
                    <option value="11" @selected($pertemuan->ke == '11')>Pertemuan ke-11</option>
                    <option value="12" @selected($pertemuan->ke == '12')>Pertemuan ke-12</option>
                    <option value="13" @selected($pertemuan->ke == '13')>Pertemuan ke-13</option>
                    <option value="14" @selected($pertemuan->ke == '14')>Pertemuan ke-14</option>
                    <option value="15" @selected($pertemuan->ke == '15')>Pertemuan ke-15</option>
                    <option value="16" @selected($pertemuan->ke == '16')>Pertemuan ke-16</option>
                </select>
            </div>
        </div>

        {{-- kode subcpmk --}}
        <div class="row mb-3">
            <div class="col">
                <label for="subcpmk_id" class="form-label">Sub CPMK<span class="text-danger">(*)</span></label>
                <select name="subcpmk_id" id="subcpmk_id" class="form-control">
                    <option value="">Pilih Sub CPMK</option>
                    @foreach ($subcpmks as $subcpmk)
                        <option value="{{ $subcpmk->id }}" @selected($pertemuan->subcpmk_id == $subcpmk->id)>{{ $subcpmk->kode }} - {{ $subcpmk->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- materi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="materi" class="form-label">Materi<span class="text-danger">(*)</span></label>
                <textarea name="materi" rows="3" class="form-control" id="materi" required>{{ $pertemuan->materi }}</textarea>
            </div>
        </div>
        {{-- tanggal pelaksanaan --}}
        <div class="row mb-3">
            <div class="col">
                <label for="tanggal" class="form-label">Tanggal Pelaksanaan</span></label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ $pertemuan->tanggal }}">
            </div>
        </div>
        {{-- jam mulai dan selesai --}}
        <div class="row mb-3">
            <div class="col">
                <label for="jam_mulai" class="form-label">Jam Mulai</span></label>
                <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" value="{{ $pertemuan->jam_mulai }}">
            </div>
            <div class="col">
                <label for="jam_selesai" class="form-label">Jam Selesai</span></label>
                <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" value="{{ $pertemuan->jam_selesai }}">
            </div>
        </div>
        {{-- dokumentasi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="dokumentasi" class="form-label">Dokumentasi (link gambar/video)</label>
                <textarea name="dokumentasi" rows="3" class="form-control" id="dokumentasi">{{ $pertemuan->dokumentasi }}</textarea>
            </div>
        </div>
        {{-- keterangan --}}
        <div class="row mb-3">
            <div class="col">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea name="keterangan" rows="3" class="form-control" id="keterangan">{{ $pertemuan->keterangan }}</textarea>
            </div>
        </div>
        <hr>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('mks.pertemuans.index', $mk) }}" class="btn btn-outline-secondary btn-sm float-end"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($pertemuan->id)
<form id="delete-form" action="{{ route('mks.pertemuans.destroy',[$mk->id,$pertemuan->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $pertemuan->ke }}: {{ $pertemuan->materi }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>
@endpush
