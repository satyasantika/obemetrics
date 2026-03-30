@extends('layouts.panel')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <x-obe.header
                    title="Edit Kontrak MK"
                    subtitle="Ubah informasi kontrak mahasiswa"
                    icon="bi bi-pencil" />

                <div class="card-body">
                    <form action="{{ route('dosen.kontrakmks.update', $kontrakMk->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">NIM</label>
                                    <input type="text" class="form-control" value="{{ $kontrakMk->mahasiswa->nim }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Nama Mahasiswa</label>
                                    <input type="text" class="form-control" value="{{ $kontrakMk->mahasiswa->nama }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Program Studi</label>
                                    <input type="text" class="form-control" value="{{ $kontrakMk->mahasiswa->prodi->jenjang }} - {{ $kontrakMk->mahasiswa->prodi->nama }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Kode MK</label>
                                    <input type="text" class="form-control" value="{{ $kontrakMk->mk->kode }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Mata Kuliah</label>
                                    <input type="text" class="form-control" value="{{ $kontrakMk->mk->nama }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Semester</label>
                                    <input type="text" class="form-control" value="{{ $kontrakMk->semester->nama }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-group">
                                <label for="kelas" class="form-label">Kelas <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('kelas') is-invalid @enderror"
                                    id="kelas"
                                    name="kelas"
                                    value="{{ old('kelas', $kontrakMk->kelas) }}"
                                    required
                                    maxlength="10"
                                    placeholder="Masukkan kelas (misal: A, B, C)">
                                @error('kelas')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check me-2"></i>Simpan Perubahan
                            </button>
                            <a href="{{ route('dosen.kontrakmks.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
