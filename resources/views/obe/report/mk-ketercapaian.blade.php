@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    <strong>Rekapitulasi persentase ketercapaian sumbangan CPL pada Mata Kuliah</strong>
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
                                <option value="">Pilih Semester</option>
                                @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" @selected((string) $semester->id === (string) $defaultSemesterId)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Target Kelulusan CPL</div>
                        <div class="col">
                            <strong id="target-kelulusan">{{ $mk->kurikulum->target_capaian_lulusan ?? 100 }}%</strong>
                        </div>
                    </div>
                    <hr>
                    @include('components.menu-mk',$mk)
                    <hr>
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
                                        <div class="card-header"><strong>Evaluasi Ketercapaian CPL {{ $kelas === '__SEMUA_KELAS__' ? 'Semua Kelas' : 'kelas ' . $kelas }}</strong></div>
                                        <div class="card-body">
                                            <div class="table-responsive nilai-matrix-wrapper">
                                                <table class="table table-bordered table-striped nilai-matrix-table mb-0">
                                                    <thead>
                                                        <tr class="text-center align-middle">
                                                            <th>CPL yang dibebankan pada MK</th>
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
    const hierarchyData = @json($hierarchyData);
    const rnData = @json($rnData);

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
        return {
            totalPk,
            totalPkRn,
            ratio,
            text: formatNum(totalPkRn) + '% dari perkiraan ' + formatNum(totalPk) + '% (' + formatNum(ratio) + '%)',
        };
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
        let totalPkAllCpl = 0;
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
                            html += '<td rowspan="' + cplRowCount + '">' + escapeHtml(cplLabel) + '</td>';
                            cplRowRendered = true;
                        }

                        if (!cpmkRowRendered) {
                            const cpmkLabel = (cpmk.kode ? cpmk.kode : '-') + ' - ' + (cpmk.nama ? cpmk.nama : '-');
                            html += '<td rowspan="' + cpmkRowCount + '">' + escapeHtml(cpmkLabel) + '</td>';
                            cpmkRowRendered = true;
                        }

                        if (!subRowRendered) {
                            const subLabel = (subcpmk.kode ? subcpmk.kode : '-') + ' - ' + (subcpmk.nama ? subcpmk.nama : '-');
                            html += '<td rowspan="' + subRowCount + '">' + escapeHtml(subLabel) + '</td>';
                            html += '<td rowspan="' + subRowCount + '">' + (subcpmk.indikator ? escapeHtml(subcpmk.indikator) : '-') + '</td>';
                            subRowRendered = true;
                        }

                        html += '<td>' + sourceLabel + '</td>';
                        html += '<td class="text-end">' + formatNum(pk) + '%</td>';
                        html += '<td class="text-end">' + formatNum(rn) + '</td>';
                        html += '<td class="text-end">' + formatNum(pkrn) + '%</td>';

                        if (subcpmk === subcpmks[0] && cpmk === cpmks[0] && source === realSources[0]) {
                            html += '<td rowspan="' + cplRowCount + '" class="text-center align-middle">'
                                + escapeHtml(cplTotals.text)
                                + '</td>';
                        }

                        html += '</tr>';
                    });
                });
            });

            ketercapaianRatios.push(cplTotals.ratio);
            totalPkAllCpl += cplTotals.totalPk;
        });

        const avgRatio = ketercapaianRatios.length > 0
            ? ketercapaianRatios.reduce(function (sum, item) { return sum + item; }, 0) / ketercapaianRatios.length
            : 0;

        html += '<tr>'
            + '<td colspan="5"><strong>Persentase ketercapaian MK terhadap perkiraan sumbangan ke CPL</strong></td>'
            + '<td><strong>Target: ' + formatNum(totalPkAllCpl) + '%</strong></td>'
            + '<td></td>'
            + '<td colspan="2"><strong>Ketercapaian: ' + formatNum(avgRatio) + '%</strong></td>'
            + '</tr>';

        tbody.innerHTML = html;
    };

    const renderAllTables = function () {
        const semesterId = semesterFilter ? String(semesterFilter.value || '') : '';

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
