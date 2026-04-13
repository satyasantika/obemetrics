<?php

namespace App\Http\Controllers\Bulk;

use App\Actions\SyncMkState;
use App\Http\Controllers\Controller;
use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\Evaluasi;
use App\Models\CplBk;
use App\Models\JoinCplCpmk;
use App\Models\JoinSubcpmkPenugasan;
use App\Models\Mk;
use App\Models\Penugasan;
use App\Models\Semester;
use App\Models\Subcpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportMkMasterController extends Controller
{
    private const TARGETS = [
        'mk_bundle' => [
            'label' => 'Master MK (CPMK, SubCPMK, Penugasan)',
            'columns' => [],
            'required' => [],
            'requires_semester' => true,
        ],
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

        if ($target === 'mk_bundle') {
            try {
                $semesterId = (string) $request->input('semester_id', '');
                $this->assertSemester($semesterId);

                $extension = Str::lower((string) $request->file('file')->getClientOriginalExtension());
                if ($extension === 'csv') {
                    return back()->with('error', 'File CSV tidak didukung untuk target ini. Gunakan template multi-sheet berformat .xlsx atau .ods.');
                }

                $spreadsheet = IOFactory::load($request->file('file')->getPathname());
                $result = DB::transaction(function () use ($spreadsheet, $mk, $semesterId) {
                    return $this->commitMkBundle($spreadsheet, $mk, $semesterId);
                });

                $mkFresh = $mk->fresh();
                SyncMkState::sync($mkFresh);

                $successMsg = "Import master MK berhasil: {$result['cpmks']} CPMK, {$result['subcpmks']} SubCPMK, {$result['penugasans']} Penugasan diproses.";

                $hasCpmk       = $mkFresh->cpmks()->exists();
                $hasSubcpmk    = $mkFresh->joinCplCpmks()->whereHas('subcpmks')->exists();
                $hasPenugasan  = $mkFresh->penugasans()->exists();
                $noJoinMapping = !$mkFresh->joinSubcpmkPenugasans()->exists();

                if ($hasCpmk && $hasSubcpmk && $hasPenugasan && $noJoinMapping) {
                    return to_route('mks.joinsubcpmkpenugasans.index', ['mk' => $mk->id, 'semester_id' => $semesterId])
                        ->with('success', $successMsg . ' Lanjutkan dengan mapping SubCPMK ke Penugasan.');
                }

                return redirect()->to($this->resolveReturnUrl($request))
                    ->with('success', $successMsg);
            } catch (\Throwable $e) {
                return back()->with('error', 'Gagal memproses import master MK: ' . $e->getMessage());
            }
        }

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

                SyncMkState::sync($mk->fresh());
                return to_route('mks.joinsubcpmkpenugasans.index', ['mk' => $mk->id, 'semester_id' => $request->semester_id])
                    ->with('success', "data SubCPMK telah diimport ke Penugasan.");
            }

            if ($target === 'join_cpl_cpmks') {
                $result = $this->saveJoinCplCpmkMatrix($rows, $mk);

                $redirect = redirect()->to($this->resolveReturnUrl($request))
                    ->with('success', "Interaksi CPL >< CPMK berhasil disimpan ({$result['linked']} aktif).");

                if (($result['removed'] ?? 0) > 0) {
                    $redirect->with('danger', "{$result['removed']} interaksi dibuang karena sel pada template dikosongkan.");
                }

                SyncMkState::sync($mk->fresh());
                return $redirect;
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

                $previewRows[] = $this->decoratePreviewRow($target, $normalizedRow, $mk, $request->semester_id);
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data valid untuk dipreview.');
            }

            $previewPayload = [
                'target' => $target,
                'semester_id' => $request->semester_id,
                'filename' => $request->file('file')->getClientOriginalName(),
                'rows' => $previewRows,
                'token' => (string) Str::uuid(),
            ];

            session([
                $this->previewSessionKey($mk, $target) => $previewPayload,
            ]);

            Cache::put(
                $this->previewCacheKey($mk, $target, (string) ($previewPayload['token'] ?? '')),
                $previewPayload,
                now()->addHours(2)
            );

            return to_route('settings.import.mk-master', $this->withReturnUrl([
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
        $previewToken = (string) $request->input('preview_token', '');

        if (empty($rows) && $previewToken !== '') {
            $cachedPreview = Cache::get($this->previewCacheKey($mk, $target, $previewToken), []);
            if (!empty($cachedPreview) && is_array($cachedPreview)) {
                $preview = $cachedPreview;
                $rows = $preview['rows'] ?? [];
            }
        }

        Log::info('Import MK Master commit requested', [
            'mk_id' => $mk->id,
            'target' => $target,
            'preview_rows' => is_array($rows) ? count($rows) : 0,
            'selected_count' => count((array) $request->input('selected', [])),
            'semester_id' => $request->input('semester_id') ?: ($preview['semester_id'] ?? null),
            'session_driver' => config('session.driver'),
        ]);

        if (empty($rows)) {
            Log::warning('Import MK Master commit aborted: preview rows not found in session', [
                'mk_id' => $mk->id,
                'target' => $target,
                'session_key' => $this->previewSessionKey($mk, $target),
            ]);

            return to_route('settings.import.mk-master', $this->withReturnUrl([
                'mk' => $mk->id,
                'target' => $target,
                'semester_id' => $request->input('semester_id') ?: ($preview['semester_id'] ?? null),
            ], $request))
                ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        $semesterId = $request->input('semester_id') ?: ($preview['semester_id'] ?? null);
        if (!empty($meta['requires_semester']) && empty($semesterId)) {
            return to_route('settings.import.mk-master', $this->withReturnUrl([
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
                Log::warning('Import MK Master commit skip: selected index not found', [
                    'mk_id' => $mk->id,
                    'target' => $target,
                    'selected_index' => $idx,
                ]);
                continue;
            }

            if (($rows[$idx]['can_save'] ?? true) === false) {
                $skipped[] = 'Baris ' . ($idx + 2) . ': ' . ($rows[$idx]['status_message'] ?? 'Data tidak valid.');
                Log::warning('Import MK Master commit skip: row not saveable', [
                    'mk_id' => $mk->id,
                    'target' => $target,
                    'selected_index' => $idx,
                    'status_message' => $rows[$idx]['status_message'] ?? 'Data tidak valid.',
                ]);
                continue;
            }

            try {
                $this->persistRow($target, $rows[$idx], $mk, $semesterId);
                $saved++;
            } catch (\Throwable $e) {
                $skipped[] = 'Baris ' . ($idx + 2) . ': ' . $e->getMessage();
                Log::error('Import MK Master commit row failed', [
                    'mk_id' => $mk->id,
                    'target' => $target,
                    'selected_index' => $idx,
                    'row' => $rows[$idx],
                    'semester_id' => $semesterId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        session()->forget($this->previewSessionKey($mk, $target));

        $tokenToForget = (string) ($preview['token'] ?? $previewToken ?? '');
        if ($tokenToForget !== '') {
            Cache::forget($this->previewCacheKey($mk, $target, $tokenToForget));
        }

        Log::info('Import MK Master commit finished', [
            'mk_id' => $mk->id,
            'target' => $target,
            'saved' => $saved,
            'skipped' => count($skipped),
        ]);

        if ($saved === 0) {
            $message = 'Tidak ada data yang berhasil diproses.';
            if (!empty($skipped)) {
                $message .= ' Penyebab: ' . implode(' | ', array_slice($skipped, 0, 5));
            }

            return redirect()->to($this->resolveReturnUrl($request))
                ->with('error', $message);
        }

        $message = "{$saved} baris berhasil diproses.";
        if (!empty($skipped)) {
            $message .= ' Beberapa baris dilewati: ' . implode(' | ', array_slice($skipped, 0, 5));
        }

        SyncMkState::sync($mk->fresh());
        return redirect()->to($this->resolveReturnUrl($request))
            ->with('success', $message);
    }

    public function template(Mk $mk, Request $request)
    {
        try {
            $target = $this->resolveTarget($request->query('target'));
            $meta = self::TARGETS[$target];
            $semesterId = (string) $request->query('semester_id', '');

            if (!empty($meta['requires_semester']) && $semesterId === '') {
                return redirect()->to($this->resolveReturnUrl($request))
                    ->with('error', 'Semester wajib dipilih sebelum mengunduh template.');
            }

            if ($target === 'mk_bundle') {
                $this->assertSemester($semesterId);

                $spreadsheet = $this->buildMkBundleTemplate($mk, $semesterId);
                $writer = new Xlsx($spreadsheet);
                $waktuDownload = now()->format('YmdHis');
                $fileName = 'import' . $waktuDownload . '-master-mk-' . Str::slug((string) ($mk->kode ?? 'mk'), '-') . '.xlsx';

                return response()->streamDownload(function () use ($writer) {
                    $writer->save('php://output');
                }, $fileName, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
            }

            if ($target === 'join_subcpmk_penugasans') {
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

            if ($rowIndex > 2 && $columnIndex > 2) {
                $lastColumn = Coordinate::stringFromColumnIndex($columnIndex - 1);
                $sheet->getStyle('B2:' . $lastColumn . ($rowIndex - 1))
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFFF59D');
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

        if ($target === 'join_cpl_cpmks') {
            $joinCplBks = $mk->joinCplMks->pluck('joinCplBk')->flatten()->filter()->unique('id')->values();
            $cpmks = $mk->cpmks()->orderBy('kode')->get();

            $linkedMap = JoinCplCpmk::query()
                ->where('mk_id', $mk->id)
                ->whereIn('cpl_bk_id', $joinCplBks->pluck('id'))
                ->whereIn('cpmk_id', $cpmks->pluck('id'))
                ->get()
                ->keyBy(fn ($row) => $row->cpmk_id . '_' . $row->cpl_bk_id);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', '');
            $sheet->getColumnDimension('A')->setWidth(36);

            $columnIndex = 2;
            foreach ($joinCplBks as $joinCplBk) {
                $column = Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue($column . '1', (string) ($joinCplBk->cpl->kode ?? ''));
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($column)->setWidth(16);
                $columnIndex++;
            }

            $rowIndex = 2;
            foreach ($cpmks as $cpmk) {
                $sheet->setCellValue('A' . $rowIndex, trim((string) ($cpmk->kode . "\n" . $cpmk->nama)));
                $sheet->getStyle('A' . $rowIndex)->getAlignment()->setWrapText(true);

                $columnIndex = 2;
                foreach ($joinCplBks as $joinCplBk) {
                    $column = Coordinate::stringFromColumnIndex($columnIndex);
                    $isLinked = $linkedMap->has($cpmk->id . '_' . $joinCplBk->id);
                    $sheet->setCellValue($column . $rowIndex, $isLinked ? 'V' : '');
                    $columnIndex++;
                }

                $rowIndex++;
            }

            $writer = new Xlsx($spreadsheet);
            $waktuDownload = now()->format('YmdHis');
            $fileName = 'import' . $waktuDownload . '-interaksi-cpl-cpmk-' . Str::slug((string) ($mk->kode ?? 'mk'), '-') . '.xlsx';

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
        } catch (\Throwable $e) {
            Log::warning('Import MK Master template request failed', [
                'mk_id' => $mk->id,
                'target' => (string) $request->query('target', ''),
                'semester_id' => (string) $request->query('semester_id', ''),
                'error' => $e->getMessage(),
            ]);

            return redirect()->to($this->resolveReturnUrl($request))
                ->with('error', 'Gagal mengunduh template: ' . $e->getMessage());
        }
    }

    public function clear(Mk $mk, Request $request)
    {
        $target = $this->resolveTarget($request->input('target'));
        $preview = session($this->previewSessionKey($mk, $target), []);
        $token = (string) ($request->input('preview_token') ?: ($preview['token'] ?? ''));

        session()->forget($this->previewSessionKey($mk, $target));

        if ($token !== '') {
            Cache::forget($this->previewCacheKey($mk, $target, $token));
        }

        return to_route('settings.import.mk-master', $this->withReturnUrl([
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
                    ->where('kode', $kodeCpl)
                    ->whereHas('kurikulums', function ($query) use ($mk) {
                        $query->where('kurikulums.id', $mk->kurikulum_id);
                    })
                    ->first();
                if (!$cpl) {
                    throw new \RuntimeException('CPL tidak ditemukan: ' . $kodeCpl);
                }

                $cpmk = Cpmk::query()->where('mk_id', $mk->id)->where('kode', $kodeCpmk)->first();
                if (!$cpmk) {
                    throw new \RuntimeException('CPMK tidak ditemukan: ' . $kodeCpmk);
                }

                $joinCplBks = CplBk::query()
                    ->where('cpl_id', $cpl->id)
                    ->whereIn('bk_id', function ($query) use ($mk) {
                        $query->select('bk_id')
                            ->from('kurikulum_bks')
                            ->where('kurikulum_id', $mk->kurikulum_id);
                    })
                    ->get();

                if ($joinCplBks->isEmpty()) {
                    throw new \RuntimeException('Relasi CPL >< BK belum ada untuk kode_cpl: ' . $kodeCpl);
                }

                foreach ($joinCplBks as $joinCplBk) {
                    JoinCplCpmk::updateOrCreate(
                        ['mk_id' => $mk->id, 'cpl_bk_id' => $joinCplBk->id, 'cpmk_id' => $cpmk->id],
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
                    ->where('kode', $kodeCpl)
                    ->whereHas('kurikulums', function ($query) use ($mk) {
                        $query->where('kurikulums.id', $mk->kurikulum_id);
                    })
                    ->first();
                if (!$cpl) {
                    throw new \RuntimeException('CPL tidak ditemukan: ' . $kodeCpl);
                }

                $joinCplBkIds = CplBk::query()
                    ->where('cpl_id', $cpl->id)
                    ->whereIn('bk_id', function ($query) use ($mk) {
                        $query->select('bk_id')
                            ->from('kurikulum_bks')
                            ->where('kurikulum_id', $mk->kurikulum_id);
                    })
                    ->pluck('id');

                if ($joinCplBkIds->isEmpty()) {
                    throw new \RuntimeException('Relasi CPL >< BK tidak ditemukan untuk kode_cpl: ' . $kodeCpl);
                }

                $joinCplCpmkQuery = JoinCplCpmk::query()
                    ->where('mk_id', $mk->id)
                    ->whereIn('cpl_bk_id', $joinCplBkIds);

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

    private function decoratePreviewRow(string $target, array $row, Mk $mk, ?string $semesterId): array
    {
        if (!in_array($target, ['cpmks', 'subcpmks', 'penugasans'], true)) {
            return $row;
        }

        $status = [
            'exists' => false,
            'can_save' => true,
            'status_message' => null,
        ];

        if ($target === 'cpmks') {
            $kode = trim((string) ($row['kode'] ?? ''));
            if ($kode === '') {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Kode CPMK wajib diisi',
                ]);
            }

            $status['exists'] = Cpmk::query()
                ->where('mk_id', $mk->id)
                ->where('kode', $kode)
                ->exists();

            return array_merge($row, $status);
        }

        if ($target === 'subcpmks') {
            $kode = trim((string) ($row['kode'] ?? ''));
            $kodeCpl = trim((string) ($row['kode_cpl'] ?? ''));
            if ($kode === '' || $kodeCpl === '' || empty($semesterId)) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Kode SubCPMK, kode CPL, dan semester wajib valid',
                ]);
            }

            $cpl = Cpl::query()
                ->where('kode', $kodeCpl)
                ->whereHas('kurikulums', function ($query) use ($mk) {
                    $query->where('kurikulums.id', $mk->kurikulum_id);
                })
                ->first();
            if (!$cpl) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'CPL tidak ditemukan: ' . $kodeCpl,
                ]);
            }

            $joinCplBkIds = CplBk::query()
                ->where('cpl_id', $cpl->id)
                ->whereIn('bk_id', function ($query) use ($mk) {
                    $query->select('bk_id')
                        ->from('kurikulum_bks')
                        ->where('kurikulum_id', $mk->kurikulum_id);
                })
                ->pluck('id');
            if ($joinCplBkIds->isEmpty()) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Relasi CPL >< BK tidak ditemukan',
                ]);
            }

            $joinCplCpmkQuery = JoinCplCpmk::query()
                ->where('mk_id', $mk->id)
                ->whereIn('cpl_bk_id', $joinCplBkIds);

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
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Join CPL CPMK tidak ditemukan',
                ]);
            }

            $status['exists'] = Subcpmk::query()
                ->where('join_cpl_cpmk_id', $joinCplCpmk->id)
                ->where('semester_id', $semesterId)
                ->where('kode', $kode)
                ->exists();

            return array_merge($row, $status);
        }

        $kode = trim((string) ($row['kode'] ?? ''));
        $kodeEvaluasi = trim((string) ($row['kode_evaluasi'] ?? ''));
        if ($kode === '' || $kodeEvaluasi === '' || empty($semesterId)) {
            return array_merge($row, [
                'exists' => false,
                'can_save' => false,
                'status_message' => 'Kode penugasan, kode evaluasi, dan semester wajib valid',
            ]);
        }

        $evaluasiExists = Evaluasi::query()->where('kode', $kodeEvaluasi)->exists();
        if (!$evaluasiExists) {
            return array_merge($row, [
                'exists' => false,
                'can_save' => false,
                'status_message' => 'Evaluasi tidak ditemukan: ' . $kodeEvaluasi,
            ]);
        }

        $status['exists'] = Penugasan::query()
            ->where('mk_id', $mk->id)
            ->where('semester_id', $semesterId)
            ->where('kode', $kode)
            ->exists();

        return array_merge($row, $status);
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

    private function previewCacheKey(Mk $mk, string $target, string $token): string
    {
        return 'import_mk_master_cache_' . $mk->id . '_' . $target . '_' . $token;
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

    private function saveJoinCplCpmkMatrix(array $rows, Mk $mk): array
    {
        $joinCplBks = $mk->joinCplMks->pluck('joinCplBk')->flatten()->filter()->unique('id')->values();
        if ($joinCplBks->isEmpty()) {
            throw new \RuntimeException('Data relasi CPL >< BK belum tersedia untuk MK ini.');
        }

        $cpmks = $mk->cpmks()->orderBy('kode')->get();
        if ($cpmks->isEmpty()) {
            throw new \RuntimeException('Data CPMK belum tersedia untuk MK ini.');
        }

        $joinCplBkByColumn = [];
        $columnIndex = 2;
        foreach ($joinCplBks as $joinCplBk) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $joinCplBkByColumn[$column] = $joinCplBk;
            $columnIndex++;
        }

        $desiredPairs = [];
        $scopeCpmkIds = [];
        $scopeCplBkIds = $joinCplBks->pluck('id')->values()->all();

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 1) {
                continue;
            }

            $cpmkCell = trim((string) ($row['A'] ?? ''));
            if ($cpmkCell === '') {
                continue;
            }

            $kodeCpmk = trim((string) Str::before($cpmkCell, "\n"));
            $cpmk = Cpmk::query()->where('mk_id', $mk->id)->where('kode', $kodeCpmk)->first();
            if (!$cpmk) {
                throw new \RuntimeException('CPMK tidak ditemukan pada baris ' . $rowIndex . ': ' . $kodeCpmk);
            }

            $scopeCpmkIds[] = $cpmk->id;

            foreach ($joinCplBkByColumn as $columnLetter => $joinCplBk) {
                $raw = trim((string) ($row[$columnLetter] ?? ''));
                if ($raw === '') {
                    continue;
                }

                if (Str::upper($raw) !== 'V') {
                    throw new \RuntimeException('Nilai tidak valid pada baris ' . $rowIndex . ', kolom ' . $columnLetter . '. Gunakan huruf V atau kosong.');
                }

                $desiredPairs[$cpmk->id . '_' . $joinCplBk->id] = [
                    'mk_id' => $mk->id,
                    'cpmk_id' => $cpmk->id,
                    'cpl_bk_id' => $joinCplBk->id,
                ];
            }
        }

        $scopeCpmkIds = array_values(array_unique($scopeCpmkIds));
        if (empty($scopeCpmkIds)) {
            throw new \RuntimeException('Tidak ada baris CPMK pada template interaksi.');
        }

        $existingRows = JoinCplCpmk::query()
            ->where('mk_id', $mk->id)
            ->whereIn('cpmk_id', $scopeCpmkIds)
            ->whereIn('cpl_bk_id', $scopeCplBkIds)
            ->get();

        $desiredKeys = array_keys($desiredPairs);
        $removed = 0;
        foreach ($existingRows as $existingRow) {
            $key = $existingRow->cpmk_id . '_' . $existingRow->cpl_bk_id;
            if (!in_array($key, $desiredKeys, true)) {
                $existingRow->delete();
                $removed++;
            }
        }

        foreach ($desiredPairs as $pair) {
            JoinCplCpmk::updateOrCreate(
                [
                    'mk_id' => $pair['mk_id'],
                    'cpmk_id' => $pair['cpmk_id'],
                    'cpl_bk_id' => $pair['cpl_bk_id'],
                ],
                []
            );
        }

        return [
            'linked' => count($desiredPairs),
            'removed' => $removed,
        ];
    }

    private function buildMkBundleTemplate(Mk $mk, string $semesterId): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $evaluasiCodes = Evaluasi::query()
            ->orderBy('kode')
            ->pluck('kode')
            ->map(fn ($kode) => trim((string) $kode))
            ->filter(fn ($kode) => $kode !== '')
            ->unique()
            ->values()
            ->all();

        $evaluasiRangeFormula = null;
        if (!empty($evaluasiCodes)) {
            $listSheet = $spreadsheet->createSheet(0);
            $listSheet->setTitle('REF_EVALUASI');
            foreach ($evaluasiCodes as $idx => $kode) {
                $listSheet->setCellValue('A' . ($idx + 1), $kode);
            }
            $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            $evaluasiRangeFormula = '=REF_EVALUASI!$A$1:$A$' . count($evaluasiCodes);
        }

        $sheetDefinitions = [
            [
                'name' => 'CPMK',
                'columns' => ['kode', 'nama', 'deskripsi'],
                'required' => ['kode', 'nama'],
                'rows' => $mk->cpmks()->orderBy('kode')->get(['kode', 'nama', 'deskripsi'])
                    ->map(fn ($item) => [
                        'kode' => (string) ($item->kode ?? ''),
                        'nama' => (string) ($item->nama ?? ''),
                        'deskripsi' => (string) ($item->deskripsi ?? ''),
                    ])->all(),
                'examples' => [
                    ['kode' => 'CPMK-1', 'nama' => 'Mahasiswa mampu menjelaskan konsep dasar.', 'deskripsi' => 'Contoh deskripsi CPMK'],
                ],
            ],
            [
                'name' => 'SubCPMK',
                'columns' => ['kode_cpl', 'kode', 'nama', 'kompetensi_c', 'kompetensi_a', 'kompetensi_p', 'indikator', 'evaluasi', 'kode_cpmk', 'nama_cpmk'],
                'required' => ['kode_cpl', 'kode', 'nama'],
                'rows' => $this->mkBundleSubcpmkRows($mk, $semesterId),
                'examples' => [
                    [
                        'kode_cpl' => 'CPL-01',
                        'kode' => 'SCPMK-1',
                        'nama' => 'Mampu menyusun algoritma sederhana',
                        'kompetensi_c' => 'C3',
                        'kompetensi_a' => 'A2',
                        'kompetensi_p' => 'P3',
                        'indikator' => 'Menyusun flowchart yang benar',
                        'evaluasi' => 'Kuis',
                        'kode_cpmk' => 'CPMK-1',
                        'nama_cpmk' => 'Mahasiswa mampu menjelaskan konsep dasar.',
                    ],
                ],
            ],
            [
                'name' => 'Penugasan',
                'columns' => ['kode', 'nama', 'bobot', 'kode_evaluasi'],
                'required' => ['kode', 'nama', 'bobot', 'kode_evaluasi'],
                'rows' => Penugasan::query()->where('mk_id', $mk->id)->where('semester_id', $semesterId)
                    ->with('evaluasi:id,kode')
                    ->orderBy('kode')
                    ->get(['id', 'kode', 'nama', 'bobot', 'evaluasi_id'])
                    ->map(fn ($item) => [
                        'kode' => (string) ($item->kode ?? ''),
                        'nama' => (string) ($item->nama ?? ''),
                        'bobot' => (string) ($item->bobot ?? ''),
                        'kode_evaluasi' => (string) ($item->evaluasi->kode ?? ''),
                    ])->all(),
                'examples' => [
                    ['kode' => 'T1', 'nama' => 'Tugas Individu 1', 'bobot' => '10', 'kode_evaluasi' => 'EVAL-01'],
                ],
            ],
        ];

        foreach ($sheetDefinitions as $index => $definition) {
            $sheet = $spreadsheet->createSheet($index);
            $sheet->setTitle($definition['name']);

            $columns = $definition['columns'];
            $requiredColumns = $definition['required'];
            $rows = !empty($definition['rows']) ? $definition['rows'] : $definition['examples'];

            $columnLetters = [];
            foreach ($columns as $columnIndex => $columnName) {
                $letter = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $columnLetters[$columnName] = $letter;
                $sheet->setCellValue($letter . '1', $columnName);
                $sheet->getStyle($letter . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($letter)->setWidth(max(16, strlen($columnName) + 3));
            }

            $rowIndex = 2;
            foreach ($rows as $row) {
                foreach ($columns as $columnName) {
                    $sheet->setCellValue($columnLetters[$columnName] . $rowIndex, (string) ($row[$columnName] ?? ''));
                }
                $rowIndex++;
            }

            $lastDataRow = max(2, $rowIndex - 1);
            foreach ($requiredColumns as $requiredColumn) {
                if (!isset($columnLetters[$requiredColumn])) {
                    continue;
                }
                $letter = $columnLetters[$requiredColumn];
                $sheet->getStyle($letter . '2:' . $letter . $lastDataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFFFF00');
            }

            if (($definition['name'] ?? '') === 'Penugasan' && isset($columnLetters['kode_evaluasi']) && $evaluasiRangeFormula) {
                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Nilai tidak valid');
                $validation->setError('Pilih kode evaluasi dari daftar.');
                $validation->setPromptTitle('Kode Evaluasi');
                $validation->setPrompt('Pilih salah satu kode evaluasi yang tersedia.');
                $validation->setFormula1($evaluasiRangeFormula);

                $validationLastRow = max(200, $lastDataRow + 100);
                $targetColumn = $columnLetters['kode_evaluasi'];
                for ($row = 2; $row <= $validationLastRow; $row++) {
                    $sheet->getCell($targetColumn . $row)->setDataValidation(clone $validation);
                }
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function mkBundleSubcpmkRows(Mk $mk, string $semesterId): array
    {
        return Subcpmk::query()
            ->where('semester_id', $semesterId)
            ->whereHas('joinCplCpmk', function ($query) use ($mk) {
                $query->where('mk_id', $mk->id);
            })
            ->with(['joinCplCpmk.cpmk:id,kode,nama', 'joinCplCpmk.joinCplBk.cpl:id,kode'])
            ->orderBy('kode')
            ->get(['id', 'join_cpl_cpmk_id', 'kode', 'nama', 'kompetensi_c', 'kompetensi_a', 'kompetensi_p', 'indikator', 'evaluasi'])
            ->map(function ($item) {
                $cplKode = (string) ($item->joinCplCpmk?->joinCplBk?->cpl?->kode ?? '');
                $cpmkKode = (string) ($item->joinCplCpmk?->cpmk?->kode ?? '');
                $cpmkNama = (string) ($item->joinCplCpmk?->cpmk?->nama ?? '');

                return [
                    'kode_cpl' => $cplKode,
                    'kode' => (string) ($item->kode ?? ''),
                    'nama' => (string) ($item->nama ?? ''),
                    'kompetensi_c' => (string) ($item->kompetensi_c ?? ''),
                    'kompetensi_a' => (string) ($item->kompetensi_a ?? ''),
                    'kompetensi_p' => (string) ($item->kompetensi_p ?? ''),
                    'indikator' => (string) ($item->indikator ?? ''),
                    'evaluasi' => (string) ($item->evaluasi ?? ''),
                    'kode_cpmk' => $cpmkKode,
                    'nama_cpmk' => $cpmkNama,
                ];
            })
            ->all();
    }

    private function commitMkBundle(Spreadsheet $spreadsheet, Mk $mk, string $semesterId): array
    {
        $summary = [
            'cpmks' => 0,
            'subcpmks' => 0,
            'penugasans' => 0,
        ];

        $this->importMkBundleSheet($spreadsheet, 'CPMK', ['kode', 'nama', 'deskripsi'], ['kode', 'nama'], function (array $row) use ($mk, &$summary) {
            Cpmk::updateOrCreate(
                [
                    'mk_id' => $mk->id,
                    'kode' => $this->required($row['kode'] ?? null, 'kode'),
                ],
                [
                    'nama' => $this->required($row['nama'] ?? null, 'nama'),
                    'deskripsi' => $row['deskripsi'] ?? null,
                ]
            );

            $summary['cpmks']++;
        });

        $this->importMkBundleSheet(
            $spreadsheet,
            'SubCPMK',
            ['kode_cpl', 'kode', 'nama', 'kompetensi_c', 'kompetensi_a', 'kompetensi_p', 'indikator', 'evaluasi', 'kode_cpmk', 'nama_cpmk'],
            ['kode_cpl', 'kode', 'nama'],
            function (array $row) use ($mk, $semesterId, &$summary) {
                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $kodeCpl = $this->required($row['kode_cpl'] ?? null, 'kode_cpl');

                $cpl = Cpl::query()
                    ->where('kode', $kodeCpl)
                    ->whereHas('kurikulums', function ($query) use ($mk) {
                        $query->where('kurikulums.id', $mk->kurikulum_id);
                    })
                    ->first();
                if (!$cpl) {
                    throw new \RuntimeException('CPL tidak ditemukan: ' . $kodeCpl);
                }

                $joinCplBkIds = CplBk::query()
                    ->where('cpl_id', $cpl->id)
                    ->whereIn('bk_id', function ($query) use ($mk) {
                        $query->select('bk_id')
                            ->from('kurikulum_bks')
                            ->where('kurikulum_id', $mk->kurikulum_id);
                    })
                    ->pluck('id');

                if ($joinCplBkIds->isEmpty()) {
                    throw new \RuntimeException('Relasi CPL >< BK tidak ditemukan untuk kode_cpl: ' . $kodeCpl);
                }

                $kodeCpmk = trim((string) ($row['kode_cpmk'] ?? ''));
                $namaCpmk = trim((string) ($row['nama_cpmk'] ?? ''));
                $joinCplCpmk = $this->resolveJoinCplCpmkForBundleSubcpmk($mk, $joinCplBkIds->all(), $kodeCpmk, $namaCpmk, $kode);

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

                $summary['subcpmks']++;
            }
        );

        $this->importMkBundleSheet($spreadsheet, 'Penugasan', ['kode', 'nama', 'bobot', 'kode_evaluasi'], ['kode', 'nama', 'bobot', 'kode_evaluasi'], function (array $row) use ($mk, $semesterId, &$summary) {
            $evaluasi = Evaluasi::query()->where('kode', $this->required($row['kode_evaluasi'] ?? null, 'kode_evaluasi'))->first();
            if (!$evaluasi) {
                throw new \RuntimeException('Evaluasi tidak ditemukan: ' . ($row['kode_evaluasi'] ?? ''));
            }

            Penugasan::updateOrCreate(
                [
                    'mk_id' => $mk->id,
                    'semester_id' => $semesterId,
                    'kode' => $this->required($row['kode'] ?? null, 'kode'),
                ],
                [
                    'nama' => $this->required($row['nama'] ?? null, 'nama'),
                    'bobot' => (float) $this->required($row['bobot'] ?? null, 'bobot'),
                    'evaluasi_id' => $evaluasi->id,
                ]
            );

            $summary['penugasans']++;
        });

        return $summary;
    }

    private function importMkBundleSheet(Spreadsheet $spreadsheet, string $sheetName, array $columns, array $requiredColumns, callable $handler): void
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        if (!$sheet) {
            throw new \RuntimeException("Sheet '{$sheetName}' tidak ditemukan.");
        }

        $rows = $sheet->toArray(null, true, true, true);
        $headerMap = $this->buildHeaderMap($rows[1] ?? []);

        $missingHeaders = collect($columns)
            ->filter(fn ($column) => !array_key_exists($column, $headerMap))
            ->values()
            ->all();

        if (!empty($missingHeaders)) {
            throw new \RuntimeException("Sheet '{$sheetName}' tidak memiliki kolom: " . implode(', ', $missingHeaders));
        }

        foreach ($rows as $index => $row) {
            if ($index === 1) {
                continue;
            }

            $normalized = [];
            foreach ($columns as $column) {
                $normalized[$column] = $this->cellValue($row[$headerMap[$column] ?? ''] ?? null);
            }

            if ($this->isEmptyRow($normalized)) {
                continue;
            }

            foreach ($requiredColumns as $required) {
                $this->required($normalized[$required] ?? null, $sheetName . '.' . $required . ' (baris ' . $index . ')');
            }

            $handler($normalized);
        }
    }

    private function resolveJoinCplCpmkForBundleSubcpmk(Mk $mk, array $joinCplBkIds, string $kodeCpmk, string $namaCpmk, string $kodeSubcpmk): JoinCplCpmk
    {
        $query = JoinCplCpmk::query()
            ->where('mk_id', $mk->id)
            ->whereIn('cpl_bk_id', $joinCplBkIds);

        $selectedCpmk = null;
        if ($kodeCpmk !== '') {
            $selectedCpmk = Cpmk::query()->where('mk_id', $mk->id)->where('kode', $kodeCpmk)->first();
            if (!$selectedCpmk) {
                throw new \RuntimeException('CPMK tidak ditemukan untuk SubCPMK ' . $kodeSubcpmk . ': ' . $kodeCpmk);
            }
            $query->where('cpmk_id', $selectedCpmk->id);
        } elseif ($namaCpmk !== '') {
            $selectedCpmk = Cpmk::query()->where('mk_id', $mk->id)->where('nama', $namaCpmk)->first();
            if (!$selectedCpmk) {
                throw new \RuntimeException('CPMK tidak ditemukan untuk SubCPMK ' . $kodeSubcpmk . ': ' . $namaCpmk);
            }
            $query->where('cpmk_id', $selectedCpmk->id);
        }

        $existing = $query->first();
        if ($existing) {
            return $existing;
        }

        if (!$selectedCpmk) {
            throw new \RuntimeException('Join CPL CPMK tidak ditemukan untuk SubCPMK ' . $kodeSubcpmk . '. Isi kode_cpmk atau nama_cpmk yang valid pada sheet SubCPMK.');
        }

        $joinCplBkId = (string) ($joinCplBkIds[0] ?? '');
        if ($joinCplBkId === '') {
            throw new \RuntimeException('Relasi CPL >< BK tidak tersedia untuk membentuk Join CPL CPMK pada SubCPMK ' . $kodeSubcpmk . '.');
        }

        return JoinCplCpmk::updateOrCreate(
            [
                'mk_id' => $mk->id,
                'cpl_bk_id' => $joinCplBkId,
                'cpmk_id' => $selectedCpmk->id,
            ],
            []
        );
    }
}
