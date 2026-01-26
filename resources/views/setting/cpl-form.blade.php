@extends('layouts.setting-form')

@push('header')
    {{ $cpl->id ? 'Edit' : 'Tambah' }} Data CPL</strong>
    <a href="{{ route('kurikulums.cpls.index', $kurikulum) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
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
    {{-- form CPL --}}
    <form id="formAction" action="{{ $cpl->id ? route('kurikulums.cpls.update',[$kurikulum->id,$cpl->id]) : route('kurikulums.cpls.store', $kurikulum) }}" method="post">
        @csrf
        @if ($cpl->id)
            @method('PUT')
        @endif
        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">

        {{-- kode --}}
        <div class="row mb-3 p-2">
            <label for="kode" class="form-label">Kode CPL <span class="text-danger">(*)</span></label>
            <input type="text" placeholder="" value="{{ $cpl->kode }}" name="kode" class="form-control" id="kode" required autofocus>
        </div>
        {{-- nama --}}
        <div class="row mb-3 p-2">
            <label for="nama" class="form-label">Nama CPL <span class="text-danger">(*)</span></label>
            <textarea name="nama" rows="12" class="form-control" id="nama">{{ $cpl->nama }}</textarea>
        </div>
        {{-- cakupan --}}
        <div class="row mb-3 p-2">
            <label for="cakupan" class="form-label">Cakupan CPL <span class="text-danger">(*)</span></label>
            <select name="cakupan" class="form-select" id="cakupan">
                <option value="">- Pilih Cakupan -</option>
                <option value="Universitas" @selected($cpl->cakupan == 'Universitas')>Universitas</option>
                <option value="Fakultas" @selected($cpl->cakupan == 'Fakultas')>Fakultas</option>
                <option value="Program Studi" @selected($cpl->cakupan == 'Program Studi')>Program Studi</option>
            </select>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('kurikulums.cpls.index', $kurikulum) }}" class="btn btn-outline-secondary btn-sm float-end"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($cpl->id)
<form id="delete-form" action="{{ route('kurikulums.cpls.destroy',[$kurikulum->id,$cpl->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $cpl->name }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>
@endpush
