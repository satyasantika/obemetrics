@extends('layouts.setting-form')

@push('header')
    {{ $prodiuser->id ? 'Edit' : 'Tambah' }} Data Pengelola Program Studi {{ $prodi->jenjang.' '.$prodi->nama }}
@endpush

@push('body')
@php
    $prodiKurikulumIds = $prodi->kurikulums->pluck('id');
    $mkIds = $prodi->kurikulums->pluck('mks')->flatten()->pluck('id');
    $joinProdiLocked = $prodiuser->id
        ? (\App\Models\JoinMkUser::where('user_id', $prodiuser->user_id)->whereIn('kurikulum_id', $prodiKurikulumIds)->exists()
            || \App\Models\KontrakMk::where('user_id', $prodiuser->user_id)->whereIn('mk_id', $mkIds)->exists())
        : false;
@endphp
<form id="formAction" action="{{ $prodiuser->id ? route('prodis.prodiusers.update',[$prodi->id,$prodiuser->id]) : route('prodis.prodiusers.store',$prodi->id) }}" method="post">
    @csrf
    @if ($prodiuser->id)
        @method('PUT')
    @endif
    {{-- Pilihan User --}}
    <div class="row mb-3">
        <input type="hidden" name="prodi_id" value="{{ $prodi->id }}">
        <label for="user_id" class="col-md-4 col-form-label text-md-end">Pilih User</label>
        <div class="col-md-8">
            <select id="user_id" class="form-control @error('user_id') is-invalid @enderror" name="user_id" required @disabled($joinProdiLocked)>
                @if (!$prodiuser->id)
                <option value="">-- Pilih user --</option>
                @endif
                @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected($user->id==$prodiuser->user_id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    {{-- Status (deskripsi) user --}}
    <div class="row mb-3">
        <label for="status" class="col-md-4 col-form-label text-md-end">status Kantor</label>
        <div class="col-md-8">
            <textarea name="status" rows="3" class="form-control" id="status" @disabled($joinProdiLocked)>{{ $prodiuser->status }}</textarea>
        </div>
    </div>
    {{-- submit Button --}}
    <div class="row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" for="formAction" class="btn btn-success btn-sm" @disabled($joinProdiLocked)><i class="bi bi-save"></i> Save</button>
            <a href="{{ route('prodis.prodiusers.index',$prodi->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            @if ($joinProdiLocked)
                <span class="badge bg-secondary">Data digunakan, tidak dapat diedit</span>
            @endif
        </div>
    </div>
</form>
@if ($prodiuser->id && !$joinProdiLocked)
    <form id="delete-form" action="{{ route('prodis.prodiusers.destroy',[$prodi->id,$prodiuser->id]) }}" method="POST">
        @csrf
        @method('DELETE')
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $prodiuser->user->name }} dari prodi {{ $prodiuser->prodi->nama }}?');">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@elseif ($prodiuser->id)
    <span class="badge bg-secondary float-end">Data digunakan, tidak dapat dihapus</span>
@endif

@endpush
