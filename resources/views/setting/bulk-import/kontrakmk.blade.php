@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Import Data Kontrak Mata Kuliah
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                    @stack('header')
                </div>

                <div class="card-body">
                    @include('layouts.alert')
                    <form action="{{ route('setting.import.kontrakmks') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mt-3">
                            <div class="col-md-3 text-end">
                                Semester <span class="text-danger">*</span>
                            </div>
                            <div class="col">
                                <select name="semester_id" class="form-control" required>
                                    <option value="">-Pilih Semester-</option>
                                    @foreach ($semesters as $semester)
                                        <option value="{{ $semester->id }}" @selected(old('semester_id', $preview['semester_id'] ?? '') == $semester->id)>
                                            {{ $semester->kode }} - {{ $semester->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3 text-end">
                                File Upload <span class="text-danger">*</span>
                            </div>
                            <div class="col">
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.ods" required>
                                <small class="text-muted d-block mt-1">
                                    Unduh template: <a href="{{ route('setting.import.kontrakmks.template') }}">template-import-kontrakmk.xlsx</a>
                                </small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3"></div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload"></i> Upload &amp; Preview
                                </button>
                            </div>
                        </div>
                        <span class="text-danger">(*) Wajib diisi.</span></label>
                    </form>
                </div>


            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col">

            @if (!empty($preview['rows']))
            <div class="card mt-3">
                <div class="card-header">
                    <span class="h5">Preview Data Kontrak MK @if(!empty($preview['filename']))({{ $preview['filename'] }})@endif</span>
                    <form action="{{ route('setting.import.kontrakmks.clear') }}" method="POST" class="float-end" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus preview data?');">
                            <i class="bi bi-x-circle"></i> Kosongkan Preview
                        </button>
                    </form>
                </div>
                <div class="card-body border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <input type="checkbox" id="select-all" class="form-check-input">
                            <label for="select-all" class="form-check-label">Pilih semua</label>
                        </div>
                    </div>
                    <form action="{{ route('setting.import.kontrakmks.commit') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered" id="preview-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">Pilih</th>
                                        <th>Kode Semester</th>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Kode MK</th>
                                        <th>NIDN</th>
                                        <th>Nama Dosen</th>
                                        <th>Kelas</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview['rows'] as $index => $row)
                                        @php
                                            $hasError = !$row['mahasiswa_exists'] || !$row['mk_exists'] || !$row['dosen_exists'];
                                            $rowClass = $hasError ? 'table-danger' : ($row['exists'] ? 'table-warning' : '');
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    name="selected[]"
                                                    value="{{ $index }}"
                                                    class="form-check-input row-check"
                                                    @checked(!$row['exists'] && !$hasError)
                                                    @disabled($hasError)
                                                >
                                            </td>
                                            <td>{{ $row['kode_semester'] }}</td>
                                            <td>{{ $row['nim'] }}</td>
                                            <td>{{ $row['nama_mahasiswa'] }}</td>
                                            <td>{{ $row['kode_mk'] }}</td>
                                            <td>{{ $row['nidn'] }}</td>
                                            <td>{{ $row['nama_dosen'] }}</td>
                                            <td>{{ $row['kelas'] }}</td>
                                            <td>
                                                @if (!$row['mahasiswa_exists'])
                                                    <span class="badge bg-danger">Mahasiswa tidak ditemukan</span>
                                                @elseif (!$row['mk_exists'])
                                                    <span class="badge bg-danger">MK tidak ditemukan</span>
                                                @elseif (!$row['dosen_exists'])
                                                    <span class="badge bg-danger">Dosen tidak ditemukan</span>
                                                @elseif ($row['exists'])
                                                    <span class="badge bg-warning text-dark">Sudah ada</span>
                                                @else
                                                    <span class="badge bg-success">Baru</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-save"></i> Simpan Data Terpilih
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const rowChecks = document.querySelectorAll('.row-check:not([disabled])');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowChecks.forEach(function (chk) {
                chk.checked = selectAll.checked;
            });
        });
    }

    if (window.jQuery && $.fn.DataTable && document.getElementById('preview-table')) {
        $('#preview-table').DataTable({
            pageLength: 10,
            order: [],
        });
    }
});
</script>
@endpush
