@extends('layouts.panel')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    Import Data Nilai Tugas ({{ $kelasLabel ?? 'Semua Kelas' }})
                </div>

                <div class="card-body">

                    <div class="row">
                        <div class="col-md-3">Mata Kuliah</div>
                        <div class="col"><strong>{{ $mk->kode }} - {{ $mk->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Program Studi</div>
                        <div class="col"><strong>{{ $mk->kurikulum->prodi->jenjang }} {{ $mk->kurikulum->prodi->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Semester kontrak</div>
                        <div class="col"><strong>{{ $semesterLabel ?? '—' }}</strong></div>
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
                        $importQuery = $kelasQuery;
                        if (!empty($selectedSemesterId ?? null)) {
                            $importQuery['semester_id'] = $selectedSemesterId;
                        }
                        $templateSuffix = !empty($kelasFilter)
                            ? '-' . \Illuminate\Support\Str::slug($kelasFilter, '-')
                            : '-semua-kelas';
                        $semSlug = !empty($selectedSemesterId ?? null)
                            ? '-' . \Illuminate\Support\Str::slug((string) (($semesterOptions ?? collect())->firstWhere('id', $selectedSemesterId)?->kode ?? 'semester'), '-')
                            : '';
                        $nilaiReturnDefault = route('mks.nilais.index', array_merge(['mk' => $mk->id], !empty($selectedSemesterId ?? null) ? ['semester_id' => $selectedSemesterId] : []));
                    @endphp

                    @if (($semesterOptions ?? collect())->isNotEmpty())
                        <div class="row mt-2">
                            <div class="col-md-3">Ubah semester</div>
                            <div class="col">
                                <select id="import-semester-filter" class="form-select form-select-sm" style="max-width: 320px;">
                                    @foreach ($semesterOptions as $semester)
                                        <option value="{{ $semester->id }}" @selected((string) $semester->id === (string) ($selectedSemesterId ?? ''))>{{ $semester->kode }} — {{ $semester->nama }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Hanya mahasiswa yang dikontrak pada semester ini yang dapat diimpor.</small>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('settings.import.nilais', array_merge(['mk' => $mk->id], $importQuery)) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <input type="hidden" name="kelas" value="{{ $kelasFilter ?? '__SEMUA_KELAS__' }}">
                        <input type="hidden" name="semester_id" value="{{ $selectedSemesterId ?? '' }}">
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? $nilaiReturnDefault }}">
                        <div class="row mt-3">
                            <div class="col-md-3 text-end">
                                File Upload <span class="text-danger">*</span>
                            </div>
                            <div class="col">
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.ods" required>
                                <small class="text-muted d-block mt-1">
                                    Unduh template: <a href="{{ route('settings.import.nilais.template', array_merge(['mk' => $mk->id], $importQuery)) }}">template-import-nilai-{{ \Illuminate\Support\Str::slug($mk->kode ?? 'mk', '-') }}{{ $templateSuffix }}{{ $semSlug }}.xlsx</a>
                                </small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3"></div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload"></i> Unggah &amp; Simpan
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
                    <form action="{{ route('settings.import.nilais.clear', array_merge(['mk' => $mk->id], $importQuery)) }}" method="POST" class="float-end" style="display: inline;">
                        @csrf
                        <input type="hidden" name="kelas" value="{{ $kelasFilter ?? '__SEMUA_KELAS__' }}">
                        <input type="hidden" name="semester_id" value="{{ $selectedSemesterId ?? '' }}">
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? $nilaiReturnDefault }}">
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

                    <form action="{{ route('settings.import.nilais.commit', array_merge(['mk' => $mk->id], $importQuery)) }}" method="POST">
                        @csrf
                        <input type="hidden" name="kelas" value="{{ $kelasFilter ?? '__SEMUA_KELAS__' }}">
                        <input type="hidden" name="semester_id" value="{{ $selectedSemesterId ?? '' }}">
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? $nilaiReturnDefault }}">
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
    const semesterFilter = document.getElementById('import-semester-filter');
    if (semesterFilter) {
        semesterFilter.addEventListener('change', function () {
            const selectedSemesterId = (semesterFilter.value ?? '').trim();
            const url = new URL(window.location.href);
            if (selectedSemesterId === '') {
                url.searchParams.delete('semester_id');
            } else {
                url.searchParams.set('semester_id', selectedSemesterId);
            }
            window.location.assign(url.toString());
        });
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
        });
    }
});
</script>
@endpush
