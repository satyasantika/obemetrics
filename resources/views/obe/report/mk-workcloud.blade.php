@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <x-obe.header
                title="Rekap Nilai per Kategori Workcloud"
                subtitle="Rekap komponen nilai mahasiswa berdasarkan kategori penilaian"
                icon="bi bi-grid-1x2-fill" />
                <div class="card-body bg-light-subtle">
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="p-3 rounded-3 border bg-white d-flex flex-column align-items-start gap-2">
                                    <span>Semester :</span>
                                    <select id="semester-filter" class="form-control form-control-sm w-100" style="max-width: 320px;">
                                        @foreach ($semesters as $semester)
                                            <option value="{{ $semester->id }}" @selected((string) $semester->id === (string) $selectedSemesterId)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                            </div>
                    @php
                        $defaultSemesterIdForExport = $selectedSemesterId;
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
                                        data-kelas="{{ $kelas }}"
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
                                    @php
                                        $exportQuery = ['kelas' => $kelas];
                                        if ($defaultSemesterIdForExport) {
                                            $exportQuery['semester_id'] = $defaultSemesterIdForExport;
                                        }
                                    @endphp
                                    <a
                                        href="{{ route('mks.workclouds.export-kelas', $mk->id) . '?' . http_build_query($exportQuery) }}"
                                        class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm btn-export-kelas"
                                        data-base-url="{{ route('mks.workclouds.export-kelas', $mk->id) }}"
                                        data-kelas="{{ $kelas }}">
                                        <i class="bi bi-file-earmark-excel"></i> Download Excel Kelas {{ $kelas }}
                                    </a>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="table-responsive nilai-matrix-wrapper rounded-3 border bg-white shadow-sm">
                                        <table class="table table-hover align-middle nilai-matrix-table mb-0">
                                            <thead class="table-light">
                                                <tr class="text-center align-middle">
                                                    <th rowspan="3">No</th>
                                                    <th rowspan="3">Mahasiswa</th>
                                                    <th colspan="2">Nilai Akhir</th>
                                                    <th colspan="{{ max(1, $workclouds->count()) }}">Nilai Komponen Evaluasi</th>
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
                                                    <td class="text-center">{{ number_format((float) $kontrakMk->nilai_angka, 2) ?? '-' }}</td>
                                                    <td class="text-center">{{ $kontrakMk->nilai_huruf ?? '-' }}</td>
                                                    {{-- Nilai Komponen Evaluasi per Workcloud --}}
                                                    @forelse ($workclouds as $workcloud)
                                                        <td class="text-center">
                                                            @php
                                                                $key = $kontrakMk->mahasiswa_id . '_' . $kontrakMk->semester_id . '_' . $workcloud;
                                                                $avgObj = $avgByWorkcloud[$key] ?? null;
                                                            @endphp
                                                            {{ $avgObj ? number_format((float) $avgObj->avg_nilai, 2) : '0.00' }}
                                                        </td>
                                                    @empty
                                                        <td class="text-center"><span class="text-muted">0.00</span></td>
                                                    @endforelse
                                                </tr>
                                            @endforeach
                                            {{-- Rata-rata Kelas --}}
                                            <tr class="fw-bold table-secondary">
                                                <td colspan="2">RATA-RATA KELAS</td>
                                                <td class="text-center">{{ $kelasAvgNilaiAngka !== null ? number_format((float) $kelasAvgNilaiAngka, 2) : '-' }}</td>
                                                <td class="text-center"></td>
                                                @forelse ($workclouds as $workcloud)
                                                    @php
                                                        $aggKey = $kelas . '_' . $workcloud;
                                                        $kelasWorkcloudAvg = $classAvgByWorkcloud[$aggKey] ?? null;
                                                    @endphp
                                                    <td class="text-center">{{ $kelasWorkcloudAvg !== null ? number_format((float) $kelasWorkcloudAvg, 2) : '0.00' }}</td>
                                                @empty
                                                    <td class="text-center"><span class="text-muted">0.00</span></td>
                                                @endforelse
                                            </tr>
                                            <tr class="matrix-empty-row" style="display:none;">
                                                <td colspan="{{ max(5, $workclouds->count() + 1) }}"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle p-2">
                                                    Tidak ada data mahasiswa pada semester yang dipilih.</span>
                                                </td>
                                            </tr>
                                            @else
                                            <tr>
                                                <td colspan="{{ max(5, $workclouds->count() + 1) }}"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle p-2">
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
    const kelasPerSemester = @json($kelasPerSemester);

    const updateKelasTabsForSemester = function (semId) {
        if (!semId) return;
        const semesterKelas = kelasPerSemester[String(semId)];
        const visible = semesterKelas ? new Set(semesterKelas.map(String)) : null;
        const navItems = document.querySelectorAll('#kelasTab .nav-item');
        let firstVisibleBtn = null;
        let activeIsHidden = false;

        navItems.forEach(function (navItem) {
            const btn = navItem.querySelector('.nav-link');
            if (!btn) return;
            const isVisible = !visible || visible.has(String(btn.getAttribute('data-kelas') || ''));
            navItem.style.display = isVisible ? '' : 'none';
            if (isVisible && !firstVisibleBtn) firstVisibleBtn = btn;
            if (!isVisible && btn.classList.contains('active')) activeIsHidden = true;
        });

        if (activeIsHidden && firstVisibleBtn && window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(firstVisibleBtn).show();
        }
    };

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

                if (selectedSemesterId && visibleCount === 0) {
                    matrixRows.forEach(function (row) {
                        row.style.display = '';
                    });
                    visibleCount = matrixRows.length;
                }

                if (matrixEmptyRow) {
                    matrixEmptyRow.style.display = visibleCount === 0 ? '' : 'none';
                }
            });

            syncExportLinks();
        };

        semesterFilter.addEventListener('change', function () {
            applySemesterFilter();
            updateKelasTabsForSemester(semesterFilter.value);
            fetch('{{ route('mks.semester.set', $mk->id) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ semester_id: semesterFilter.value })
            });
        });
        applySemesterFilter();
        updateKelasTabsForSemester(semesterFilter ? semesterFilter.value : '');
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
    background: var(--bs-light);
    z-index: 20;
}

.nilai-matrix-table .sticky-col {
    position: sticky;
    left: 0;
    background: var(--bs-white);
    z-index: 15;
    min-width: 240px;
}

.nilai-matrix-table thead .sticky-col {
    z-index: 25;
}
</style>
@endpush

@endsection
