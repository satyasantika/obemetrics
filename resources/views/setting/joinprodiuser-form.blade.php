@extends('layouts.setting-form')

@push('header')
    {{ $joinprodiuser->id ? 'Edit' : 'Tambah' }} Data Pengelola Program Studi {{ $prodi->jenjang.' '.$prodi->nama }},
    <a href="{{ route('prodis.joinprodiusers.index',$prodi->id) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')
<form id="formAction" action="{{ $joinprodiuser->id ? route('prodis.joinprodiusers.update',[$prodi->id,$joinprodiuser->id]) : route('prodis.joinprodiusers.store',$prodi->id) }}" method="post">
    @csrf
    @if ($joinprodiuser->id)
        @method('PUT')
    @endif
    {{-- Pilihan User --}}
    <div class="row mb-3">
        <input type="hidden" name="prodi_id" value="{{ $prodi->id }}">
        <label for="user_id" class="col-md-4 col-form-label text-md-end">Pilih User</label>
        <div class="col-md-8">
            <select id="user_id" class="form-control @error('user_id') is-invalid @enderror" name="user_id" required>
                @if (!$joinprodiuser->id)
                <option value="">-- Pilih user --</option>
                @endif
                @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected($user->id==$joinprodiuser->user_id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    {{-- Status (deskripsi) user --}}
    <div class="row mb-3">
        <label for="status" class="col-md-4 col-form-label text-md-end">status Kantor</label>
        <div class="col-md-8">
            <textarea name="status" rows="3" class="form-control" id="status">{{ $joinprodiuser->status }}</textarea>
        </div>
    </div>
    {{-- submit Button --}}
    <div class="row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
            <a href="{{ route('prodis.joinprodiusers.index',$prodi->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
        </div>
    </div>
</form>
@if ($joinprodiuser->id)
    <form id="delete-form" action="{{ route('prodis.joinprodiusers.destroy',[$prodi->id,$joinprodiuser->id]) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" for="delete-form" onclick="return confirm('Yakin akan menghapus {{ $joinprodiuser->name }}?');" class="btn btn-outline-danger btn-sm float-end">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@endif

@endpush
