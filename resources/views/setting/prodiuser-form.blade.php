@extends('layouts.setting-form')

@push('header')
    {{ $prodiuser->id ? 'Edit' : 'Tambah' }} @isset($header) {{ $header }} @endisset
    @if ($prodiuser->id)
        <form id="delete-form" action="{{ route('prodiusers.destroy',$prodiuser->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $prodiuser->name }}?');">
                {{ __('delete') }}
            </button>
        </form>
    @endif
@endpush

@push('body')
<form id="formAction" action="{{ $prodiuser->id ? route('prodiusers.update',$prodiuser->id) : route('prodiusers.store') }}" method="post">
    @csrf
    @if ($prodiuser->id)
        @method('PUT')
    @endif
    <div class="row mb-3">
        <input type="hidden" name="prodi_id" value="{{ $prodi->id }}">
        <label for="user_id" class="col-md-4 col-form-label text-md-end">Pilih User</label>
        <div class="col-md-8">
            <select id="user_id" class="form-control @error('user_id') is-invalid @enderror" name="user_id" required>
                @if (!$prodiuser->id)
                <option value="">-- Pilih user --</option>
                @endif
                @foreach ($users as $user)
                <option value="{{ $user->id }}" @checked($user->id==$prodiuser->user_id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
            <a href="{{ route('prodis.prodiusers.index',$prodi->id) }}" class="btn btn-outline-secondary btn-sm">Close</a>
        </div>
    </div>
</form>
@endpush
