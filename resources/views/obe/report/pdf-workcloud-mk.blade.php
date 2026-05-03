@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- @include('components.mk-flow-info', ['mk' => $mk]) --}}
            @include('components.identitas-mk', $mk)
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card" id="report-card-mk-prodi">
                <x-obe.header
                    title="Laporan Mata Kuliah ke Prodi"
                    subtitle="Portofolio penilaian dan evaluasi per kelas"
                    icon="bi bi-journal-check" />
                <div class="card-body bg-light-subtle">
                    <div class="card mb-3 identity-card border-0 shadow-sm">
                        <div class="card-header fw-semibold identity-header">A. Identitas Mata Kuliah</div>
                        <div class="card-body bg-white identity-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle mb-0 identity-table">
                                    <tbody>
                                        <tr>
                                            <th style="width: 220px;">Mata Kuliah</th>
                                            <td>
                                                <div class="identity-main">{{ $mk->nama }}</div>
                                                {{-- <div class="identity-sub text-muted">Dokumen portofolio kelas aktif</div> --}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Kode MK / SKS</th>
                                            <td>
                                                <span class="identity-chip">{{ $mk->kode }}</span>
                                                <span class="identity-separator">/</span>
                                                <span class="identity-chip identity-chip-muted">{{ $mk->sks }} SKS</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Semester</th>
                                            <td><span class="identity-chip">{{ $semester?->kode ? $semester->kode . ' - ' . $semester->nama : '-' }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Dosen Pengampu</th>
                                            <td><span class="identity-chip identity-chip-soft">{{ $mk->dosenPengampu->nama ?? auth()->user()->name ?? '-' }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Target Kelulusan CPL</th>
                                            <td><span class="identity-chip identity-chip-success">{{ number_format((float) $targetKelulusan, 2) }}%</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if (collect($semesters ?? [])->isNotEmpty())
                    <div class="row mb-3">
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3 border bg-white d-flex flex-column align-items-start gap-2">
                                <span>Semester :</span>
                                <select id="laporan-semester-filter" class="form-control form-control-sm w-100" style="max-width: 320px;">
                                    @foreach ($semesters as $sem)
                                        <option value="{{ $sem->id }}" @selected((string) $sem->id === $selectedSemesterId)>{{ $sem->kode }} - {{ $sem->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif

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
                                    $data = $reportByClass[$kelas] ?? [];
                                    $assessmentPlan = $data['assessment_plan'] ?? [];
                                    $nilaiColumns = $data['nilai_columns'] ?? [];
                                    $nilaiRows = $data['nilai_rows'] ?? [];
                                    $avgPerColumn = $data['avg_per_column'] ?? [];
                                    $achievementRows = $data['achievement_rows'] ?? [];
                                    $ketercapaianRows = $data['ketercapaian_rows'] ?? [];
                                    $ketercapaianDetailRows = $data['ketercapaian_detail_rows'] ?? [];
                                    $gradeDist = $data['grade_distribution'] ?? ['total' => 0, 'counts' => []];

                                    $detailRows = collect($ketercapaianDetailRows)->values();
                                    $cplRowsMap = $detailRows->groupBy('cpl')->map(fn ($rows) => $rows->count())->all();
                                    $cpmkRowsMap = $detailRows->groupBy(fn ($row) => ($row['cpl'] ?? '-') . '||' . ($row['cpmk'] ?? '-'))->map(fn ($rows) => $rows->count())->all();
                                    $subcpmkRowsMap = $detailRows->groupBy(fn ($row) => ($row['cpl'] ?? '-') . '||' . ($row['cpmk'] ?? '-') . '||' . ($row['subcpmk'] ?? '-'))->map(fn ($rows) => $rows->count())->all();

                                    $buildSpyderSvg = function (array $labels, array $values, string $title, ?float $target = null) {
                                        $axisCount = count($labels);
                                        if ($axisCount === 0) {
                                            return '<div class="text-muted">Belum ada data grafik.</div>';
                                        }

                                        $size = 280;
                                        $centerX = 140;
                                        $centerY = 140;
                                        $radius = 92;
                                        $rings = [20, 40, 60, 80, 100];

                                        $escape = fn ($text) => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
                                        $pointFor = function (int $index, float $percent) use ($axisCount, $centerX, $centerY, $radius) {
                                            $angle = -M_PI_2 + (2 * M_PI * $index / $axisCount);
                                            $scale = max(0, min(100, $percent)) / 100;
                                            $x = $centerX + cos($angle) * $radius * $scale;
                                            $y = $centerY + sin($angle) * $radius * $scale;
                                            return [$x, $y];
                                        };

                                        $svg = '<svg width="100%" viewBox="0 0 ' . $size . ' ' . $size . '" xmlns="http://www.w3.org/2000/svg">';
                                        foreach ($rings as $ring) {
                                            $ringPoints = [];
                                            for ($axisIndex = 0; $axisIndex < $axisCount; $axisIndex++) {
                                                [$x, $y] = $pointFor($axisIndex, (float) $ring);
                                                $ringPoints[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
                                            }
                                            $svg .= '<polygon points="' . implode(' ', $ringPoints) . '" fill="none" stroke="#d9d9d9" stroke-width="1"/>';
                                        }

                                        $dataPoints = [];
                                        $targetPoints = [];
                                        for ($axisIndex = 0; $axisIndex < $axisCount; $axisIndex++) {
                                            [$outerX, $outerY] = $pointFor($axisIndex, 100);
                                            $svg .= '<line x1="' . $centerX . '" y1="' . $centerY . '" x2="' . number_format($outerX, 2, '.', '') . '" y2="' . number_format($outerY, 2, '.', '') . '" stroke="#d0d0d0" stroke-width="1"/>';

                                            $labelX = $centerX + ($outerX - $centerX) * 1.16;
                                            $labelY = $centerY + ($outerY - $centerY) * 1.16;
                                            $label = $escape($labels[$axisIndex] ?? '-');
                                            $svg .= '<text x="' . number_format($labelX, 2, '.', '') . '" y="' . number_format($labelY, 2, '.', '') . '" font-size="8" text-anchor="middle" fill="#333">' . $label . '</text>';

                                            [$valueX, $valueY] = $pointFor($axisIndex, (float) ($values[$axisIndex] ?? 0));
                                            $dataPoints[] = number_format($valueX, 2, '.', '') . ',' . number_format($valueY, 2, '.', '');

                                            if ($target !== null) {
                                                [$targetX, $targetY] = $pointFor($axisIndex, $target);
                                                $targetPoints[] = number_format($targetX, 2, '.', '') . ',' . number_format($targetY, 2, '.', '');
                                            }
                                        }

                                        if ($target !== null && count($targetPoints) === $axisCount) {
                                            $svg .= '<polygon points="' . implode(' ', $targetPoints) . '" fill="none" stroke="#dc3545" stroke-width="1.5" stroke-dasharray="4 3"/>';
                                        }

                                        $svg .= '<polygon points="' . implode(' ', $dataPoints) . '" fill="rgba(54, 162, 235, 0.28)" stroke="#2f80ed" stroke-width="2"/>';
                                        foreach ($dataPoints as $point) {
                                            [$dotX, $dotY] = explode(',', $point);
                                            $svg .= '<circle cx="' . $dotX . '" cy="' . $dotY . '" r="2" fill="#2f80ed"/>';
                                        }

                                        $svg .= '</svg>';
                                        $legend = $target !== null
                                            ? '<div class="small text-muted mt-1">Target kelulusan: ' . number_format((float) $target, 2) . '%</div>'
                                            : '';

                                        return '<div><div class="fw-semibold mb-2">' . $escape($title) . '</div>' . $svg . $legend . '</div>';
                                    };

                                    $cplChartLabels = collect($achievementRows)->pluck('kode')->filter()->values()->all();
                                    $cplChartValues = collect($achievementRows)->pluck('avg')->map(fn ($val) => (float) ($val ?? 0))->values()->all();

                                    $cpmkChart = collect($detailRows)
                                        ->groupBy(fn ($row) => (string) ($row['cpmk_code'] ?? '-'))
                                        ->map(function ($rows, $label) {
                                            $ratios = $rows->pluck('cpmk_ratio')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
                                            return ['label' => $label, 'value' => $ratios->isNotEmpty() ? $ratios->avg() : 0];
                                        })
                                        ->sortBy('label')
                                        ->values();

                                    $subcpmkChart = collect($detailRows)
                                        ->groupBy(fn ($row) => (string) ($row['subcpmk_code'] ?? '-'))
                                        ->map(function ($rows, $label) {
                                            $ratios = $rows->pluck('subcpmk_ratio')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
                                            return ['label' => $label, 'value' => $ratios->isNotEmpty() ? $ratios->avg() : 0];
                                        })
                                        ->sortBy('label')
                                        ->values();

                                    $penugasanChart = collect($nilaiColumns)
                                        ->map(function ($col) use ($avgPerColumn) {
                                            $penugasanId = (string) ($col['penugasan_id'] ?? '');
                                            $asesmen = (string) ($col['asesmen'] ?? '-');
                                            $kode = trim(explode(' - ', $asesmen)[0] ?? $asesmen);

                                            return [
                                                'label' => $kode !== '' ? $kode : '-',
                                                'value' => (float) ($avgPerColumn[$penugasanId] ?? 0),
                                            ];
                                        })
                                        ->sortBy('label')
                                        ->values();
                                @endphp
                                <div
                                    class="tab-pane fade {{ $kelas === $defaultKelas ? 'show active' : '' }}"
                                    id="{{ $kelasPaneId }}"
                                    role="tabpanel"
                                    aria-labelledby="{{ $kelasPaneId }}-tab">

                                    <div class="d-flex justify-content-end gap-2 mb-3">
                                        <a href="{{ route('mks.laporan.download', ['mk' => $mk->id, 'kelas' => $kelas]) }}" class="btn btn-outline-danger btn-sm" target="_blank" rel="noopener">
                                            <i class="bi bi-file-earmark-pdf"></i> Lihat PDF Kelas {{ $kelas }}
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-print-laporan">
                                            <i class="bi bi-printer"></i> Cetak
                                        </button>
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-header bg-primary-subtle text-primary-emphasis fw-semibold">Tabel Rencana Evaluasi (<i>Assessment Plan</i>)</div>
                                        <div class="card-body bg-white">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle mb-0">
                                                    <thead class="table-light text-center">
                                                        <tr>
                                                            <th>Komponen Penilaian</th>
                                                            <th>Bentuk Asesmen & Instrumen</th>
                                                            <th>Bobot (%)</th>
                                                            <th>Mengukur CPL</th>
                                                            <th>Mengukur CPMK</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $assessmentPlanGrouped = collect($assessmentPlan)
                                                                ->groupBy(fn ($row) => (string) ($row['workcloud'] ?? '-'))
                                                                ->map(function ($rows) {
                                                                    return $rows->sortBy(fn ($item) => mb_strtolower((string) ($item['penugasan'] ?? '')))->values();
                                                                });
                                                        @endphp
                                                        @forelse ($assessmentPlanGrouped as $komponen => $rows)
                                                            @foreach ($rows as $indexRow => $row)
                                                                <tr>
                                                                    @if ($indexRow === 0)
                                                                        <td rowspan="{{ count($rows) }}">{{ $komponen }}</td>
                                                                    @endif
                                                                    <td>{{ $row['penugasan'] ?? '-' }}</td>
                                                                    <td class="text-end">{{ number_format((float) ($row['bobot'] ?? 0), 2) }}</td>
                                                                    <td>{!! collect($row['cpl_items'] ?? [])->isNotEmpty() ? e(collect($row['cpl_items'])->implode(', ')) : '<span class="text-muted">-</span>' !!}</td>
                                                                    <td>{!! collect($row['cpmk_items'] ?? [])->isNotEmpty() ? e(collect($row['cpmk_items'])->implode(', ')) : '<span class="text-muted">-</span>' !!}</td>
                                                                </tr>
                                                            @endforeach
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center text-muted">Belum ada data rencana evaluasi.</td>
                                                            </tr>
                                                        @endforelse
                                                        @if (collect($assessmentPlan)->isNotEmpty())
                                                            <tr class="table-light fw-semibold">
                                                                <td colspan="2" class="text-end">Total</td>
                                                                <td class="text-end">{{ number_format((float) collect($assessmentPlan)->sum('bobot'), 2) }}</td>
                                                                <td class="text-center">-</td>
                                                                <td class="text-center">-</td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-header bg-info-subtle text-info-emphasis fw-semibold">B. Tabel Nilai Mahasiswa (<i>Workcloud Utama</i>)</div>
                                        <div class="card-body bg-white">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle mb-0">
                                                    <thead class="table-light text-center">
                                                        <tr>
                                                            <th rowspan="2">No.</th>
                                                            <th rowspan="2">NPM</th>
                                                            <th rowspan="2">Nama Mahasiswa</th>
                                                            @foreach ($nilaiColumns as $col)
                                                                <th>
                                                                    {{ $col['asesmen'] ?? $col['asesmen'] ?? '-' }}
                                                                    <br><small class="text-muted">{{ $col['cpl_label'] ?? '-' }}</small>
                                                                </th>
                                                            @endforeach
                                                            <th rowspan="2">Nilai Akhir</th>
                                                            <th rowspan="2">Huruf Mutu</th>
                                                        </tr>
                                                        <tr>
                                                            @foreach ($nilaiColumns as $col)
                                                                <th>{{ number_format((float) ($col['bobot'] ?? 0), 2) }}%</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($nilaiRows as $index => $row)
                                                            <tr>
                                                                <td class="text-center">{{ $index + 1 }}</td>
                                                                <td>{{ $row['nim'] ?? '-' }}</td>
                                                                <td>{{ $row['nama'] ?? '-' }}</td>
                                                                @foreach ($nilaiColumns as $col)
                                                                    @php
                                                                        $penugasanId = (string) ($col['penugasan_id'] ?? '');
                                                                        $nilai = $row['scores'][$penugasanId] ?? null;
                                                                    @endphp
                                                                    <td class="text-end">{{ $nilai !== null ? number_format((float) $nilai, 2) : '-' }}</td>
                                                                @endforeach
                                                                <td class="text-end">{{ $row['nilai_akhir'] !== null ? number_format((float) $row['nilai_akhir'], 2) : '-' }}</td>
                                                                <td class="text-center">{{ $row['nilai_huruf'] ?? '-' }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="{{ 5 + count($nilaiColumns) }}" class="text-center text-muted">Belum ada data nilai mahasiswa pada kelas ini.</td>
                                                            </tr>
                                                        @endforelse
                                                        @if (collect($nilaiRows)->isNotEmpty())
                                                            <tr class="table-light fw-semibold">
                                                                <td colspan="3" class="text-end">Rata-rata Kelas</td>
                                                                @foreach ($nilaiColumns as $col)
                                                                    @php
                                                                        $penugasanId = (string) ($col['penugasan_id'] ?? '');
                                                                        $nilaiAvg = $avgPerColumn[$penugasanId] ?? null;
                                                                    @endphp
                                                                    <td class="text-end fw-semibold">{{ $nilaiAvg !== null ? number_format((float) $nilaiAvg, 2) : '-' }}</td>
                                                                @endforeach
                                                                <td class="text-end">{{ isset($data['avg_final_score']) && $data['avg_final_score'] !== null ? number_format((float) $data['avg_final_score'], 2) : '-' }}</td>
                                                                <td class="text-center">-</td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-header bg-warning-subtle text-warning-emphasis fw-semibold">C1. Evaluasi Ketercapaian CPL</div>
                                        <div class="card-body bg-white">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle mb-0">
                                                    <thead class="table-light text-center">
                                                        <tr>
                                                            <th>Kode CPL</th>
                                                            <th>Deskripsi Singkat CPL</th>
                                                            <th>Komponen Penilaian Penyumbang Nilai</th>
                                                            <th>Rata-rata Capaian Kelas</th>
                                                            <th>Status Ketercapaian</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($achievementRows as $row)
                                                            <tr>
                                                                <td class="text-center">{{ $row['kode'] ?? '-' }}</td>
                                                                <td>{{ $row['nama'] ?? '-' }}</td>
                                                                <td>{!! collect($row['components'] ?? [])->isNotEmpty() ? e(collect($row['components'])->implode(', ')) : '<span class="text-muted">-</span>' !!}</td>
                                                                <td class="text-end">{{ $row['avg'] !== null ? number_format((float) $row['avg'], 2) . '%' : '-' }}</td>
                                                                <td class="text-center">
                                                                    @if ($row['status'] === true)
                                                                        <span class="badge bg-success">Tercapai</span>
                                                                    @elseif ($row['status'] === false)
                                                                        <span class="badge bg-danger">Belum Tercapai</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center text-muted">Belum ada data evaluasi ketercapaian CPL.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-header bg-secondary-subtle text-secondary-emphasis fw-semibold">C2. Detail Ketercapaian CPL-CPMK-SubCPMK</div>
                                        <div class="card-body bg-white">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle mb-0">
                                                    <thead class="table-light text-center">
                                                        <tr>
                                                            <th>CPL</th>
                                                            <th>CPMK</th>
                                                            <th>SubCPMK</th>
                                                            <th>Indikator</th>
                                                            <th>Sumber Data</th>
                                                            <th>PK (%)</th>
                                                            <th>RN</th>
                                                            <th>PK × RN (%)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($detailRows as $idx => $row)
                                                            @php
                                                                $cplKey = $row['cpl'] ?? '-';
                                                                $cpmkKey = ($row['cpl'] ?? '-') . '||' . ($row['cpmk'] ?? '-');
                                                                $subcpmkKey = ($row['cpl'] ?? '-') . '||' . ($row['cpmk'] ?? '-') . '||' . ($row['subcpmk'] ?? '-');
                                                                $prev = $idx > 0 ? $detailRows[$idx - 1] : null;
                                                                $isFirstCpl = !$prev || (($prev['cpl'] ?? '-') !== ($row['cpl'] ?? '-'));
                                                                $isFirstCpmk = !$prev || (($prev['cpl'] ?? '-') !== ($row['cpl'] ?? '-')) || (($prev['cpmk'] ?? '-') !== ($row['cpmk'] ?? '-'));
                                                                $isFirstSub = !$prev || (($prev['cpl'] ?? '-') !== ($row['cpl'] ?? '-')) || (($prev['cpmk'] ?? '-') !== ($row['cpmk'] ?? '-')) || (($prev['subcpmk'] ?? '-') !== ($row['subcpmk'] ?? '-'));
                                                            @endphp
                                                            <tr>
                                                                @if ($isFirstCpl)
                                                                    <td rowspan="{{ $cplRowsMap[$cplKey] ?? 1 }}">{{ $row['cpl'] ?? '-' }}<br><small class="text-muted">ketercapaian: {{ $row['cpl_ratio'] !== null ? number_format((float) $row['cpl_ratio'], 2) : '-' }}%</small></td>
                                                                @endif
                                                                @if ($isFirstCpmk)
                                                                    <td rowspan="{{ $cpmkRowsMap[$cpmkKey] ?? 1 }}">{{ $row['cpmk'] ?? '-' }}<br><small class="text-muted">ketercapaian: {{ $row['cpmk_ratio'] !== null ? number_format((float) $row['cpmk_ratio'], 2) : '-' }}%</small></td>
                                                                @endif
                                                                @if ($isFirstSub)
                                                                    <td rowspan="{{ $subcpmkRowsMap[$subcpmkKey] ?? 1 }}">{{ $row['subcpmk'] ?? '-' }}<br><small class="text-muted">ketercapaian: {{ $row['subcpmk_ratio'] !== null ? number_format((float) $row['subcpmk_ratio'], 2) : '-' }}%</small></td>
                                                                @endif
                                                                <td>{{ $row['indikator'] ?? '-' }}</td>
                                                                <td>{{ $row['source'] ?? '-' }}</td>
                                                                <td class="text-end">{{ number_format((float) ($row['pk'] ?? 0), 2) }}</td>
                                                                <td class="text-end">{{ number_format((float) ($row['rn'] ?? 0), 2) }}</td>
                                                                <td class="text-end">{{ number_format((float) ($row['pkrn'] ?? 0), 2) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="8" class="text-center text-muted">Belum ada data detail ketercapaian.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-0">
                                        <div class="card-header bg-success-subtle text-success-emphasis fw-semibold">D. Distribusi Nilai</div>
                                        <div class="card-body bg-white">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle mb-0">
                                                    <thead class="table-light text-center">
                                                        <tr>
                                                            <th>Nilai Huruf</th>
                                                            <th>Jumlah Mahasiswa</th>
                                                            <th>Persentase (%)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($gradeOrder as $grade)
                                                            @php
                                                                $jumlah = (int) ($gradeDist['counts'][$grade] ?? 0);
                                                                $total = (int) ($gradeDist['total'] ?? 0);
                                                                $persentase = $total > 0 ? ($jumlah / $total) * 100 : 0;
                                                            @endphp
                                                            <tr>
                                                                <td class="text-center">{{ $grade }}</td>
                                                                <td class="text-center">{{ $jumlah }}</td>
                                                                <td class="text-center">{{ number_format($persentase, 2) }}%</td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="table-light fw-semibold">
                                                            <td class="text-center">TOTAL</td>
                                                            <td class="text-center">{{ (int) ($gradeDist['total'] ?? 0) }}</td>
                                                            <td class="text-center">{{ (int) ($gradeDist['total'] ?? 0) > 0 ? '100.00%' : '0.00%' }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mt-3 spyder-card">
                                        <div class="card-header bg-dark-subtle text-dark-emphasis fw-semibold">E1. Jaring Laba-laba Ketercapaian CPL</div>
                                        <div class="card-body bg-white">
                                            {!! $buildSpyderSvg($cplChartLabels, $cplChartValues, 'Jaring Laba-laba Ketercapaian CPL', (float) $targetKelulusan) !!}
                                        </div>
                                    </div>

                                    <div class="card mt-3 spyder-card">
                                        <div class="card-header bg-dark-subtle text-dark-emphasis fw-semibold">E2. Jaring Laba-laba Ketercapaian CPMK</div>
                                        <div class="card-body bg-white">
                                            {!! $buildSpyderSvg($cpmkChart->pluck('label')->all(), $cpmkChart->pluck('value')->all(), 'Jaring Laba-laba Ketercapaian CPMK', (float) $targetKelulusan) !!}
                                        </div>
                                    </div>

                                    <div class="card mt-3 spyder-card">
                                        <div class="card-header bg-dark-subtle text-dark-emphasis fw-semibold">E3. Jaring Laba-laba Ketercapaian SubCPMK</div>
                                        <div class="card-body bg-white">
                                            {!! $buildSpyderSvg($subcpmkChart->pluck('label')->all(), $subcpmkChart->pluck('value')->all(), 'Jaring Laba-laba Ketercapaian SubCPMK', (float) $targetKelulusan) !!}
                                        </div>
                                    </div>

                                    <div class="card mt-3 mb-0 spyder-card">
                                        <div class="card-header bg-dark-subtle text-dark-emphasis fw-semibold">E4. Jaring Laba-laba Rata-rata Penugasan</div>
                                        <div class="card-body bg-white">
                                            {!! $buildSpyderSvg($penugasanChart->pluck('label')->all(), $penugasanChart->pluck('value')->all(), 'Jaring Laba-laba Rata-rata Penugasan') !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">Belum ada data kelas pada mata kuliah ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .identity-card {
        border-radius: 16px;
        overflow: hidden;
    }

    .identity-header {
        border: 0;
        color: #0f172a;
        text-shadow: none;
        letter-spacing: .03em;
        background: linear-gradient(135deg, #dbeafe 0%, #e2e8f0 52%, #f1f5f9 100%);
        border-bottom: 1px solid #cbd5e1;
    }

    .identity-body {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .identity-table {
        border-color: #dbe7f3;
    }

    .identity-table th {
        color: #0f172a;
        font-weight: 700;
        background: #f1f5f9;
    }

    .identity-table td {
        color: #0f172a;
    }

    .identity-main {
        font-size: 1.03rem;
        font-weight: 700;
        color: #0f172a;
    }

    .identity-sub {
        font-size: .82rem;
        margin-top: .15rem;
        color: #334155 !important;
    }

    .identity-chip {
        display: inline-flex;
        align-items: center;
        padding: .22rem .62rem;
        border-radius: 999px;
        border: 1px solid #9ec5fe;
        background: #dbeafe;
        color: #102a56;
        font-weight: 600;
        font-size: .8rem;
    }

    .identity-chip-soft {
        border-color: #a5b4fc;
        background: #e0e7ff;
        color: #312e81;
    }

    .identity-chip-success {
        border-color: #86efac;
        background: #dcfce7;
        color: #14532d;
    }

    .identity-chip-muted {
        border-color: #94a3b8;
        background: #e2e8f0;
        color: #0f172a;
    }

    .identity-separator {
        margin: 0 .35rem;
        color: #334155;
        font-weight: 600;
    }

    .spyder-card {
        height: 480px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .spyder-card .card-header {
        flex: 0 0 auto;
    }

    .spyder-card .card-body {
        flex: 1 1 auto;
        overflow: hidden;
        display: flex;
        align-items: stretch;
    }

    .spyder-card .card-body > div {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .spyder-card .card-body svg {
        width: 100%;
        height: 100%;
        flex: 1 1 auto;
        min-height: 0;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #report-card-mk-prodi,
        #report-card-mk-prodi * {
            visibility: visible !important;
        }

        #report-card-mk-prodi {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        #report-card-mk-prodi .btn,
        #report-card-mk-prodi .nav-tabs {
            display: none !important;
        }

        #report-card-mk-prodi .tab-pane {
            display: none !important;
            opacity: 1 !important;
        }

        #report-card-mk-prodi .tab-pane.show.active {
            display: block !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-print-laporan').forEach(function (button) {
            button.addEventListener('click', function () {
                window.print();
            });
        });

        const laporanSemesterFilter = document.getElementById('laporan-semester-filter');
        if (laporanSemesterFilter) {
            laporanSemesterFilter.addEventListener('change', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('semester_id', this.value);
                window.location.href = url.toString();
            });
        }
    });
</script>
@endpush
