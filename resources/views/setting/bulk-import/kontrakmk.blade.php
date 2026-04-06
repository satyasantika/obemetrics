@extends('layouts.panel')

@section('content')
<div class="container">
    @if (isset($kurikulum) && $kurikulum)
        @include('components.identitas-kurikulum', ['kurikulum' => $kurikulum])
    @endif
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Import Data Kontrak Mata Kuliah
                    @stack('header')
                </div>

                <div class="card-body">
                     <form action="{{ route('settings.import.kontrakmks') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('kontrakmks.index') }}">
                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id ?? '' }}">

                        <div id="semester-alert" class="alert alert-warning py-2 px-3 d-flex align-items-center gap-2 @if(!empty($preview['semester_id'])) d-none @endif" role="alert">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                            <span>Pilih <strong>semester</strong> terlebih dahulu sebelum mengunduh template atau mengunggah file.</span>
                        </div>

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
                                <input type="file" name="file" id="file-input" class="form-control" accept=".csv,.xlsx,.ods" required
                                       @if(empty($preview['semester_id'])) disabled @endif>
                                <small class="text-muted d-block mt-1">
                                    Unduh template:
                                    <a id="template-link" href="{{ route('settings.import.kontrakmks.template') }}"
                                       @if(empty($preview['semester_id'])) class="pe-none text-muted" aria-disabled="true" tabindex="-1" @endif>
                                        template-import-kontrakmk.xlsx
                                    </a>
                                </small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3"></div>
                            <div class="col">
                                <button type="submit" id="submit-btn" class="btn btn-primary btn-sm"
                                        @if(empty($preview['semester_id'])) disabled @endif>
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
                    <form action="{{ route('settings.import.kontrakmks.clear') }}" method="POST" class="float-end" style="display: inline;">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('kontrakmks.index') }}">
                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id ?? '' }}">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus preview data?');">
                            <i class="bi bi-x-circle"></i> Kosongkan Preview
                        </button>
                    </form>
                </div>
                <div class="card-body border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="form-check m-0">
                            <input type="checkbox" id="select-all" class="form-check-input">
                            <label for="select-all" class="form-check-label">Pilih semua</label>
                        </div>
                    </div>
                    <form action="{{ route('settings.import.kontrakmks.commit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('kontrakmks.index') }}">
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
                                            <td class="text-center align-top">
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
    const semesterSelect = document.querySelector('select[name="semester_id"]');
    const fileInput = document.getElementById('file-input');
    const submitBtn = document.getElementById('submit-btn');
    const fileHint = document.getElementById('file-hint');
    const semesterAlert = document.getElementById('semester-alert');
    const templateLink = document.getElementById('template-link');

    function toggleFileInput() {
        const selected = semesterSelect && semesterSelect.value !== '';
        if (fileInput) fileInput.disabled = !selected;
        if (submitBtn) submitBtn.disabled = !selected;
        if (fileHint) fileHint.classList.toggle('d-none', selected);
        if (semesterAlert) semesterAlert.classList.toggle('d-none', selected);
        if (templateLink) {
            templateLink.classList.toggle('pe-none', !selected);
            templateLink.classList.toggle('text-muted', !selected);
            templateLink.setAttribute('aria-disabled', selected ? 'false' : 'true');
            templateLink.setAttribute('tabindex', selected ? '0' : '-1');
        }
    }

    if (semesterSelect) {
        semesterSelect.addEventListener('change', toggleFileInput);
        toggleFileInput();
    }

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
            lengthMenu: [[10, 25, 50, 100, 200, 500, -1],
                            [10, 25, 50, 100, 200, 500, "All"]],
        });
    }
});
</script>
@endpush
