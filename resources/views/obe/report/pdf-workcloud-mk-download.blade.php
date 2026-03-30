<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan MK {{ $mk->kode }} Kelas {{ $kelas }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h2 { margin: 0 0 8px 0; }
        .section-title { margin-top: 12px; margin-bottom: 6px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #777; padding: 4px; vertical-align: top; }
        th { background: #f1f1f1; text-align: center; }
        .table-b { table-layout: fixed; width: 100%; }
        .table-b th, .table-b td { word-break: break-word; overflow-wrap: anywhere; }
        .table-b .text-xs { font-size: 8px; line-height: 1.2; }
        .repeat-header thead { display: table-header-group; }
        .spyder-wrap { width: 100%; }
        .spyder-title { font-weight: bold; margin-bottom: 4px; text-align: center; }
        .spyder-box { border: 1px solid #777; padding: 6px; }
        .spyder-grid { width: 100%; border-collapse: separate; border-spacing: 8px; margin-top: 4px; }
        .spyder-grid td { width: 50%; vertical-align: top; }
        .spyder-card { border: 1px solid #777; height: 245px; padding: 6px; }
        .spyder-card-title { font-weight: bold; text-align: center; margin-bottom: 4px; }
        .spyder-card-body { display: table; width: 100%; height: 195px; }
        .spyder-card-body-inner { display: table-cell; vertical-align: middle; text-align: center; }
        .spyder-legend { margin-top: 4px; text-align: center; font-size: 8px; color: #666; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #666; }
        .list { margin: 6px 0 10px 18px; padding: 0; }
        .list li { margin: 2px 0; }
        tr { page-break-inside: avoid; }
        @page { margin: 24px 22px 42px 22px; }
    </style>
</head>
<body>
    <h2>DOKUMEN PORTOFOLIO PENILAIAN & EVALUASI MATA KULIAH (WORKCLOUD)</h2>

    <div class="section-title">A. IDENTITAS DAN RENCANA PENILAIAN</div>
    <table>
        <tbody>
            <tr><th style="width: 200px;">Mata Kuliah</th><td>{{ $mk->nama }} ({{ $mk->kode }})</td></tr>
            <tr><th>Kelas</th><td>{{ $kelas }}</td></tr>
            <tr><th>SKS</th><td>{{ $mk->sks }}</td></tr>
            <tr><th>Semester</th><td>{{ $semester?->kode ? $semester->kode . ' - ' . $semester->nama : '-' }}</td></tr>
            <tr><th>Dosen Pengampu</th><td>{{ $mk->dosenPengampu->nama ?? auth()->user()->name ?? '-' }}</td></tr>
            <tr><th>Target Kelulusan CPL</th><td>{{ number_format((float) $targetKelulusan, 2) }}%</td></tr>
        </tbody>
    </table>

    @php
        $assessmentPlan = $data['assessment_plan'] ?? [];
        $nilaiColumns = $data['nilai_columns'] ?? [];
        $nilaiRows = $data['nilai_rows'] ?? [];
        $avgPerColumn = $data['avg_per_column'] ?? [];
        $achievementRows = $data['achievement_rows'] ?? [];
        $ketercapaianDetailRows = $data['ketercapaian_detail_rows'] ?? [];
        $gradeDist = $data['grade_distribution'] ?? ['total' => 0, 'counts' => []];

        $detailRows = collect($ketercapaianDetailRows)->values();

        $buildSpyderSvg = function (array $labels, array $values, string $title, ?float $target = null) {
            $axisCount = count($labels);
            if ($axisCount === 0) {
                return '<div class="muted">Belum ada data grafik.</div>';
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

            $svg = '<svg width="280" height="280" viewBox="0 0 ' . $size . ' ' . $size . '" xmlns="http://www.w3.org/2000/svg">';
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

            $svg .= '<polygon points="' . implode(' ', $dataPoints) . '" fill="#36a2eb" fill-opacity="0.28" stroke="#2f80ed" stroke-width="2"/>';
            foreach ($dataPoints as $point) {
                [$dotX, $dotY] = explode(',', $point);
                $svg .= '<circle cx="' . $dotX . '" cy="' . $dotY . '" r="2" fill="#2f80ed"/>';
            }

            $svg .= '</svg>';
            $encodedSvg = base64_encode($svg);

            $legend = '<div class="spyder-legend">'
                . '<span style="color:#2f80ed;">&#9632;</span> Capaian'
                . ($target !== null
                    ? ' &nbsp; <span style="color:#dc3545;">&#8212;&#8212;</span> Target ' . number_format((float) $target, 2) . '%'
                    : '')
                . '</div>';

            return '<div class="spyder-wrap">'
                . '<div class="spyder-title">' . $escape($title) . '</div>'
                . '<img src="data:image/svg+xml;base64,' . $encodedSvg . '" alt="' . $escape($title) . '" style="width: 100%; max-width: 210px; height: auto; display: block; margin: 0 auto;"/>'
                . $legend
                . '</div>';
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

    <div class="section-title">Tabel Rencana Evaluasi (Assessment Plan)</div>
    <table>
        <thead>
            <tr>
                <th>Komponen Penilaian</th>
                <th>Bentuk Asesmen & Instrumen</th>
                <th>Bobot (%)</th>
                <th>Mengukur CPL</th>
                <th>Mengukur CPMK</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assessmentPlan as $row)
                <tr>
                    <td>{{ $row['workcloud'] ?? '-' }}</td>
                    <td>{{ $row['penugasan'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format((float) ($row['bobot'] ?? 0), 2) }}</td>
                    <td>{{ collect($row['cpl_items'] ?? [])->implode(', ') ?: '-' }}</td>
                    <td>{{ collect($row['cpmk_items'] ?? [])->implode(', ') ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center muted">Belum ada data rencana evaluasi.</td></tr>
            @endforelse
            <tr>
                <td colspan="2" class="text-right muted">Total</td>
                <td colspan="3"><strong>{{ number_format((float) collect($assessmentPlan)->sum(fn($row) => $row['bobot'] ?? 0), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">B. Tabel Nilai Mahasiswa</div>
    @php
        $assessmentCount = max(1, count($nilaiColumns));
        $fixedPercent = 4 + 10 + 17 + 8 + 6;
        $assessmentPercent = max(2, (100 - $fixedPercent) / $assessmentCount);
    @endphp
    <table class="table-b">
        <colgroup>
            <col style="width: 2%;">
            <col style="width: 10%;">
            <col style="width: 19%;">
            @foreach ($nilaiColumns as $col)
                <col style="width: {{ number_format($assessmentPercent, 4, '.', '') }}%;">
            @endforeach
            <col style="width: 8%;">
            <col style="width: 6%;">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">NPM</th>
                <th rowspan="2">Nama Mahasiswa</th>
                @foreach ($nilaiColumns as $col)
                    <th class="text-xs">
                        {{ $col['asesmen'] ?? $col['label'] ?? '-' }}
                        <br><span class="muted">{{ $col['cpl_label'] ?? '-' }}</span>
                    </th>
                @endforeach
                <th rowspan="2">Nilai Akhir</th>
                <th rowspan="2">Huruf Mutu</th>
            </tr>
            <tr>
                @foreach ($nilaiColumns as $col)
                    <th class="text-xs">{{ number_format((float) ($col['bobot'] ?? 0), 2) }}%</th>
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
                        <td class="text-right">{{ $nilai !== null ? number_format((float) $nilai, 2) : '-' }}</td>
                    @endforeach
                    <td class="text-right">{{ $row['nilai_akhir'] !== null ? number_format((float) $row['nilai_akhir'], 2) : '-' }}</td>
                    <td class="text-center">{{ $row['nilai_huruf'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ 5 + count($nilaiColumns) }}" class="text-center muted">Belum ada data nilai mahasiswa pada kelas ini.</td></tr>
            @endforelse
            @if (collect($nilaiRows)->isNotEmpty())
                <tr>
                    <td colspan="3" class="text-right"><strong>Rata-rata Kelas</strong></td>
                    @foreach ($nilaiColumns as $col)
                        @php
                            $penugasanId = (string) ($col['penugasan_id'] ?? '');
                            $nilaiAvg = $avgPerColumn[$penugasanId] ?? null;
                        @endphp
                        <td class="text-right"><strong>{{ $nilaiAvg !== null ? number_format((float) $nilaiAvg, 2) : '-' }}</strong></td>
                    @endforeach
                    <td class="text-right"><strong>{{ isset($data['avg_final_score']) && $data['avg_final_score'] !== null ? number_format((float) $data['avg_final_score'], 2) : '-' }}</strong></td>
                    <td class="text-center">-</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">C1. Evaluasi Ketercapaian CPL</div>
    <table class="repeat-header">
        <thead>
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
                    <td>{{ collect($row['components'] ?? [])->implode(', ') ?: '-' }}</td>
                    <td class="text-right">{{ $row['avg'] !== null ? number_format((float) $row['avg'], 2) . '%' : '-' }}</td>
                    <td class="text-center">
                        @if ($row['status'] === true)
                            Tercapai
                        @elseif ($row['status'] === false)
                            Belum Tercapai
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center muted">Belum ada data evaluasi ketercapaian CPL.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">C2. Detail Ketercapaian CPL-CPMK-SubCPMK</div>
    <table class="repeat-header">
        <thead>
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
            @forelse ($detailRows as $row)
                <tr>
                    <td>{{ $row['cpl'] ?? '-' }}<br><span class="muted">ketercapaian: {{ $row['cpl_ratio'] !== null ? number_format((float) $row['cpl_ratio'], 2) : '-' }}%</span></td>
                    <td>{{ $row['cpmk'] ?? '-' }}<br><span class="muted">ketercapaian: {{ $row['cpmk_ratio'] !== null ? number_format((float) $row['cpmk_ratio'], 2) : '-' }}%</span></td>
                    <td>{{ $row['subcpmk'] ?? '-' }}<br><span class="muted">ketercapaian: {{ $row['subcpmk_ratio'] !== null ? number_format((float) $row['subcpmk_ratio'], 2) : '-' }}%</span></td>
                    <td>{{ $row['indikator'] ?? '-' }}</td>
                    <td>{{ $row['source'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format((float) ($row['pk'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($row['rn'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($row['pkrn'] ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center muted">Belum ada data detail ketercapaian.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">D. Distribusi Nilai</div>
    <table>
        <thead>
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
            <tr>
                <td class="text-center"><strong>TOTAL</strong></td>
                <td class="text-center"><strong>{{ (int) ($gradeDist['total'] ?? 0) }}</strong></td>
                <td class="text-center"><strong>{{ (int) ($gradeDist['total'] ?? 0) > 0 ? '100.00%' : '0.00%' }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">E. Grafik Spyderweb</div>
    <table class="spyder-grid">
        <tr>
            <td>
                <div class="spyder-card">
                    <div class="spyder-card-title">E1. Jaring Laba-laba Ketercapaian CPL</div>
                    <div class="spyder-card-body">
                        <div class="spyder-card-body-inner">
                            {!! $buildSpyderSvg($cplChartLabels, $cplChartValues, 'CPL', (float) $targetKelulusan) !!}
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div class="spyder-card">
                    <div class="spyder-card-title">E2. Jaring Laba-laba Ketercapaian CPMK</div>
                    <div class="spyder-card-body">
                        <div class="spyder-card-body-inner">
                            {!! $buildSpyderSvg($cpmkChart->pluck('label')->all(), $cpmkChart->pluck('value')->all(), 'CPMK', (float) $targetKelulusan) !!}
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="spyder-card">
                    <div class="spyder-card-title">E3. Jaring Laba-laba Ketercapaian SubCPMK</div>
                    <div class="spyder-card-body">
                        <div class="spyder-card-body-inner">
                            {!! $buildSpyderSvg($subcpmkChart->pluck('label')->all(), $subcpmkChart->pluck('value')->all(), 'SubCPMK', (float) $targetKelulusan) !!}
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div class="spyder-card">
                    <div class="spyder-card-title">E4. Jaring Laba-laba Rata-rata Penugasan</div>
                    <div class="spyder-card-body">
                        <div class="spyder-card-body-inner">
                            {!! $buildSpyderSvg($penugasanChart->pluck('label')->all(), $penugasanChart->pluck('value')->all(), 'Penugasan', (float) $targetKelulusan) !!}
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
            $size = 9;
            $pageHeight = $pdf->get_height();
            $y = $pageHeight - 28;
            $downloadText = 'Diunduh: {{ $downloadedAt }}';
            $pageText = 'Halaman {PAGE_NUM} dari {PAGE_COUNT}';
            $copyrightText = '© Obemetrics';

            $centerWidth = $fontMetrics->getTextWidth($pageText, $font, $size);
            $centerX = ($pdf->get_width() - $centerWidth) / 2;

            $rightWidth = $fontMetrics->getTextWidth($copyrightText, $font, $size);
            $rightX = $pdf->get_width() - 22 - $rightWidth;

            $pdf->page_text(22, $y, $downloadText, $font, $size, [0, 0, 0]);
            $pdf->page_text($centerX, $y, $pageText, $font, $size, [0, 0, 0]);
            $pdf->page_text($rightX, $y, $copyrightText, $font, $size, [0, 0, 0]);
        }
    </script>
</body>
</html>
