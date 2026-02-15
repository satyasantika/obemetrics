@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    <strong>Evaluasi Ketercapaian CPL</strong>
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
                            {{-- <input type="number" step="1" min="0" max="100" class="form-control form-control-sm" id="target-kelulusan" value="{{ $targetKelulusan ?? '' }}" placeholder="Masukkan target kelulusan CPL (%)" style="max-width: 320px;"> --}}
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
                            @foreach ($kelasList as $kelas)
                                @php
                                    $kelasSlug = \Illuminate\Support\Str::slug($kelas, '-');
                                    $kelasPaneId = 'kelas-' . ($kelasSlug !== '' ? $kelasSlug : 'tanpa-kelas');
                                @endphp
                                <div
                                    class="tab-pane fade {{ $kelas === $defaultKelas ? 'show active' : '' }}"
                                    id="{{ $kelasPaneId }}"
                                    role="tabpanel"
                                    aria-labelledby="{{ $kelasPaneId }}-tab">
                                    <div class="card mb-3">
                                        <div class="card-header"><strong>Evaluasi Ketercapaian CPL kelas {{ $kelas }}</strong></div>
                                        <div class="card-body">
                                            <div class="table-responsive nilai-matrix-wrapper">
                                                <table class="table table-bordered table-striped nilai-matrix-table mb-0">
                                                    <thead>
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
                                        <div class="card-header"><strong>DISTRIBUSI NILAI & KESIMPULAN (Student's Final Grade)</strong></div>
                                        <div class="card-body">
                                            <div class="table-responsive nilai-matrix-wrapper">
                                                <table class="table table-bordered table-striped nilai-matrix-table mb-0">
                                                    <thead>
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
    const cplRows = @json($cplRows);
    const gradeOrder = @json($gradeOrder);
    const achievementData = @json($achievementData);
    const componentsData = @json($componentsData);
    const gradeDistributionData = @json($gradeDistributionData);

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

        kelasList.forEach(function (kelas) {
            renderCplTable(kelas, semesterId, target);
            renderGradeTable(kelas, semesterId);
        });
    };

    if (semesterFilter) {
        semesterFilter.addEventListener('change', renderAllTables);
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
