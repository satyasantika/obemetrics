@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Rekap Nilai per Kategori Workcloud
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas mata kuliah --}}
                    @include('components.identitas-mk', $mk)
                    <div class="row">
                        <div class="col-md-3">Semester</div>
                        <div class="col">
                            <select id="semester-filter" class="form-control form-control-sm" style="max-width: 320px;">
                                @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" @selected($semester->status_aktif)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr>
                    @include('components.menu-mk',$mk)
                    <hr>
                    @php
                        $kelasGroups = $kontrakMks
                            ->groupBy(function ($item) {
                                return trim((string) ($item->kelas ?? '')) !== '' ? trim((string) $item->kelas) : 'Tanpa Kelas';
                            })
                            ->sortKeys();
                        $defaultKelas = $kelasGroups->keys()->first();
                    @endphp

                    {{-- matriks nilai workcloud --}}
                    @if ($kelasGroups->isNotEmpty())
                        <ul class="nav nav-tabs" id="kelasTab" role="tablist">
                            @foreach ($kelasGroups as $kelas => $kelasKontrakMks)
                                @php
                                    $kelasSlug = \Illuminate\Support\Str::slug($kelas, '-');
                                    $kelasPaneId = 'kelas-' . ($kelasSlug !== '' ? $kelasSlug : 'tanpa-kelas');
                                @endphp
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link {{ $kelas === $defaultKelas ? 'active' : '' }}"
                                        id="{{ $kelasPaneId }}-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#{{ $kelasPaneId }}"
                                        type="button"
                                        role="tab"
                                        aria-controls="{{ $kelasPaneId }}"
                                        aria-selected="{{ $kelas === $defaultKelas ? 'true' : 'false' }}">
                                        Kelas {{ $kelas }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content pt-3" id="kelasTabContent">
                        @foreach ($kelasGroups as $kelas => $kelasKontrakMks)
                            @php
                                $kelasSlug = \Illuminate\Support\Str::slug($kelas, '-');
                                $kelasPaneId = 'kelas-' . ($kelasSlug !== '' ? $kelasSlug : 'tanpa-kelas');
                                $kelasRows = $kelasKontrakMks->values();
                                $kelasAvgNilaiAngka = $kelasRows->whereNotNull('nilai_angka')->average('nilai_angka');
                            @endphp
                            <div
                                class="tab-pane fade {{ $kelas === $defaultKelas ? 'show active' : '' }}"
                                id="{{ $kelasPaneId }}"
                                role="tabpanel"
                                aria-labelledby="{{ $kelasPaneId }}-tab">
                                <div class="d-flex justify-content-end mb-2">
                                    <a
                                        href="#"
                                        class="btn btn-success btn-sm btn-export-kelas"
                                        data-base-url="{{ route('mks.workclouds.export-kelas', $mk->id) }}"
                                        data-kelas="{{ $kelas }}">
                                        <i class="bi bi-file-earmark-excel"></i> Download Excel Kelas {{ $kelas }}
                                    </a>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="table-responsive nilai-matrix-wrapper">
                                        <table class="table table-bordered table-striped nilai-matrix-table mb-0">
                                            <thead>
                                                <tr class="text-center align-middle">
                                                    <th rowspan="3">No</th>
                                                    <th rowspan="3">Mahasiswa</th>
                                                    <th colspan="2">Nilai Akhir</th>
                                                    <th colspan="6">Nilai Komponen Evaluasi</th>
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    <th rowspan="2">Nilai</th>
                                                    <th rowspan="2">Grade</th>
                                                    @forelse ($workcloudMetas as $workcloudMeta)
                                                        <th>
                                                            <span class="fw-bold">{{ $workcloudMeta['name'] }}</span>
                                                            <br>
                                                            <span class="text-primary">({{ number_format((float) $workcloudMeta['bobot'], 2) }}%)</span>
                                                        </th>
                                                    @empty
                                                        <th>Belum ada kategori workcloud</th>
                                                    @endforelse
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    @forelse ($workcloudMetas as $workcloudMeta)
                                                        <th>
                                                            <small class="text-muted">CPL:
                                                                @if (!empty($workcloudMeta['cpls']))
                                                                    <span class="badge bg-dark text-white">{{ implode(', ', $workcloudMeta['cpls']) }}</span>
                                                                @else
                                                                    <span class="badge bg-danger text-white">-</span>
                                                                @endif
                                                            </small>
                                                        </th>
                                                    @empty
                                                        <th>Belum ada kategori workcloud</th>
                                                    @endforelse
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @if ($kelasRows->isNotEmpty())
                                            @foreach ($kelasRows as $no => $kontrakMk)
                                                <tr class="matriks-row" data-semester-id="{{ $kontrakMk->semester_id }}" style="vertical-align: text-top;">
                                                    <td class="text-center">{{ $no + 1 }}</td>
                                                    <td class="sticky-col">
                                                        <small class="text-muted">{{ $kontrakMk->mahasiswa->nim }}</small><br>
                                                        {{ $kontrakMk->mahasiswa->nama }}
                                                    </td>
                                                    <td class="text-end">{{ number_format((float) $kontrakMk->nilai_angka, 2) ?? '-' }}</td>
                                                    <td class="text-center">{{ $kontrakMk->nilai_huruf ?? '-' }}</td>
                                                    {{-- Nilai Komponen Evaluasi per Workcloud --}}
                                                    @forelse ($workclouds as $workcloud)
                                                        <td class="text-end">
                                                            @php
                                                                $key = $kontrakMk->mahasiswa_id . '_' . $kontrakMk->semester_id . '_' . $workcloud;
                                                                $avgObj = $avgByWorkcloud[$key] ?? null;
                                                            @endphp
                                                            {{ $avgObj ? number_format((float) $avgObj->avg_nilai, 2) : '-' }}
                                                        </td>
                                                    @empty
                                                        <td class="text-end"><span class="text-muted">-</span></td>
                                                    @endforelse
                                                </tr>
                                            @endforeach
                                            {{-- Rata-rata Kelas --}}
                                            <tr>
                                                <td colspan="2">RATA-RATA KELAS</td>
                                                <td class="text-end">{{ $kelasAvgNilaiAngka !== null ? number_format((float) $kelasAvgNilaiAngka, 2) : '-' }}</td>
                                                <td></td>
                                                @forelse ($workclouds as $workcloud)
                                                    @php
                                                        $kelasWorkcloudValues = $kelasRows->map(function ($row) use ($workcloud, $avgByWorkcloud) {
                                                            $rowKey = $row->mahasiswa_id . '_' . $row->semester_id . '_' . $workcloud;
                                                            return isset($avgByWorkcloud[$rowKey]) ? (float) $avgByWorkcloud[$rowKey]->avg_nilai : null;
                                                        })->filter(function ($item) {
                                                            return $item !== null;
                                                        });
                                                        $kelasWorkcloudAvg = $kelasWorkcloudValues->count() > 0 ? $kelasWorkcloudValues->average() : null;
                                                    @endphp
                                                    <td class="text-end">{{ $kelasWorkcloudAvg !== null ? number_format((float) $kelasWorkcloudAvg, 2) : '-' }}</td>
                                                @empty
                                                    <td class="text-end"><span class="text-muted">-</span></td>
                                                @endforelse
                                            </tr>
                                            <tr class="matrix-empty-row" style="display:none;">
                                                <td colspan="{{ max(5, $workclouds->count() + 1) }}"><span class="bg-warning text-dark p-2">
                                                    Tidak ada data mahasiswa pada semester yang dipilih.</span>
                                                </td>
                                            </tr>
                                            @else
                                            <tr>
                                                <td colspan="{{ max(5, $workclouds->count() + 1) }}"><span class="bg-warning text-dark p-2">
                                                    Belum ada data kontrak mahasiswa untuk Mata Kuliah ini.</span>
                                                </td>
                                            </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">Belum ada data kontrak mahasiswa untuk mata kuliah ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const semesterFilter = document.getElementById('semester-filter');
    const matrixTables = document.querySelectorAll('.nilai-matrix-table');
    const exportButtons = document.querySelectorAll('.btn-export-kelas');

    const syncExportLinks = function () {
        const selectedSemesterId = semesterFilter ? semesterFilter.value : '';

        exportButtons.forEach(function (button) {
            const baseUrl = button.getAttribute('data-base-url') || '';
            const kelas = button.getAttribute('data-kelas') || '';
            const params = new URLSearchParams();

            params.set('kelas', kelas);
            if (selectedSemesterId) {
                params.set('semester_id', selectedSemesterId);
            }

            button.setAttribute('href', baseUrl + '?' + params.toString());
        });
    };

    if (semesterFilter && matrixTables.length > 0) {
        const applySemesterFilter = function () {
            const selectedSemesterId = semesterFilter.value;

            matrixTables.forEach(function (table) {
                const matrixRows = table.querySelectorAll('.matriks-row');
                const matrixEmptyRow = table.querySelector('.matrix-empty-row');
                let visibleCount = 0;

                matrixRows.forEach(function (row) {
                    const rowSemesterId = row.getAttribute('data-semester-id');
                    const isVisible = !selectedSemesterId || selectedSemesterId === rowSemesterId;

                    row.style.display = isVisible ? '' : 'none';
                    if (isVisible) {
                        visibleCount++;
                    }
                });

                if (matrixEmptyRow) {
                    matrixEmptyRow.style.display = visibleCount === 0 ? '' : 'none';
                }
            });

            syncExportLinks();
        };

        semesterFilter.addEventListener('change', applySemesterFilter);
        applySemesterFilter();
    } else {
        syncExportLinks();
    }

});
</script>
@endpush

@push('styles')
<style>
.nilai-matrix-wrapper {
    max-height: 70vh;
    overflow: auto;
}

.nilai-matrix-table thead th {
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 20;
}

.nilai-matrix-table .sticky-col {
    position: sticky;
    left: 0;
    background: #fff;
    z-index: 15;
    min-width: 240px;
}

.nilai-matrix-table thead .sticky-col {
    z-index: 25;
}
</style>
@endpush

@endsection
