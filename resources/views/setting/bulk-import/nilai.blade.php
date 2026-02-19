@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    Import Data Nilai Tugas ({{ $kelasLabel ?? 'Semua Kelas' }})
                    <a href="{{ route('mks.nilais.index', [$mk->id]) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali ke Penilaian</a>
                </div>

                <div class="card-body">
                    @include('layouts.alert')

                    <div class="row">
                        <div class="col-md-3">Mata Kuliah</div>
                        <div class="col"><strong>{{ $mk->kode }} - {{ $mk->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Program Studi</div>
                        <div class="col"><strong>{{ $mk->kurikulum->prodi->jenjang }} {{ $mk->kurikulum->prodi->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Cakupan Import</div>
                        <div class="col"><strong>{{ $kelasLabel ?? 'Semua Kelas' }}</strong></div>
                    </div>

                    @php
                        $kelasQuery = [];
                        if (!empty($kelasFilter)) {
                            $kelasQuery['kelas'] = $kelasFilter;
                        } elseif (($kelasFilter ?? null) === null) {
                            $kelasQuery['kelas'] = '__SEMUA_KELAS__';
                        }
                        $templateSuffix = !empty($kelasFilter)
                            ? '-' . \Illuminate\Support\Str::slug($kelasFilter, '-')
                            : '-semua-kelas';
                    @endphp

                    <form action="{{ route('setting.import.nilais', array_merge(['mk' => $mk->id], $kelasQuery)) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <input type="hidden" name="kelas" value="{{ $kelasFilter ?? '__SEMUA_KELAS__' }}">
                        <div class="row mt-3">
                            <div class="col-md-3 text-end">
                                File Upload <span class="text-danger">*</span>
                            </div>
                            <div class="col">
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.ods" required>
                                <small class="text-muted d-block mt-1">
                                    Unduh template: <a href="{{ route('setting.import.nilais.template', array_merge(['mk' => $mk->id], $kelasQuery)) }}">template-import-nilai-{{ \Illuminate\Support\Str::slug($mk->kode ?? 'mk', '-') }}{{ $templateSuffix }}.xlsx</a>
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
                    <span class="h5">Preview Nilai @if(!empty($preview['filename']))({{ $preview['filename'] }})@endif</span>
                    <form action="{{ route('setting.import.nilais.clear', array_merge(['mk' => $mk->id], $kelasQuery)) }}" method="POST" class="float-end" style="display: inline;">
                        @csrf
                        <input type="hidden" name="kelas" value="{{ $kelasFilter ?? '__SEMUA_KELAS__' }}">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus preview data?');">
                            <i class="bi bi-x-circle"></i> Kosongkan Preview
                        </button>
                    </form>
                </div>
                <div class="card-body border-top">
                    <div class="mb-2">
                        <input type="checkbox" id="select-all" class="form-check-input">
                        <label for="select-all" class="form-check-label">Pilih semua baris valid</label>
                    </div>

                    <form action="{{ route('setting.import.nilais.commit', array_merge(['mk' => $mk->id], $kelasQuery)) }}" method="POST">
                        @csrf
                        <input type="hidden" name="kelas" value="{{ $kelasFilter ?? '__SEMUA_KELAS__' }}">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="preview-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">Pilih</th>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Semester</th>
                                        @foreach ($penugasans as $penugasan)
                                            <th>{{ $penugasan->kode }}</th>
                                        @endforeach
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (($preview['rows'] ?? []) as $index => $row)
                                        <tr class="{{ ($row['can_save'] ?? false) ? '' : 'table-danger' }}">
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    name="selected[]"
                                                    value="{{ $index }}"
                                                    class="form-check-input row-check"
                                                    @checked($row['can_save'] ?? false)
                                                    @disabled(!($row['can_save'] ?? false))
                                                >
                                            </td>
                                            <td>{{ $row['nim'] ?? '' }}</td>
                                            <td>{{ $row['nama_mahasiswa'] ?? '' }}</td>
                                            <td>{{ $row['semester_kode'] ?? '-' }}</td>
                                            @foreach ($penugasans as $penugasan)
                                                <td>{{ $row['scores'][$penugasan->id] ?? '' }}</td>
                                            @endforeach
                                            <td>
                                                @if ($row['can_save'] ?? false)
                                                    <span class="badge bg-success">Siap disimpan</span>
                                                @else
                                                    <span class="badge bg-danger">{{ implode('; ', $row['errors'] ?? ['Data tidak valid']) }}</span>
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
