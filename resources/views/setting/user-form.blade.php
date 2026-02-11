@extends('layouts.setting-form')

@push('header')
    {{ $user->id ? 'Edit' : 'Tambah' }} {{ $header }}
@endpush

@push('body')

<form id="formAction" action="{{ $user->id ? route('users.update',$user->id) : route('users.store') }}" method="post">
    @csrf
    @if ($user->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- Nama Lengkap --}}
        <div class="row mb-3">
            <label for="name" class="col-md-4 col-form-label text-md-end">Nama Lengkap</label>
            <div class="col-md-8">
                <input type="text" placeholder="Nama Lengkap (bergelar - bila ada)" value="{{ $user->name }}" name="name" class="form-control" id="name" required autofocus>
            </div>
        </div>
        {{-- Username --}}
        <div class="row mb-3">
            <label for="username" class="col-md-4 col-form-label text-md-end">Username</label>
            <div class="col-md-8">
                <input type="text" placeholder="username" value="{{ $user->username }}" name="username" class="form-control" id="username" required>
            </div>
        </div>
        {{-- Email --}}
        <div class="row mb-3">
            <label for="email" class="col-md-4 col-form-label text-md-end">Alamat Email</label>
            <div class="col-md-8">
                <input type="email" placeholder="email" value="{{ $user->email }}" name="email" class="form-control" id="email" required>
            </div>
        </div>
        {{-- Password --}}
        <div class="row mb-3">
            <label for="password" class="col-md-4 col-form-label text-md-end">Password</label>
            <div class="col-md-8">
                @if ($user->id)
                    <a class="btn btn-warning btn-sm" href="#" onclick="event.preventDefault(); if (confirm('yakin direset?')){ document.getElementById('formReset').submit(); }">
                        {{ __('Reset') }}
                    </a>

                    <input type="hidden" value="{{ $user->password }}" name="password" class="form-control" id="password">
                     tobe: pass = username
                @else
                    <input type="password" placeholder="password" value="{{ $user->password }}" name="password" class="form-control" id="password" required>
                @endif
            </div>
        </div>
        {{-- Phone --}}
        <div class="row mb-3">
            <label for="phone" class="col-md-4 col-form-label text-md-end">no. WA aktif</label>
            <div class="col-md-8">
                <input type="text" placeholder="phone" value="{{ $user->phone }}" name="phone" class="form-control" id="phone">
            </div>
        </div>
        {{-- NIDN --}}
        <div class="row mb-3">
            <label for="nidn" class="col-md-4 col-form-label text-md-end">NIDN</label>
            <div class="col-md-8">
                <input type="text" placeholder="NIDN" value="{{ $user->nidn }}" name="nidn" class="form-control" id="nidn">
                <small class="text-muted">Nomor Induk Dosen Nasional</small>
            </div>
        </div>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </div>
</form>

@if ($user->id)
<form id="formReset" action="{{ route('users.resetpassword',$user->id) }}" method="POST" class="d-none">
    @csrf
</form>
@endif

@if ($user->id)
    <form id="delete-form" action="{{ route('users.destroy',$user->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $user->name }}?');">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@endif

@endpush
