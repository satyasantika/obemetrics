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
                    title="Rekapitulasi Ketercapaian Sumbangan CPL"
                    subtitle="Rekap kontribusi CPL pada mata kuliah per kelas"
                    icon="bi bi-bar-chart-steps" />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-3 g-3">
                        <div class="col-md-6 d-flex">
                            <div class="p-3 rounded-3 border bg-white d-flex flex-column align-items-start gap-2 h-100 w-100">
                                <span>Semester :</span>
                                <select id="semester-filter" class="form-control form-control-sm w-100" style="max-width: 320px;">
                                    @foreach ($semesters as $semester)
                                        <option value="{{ $semester->id }}" @selected((string) $semester->id === (string) $defaultSemesterId)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
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
                                    $kelasLabel = $kelas === '__SEMUA_KELAS__' ? 'Semua Kelas' : $kelas;
                                    $kelasSlug = \Illuminate\Support\Str::slug($kelasLabel, '-');
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
                                        {{ $kelasLabel }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content pt-3" id="kelasTabContent">
                            @foreach ($kelasList as $kelas)
                                @php
                                    $kelasLabel = $kelas === '__SEMUA_KELAS__' ? 'Semua Kelas' : $kelas;
                                    $kelasSlug = \Illuminate\Support\Str::slug($kelasLabel, '-');
                                    $kelasPaneId = 'kelas-' . ($kelasSlug !== '' ? $kelasSlug : 'tanpa-kelas');
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
                                                <span>Evaluasi Ketercapaian CPL {{ $kelas === '__SEMUA_KELAS__' ? 'Semua Kelas' : 'kelas ' . $kelas }}</span>
                                            </div>
                                        </div>
                                        <div class="card-body bg-light-subtle">
                                            <div class="table-responsive nilai-matrix-wrapper rounded-3 border bg-white shadow-sm">
                                                <table class="table table-hover align-middle nilai-matrix-table mb-0">
                                                    <thead class="table-light">
                                                        <tr class="text-center align-middle">
                                                            <th class="sticky-col">CPL yang dibebankan pada MK</th>
                                                            <th>CPMK yang Relevan dengan CPL</th>
                                                            <th>SubCPMK sebagai kemampuan akhir yang relevan</th>
                                                            <th>Indikator</th>
                                                            <th>Sumber Data sesuai Indikator</th>
                                                            <th>Perkiraan Bobot (PK)</th>
                                                            <th>Rerata Nilai (RN)</th>
                                                            <th>PK x RN</th>
                                                            <th>Ketercapaian</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="cpl-achievement-body" data-kelas="{{ $kelas }}"></tbody>
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

    const kelasList = @json($kelasList);
    const kelasPerSemester = @json($kelasPerSemester);
    const hierarchyData = @json($hierarchyData);
    const rnData = @json($rnData);
    const targetKelulusan = parseFloat((document.getElementById('target-kelulusan')?.textContent ?? '100').replace('%', '')) || 100;

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

    const formatNum = function (numberValue) {
        return Number(numberValue || 0).toFixed(2);
    };

    const calculateCplTotals = function (cpl, rnMap) {
        let totalPk = 0;
        let totalPkRn = 0;
        const cpmks = Array.isArray(cpl.cpmks) ? cpl.cpmks : [];

        cpmks.forEach(function (cpmk) {
            const subcpmks = Array.isArray(cpmk.subcpmks) ? cpmk.subcpmks : [];
            subcpmks.forEach(function (subcpmk) {
                const sources = Array.isArray(subcpmk.sources) ? subcpmk.sources : [];
                sources.forEach(function (source) {
                    const pk = Number(source.pk/100 ?? 0);
                    const rn = Number(rnMap?.[String(source.penugasan_id)] ?? 0);
                    totalPk += pk;
                    totalPkRn += (pk * rn) / 100;
                });
            });
        });

        const ratio = totalPkRn > 0 ? (totalPkRn / totalPk) * 100 : 0;
        return { totalPk, totalPkRn, ratio };
    };

    const calculateCpmkTotals = function (cpmk, rnMap) {
        let totalPk = 0;
        let totalPkRn = 0;
        const subcpmks = Array.isArray(cpmk?.subcpmks) ? cpmk.subcpmks : [];

        subcpmks.forEach(function (subcpmk) {
            const sources = Array.isArray(subcpmk?.sources) ? subcpmk.sources : [];
            sources.forEach(function (source) {
                const pk = Number(source.pk/100 ?? 0);
                const rn = Number(rnMap?.[String(source.penugasan_id)] ?? 0);
                totalPk += pk;
                totalPkRn += (pk * rn) / 100;
            });
        });

        const ratio = totalPk > 0 ? (totalPkRn / totalPk) * 100 : 0;
        return { totalPk, totalPkRn, ratio };
    };

    const calculateSubcpmkTotals = function (subcpmk, rnMap) {
        let totalPk = 0;
        let totalPkRn = 0;
        const sources = Array.isArray(subcpmk?.sources) ? subcpmk.sources : [];

        sources.forEach(function (source) {
            const pk = Number(source.pk/100 ?? 0);
            const rn = Number(rnMap?.[String(source.penugasan_id)] ?? 0);
            totalPk += pk;
            totalPkRn += (pk * rn) / 100;
        });

        const ratio = totalPk > 0 ? (totalPkRn / totalPk) * 100 : 0;
        return { totalPk, totalPkRn, ratio };
    };

    const renderCplTable = function (kelas, semesterId) {
        const tbody = document.querySelector('.cpl-achievement-body[data-kelas="' + CSS.escape(kelas) + '"]');
        if (!tbody) {
            return;
        }

        if (!semesterId) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center"><span class="text-muted">Pilih semester terlebih dahulu.</span></td></tr>';
            return;
        }

        if (!Array.isArray(hierarchyData) || hierarchyData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center"><span class="text-muted">Belum ada CPL/CPMK/SubCPMK terpetakan pada mata kuliah ini.</span></td></tr>';
            return;
        }

        const rnMap = rnData?.[kelas]?.[semesterId] ?? {};
        let html = '';
        const totalPkAllCpl = hierarchyData.reduce(function (sum, cpl) {
            return sum + calculateCplTotals(cpl, rnMap).totalPk;
        }, 0);
        const isTargetComplete = Math.abs(totalPkAllCpl - 100) < 0.01;
        const ketercapaianRatios = [];

        hierarchyData.forEach(function (cpl) {
            const cpmks = Array.isArray(cpl.cpmks) ? cpl.cpmks : [];
            const cplRowCount = cpmks.reduce(function (sum, cpmk) {
                const subcpmks = Array.isArray(cpmk.subcpmks) ? cpmk.subcpmks : [];
                const subRows = subcpmks.reduce(function (subSum, subcpmk) {
                    const sourceCount = Array.isArray(subcpmk.sources) ? subcpmk.sources.length : 0;
                    return subSum + (sourceCount > 0 ? sourceCount : 1);
                }, 0);
                return sum + (subRows > 0 ? subRows : 1);
            }, 0);

            if (cplRowCount === 0) {
                return;
            }

            const cplTotals = calculateCplTotals(cpl, rnMap);
            let cplRowRendered = false;

            cpmks.forEach(function (cpmk) {
                const subcpmks = Array.isArray(cpmk.subcpmks) && cpmk.subcpmks.length > 0
                    ? cpmk.subcpmks
                    : [{ kode: '-', nama: '-', indikator: '-', sources: [] }];
                const cpmkRowCount = subcpmks.reduce(function (sum, subcpmk) {
                    const sourceCount = Array.isArray(subcpmk.sources) ? subcpmk.sources.length : 0;
                    return sum + (sourceCount > 0 ? sourceCount : 1);
                }, 0);
                let cpmkRowRendered = false;

                subcpmks.forEach(function (subcpmk) {
                    const realSources = Array.isArray(subcpmk.sources) && subcpmk.sources.length > 0
                        ? subcpmk.sources
                        : [{ penugasan_id: null, kode: '-', kategori: '-', pk: 0 }];
                    const subRowCount = realSources.length;
                    let subRowRendered = false;

                    realSources.forEach(function (source) {
                        const pk = Number(source.pk/100 ?? 0);
                        const rn = Number(rnMap?.[String(source.penugasan_id)] ?? 0);
                        const pkrn = (pk * rn) / 100;
                        const sourceLabel = source && source.kode
                            ? escapeHtml(source.kode) + (source.kategori ? ' - ' + escapeHtml(source.kategori) : '')
                            : '-';

                        html += '<tr>';

                        if (!cplRowRendered) {
                            const cplLabel = (cpl.kode ? cpl.kode : '-') + ' - ' + (cpl.nama ? cpl.nama : '-');
                            html += '<td rowspan="' + cplRowCount + '" class="sticky-col">'
                                + escapeHtml(cplLabel)
                                + '<br><span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle mt-1">(ketercapaian: '
                                + formatNum(cplTotals.ratio)
                                + '%)</span></td>';
                            cplRowRendered = true;
                        }

                        if (!cpmkRowRendered) {
                            const cpmkLabel = (cpmk.kode ? cpmk.kode : '-') + ' - ' + (cpmk.nama ? cpmk.nama : '-');
                            const cpmkTotals = calculateCpmkTotals(cpmk, rnMap);
                            html += '<td rowspan="' + cpmkRowCount + '">'
                                + escapeHtml(cpmkLabel)
                                + '<br><span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle mt-1">(ketercapaian: '
                                + formatNum(cpmkTotals.ratio)
                                + '%)</span></td>';
                            cpmkRowRendered = true;
                        }

                        if (!subRowRendered) {
                            const subLabel = (subcpmk.kode ? subcpmk.kode : '-') + ' - ' + (subcpmk.nama ? subcpmk.nama : '-');
                            const subcpmkTotals = calculateSubcpmkTotals(subcpmk, rnMap);
                            html += '<td rowspan="' + subRowCount + '">'
                                + escapeHtml(subLabel)
                                + '<br><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mt-1">(ketercapaian: '
                                + formatNum(subcpmkTotals.ratio)
                                + '%)</span></td>';
                            html += '<td rowspan="' + subRowCount + '">' + (subcpmk.indikator ? escapeHtml(subcpmk.indikator) : '-') + '</td>';
                            subRowRendered = true;
                        }

                        html += '<td>' + sourceLabel + '</td>';
                        html += '<td class="text-end">' + formatNum(pk) + '%</td>';
                        html += '<td class="text-end">' + formatNum(rn) + '</td>';
                        html += '<td class="text-end">' + formatNum(pkrn) + '%</td>';

                        if (subcpmk === subcpmks[0] && cpmk === cpmks[0] && source === realSources[0]) {
                            const isAchieved = cplTotals.ratio >= targetKelulusan;
                            const ketercapaianContent = isTargetComplete
                                ? '<strong class="d-block fs-5 ' + (isAchieved ? 'text-success' : 'text-danger') + '">' + formatNum(cplTotals.ratio) + '%</strong>'
                                    + '<small class="text-muted d-block">(' + formatNum(cplTotals.totalPkRn) + '% dari PK ' + formatNum(cplTotals.totalPk) + '%)</small>'
                                    + '<span class="badge ' + (isAchieved ? 'bg-success' : 'bg-danger') + ' mt-1">' + (isAchieved ? 'Tercapai' : 'Belum Tercapai') + '</span>'
                                : '<div class="alert alert-danger mb-0 py-2 px-2 small d-flex flex-column align-items-center justify-content-center gap-1"><div class="d-flex align-items-center gap-1"><i class="bi bi-exclamation-triangle-fill"></i><span>Ketercapaian belum bisa ditampilkan.</span></div><div>Target saat ini: <strong>' + formatNum(totalPkAllCpl) + '%</strong> (menunggu 100%).</div></div>';
                            html += '<td rowspan="' + cplRowCount + '" class="text-center ' + (isTargetComplete ? '' : 'bg-danger-subtle') + '">'
                                + ketercapaianContent
                                + '</td>';
                        }

                        html += '</tr>';
                    });
                });
            });

            ketercapaianRatios.push(cplTotals.ratio);
        });

        const avgRatio = ketercapaianRatios.length > 0
            ? ketercapaianRatios.reduce(function (sum, item) { return sum + item; }, 0) / ketercapaianRatios.length
            : 0;

        if (isTargetComplete) {
            html += '<tr class="table-light">'
                + '<td colspan="9">'
                + '<div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 py-2">'
                + '<div class="fw-semibold text-success-emphasis">Persentase ketercapaian MK terhadap perkiraan sumbangan ke CPL</div>'
                + '<div class="d-flex flex-wrap gap-2">'
                + '<div class="rounded-3 border px-3 py-2 bg-white border-success-subtle text-success-emphasis text-end">'
                + '<div class="small text-uppercase fw-semibold">Target</div>'
                + '<div class="fs-4 fw-bold">' + formatNum(totalPkAllCpl) + '%</div>'
                + '</div>'
                + '<div class="rounded-3 border px-3 py-2 bg-white border-primary-subtle text-primary-emphasis text-end">'
                + '<div class="small text-uppercase fw-semibold">Ketercapaian</div>'
                + '<div class="fs-4 fw-bold">' + formatNum(avgRatio) + '%</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</td>'
                + '</tr>';
        } else {
            html += '<tr class="table-danger">'
                + '<td colspan="9">'
                + '<div class="alert alert-danger mb-0 py-2 px-3 d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2">'
                + '<div class="d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><span>Nilai ketercapaian belum ditampilkan. Menunggu target PK mencapai 100%.</span></div>'
                + '<div class="fw-semibold">Target saat ini: ' + formatNum(totalPkAllCpl) + '%</div>'
                + '</div>'
                + '</td>'
                + '</tr>';
        }

        tbody.innerHTML = html;
    };

    const renderAllTables = function () {
        const semesterId = semesterFilter ? String(semesterFilter.value || '') : '';
        updateKelasTabsForSemester(semesterId);

        kelasList.forEach(function (kelas) {
            renderCplTable(kelas, semesterId);
        });
    };

    if (semesterFilter) {
        semesterFilter.addEventListener('change', renderAllTables);
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
