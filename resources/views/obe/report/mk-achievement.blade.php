@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <x-mk-semester-bar
                mode="client"
                :semesterOptions="$semesters"
                :selectedSemesterId="$selectedSemesterId" />
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <x-obe.header
                title="Evaluasi Ketercapaian CPL"
                subtitle="Evaluasi capaian CPL berdasarkan data nilai kelas"
                icon="bi bi-award-fill" />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-3 g-3">
                        <div class="col-md-6 d-flex">
                            <div class="p-3 p-lg-4 rounded-3 border border-primary-subtle bg-primary-subtle text-primary-emphasis h-100 w-100 d-flex flex-column justify-content-between text-md-end text-start">
                                <div>
                                    <span class="small text-uppercase fw-semibold d-block">Target Kelulusan CPL</span>
                                    <strong id="target-kelulusan" class="display-6 fw-bold lh-1 d-block mt-2">{{ $mk->kurikulum->target_capaian_lulusan ?? 100 }}%</strong>
                                </div>
                                <small class="mt-2">Persentase minimum ketercapaian rata-rata kelas</small>
                            </div>
                        </div>
                    </div>
                    @php
                        $defaultKelas = collect($kelasList)->first();
                    @endphp

                    @if (collect($kelasList)->isNotEmpty())
                        <ul class="nav nav-tabs" id="kelasTab" role="tablist">
                            @foreach ($kelasList as $kelas)
                                @php
                                    $kelasSlug = \Illuminate\Support\Str::slug($kelas, '-');
                                    $kelasPaneId = 'kelas-' . ($kelasSlug !== '' ? $kelasSlug : 'tanpa-kelas');
                                    $kelasLabel = $kelas === '__SEMUA_KELAS__' ? 'Semua Kelas' : 'Kelas ' . $kelas;
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
                                        {{ $kelasLabel }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content pt-3" id="kelasTabContent">
                            @foreach ($kelasList as $kelas)
                                @php
                                    $kelasSlug = \Illuminate\Support\Str::slug($kelas, '-');
                                    $kelasPaneId = 'kelas-' . ($kelasSlug !== '' ? $kelasSlug : 'tanpa-kelas');
                                    $kelasLabel = $kelas === '__SEMUA_KELAS__' ? 'Semua Kelas' : 'kelas ' . $kelas;
                                @endphp
                                <div
                                    class="tab-pane fade {{ $kelas === $defaultKelas ? 'show active' : '' }}"
                                    id="{{ $kelasPaneId }}"
                                    role="tabpanel"
                                    aria-labelledby="{{ $kelasPaneId }}-tab">
                                    <div class="card mb-3">
                                        <div class="card-header bg-info-subtle text-info-emphasis border border-info-subtle rounded-top-3">
                                            <div class="d-flex align-items-center gap-2 fw-semibold">
                                                <i class="bi bi-bar-chart-line-fill"></i>
                                                <span>Evaluasi Ketercapaian CPL {{ $kelasLabel }}</span>
                                            </div>
                                        </div>
                                        <div class="card-body bg-light-subtle">
                                            <div class="table-responsive nilai-matrix-wrapper rounded-3 border bg-white shadow-sm">
                                                <table class="table table-hover align-middle nilai-matrix-table mb-0">
                                                    <thead class="table-light">
                                                        <tr class="text-center align-middle">
                                                            <th style="width: 12%">Kode CPL</th>
                                                            <th style="width: 30%">Deskripsi Singkat CPL</th>
                                                            <th>Komponen Penilaian Penyumbang Nilai</th>
                                                            <th style="width: 14%">Rata-rata Capaian Kelas</th>
                                                            <th style="width: 14%">Status Ketercapaian</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="cpl-achievement-body" data-kelas="{{ $kelas }}"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-header bg-success-subtle text-success-emphasis border border-success-subtle rounded-top-3">
                                            <div class="d-flex align-items-center gap-2 fw-semibold text-uppercase">
                                                <i class="bi bi-pie-chart-fill"></i>
                                                <span>Distribusi Nilai & Kesimpulan (Student's Final Grade)</span>
                                            </div>
                                        </div>
                                        <div class="card-body bg-light-subtle">
                                            <div class="table-responsive nilai-matrix-wrapper rounded-3 border bg-white shadow-sm">
                                                <table class="table table-hover align-middle nilai-matrix-table mb-0">
                                                    <thead class="table-light">
                                                        <tr class="text-center align-middle">
                                                            <th>Nilai Huruf</th>
                                                            <th>Jumlah Mahasiswa</th>
                                                            <th>Persentase (%)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="grade-distribution-body" data-kelas="{{ $kelas }}"></tbody>
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
    const targetKelulusanInput = document.getElementById('target-kelulusan');

    const kelasList = @json($kelasList);
    const kelasPerSemester = @json($kelasPerSemester);
    const cplRows = @json($cplRows);
    const gradeOrder = @json($gradeOrder);
    const achievementData = @json($achievementData);
    const componentsData = @json($componentsDataByCpl);
    const gradeDistributionData = @json($gradeDistributionData);

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

    const escapeHtml = function (text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    };

    const renderCplTable = function (kelas, semesterId, target) {
        const tbody = document.querySelector('.cpl-achievement-body[data-kelas="' + CSS.escape(kelas) + '"]');
        if (!tbody) {
            return;
        }

        if (!Array.isArray(cplRows) || cplRows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center"><span class="text-muted">Belum ada CPL yang diukur pada mata kuliah ini.</span></td></tr>';
            return;
        }

        const rowsHtml = cplRows.map(function (cpl) {
            const avgValue = achievementData?.[kelas]?.[semesterId]?.[cpl.id] ?? null;
            const semesterComponents = componentsData?.[semesterId]?.[cpl.id] ?? [];
            const allSemesterComponents = componentsData?.['all']?.[cpl.id] ?? [];
            const components = semesterComponents.length > 0 ? semesterComponents : allSemesterComponents;

            const komponenText = components.length > 0
                ? components.map(function (item) {
                    const bobot = Number(item.bobot ?? 0).toFixed(2);
                    return escapeHtml(item.workcloud) + ' (' + bobot + '%)';
                }).join('<br>')
                : '<span class="text-muted">-</span>';

            let statusHtml = '<span class="text-muted">-</span>';
            if (avgValue !== null && Number.isFinite(target)) {
                statusHtml = Number(avgValue) >= target
                    ? '<span class="badge bg-success">Tercapai</span>'
                    : '<span class="badge bg-danger">Belum Tercapai</span>';
            }

            return '<tr>'
                + '<td class="text-center align-middle">' + escapeHtml(cpl.kode ?? '-') + '</td>'
                + '<td>' + escapeHtml(cpl.nama ?? '-') + '</td>'
                + '<td>' + komponenText + '</td>'
                + '<td class="text-center align-middle">' + (avgValue !== null ? Number(avgValue).toFixed(2) + '%' : '-') + '</td>'
                + '<td class="text-center align-middle">' + statusHtml + '</td>'
                + '</tr>';
        }).join('');

        tbody.innerHTML = rowsHtml;
    };

    const renderGradeTable = function (kelas, semesterId) {
        const tbody = document.querySelector('.grade-distribution-body[data-kelas="' + CSS.escape(kelas) + '"]');
        if (!tbody) {
            return;
        }

        const dist = gradeDistributionData?.[kelas]?.[semesterId] ?? { total: 0, counts: {} };
        const total = Number(dist.total ?? 0);

        const rowsHtml = gradeOrder.map(function (grade) {
            const jumlah = Number(dist.counts?.[grade] ?? 0);
            const persentase = total > 0 ? ((jumlah / total) * 100) : 0;
            return '<tr>'
                + '<td class="text-center align-middle">' + escapeHtml(grade) + '</td>'
                + '<td class="text-center align-middle">' + jumlah + '</td>'
                + '<td class="text-center align-middle">' + persentase.toFixed(2) + '%</td>'
                + '</tr>';
        }).join('');

        tbody.innerHTML = rowsHtml;
    };

    const renderAllTables = function () {
        const semesterId = semesterFilter ? String(semesterFilter.value || '') : '';
        const target = targetKelulusanInput ? Number(targetKelulusanInput.textContent.replace('%', '')) : NaN;
        updateKelasTabsForSemester(semesterId);

        kelasList.forEach(function (kelas) {
            renderCplTable(kelas, semesterId, target);
            renderGradeTable(kelas, semesterId);
        });
    };

    if (semesterFilter) {
        semesterFilter.addEventListener('change', renderAllTables);
        semesterFilter.addEventListener('change', function () {
            fetch('{{ route('mks.semester.set', $mk->id) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ semester_id: semesterFilter.value })
            });
        });
    }
    if (targetKelulusanInput) {
        targetKelulusanInput.addEventListener('input', renderAllTables);
    }

    renderAllTables();
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
