<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\Evaluasi;
use App\Models\JoinCplBk;
use App\Models\JoinCplCpmk;
use App\Models\JoinSubcpmkPenugasan;
use App\Models\Mk;
use App\Models\Penugasan;
use App\Models\Semester;
use App\Models\Subcpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportMkMasterController extends Controller
{
    private const TARGETS = [
        'cpmks' => [
            'label' => 'CPMK',
            'columns' => ['kode', 'nama', 'deskripsi'],
            'required' => ['kode', 'nama'],
            'requires_semester' => false,
        ],
        'join_cpl_cpmks' => [
            'label' => 'CPMK pada CPL',
            'columns' => ['kode_cpl', 'cpl', 'kode_cpmk', 'nama_cpmk'],
            'required' => ['kode_cpl', 'kode_cpmk'],
            'requires_semester' => false,
        ],
        'subcpmks' => [
            'label' => 'SubCPMK',
            'columns' => ['kode_semester', 'kode_cpl', 'kode', 'nama', 'kompetensi_c', 'kompetensi_a', 'kompetensi_p', 'indikator', 'evaluasi', 'kode_cpmk', 'nama_cpmk'],
            'required' => ['kode_semester', 'kode_cpl', 'kode', 'nama'],
            'requires_semester' => true,
        ],
        'penugasans' => [
            'label' => 'Penugasan',
            'columns' => ['kode', 'nama', 'bobot', 'kode_evaluasi'],
            'required' => ['kode', 'nama', 'bobot', 'kode_evaluasi'],
            'requires_semester' => true,
        ],
        'join_subcpmk_penugasans' => [
            'label' => 'SubCPMK pada Penugasan',
            'columns' => ['kode_subcpmk', 'nama_subcpmk', 'kode_penugasan', 'nama_penugasan', 'bobot'],
            'required' => ['kode_subcpmk', 'kode_penugasan', 'bobot'],
            'requires_semester' => true,
        ],
    ];

    public function form(Mk $mk, Request $request)
    {
        $target = $this->resolveTarget($request->query('target'));
        $preview = session($this->previewSessionKey($mk, $target), []);
        $semesters = Semester::query()->orderBy('kode')->get();
        $returnUrl = $this->resolveReturnUrl($request);

        return view('setting.bulk-import.mk-master', [
            'mk' => $mk,
            'targets' => self::TARGETS,
            'target' => $target,
            'preview' => $preview,
            'semesters' => $semesters,
            'returnUrl' => $returnUrl,
        ]);
    }

    public function import(Request $request, Mk $mk)
    {
        $request->validate([
            'target' => 'required|string|in:' . implode(',', array_keys(self::TARGETS)),
            'semester_id' => 'nullable|exists:semesters,id',
            'file' => 'required|mimes:xlsx,csv,ods',
        ]);

        $target = $this->resolveTarget($request->input('target'));
        $meta = self::TARGETS[$target];

        if (!empty($meta['requires_semester']) && empty($request->semester_id)) {
            return back()->with('error', 'Semester wajib dipilih untuk target import ini.');
        }

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if ($target === 'join_subcpmk_penugasans') {
                $this->assertSemester((string) $request->semester_id);
                $previewRows = $this->parseJoinSubcpmkPenugasanMatrix($rows, $mk, (string) $request->semester_id);

                if (empty($previewRows)) {
                    return back()->with('error', 'Tidak ada bobot yang terisi pada template matriks.');
                }

                $saved = 0;
                foreach ($previewRows as $row) {
                    $this->persistRow($target, $row, $mk, (string) $request->semester_id);
                    $saved++;
                }

                session()->forget($this->previewSessionKey($mk, $target));

                return to_route('mks.joinsubcpmkpenugasans.index', ['mk' => $mk->id, 'semester_id' => $request->semester_id])
                    ->with('success', "data SubCPMK telah diimport ke Penugasan.");
            }

            $headerMap = $this->buildHeaderMap($rows[1] ?? []);
            $missingHeaders = collect($meta['required'])
                ->filter(fn ($column) => !array_key_exists($column, $headerMap))
                ->values()
                ->all();

            if (!empty($missingHeaders)) {
                return back()->with('error', 'Kolom wajib tidak ditemukan: ' . implode(', ', $missingHeaders));
            }

            $previewRows = [];
            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue;
                }

                $normalizedRow = [];
                foreach ($meta['columns'] as $column) {
                    $normalizedRow[$column] = $this->cellValue($row[$headerMap[$column] ?? ''] ?? null);
                }

                if ($this->isEmptyRow($normalizedRow)) {
                    continue;
                }

                $previewRows[] = $normalizedRow;
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data valid untuk dipreview.');
            }

            session([
                $this->previewSessionKey($mk, $target) => [
                    'target' => $target,
                    'semester_id' => $request->semester_id,
                    'filename' => $request->file('file')->getClientOriginalName(),
                    'rows' => $previewRows,
                ],
            ]);

            return to_route('setting.import.mk-master', $this->withReturnUrl([
                'mk' => $mk->id,
                'target' => $target,
                'semester_id' => $request->semester_id,
            ], $request))
                ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan diproses.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function commit(Request $request, Mk $mk)
    {
        $request->validate([
            'target' => 'required|string|in:' . implode(',', array_keys(self::TARGETS)),
            'semester_id' => 'nullable|exists:semesters,id',
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $target = $this->resolveTarget($request->input('target'));
        $meta = self::TARGETS[$target];
        $preview = session($this->previewSessionKey($mk, $target), []);
        $rows = $preview['rows'] ?? [];

        if (empty($rows)) {
            return to_route('setting.import.mk-master', $this->withReturnUrl([
                'mk' => $mk->id,
                'target' => $target,
                'semester_id' => $request->input('semester_id') ?: ($preview['semester_id'] ?? null),
            ], $request))
                ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        $semesterId = $request->input('semester_id') ?: ($preview['semester_id'] ?? null);
        if (!empty($meta['requires_semester']) && empty($semesterId)) {
            return to_route('setting.import.mk-master', $this->withReturnUrl([
                'mk' => $mk->id,
                'target' => $target,
                'semester_id' => $semesterId,
            ], $request))
                ->with('error', 'Semester wajib dipilih untuk target import ini.');
        }

        $selectedIndexes = $request->input('selected', []);
        $saved = 0;
        $skipped = [];

        foreach ($selectedIndexes as $idx) {
            if (!isset($rows[$idx])) {
                continue;
            }

            try {
                $this->persistRow($target, $rows[$idx], $mk, $semesterId);
                $saved++;
            } catch (\Throwable $e) {
                $skipped[] = 'Baris ' . ($idx + 2) . ': ' . $e->getMessage();
            }
        }

        session()->forget($this->previewSessionKey($mk, $target));

        $message = "{$saved} baris berhasil diproses.";
        if (!empty($skipped)) {
            $message .= ' Beberapa baris dilewati: ' . implode(' | ', array_slice($skipped, 0, 5));
        }

        return to_route('setting.import.mk-master', $this->withReturnUrl([
            'mk' => $mk->id,
            'target' => $target,
            'semester_id' => $semesterId,
        ], $request))
            ->with('success', $message);
    }

    public function template(Mk $mk, Request $request)
    {
        $target = $this->resolveTarget($request->query('target'));
        $meta = self::TARGETS[$target];

        if ($target === 'join_subcpmk_penugasans') {
            $semesterId = (string) $request->query('semester_id', '');
            $this->assertSemester($semesterId);

            $subcpmks = Subcpmk::query()
                ->where('semester_id', $semesterId)
                ->whereHas('joinCplCpmk', function ($query) use ($mk) {
                    $query->where('mk_id', $mk->id);
                })
                ->orderBy('kode')
                ->get();

            $penugasans = Penugasan::query()
                ->where('mk_id', $mk->id)
                ->where('semester_id', $semesterId)
                ->orderBy('kode')
                ->get();

            $bobotMap = JoinSubcpmkPenugasan::query()
                ->where('mk_id', $mk->id)
                ->where('semester_id', $semesterId)
                ->whereIn('subcpmk_id', $subcpmks->pluck('id'))
                ->whereIn('penugasan_id', $penugasans->pluck('id'))
                ->get()
                ->keyBy(fn ($item) => $item->penugasan_id . '_' . $item->subcpmk_id);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'penugasan');
            $sheet->getStyle('A1')->getFont()->setBold(true);

            $columnIndex = 2;
            foreach ($subcpmks as $subcpmk) {
                $column = Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue($column . '1', $subcpmk->kode);
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($column)->setAutoSize(true);
                $columnIndex++;
            }
            $sheet->getColumnDimension('A')->setWidth(36);

            $rowIndex = 2;
            foreach ($penugasans as $penugasan) {
                $sheet->setCellValue('A' . $rowIndex, $penugasan->kode . ': ' . $penugasan->nama);

                $columnIndex = 2;
                foreach ($subcpmks as $subcpmk) {
                    $column = Coordinate::stringFromColumnIndex($columnIndex);
                    $key = $penugasan->id . '_' . $subcpmk->id;
                    $bobot = $bobotMap[$key]->bobot ?? null;
                    $sheet->setCellValue($column . $rowIndex, $bobot);
                    $columnIndex++;
                }

                $rowIndex++;
            }

            $writer = new Xlsx($spreadsheet);
            $waktu_download = now()->format('YmdHis');
            $fileName = 'import' . $waktu_download . '-subcpmk-penugasan-matrix-' . Str::slug((string) ($mk->kode ?? 'mk'), '-') . '.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $column = 'A';
        foreach ($meta['columns'] as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        $writer = new Xlsx($spreadsheet);
        $waktu_download = now()->format('YmdHis');
        $fileName = 'import' . $waktu_download . '-' .Str::slug($meta['label'], '-') . '-' . Str::slug((string) ($mk->kode ?? 'mk'), '-') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clear(Mk $mk, Request $request)
    {
        $target = $this->resolveTarget($request->input('target'));
        session()->forget($this->previewSessionKey($mk, $target));

        return to_route('setting.import.mk-master', $this->withReturnUrl([
            'mk' => $mk->id,
            'target' => $target,
            'semester_id' => $request->input('semester_id'),
        ], $request))
            ->with('success', 'Preview berhasil dikosongkan.');
    }

    private function persistRow(string $target, array $row, Mk $mk, ?string $semesterId): void
    {
        switch ($target) {
            case 'cpmks':
                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                Cpmk::updateOrCreate(
                    ['mk_id' => $mk->id, 'kode' => $kode],
                    ['nama' => $nama, 'deskripsi' => $row['deskripsi'] ?? null]
                );
                return;

            case 'join_cpl_cpmks':
                $kodeCpl = $this->required($row['kode_cpl'] ?? null, 'kode_cpl');
                $kodeCpmk = $this->required($row['kode_cpmk'] ?? null, 'kode_cpmk');

                $cpl = Cpl::query()
                    ->where('kurikulum_id', $mk->kurikulum_id)
                    ->where('kode', $kodeCpl)
                    ->first();
                if (!$cpl) {
                    throw new \RuntimeException('CPL tidak ditemukan: ' . $kodeCpl);
                }

                $cpmk = Cpmk::query()->where('mk_id', $mk->id)->where('kode', $kodeCpmk)->first();
                if (!$cpmk) {
                    throw new \RuntimeException('CPMK tidak ditemukan: ' . $kodeCpmk);
                }

                $joinCplBks = JoinCplBk::query()
                    ->where('kurikulum_id', $mk->kurikulum_id)
                    ->where('cpl_id', $cpl->id)
                    ->get();

                if ($joinCplBks->isEmpty()) {
                    throw new \RuntimeException('Relasi CPL >< BK belum ada untuk kode_cpl: ' . $kodeCpl);
                }

                foreach ($joinCplBks as $joinCplBk) {
                    JoinCplCpmk::updateOrCreate(
                        ['mk_id' => $mk->id, 'join_cpl_bk_id' => $joinCplBk->id, 'cpmk_id' => $cpmk->id],
                        []
                    );
                }
                return;

            case 'subcpmks':
                $this->assertSemester($semesterId);

                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $kodeCpl = $this->required($row['kode_cpl'] ?? null, 'kode_cpl');

                $cpl = Cpl::query()
                    ->where('kurikulum_id', $mk->kurikulum_id)
                    ->where('kode', $kodeCpl)
                    ->first();
                if (!$cpl) {
                    throw new \RuntimeException('CPL tidak ditemukan: ' . $kodeCpl);
                }

                $joinCplBkIds = JoinCplBk::query()
                    ->where('kurikulum_id', $mk->kurikulum_id)
                    ->where('cpl_id', $cpl->id)
                    ->pluck('id');

                if ($joinCplBkIds->isEmpty()) {
                    throw new \RuntimeException('Relasi CPL >< BK tidak ditemukan untuk kode_cpl: ' . $kodeCpl);
                }

                $joinCplCpmkQuery = JoinCplCpmk::query()
                    ->where('mk_id', $mk->id)
                    ->whereIn('join_cpl_bk_id', $joinCplBkIds);

                $kodeCpmk = trim((string) ($row['kode_cpmk'] ?? ''));
                $namaCpmk = trim((string) ($row['nama_cpmk'] ?? ''));
                if ($kodeCpmk !== '') {
                    $cpmk = Cpmk::query()->where('mk_id', $mk->id)->where('kode', $kodeCpmk)->first();
                    if ($cpmk) {
                        $joinCplCpmkQuery->where('cpmk_id', $cpmk->id);
                    }
                } elseif ($namaCpmk !== '') {
                    $cpmk = Cpmk::query()->where('mk_id', $mk->id)->where('nama', $namaCpmk)->first();
                    if ($cpmk) {
                        $joinCplCpmkQuery->where('cpmk_id', $cpmk->id);
                    }
                }

                $joinCplCpmk = $joinCplCpmkQuery->first();
                if (!$joinCplCpmk) {
                    throw new \RuntimeException('Join CPL CPMK tidak ditemukan untuk baris SubCPMK.');
                }

                Subcpmk::updateOrCreate(
                    [
                        'join_cpl_cpmk_id' => $joinCplCpmk->id,
                        'semester_id' => $semesterId,
                        'kode' => $kode,
                    ],
                    [
                        'nama' => $nama,
                        'kompetensi_c' => $row['kompetensi_c'] ?? null,
                        'kompetensi_a' => $row['kompetensi_a'] ?? null,
                        'kompetensi_p' => $row['kompetensi_p'] ?? null,
                        'indikator' => $row['indikator'] ?? null,
                        'evaluasi' => $row['evaluasi'] ?? null,
                    ]
                );
                return;

            case 'penugasans':
                $this->assertSemester($semesterId);

                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $bobot = (float) $this->required($row['bobot'] ?? null, 'bobot');
                $kodeEvaluasi = $this->required($row['kode_evaluasi'] ?? null, 'kode_evaluasi');

                $evaluasi = Evaluasi::query()->where('kode', $kodeEvaluasi)->first();
                if (!$evaluasi) {
                    throw new \RuntimeException('Evaluasi tidak ditemukan: ' . $kodeEvaluasi);
                }

                Penugasan::updateOrCreate(
                    ['mk_id' => $mk->id, 'semester_id' => $semesterId, 'kode' => $kode],
                    [
                        'nama' => $nama,
                        'bobot' => $bobot,
                        'evaluasi_id' => $evaluasi->id,
                    ]
                );
                return;

            case 'join_subcpmk_penugasans':
                $this->assertSemester($semesterId);

                $kodeSubcpmk = $this->required($row['kode_subcpmk'] ?? null, 'kode_subcpmk');
                $kodePenugasan = $this->required($row['kode_penugasan'] ?? null, 'kode_penugasan');
                $bobot = (float) $this->required($row['bobot'] ?? null, 'bobot');

                $subcpmk = Subcpmk::query()
                    ->where('kode', $kodeSubcpmk)
                    ->where('semester_id', $semesterId)
                    ->whereHas('joinCplCpmk', function ($query) use ($mk) {
                        $query->where('mk_id', $mk->id);
                    })
                    ->first();
                if (!$subcpmk) {
                    throw new \RuntimeException('SubCPMK tidak ditemukan: ' . $kodeSubcpmk);
                }

                $penugasan = Penugasan::query()
                    ->where('mk_id', $mk->id)
                    ->where('semester_id', $semesterId)
                    ->where('kode', $kodePenugasan)
                    ->first();
                if (!$penugasan) {
                    throw new \RuntimeException('Penugasan tidak ditemukan: ' . $kodePenugasan);
                }

                JoinSubcpmkPenugasan::updateOrCreate(
                    [
                        'mk_id' => $mk->id,
                        'semester_id' => $semesterId,
                        'subcpmk_id' => $subcpmk->id,
                        'penugasan_id' => $penugasan->id,
                    ],
                    [
                        'bobot' => $bobot,
                    ]
                );
                return;
        }

        throw new \RuntimeException('Target import tidak dikenali.');
    }

    private function buildHeaderMap(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $columnLetter => $headerValue) {
            $normalized = $this->normalizeHeader($headerValue);
            if ($normalized !== '') {
                $map[$normalized] = $columnLetter;
            }
        }

        return $map;
    }

    private function normalizeHeader($value): string
    {
        $normalized = Str::lower(trim((string) $value));
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', $normalized) ?? '';
        return trim($normalized, '_');
    }

    private function cellValue($value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->cellValue($value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function required($value, string $column): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            throw new \RuntimeException('Kolom wajib kosong: ' . $column);
        }

        return $text;
    }

    private function resolveTarget(?string $target): string
    {
        return array_key_exists((string) $target, self::TARGETS)
            ? (string) $target
            : array_key_first(self::TARGETS);
    }

    private function previewSessionKey(Mk $mk, string $target): string
    {
        return 'import_mk_master_' . $mk->id . '_' . $target;
    }

    private function assertSemester(?string $semesterId): void
    {
        if (empty($semesterId)) {
            throw new \RuntimeException('Semester wajib dipilih.');
        }
    }

    private function resolveReturnUrl(Request $request): string
    {
        $candidate = (string) $request->query('return_url', '');
        if ($candidate === '') {
            $candidate = (string) $request->input('return_url', '');
        }
        if ($candidate === '') {
            $candidate = (string) url()->previous();
        }

        return $candidate !== '' ? $candidate : route('home');
    }

    private function withReturnUrl(array $params, Request $request): array
    {
        $params['return_url'] = $this->resolveReturnUrl($request);
        return $params;
    }

    private function parseJoinSubcpmkPenugasanMatrix(array $rows, Mk $mk, string $semesterId): array
    {
        $headerRow = $rows[1] ?? [];
        $subcpmkCodeByColumn = [];

        foreach ($headerRow as $columnLetter => $value) {
            if ($columnLetter === 'A') {
                continue;
            }

            $kodeSubcpmk = trim((string) ($value ?? ''));
            if ($kodeSubcpmk !== '') {
                $subcpmkCodeByColumn[$columnLetter] = $kodeSubcpmk;
            }
        }

        if (empty($subcpmkCodeByColumn)) {
            throw new \RuntimeException('Header SubCPMK tidak ditemukan pada template matriks.');
        }

        $subcpmkByCode = Subcpmk::query()
            ->where('semester_id', $semesterId)
            ->whereHas('joinCplCpmk', function ($query) use ($mk) {
                $query->where('mk_id', $mk->id);
            })
            ->whereIn('kode', array_values($subcpmkCodeByColumn))
            ->get()
            ->keyBy('kode');

        $previewRows = [];
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 1) {
                continue;
            }

            $penugasanLabel = trim((string) ($row['A'] ?? ''));
            if ($penugasanLabel === '') {
                continue;
            }

            $penugasanCode = trim((string) Str::before($penugasanLabel, ':'));
            if ($penugasanCode === '') {
                $penugasanCode = $penugasanLabel;
            }

            $penugasan = Penugasan::query()
                ->where('mk_id', $mk->id)
                ->where('semester_id', $semesterId)
                ->where('kode', $penugasanCode)
                ->first();

            if (!$penugasan) {
                continue;
            }

            foreach ($subcpmkCodeByColumn as $columnLetter => $kodeSubcpmk) {
                $rawValue = trim((string) ($row[$columnLetter] ?? ''));
                if ($rawValue === '') {
                    continue;
                }

                if (!is_numeric($rawValue)) {
                    throw new \RuntimeException('Nilai bobot bukan angka pada baris ' . $rowIndex . ', kolom ' . $columnLetter . '.');
                }

                $subcpmk = $subcpmkByCode[$kodeSubcpmk] ?? null;
                if (!$subcpmk) {
                    throw new \RuntimeException('SubCPMK tidak ditemukan: ' . $kodeSubcpmk);
                }

                $previewRows[] = [
                    'kode_subcpmk' => $subcpmk->kode,
                    'nama_subcpmk' => $subcpmk->nama,
                    'kode_penugasan' => $penugasan->kode,
                    'nama_penugasan' => $penugasan->nama,
                    'bobot' => $rawValue,
                ];
            }
        }

        return $previewRows;
    }
}
