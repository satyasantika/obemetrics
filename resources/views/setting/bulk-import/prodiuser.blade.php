@extends('layouts.panel')

@section('content')
<div class="container">
    <div class="row justify-content-left">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Import Data Dosen Prodi
                    @stack('header')
                </div>

                <div class="card-body">
                     <form action="{{ route('settings.import.prodiusers') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('home') }}">
                        <div class="row mt-3">
                            <div class="col-md-3 text-end">
                                File Upload <span class="text-danger">*</span>
                            </div>
                            <div class="col">
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.ods" required>
                                <small class="text-muted d-block mt-1">
                                    Unduh template: <a href="{{ route('settings.import.prodiusers.template') }}">template-import-userprodi.xlsx</a>
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
    {{-- bagian preview data --}}
    <div class="row justify-content-center">
        <div class="col-md-12">

            @if (!empty($preview['rows']))
            <div class="card mt-3">
                <div class="card-header">
                    <span class="h5">Preview Data Join Prodi User @if(!empty($preview['filename']))({{ $preview['filename'] }})@endif</span>
                    <form action="{{ route('settings.import.prodiusers.clear') }}" method="POST" class="float-end" style="display: inline;">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('home') }}">
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
                    <form action="{{ route('settings.import.prodiusers.commit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('home') }}">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="preview-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">Pilih</th>
                                        <th>Kode Program Studi</th>
                                        <th>Nama Program Studi</th>
                                        <th>NIDN</th>
                                        <th>Nama Dosen</th>
                                        <th>Status Pimpinan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview['rows'] as $index => $row)
                                        @php
                                            $hasError = !$row['prodi_exists'] || !$row['dosen_exists'];
                                            $rowClass = $hasError ? 'table-danger' : ($row['exists'] ? 'table-warning' : '');
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    name="selected[]"
                                                    value="{{ $index }}"
                                                    class="form-check-input row-check"
                                                    @checked(!$hasError)
                                                    @disabled($hasError)
                                                >
                                            </td>
                                            <td>{{ $row['kode_prodi'] }}</td>
                                            <td>{{ $row['nama_prodi'] }}</td>
                                            <td>{{ $row['nidn'] }}</td>
                                            <td>{{ $row['nama_dosen'] }}</td>
                                            <td>{{ $row['status_pimpinan_label'] ?? ((bool)($row['status_pimpinan'] ?? false) ? 'Ya' : '-') }}</td>
                                            <td>
                                                @if (!$row['prodi_exists'])
                                                    <span class="badge bg-danger">Program Studi tidak ditemukan</span>
                                                @elseif (!$row['dosen_exists'])
                                                    <span class="badge bg-danger">Dosen tidak ditemukan</span>
                                                @elseif ($row['exists'])
                                                    <span class="badge bg-warning text-dark">Sudah ada (update jika dipilih)</span>
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
            lengthMenu: [[10, 25, 50, 100, 200, 500, -1],
                            [10, 25, 50, 100, 200, 500, "All"]],
        });
    }
});
</script>
@endpush
