@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Import Data User untuk Mata Kuliah
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                    @stack('header')
                </div>

                <div class="card-body">
                    @include('layouts.alert')
                    <form action="{{ route('setting.import.joinmkusers') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mt-3">
                            <div class="col">
                                <label for="file" class="form-label">File Upload <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.ods" required>
                                <small class="text-muted d-block mt-1">
                                    Unduh template: <a href="{{ route('setting.import.joinmkusers.template') }}">template-import-user-mata-kuliah.xlsx</a>
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
                        <span class="text-danger">(*) Wajib diisi.</span>
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
                    <span class="h5">Preview Data Join MK User @if(!empty($preview['filename']))({{ $preview['filename'] }})@endif</span>
                    <form action="{{ route('setting.import.joinmkusers.clear') }}" method="POST" class="float-end" style="display: inline;">
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
                    <form action="{{ route('setting.import.joinmkusers.commit') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered" id="preview-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">Pilih</th>
                                        <th>Kode Semester</th>
                                        <th>Kode Mata Kuliah</th>
                                        <th>Nama Mata Kuliah</th>
                                        <th>NIDN</th>
                                        <th>Nama Dosen</th>
                                        <th>Koordinator</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview['rows'] as $index => $row)
                                        @php
                                            $hasError = !$row['semester_exists'] || !$row['mk_exists'] || !$row['dosen_exists'];
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
                                            <td>{{ $row['kode_mk'] }}</td>
                                            <td>{{ $row['nama_mata_kuliah'] }}</td>
                                            <td>{{ $row['nidn'] }}</td>
                                            <td>{{ $row['nama_dosen'] }}</td>
                                            <td>{{ $row['koordinator'] ?: '-' }}</td>
                                            <td>
                                                @if (!$row['semester_exists'])
                                                    <span class="badge bg-danger">Semester tidak ditemukan</span>
                                                @elseif (!$row['mk_exists'])
                                                    <span class="badge bg-danger">Mata Kuliah tidak ditemukan</span>
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
