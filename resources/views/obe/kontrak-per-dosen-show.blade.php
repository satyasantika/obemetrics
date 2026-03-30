@extends('layouts.panel')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <x-obe.header
                    title="Detail Kontrak MK"
                    subtitle="Informasi lengkap kontrak mahasiswa"
                    icon="bi bi-file-earmark-text" />

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">NIM</h6>
                            <p class="mb-0">{{ $kontrakMk->mahasiswa->nim }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Nama Mahasiswa</h6>
                            <p class="mb-0">{{ $kontrakMk->mahasiswa->nama }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Program Studi</h6>
                            <p class="mb-0">{{ $kontrakMk->mahasiswa->prodi->jenjang }} - {{ $kontrakMk->mahasiswa->prodi->nama }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Kode MK</h6>
                            <p class="mb-0">{{ $kontrakMk->mk->kode }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Mata Kuliah</h6>
                            <p class="mb-0">{{ $kontrakMk->mk->nama }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Semester</h6>
                            <p class="mb-0">{{ $kontrakMk->semester->nama }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Kelas</h6>
                            <p class="mb-0">{{ $kontrakMk->kelas ?? '-' }}</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <a href="{{ route('dosen.kontrakmks.edit', $kontrakMk->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-2"></i>Edit
                        </a>
                        <a href="{{ route('dosen.kontrakmks.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
