<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use App\Models\Bk;
use App\Models\Cpl;
use App\Models\JoinCplBk;
use App\Models\JoinCplCpmk;
use App\Models\JoinCplMk;
use App\Models\JoinMkUser;
use App\Models\JoinProfilCpl;
use App\Models\KontrakMk;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\Mk;
use App\Models\Profil;
use App\Models\ProfilIndikator;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportKurikulumMasterController extends Controller
{
    private const TARGETS = [
        'kurikulum_bundle' => [
            'label' => 'Master Kurikulum (Profil, CPL, BK, MK)',
            'columns' => [],
            'required' => [],
        ],
        'join_kurikulum_bundle' => [
            'label' => 'Interaksi Kurikulum (Profil-CPL-BK)',
            'columns' => [],
            'required' => [],
        ],
        'profils' => [
            'label' => 'Profil Lulusan',
            'columns' => ['kode', 'nama', 'deskripsi'],
            'required' => ['nama'],
        ],
        'profil_indikators' => [
            'label' => 'Indikator Profil Lulusan',
            'columns' => ['nama', 'deskripsi', 'nama_profil'],
            'required' => ['nama'],
        ],
        'cpls' => [
            'label' => 'Capaian Pembelajaran Lulusan',
            'columns' => ['kode', 'nama', 'cakupan'],
            'required' => ['kode', 'nama', 'cakupan'],
        ],
        'join_profil_cpls' => [
            'label' => 'CPL untuk Profil Lulusan',
            'columns' => ['kode_profil', 'nama_profil', 'kode_cpl', 'nama_cpl'],
            'required' => ['kode_profil', 'kode_cpl'],
        ],
        'bks' => [
            'label' => 'Bahan Kajian',
            'columns' => ['kode', 'nama', 'deskripsi'],
            'required' => ['kode', 'nama'],
        ],
        'join_cpl_bks' => [
            'label' => 'Bahan Kajian untuk CPL',
            'columns' => ['kode_cpl', 'nama_cpl', 'kode_bk', 'nama_bk'],
            'required' => ['kode_cpl', 'kode_bk'],
        ],
        'mks' => [
            'label' => 'Mata Kuliah',
            'columns' => ['kode', 'nama', 'semester', 'sks_teori', 'sks_praktik', 'sks_lapangan', 'deskripsi'],
            'required' => ['kode', 'nama', 'semester', 'sks_teori', 'sks_praktik', 'sks_lapangan'],
            'requires_semester' => false,
        ],
        'join_cpl_mks' => [
            'label' => 'Interaksi CPL-MK',
            'columns' => [],
            'required' => [],
            'requires_semester' => false,
        ],
        'mahasiswas' => [
            'label' => 'Mahasiswa Program Studi',
            'columns' => ['nim', 'nama', 'angkatan', 'email', 'phone'],
            'required' => ['nim'],
            'requires_semester' => false,
        ],
        'joinmkusers' => [
            'label' => 'Dosen Pengampu Mata Kuliah',
            'columns' => ['kode_semester', 'kode_mk', 'nama_mata_kuliah', 'nidn', 'nama_dosen', 'koordinator'],
            'required' => ['kode_semester', 'kode_mk', 'nidn'],
            'requires_semester' => false,
        ],
        'kontrakmks' => [
            'label' => 'Kontrak Mata Kuliah',
            'columns' => ['kode_semester', 'nim', 'nama_mahasiswa', 'kode_mk', 'nidn', 'nama_dosen', 'kelas'],
            'required' => ['kode_semester', 'nim', 'kode_mk', 'nidn'],
            'requires_semester' => false,
        ],
    ];

    public function form(Kurikulum $kurikulum, Request $request)
    {
        $target = $this->resolveTarget($request->query('target'));
        $preview = session($this->previewSessionKey($kurikulum, $target), []);
        $semesters = Semester::query()->orderBy('kode')->get();
        $returnUrl = $this->resolveReturnUrl($request);

        return view('setting.bulk-import.kurikulum-master', [
            'kurikulum' => $kurikulum,
            'targets' => self::TARGETS,
            'target' => $target,
            'preview' => $preview,
            'semesters' => $semesters,
            'returnUrl' => $returnUrl,
        ]);
    }

    public function import(Request $request, Kurikulum $kurikulum)
    {
        $request->validate([
            'target' => 'required|string|in:' . implode(',', array_keys(self::TARGETS)),
            'semester_id' => 'nullable|exists:semesters,id',
            'file' => 'required|mimes:xlsx,csv,ods',
        ]);

        $target = $this->resolveTarget($request->input('target'));
        $meta = self::TARGETS[$target];

        if ($target === 'kurikulum_bundle') {
            try {
                $spreadsheet = IOFactory::load($request->file('file')->getPathname());
                $result = DB::transaction(function () use ($spreadsheet, $kurikulum) {
                    return $this->commitKurikulumBundle($spreadsheet, $kurikulum);
                });

                $successMessage = "Import master kurikulum berhasil: {$result['profils']} profil, {$result['cpls']} CPL, {$result['bks']} BK, {$result['mks']} MK diproses.";
                if (($result['mks_skipped_duplicate'] ?? 0) > 0) {
                    $successMessage .= " {$result['mks_skipped_duplicate']} data MK tidak di-commit karena kode sudah pernah dipakai.";
                }

                return redirect()->to($this->resolveReturnUrl($request))
                    ->with('success', $successMessage);
            } catch (\Throwable $e) {
                return back()->with('error', 'Gagal memproses import master kurikulum: ' . $e->getMessage());
            }
        }

        if ($target === 'join_kurikulum_bundle') {
            try {
                $spreadsheet = IOFactory::load($request->file('file')->getPathname());
                $result = DB::transaction(function () use ($spreadsheet, $kurikulum) {
                    return $this->commitJoinKurikulumBundle($spreadsheet, $kurikulum);
                });

                $success = "Import interaksi master berhasil: Profil >< CPL ({$result['join_profil_cpls']['linked']} aktif), CPL >< BK ({$result['join_cpl_bks']['linked']} aktif) aktif).";

                $redirect = redirect()->to($this->resolveReturnUrl($request))
                    ->with('success', $success);

                $removed = ($result['join_profil_cpls']['removed'] ?? 0)
                    + ($result['join_cpl_bks']['removed'] ?? 0);

                if ($removed > 0) {
                    $redirect->with('danger', "{$removed} interaksi dibuang karena sel pada template dikosongkan.");
                }

                if (($result['join_cpl_bks']['locked_skipped'] ?? 0) > 0) {
                    $redirect->with('warning', "{$result['join_cpl_bks']['locked_skipped']} interaksi CPL >< BK tidak dapat dihapus karena masih dipakai dalam pembobotan CPL >< MK.");
                }

                return $redirect;
            } catch (\Throwable $e) {
                return back()->with('error', 'Gagal memproses import interaksi master: ' . $e->getMessage());
            }
        }

        if (!empty($meta['requires_semester']) && empty($request->semester_id)) {
            return back()->with('error', 'Semester wajib dipilih untuk target import ini.');
        }

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (in_array($target, ['join_profil_cpls', 'join_cpl_bks', 'join_cpl_mks'], true)) {
                $result = match ($target) {
                    'join_profil_cpls' => $this->saveJoinProfilCplMatrix($rows, $kurikulum),
                    'join_cpl_bks' => $this->saveJoinCplBkMatrix($rows, $kurikulum),
                    'join_cpl_mks' => $this->saveJoinCplMkMatrix($rows, $kurikulum),
                };

                $successLabel = match ($target) {
                    'join_profil_cpls' => 'Interaksi Profil >< CPL',
                    'join_cpl_bks' => 'Interaksi CPL >< BK',
                    'join_cpl_mks' => 'Interaksi CPL >< MK',
                };

                $redirect = redirect()->to($this->resolveReturnUrl($request))
                    ->with('success', $successLabel . " berhasil disimpan ({$result['linked']} aktif).");

                if (($result['removed'] ?? 0) > 0) {
                    $redirect->with('danger', "{$result['removed']} interaksi dibuang karena sel pada template dikosongkan.");
                }

                if (($result['locked_skipped'] ?? 0) > 0) {
                    $redirect->with('warning', "{$result['locked_skipped']} interaksi tidak dapat dihapus karena sudah dipakai pada relasi CPL >< CPMK.");
                }

                if (($result['skipped_unavailable_inputs'] ?? 0) > 0) {
                    $redirect->with('warning', "{$result['skipped_unavailable_inputs']} input pada sel non-interaksi dilewati otomatis.");
                }

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

                $previewRows[] = $this->decoratePreviewRow($target, $normalizedRow, $kurikulum, $request->semester_id);
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data valid untuk dipreview.');
            }

            session([
                $this->previewSessionKey($kurikulum, $target) => [
                    'target' => $target,
                    'semester_id' => $request->semester_id,
                    'filename' => $request->file('file')->getClientOriginalName(),
                    'rows' => $previewRows,
                ],
            ]);

            return to_route('settings.import.kurikulum-master', $this->withReturnUrl([
                'kurikulum' => $kurikulum->id,
                'target' => $target,
            ], $request))
                ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan diproses.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function commit(Request $request, Kurikulum $kurikulum)
    {
        $request->validate([
            'target' => 'required|string|in:' . implode(',', array_keys(self::TARGETS)),
            'semester_id' => 'nullable|exists:semesters,id',
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $target = $this->resolveTarget($request->input('target'));
        $meta = self::TARGETS[$target];
        $preview = session($this->previewSessionKey($kurikulum, $target), []);
        $rows = $preview['rows'] ?? [];
        $semesterId = $request->input('semester_id') ?: ($preview['semester_id'] ?? null);

        if (empty($rows)) {
            return to_route('settings.import.kurikulum-master', $this->withReturnUrl([
                'kurikulum' => $kurikulum->id,
                'target' => $target,
            ], $request))
                ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        if (!empty($meta['requires_semester']) && empty($semesterId)) {
            return to_route('settings.import.kurikulum-master', $this->withReturnUrl([
                'kurikulum' => $kurikulum->id,
                'target' => $target,
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

            if (($rows[$idx]['can_save'] ?? true) === false) {
                $skipped[] = 'Baris ' . ($idx + 2) . ': ' . ($rows[$idx]['status_message'] ?? 'Data tidak valid.');
                continue;
            }

            try {
                $this->persistRow($target, $rows[$idx], $kurikulum, $semesterId);
                $saved++;
            } catch (\Throwable $e) {
                $skipped[] = 'Baris ' . ($idx + 2) . ': ' . $e->getMessage();
            }
        }

        session()->forget($this->previewSessionKey($kurikulum, $target));

        $message = "{$saved} baris berhasil diproses.";
        if (!empty($skipped)) {
            $message .= ' Beberapa baris dilewati: ' . implode(' | ', array_slice($skipped, 0, 5));
        }

        if ($target === 'kurikulum_bundle') {
            return to_route('kurikulums.profils.index', [$kurikulum->id])
                ->with('success', $message);
        }

        return redirect()->to($this->resolveReturnUrl($request))
            ->with('success', $message);
    }

    public function template(Kurikulum $kurikulum, Request $request)
    {
        $target = $this->resolveTarget($request->query('target'));
        $meta = self::TARGETS[$target];

        if ($target === 'kurikulum_bundle') {
            $spreadsheet = $this->buildKurikulumBundleTemplate($kurikulum);
            $writer = new Xlsx($spreadsheet);
            $waktuDownload = now()->format('YmdHis');
            $fileName = 'import' . $waktuDownload . '-master-kurikulum-prodi-' . Str::slug((string) ($kurikulum->prodi->jenjang . '-' . $kurikulum->prodi->nama ?? 'kurikulum'), '-') . '.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($target === 'join_kurikulum_bundle') {
            $spreadsheet = $this->buildJoinKurikulumBundleTemplate($kurikulum);
            $writer = new Xlsx($spreadsheet);
            $waktuDownload = now()->format('YmdHis');
            $fileName = 'import' . $waktuDownload . '-interaksi-master-kurikulum-prodi-' . Str::slug((string) ($kurikulum->prodi->jenjang . '-' . $kurikulum->prodi->nama ?? 'kurikulum'), '-') . '.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($target === 'join_profil_cpls') {
            $profils = $kurikulum->profils()->orderBy('nama')->get();
            $cpls = $kurikulum->cpls()->orderBy('kode')->get();

            $linkedMap = JoinProfilCpl::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->whereIn('profil_id', $profils->pluck('id'))
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->get()
                ->keyBy(fn ($row) => $row->cpl_id . '_' . $row->profil_id);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'CPL');
            $sheet->getStyle('A1')->getFont()->setBold(true);

            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1 + $profils->count());
            $sheet->mergeCells('B1:' . $lastColumn . '1');
            $sheet->setCellValue('B1', 'PROFIL LULUSAN');
            $sheet->getStyle('B1')->getFont()->setBold(true);

            $columnIndex = 2;
            foreach ($profils as $profil) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue($column . '2', trim((string) ($profil->nama . "\n" . ($profil->deskripsi ?? ''))));
                $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
                $sheet->getStyle($column . '2')->getFont()->setBold(true);
                $sheet->getColumnDimension($column)->setWidth(28);
                $columnIndex++;
            }

            $sheet->getColumnDimension('A')->setWidth(36);

            $rowIndex = 3;
            foreach ($cpls as $cpl) {
                $sheet->setCellValue('A' . $rowIndex, trim((string) ($cpl->kode . "\n" . $cpl->nama)));
                $sheet->getStyle('A' . $rowIndex)->getAlignment()->setWrapText(true);

                $columnIndex = 2;
                foreach ($profils as $profil) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                    $isLinked = $linkedMap->has($cpl->id . '_' . $profil->id);
                    $sheet->setCellValue($column . $rowIndex, $isLinked ? 'V' : '');
                    $columnIndex++;
                }

                $rowIndex++;
            }

            if ($profils->count() > 0 && $cpls->count() > 0) {
                $this->applyInteractionValidation($sheet, 2, 1 + $profils->count(), 3, 2 + $cpls->count());
            }

            $writer = new Xlsx($spreadsheet);
            $waktuDownload = now()->format('YmdHis');
            $fileName = 'import' . $waktuDownload . '-interaksi-profil-cpl-prodi-' . Str::slug((string) ($kurikulum->prodi->jenjang . '-' . $kurikulum->prodi->nama ?? 'kurikulum'), '-') . '.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($target === 'join_cpl_bks') {
            $bks = $kurikulum->bks()->orderBy('kode')->get();
            $cpls = $kurikulum->cpls()->orderBy('kode')->get();

            $linkedMap = JoinCplBk::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->whereIn('bk_id', $bks->pluck('id'))
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->get()
                ->keyBy(fn ($row) => $row->cpl_id . '_' . $row->bk_id);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'CAPAIAN PEMBELAJARAN LULUSAN');
            $sheet->getStyle('A1')->getFont()->setBold(true);

            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1 + $bks->count());
            $sheet->mergeCells('B1:' . $lastColumn . '1');
            $sheet->setCellValue('B1', 'BAHAN KAJIAN');
            $sheet->getStyle('B1')->getFont()->setBold(true);

            $columnIndex = 2;
            foreach ($bks as $bk) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue($column . '2', trim((string) ($bk->kode . "\n" . $bk->nama)));
                $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
                $sheet->getStyle($column . '2')->getFont()->setBold(true);
                $sheet->getColumnDimension($column)->setWidth(20);
                $columnIndex++;
            }

            $sheet->getColumnDimension('A')->setWidth(36);

            $rowIndex = 3;
            foreach ($cpls as $cpl) {
                $sheet->setCellValue('A' . $rowIndex, trim((string) ($cpl->kode . "\n" . $cpl->nama)));
                $sheet->getStyle('A' . $rowIndex)->getAlignment()->setWrapText(true);

                $columnIndex = 2;
                foreach ($bks as $bk) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                    $isLinked = $linkedMap->has($cpl->id . '_' . $bk->id);
                    $sheet->setCellValue($column . $rowIndex, $isLinked ? 'V' : '');
                    $columnIndex++;
                }

                $rowIndex++;
            }

            if ($bks->count() > 0 && $cpls->count() > 0) {
                $this->applyInteractionValidation($sheet, 2, 1 + $bks->count(), 3, 2 + $cpls->count());
            }

            $writer = new Xlsx($spreadsheet);
            $waktuDownload = now()->format('YmdHis');
            $fileName = 'import' . $waktuDownload . '-interaksi-cpl-bk-prodi-' . Str::slug((string) ($kurikulum->prodi->jenjang . '-' . $kurikulum->prodi->nama ?? 'kurikulum'), '-') . '.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($target === 'join_cpl_mks') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $this->fillJoinCplMkSheet($sheet, $kurikulum, true);

            $writer = new Xlsx($spreadsheet);
            $waktuDownload = now()->format('YmdHis');
            $fileName = 'import' . $waktuDownload . '-interaksi-cpl-mk-prodi-' . Str::slug((string) ($kurikulum->prodi->jenjang . '-' . $kurikulum->prodi->nama ?? 'kurikulum'), '-') . '.xlsx';

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
        $fileName = 'import' . $waktu_download . '-' . Str::slug($meta['label'], '-') . '-prodi-' . Str::slug((string) ($kurikulum->prodi->jenjang . '-' . $kurikulum->prodi->nama ?? 'kurikulum'), '-') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clear(Kurikulum $kurikulum, Request $request)
    {
        $target = $this->resolveTarget($request->input('target'));
        session()->forget($this->previewSessionKey($kurikulum, $target));

        return to_route('settings.import.kurikulum-master', $this->withReturnUrl([
            'kurikulum' => $kurikulum->id,
            'target' => $target,
        ], $request))
            ->with('success', 'Preview berhasil dikosongkan.');
    }

    private function persistRow(string $target, array $row, Kurikulum $kurikulum, ?string $semesterId): void
    {
        switch ($target) {
            case 'profils':
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $kode = trim((string) ($row['kode'] ?? ''));

                if ($kode !== '') {
                    Profil::updateOrCreate(
                        ['kurikulum_id' => $kurikulum->id, 'kode' => $kode],
                        [
                            'nama' => $nama,
                            'deskripsi' => $row['deskripsi'] ?? null,
                        ]
                    );
                } else {
                    Profil::updateOrCreate(
                        ['kurikulum_id' => $kurikulum->id, 'nama' => $nama],
                        [
                            'kode' => null,
                            'deskripsi' => $row['deskripsi'] ?? null,
                        ]
                    );
                }
                return;

            case 'profil_indikators':
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $profil = $this->findProfilByRow($kurikulum, $row);
                if (!$profil) {
                    throw new \RuntimeException('Profil tidak ditemukan dari kolom nama_profil/kode_profil.');
                }
                ProfilIndikator::updateOrCreate(
                    ['profil_id' => $profil->id, 'nama' => $nama],
                    ['deskripsi' => $row['deskripsi'] ?? null]
                );
                return;

            case 'cpls':
                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $cakupan = $this->required($row['cakupan'] ?? null, 'cakupan');
                Cpl::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'kode' => $kode],
                    ['nama' => $nama, 'cakupan' => $cakupan]
                );
                return;

            case 'join_profil_cpls':
                $profil = $this->findProfilByRow($kurikulum, $row);
                if (!$profil) {
                    throw new \RuntimeException('Profil tidak ditemukan dari kolom nama_profil/kode_profil.');
                }

                $kodeCpl = $this->required($row['kode_cpl'] ?? null, 'kode_cpl');
                $cpl = Cpl::query()
                    ->where('kurikulum_id', $kurikulum->id)
                    ->where('kode', $kodeCpl)
                    ->first();
                if (!$cpl) {
                    throw new \RuntimeException('CPL tidak ditemukan untuk kode_cpl: ' . $kodeCpl);
                }

                JoinProfilCpl::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'profil_id' => $profil->id, 'cpl_id' => $cpl->id],
                    []
                );
                return;

            case 'bks':
                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                Bk::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'kode' => $kode],
                    ['nama' => $nama, 'deskripsi' => $row['deskripsi'] ?? null]
                );
                return;

            case 'join_cpl_bks':
                $kodeCpl = $this->required($row['kode_cpl'] ?? null, 'kode_cpl');
                $kodeBk = $this->required($row['kode_bk'] ?? null, 'kode_bk');

                $cpl = Cpl::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeCpl)->first();
                $bk = Bk::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeBk)->first();
                if (!$cpl || !$bk) {
                    throw new \RuntimeException('CPL/BK tidak ditemukan untuk relasi.');
                }

                JoinCplBk::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'cpl_id' => $cpl->id, 'bk_id' => $bk->id],
                    []
                );
                return;

            case 'mks':
                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $semester = (int) $this->required($row['semester'] ?? null, 'semester');
                $sksTeori = (int) $this->required($row['sks_teori'] ?? null, 'sks_teori');
                $sksPraktik = (int) $this->required($row['sks_praktik'] ?? null, 'sks_praktik');
                $sksLapangan = (int) $this->required($row['sks_lapangan'] ?? null, 'sks_lapangan');
                $sks = $sksTeori + $sksPraktik + $sksLapangan;

                Mk::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'kode' => $kode],
                    [
                        'nama' => $nama,
                        'semester' => $semester,
                        'sks_teori' => $sksTeori,
                        'sks_praktik' => $sksPraktik,
                        'sks_lapangan' => $sksLapangan,
                        'sks' => $sks,
                        'deskripsi' => $row['deskripsi'] ?? null,
                    ]
                );
                return;

            case 'mahasiswas':
                $nim = $this->required($row['nim'] ?? null, 'nim');
                Mahasiswa::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'nama' => $row['nama'] ?? null,
                        'angkatan' => $row['angkatan'] ?? null,
                        'email' => $row['email'] ?? null,
                        'phone' => $row['phone'] ?? null,
                        'prodi_id' => $kurikulum->prodi_id,
                    ]
                );
                return;

            case 'joinmkusers':
                $kodeSemester = $this->required($row['kode_semester'] ?? null, 'kode_semester');
                $kodeMk = $this->required($row['kode_mk'] ?? null, 'kode_mk');
                $nidn = $this->required($row['nidn'] ?? null, 'nidn');

                $semester = Semester::query()->where('kode', $kodeSemester)->first();
                if (!$semester) {
                    throw new \RuntimeException('Semester tidak ditemukan: ' . $kodeSemester);
                }

                $mk = Mk::query()
                    ->where('kurikulum_id', $kurikulum->id)
                    ->where('kode', $kodeMk)
                    ->first();
                if (!$mk) {
                    throw new \RuntimeException('MK tidak ditemukan pada kurikulum ini: ' . $kodeMk);
                }

                $user = User::query()->where('nidn', $nidn)->first();
                if (!$user) {
                    throw new \RuntimeException('User dosen tidak ditemukan untuk NIDN: ' . $nidn);
                }

                $isKoordinator = in_array(Str::lower(trim((string) ($row['koordinator'] ?? ''))), ['ya', 'yes', '1', 'true'], true);

                JoinMkUser::updateOrCreate(
                    [
                        'semester_id' => $semester->id,
                        'mk_id' => $mk->id,
                        'kurikulum_id' => $kurikulum->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'koordinator' => $isKoordinator,
                    ]
                );
                return;

            case 'kontrakmks':
                $kodeSemester = $this->required($row['kode_semester'] ?? null, 'kode_semester');
                $nim = $this->required($row['nim'] ?? null, 'nim');
                $kodeMk = $this->required($row['kode_mk'] ?? null, 'kode_mk');
                $nidn = $this->required($row['nidn'] ?? null, 'nidn');

                $semester = Semester::query()->where('kode', $kodeSemester)->first();
                if (!$semester) {
                    throw new \RuntimeException('Semester tidak ditemukan: ' . $kodeSemester);
                }

                $mahasiswa = Mahasiswa::query()->where('nim', $nim)->first();
                if (!$mahasiswa) {
                    throw new \RuntimeException('Mahasiswa tidak ditemukan: ' . $nim);
                }

                $mk = Mk::query()
                    ->where('kurikulum_id', $kurikulum->id)
                    ->where('kode', $kodeMk)
                    ->first();
                if (!$mk) {
                    throw new \RuntimeException('MK tidak ditemukan pada kurikulum ini: ' . $kodeMk);
                }

                $user = User::query()->where('nidn', $nidn)->first();
                if (!$user) {
                    throw new \RuntimeException('User dosen tidak ditemukan untuk NIDN: ' . $nidn);
                }

                KontrakMk::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa->id,
                        'mk_id' => $mk->id,
                        'user_id' => $user->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'kelas' => $row['kelas'] ?? null,
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

    private function decoratePreviewRow(string $target, array $row, Kurikulum $kurikulum, ?string $semesterId): array
    {
        if (!in_array($target, ['profils', 'profil_indikators', 'cpls', 'bks', 'mks', 'joinmkusers'], true)) {
            return $row;
        }

        $status = [
            'exists' => false,
            'can_save' => true,
            'status_message' => null,
        ];

        if ($target === 'profils') {
            $kode = trim((string) ($row['kode'] ?? ''));
            $nama = trim((string) ($row['nama'] ?? ''));

            $query = Profil::query()->where('kurikulum_id', $kurikulum->id);
            if ($kode !== '') {
                $query->where('kode', $kode);
            } else {
                $query->where('nama', $nama);
            }

            $status['exists'] = $query->exists();

            return array_merge($row, $status);
        }

        if ($target === 'profil_indikators') {
            $nama = trim((string) ($row['nama'] ?? ''));
            if ($nama === '') {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Nama indikator wajib diisi',
                ]);
            }

            $profil = $this->findProfilByRow($kurikulum, $row);
            if (!$profil) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Profil tidak ditemukan',
                ]);
            }

            $status['exists'] = ProfilIndikator::query()
                ->where('profil_id', $profil->id)
                ->where('nama', $nama)
                ->exists();

            return array_merge($row, $status);
        }

        if ($target === 'cpls') {
            $kode = trim((string) ($row['kode'] ?? ''));
            if ($kode === '') {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Kode CPL wajib diisi',
                ]);
            }

            $status['exists'] = Cpl::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kode)
                ->exists();

            return array_merge($row, $status);
        }

        if ($target === 'mks') {
            $kodeMk = trim((string) ($row['kode'] ?? ''));
            if ($kodeMk === '') {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Kode MK wajib diisi',
                ]);
            }

            $usedInOtherKurikulum = Mk::query()
                ->where('kurikulum_id', '!=', $kurikulum->id)
                ->whereRaw('LOWER(TRIM(kode)) = ?', [Str::lower($kodeMk)])
                ->exists();

            if ($usedInOtherKurikulum) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Sudah digunakan kurikulum lain',
                ]);
            }

            $status['exists'] = Mk::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kodeMk)
                ->exists();

            return array_merge($row, $status);
        }

        if ($target === 'joinmkusers') {
            $kodeSemester = trim((string) ($row['kode_semester'] ?? ''));
            $kodeMk = trim((string) ($row['kode_mk'] ?? ''));
            $nidn = trim((string) ($row['nidn'] ?? ''));

            if ($kodeSemester === '' || $kodeMk === '' || $nidn === '') {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Kode semester, kode MK, dan NIDN wajib diisi',
                ]);
            }

            $semester = Semester::query()->where('kode', $kodeSemester)->first();
            if (!$semester) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Semester tidak ditemukan: ' . $kodeSemester,
                ]);
            }

            $mk = Mk::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kodeMk)
                ->first();
            if (!$mk) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'MK tidak ditemukan: ' . $kodeMk,
                ]);
            }

            $user = User::query()->where('nidn', $nidn)->first();
            if (!$user) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Dosen tidak ditemukan: ' . $nidn,
                ]);
            }

            $status['exists'] = JoinMkUser::query()
                ->where('semester_id', $semester->id)
                ->where('mk_id', $mk->id)
                ->where('kurikulum_id', $kurikulum->id)
                ->where('user_id', $user->id)
                ->exists();

            return array_merge($row, $status);
        }

        $kodeBk = trim((string) ($row['kode'] ?? ''));
        if ($kodeBk === '') {
            return array_merge($row, [
                'exists' => false,
                'can_save' => false,
                'status_message' => 'Kode BK wajib diisi',
            ]);
        }

        $status['exists'] = Bk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->where('kode', $kodeBk)
            ->exists();

        return array_merge($row, $status);
    }

    private function resolveTarget(?string $target): string
    {
        return array_key_exists((string) $target, self::TARGETS)
            ? (string) $target
            : array_key_first(self::TARGETS);
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

    private function previewSessionKey(Kurikulum $kurikulum, string $target): string
    {
        return 'import_kurikulum_master_' . $kurikulum->id . '_' . $target;
    }

    private function findProfilByRow(Kurikulum $kurikulum, array $row): ?Profil
    {
        $namaProfil = trim((string) ($row['nama_profil'] ?? ''));
        $kodeProfil = trim((string) ($row['kode_profil'] ?? ''));

        if ($kodeProfil !== '') {
            $profil = Profil::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kodeProfil)
                ->first();
            if ($profil) {
                return $profil;
            }
        }

        if ($namaProfil !== '') {
            $profil = Profil::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('nama', $namaProfil)
                ->first();
            if ($profil) {
                return $profil;
            }
        }

        return null;
    }

    private function saveJoinProfilCplMatrix(array $rows, Kurikulum $kurikulum): array
    {
        $headerRow = $rows[2] ?? [];
        $profilByColumn = [];

        foreach ($headerRow as $columnLetter => $value) {
            if ($columnLetter === 'A') {
                continue;
            }

            $profilHeader = trim((string) ($value ?? ''));
            if ($profilHeader === '') {
                continue;
            }

            $profilName = trim((string) Str::before($profilHeader, "\n"));
            $profil = Profil::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('nama', $profilName)
                ->first();

            if (!$profil) {
                throw new \RuntimeException('Profil tidak ditemukan pada header: ' . $profilName);
            }

            $profilByColumn[$columnLetter] = $profil;
        }

        if (empty($profilByColumn)) {
            throw new \RuntimeException('Header profil tidak ditemukan pada template interaksi.');
        }

        $desiredPairs = [];
        $scopeCplIds = [];
        $scopeProfilIds = collect($profilByColumn)->pluck('id')->unique()->values()->all();

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex <= 2) {
                continue;
            }

            $cplCell = trim((string) ($row['A'] ?? ''));
            if ($cplCell === '') {
                continue;
            }

            $kodeCpl = trim((string) Str::before($cplCell, "\n"));
            if ($kodeCpl === '') {
                continue;
            }

            $cpl = Cpl::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kodeCpl)
                ->first();
            if (!$cpl) {
                throw new \RuntimeException('CPL tidak ditemukan pada baris ' . $rowIndex . ': ' . $kodeCpl);
            }

            $scopeCplIds[] = $cpl->id;

            foreach ($profilByColumn as $columnLetter => $profil) {
                $raw = trim((string) ($row[$columnLetter] ?? ''));
                if ($raw === '') {
                    continue;
                }

                if (Str::upper($raw) !== 'V') {
                    throw new \RuntimeException('Nilai tidak valid pada baris ' . $rowIndex . ', kolom ' . $columnLetter . '. Gunakan huruf V atau kosong.');
                }

                $desiredPairs[$cpl->id . '_' . $profil->id] = [
                    'kurikulum_id' => $kurikulum->id,
                    'cpl_id' => $cpl->id,
                    'profil_id' => $profil->id,
                ];
            }
        }

        $scopeCplIds = array_values(array_unique($scopeCplIds));
        if (empty($scopeCplIds)) {
            throw new \RuntimeException('Tidak ada baris CPL pada template interaksi.');
        }

        $existingRows = JoinProfilCpl::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('cpl_id', $scopeCplIds)
            ->whereIn('profil_id', $scopeProfilIds)
            ->get();

        $desiredKeys = array_keys($desiredPairs);
        $removed = 0;
        foreach ($existingRows as $existingRow) {
            $key = $existingRow->cpl_id . '_' . $existingRow->profil_id;
            if (!in_array($key, $desiredKeys, true)) {
                $existingRow->delete();
                $removed++;
            }
        }

        foreach ($desiredPairs as $pair) {
            JoinProfilCpl::updateOrCreate(
                [
                    'kurikulum_id' => $pair['kurikulum_id'],
                    'profil_id' => $pair['profil_id'],
                    'cpl_id' => $pair['cpl_id'],
                ],
                []
            );
        }

        return [
            'linked' => count($desiredPairs),
            'removed' => $removed,
        ];
    }

    private function saveJoinCplBkMatrix(array $rows, Kurikulum $kurikulum): array
    {
        $headerRow = $rows[2] ?? [];
        $bkByColumn = [];

        foreach ($headerRow as $columnLetter => $value) {
            if ($columnLetter === 'A') {
                continue;
            }

            $header = trim((string) ($value ?? ''));
            if ($header === '') {
                continue;
            }

            $kodeBk = trim((string) Str::before($header, "\n"));
            $bk = Bk::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeBk)->first();
            if (!$bk) {
                throw new \RuntimeException('BK tidak ditemukan pada header: ' . $kodeBk);
            }

            $bkByColumn[$columnLetter] = $bk;
        }

        if (empty($bkByColumn)) {
            throw new \RuntimeException('Header BK tidak ditemukan pada template interaksi.');
        }

        $desiredPairs = [];
        $scopeCplIds = [];
        $scopeBkIds = collect($bkByColumn)->pluck('id')->unique()->values()->all();

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex <= 2) {
                continue;
            }

            $cplCell = trim((string) ($row['A'] ?? ''));
            if ($cplCell === '') {
                continue;
            }

            $kodeCpl = trim((string) Str::before($cplCell, "\n"));
            $cpl = Cpl::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeCpl)->first();
            if (!$cpl) {
                throw new \RuntimeException('CPL tidak ditemukan pada baris ' . $rowIndex . ': ' . $kodeCpl);
            }

            $scopeCplIds[] = $cpl->id;

            foreach ($bkByColumn as $columnLetter => $bk) {
                $raw = trim((string) ($row[$columnLetter] ?? ''));
                if ($raw === '') {
                    continue;
                }
                if (Str::upper($raw) !== 'V') {
                    throw new \RuntimeException('Nilai tidak valid pada baris ' . $rowIndex . ', kolom ' . $columnLetter . '. Gunakan huruf V atau kosong.');
                }

                $desiredPairs[$cpl->id . '_' . $bk->id] = [
                    'kurikulum_id' => $kurikulum->id,
                    'cpl_id' => $cpl->id,
                    'bk_id' => $bk->id,
                ];
            }
        }

        $scopeCplIds = array_values(array_unique($scopeCplIds));
        if (empty($scopeCplIds)) {
            throw new \RuntimeException('Tidak ada baris CPL pada template interaksi.');
        }

        $existingRows = JoinCplBk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('cpl_id', $scopeCplIds)
            ->whereIn('bk_id', $scopeBkIds)
            ->get();

        $desiredKeys = array_keys($desiredPairs);
        $removed = 0;
        $lockedSkipped = 0;

        $lockedJoinCplBkIds = JoinCplMk::query()
            ->whereIn('join_cpl_bk_id', $existingRows->pluck('id'))
            ->pluck('join_cpl_bk_id')
            ->unique()
            ->flip();

        foreach ($existingRows as $existingRow) {
            $key = $existingRow->cpl_id . '_' . $existingRow->bk_id;
            if (!in_array($key, $desiredKeys, true)) {
                if ($lockedJoinCplBkIds->has($existingRow->id)) {
                    $lockedSkipped++;
                    continue;
                }

                $existingRow->delete();
                $removed++;
            }
        }

        foreach ($desiredPairs as $pair) {
            JoinCplBk::updateOrCreate(
                [
                    'kurikulum_id' => $pair['kurikulum_id'],
                    'cpl_id' => $pair['cpl_id'],
                    'bk_id' => $pair['bk_id'],
                ],
                []
            );
        }

        return [
            'linked' => count($desiredPairs),
            'removed' => $removed,
            'locked_skipped' => $lockedSkipped,
        ];
    }

    private function saveJoinCplMkFromBkMatrix(array $rows, Kurikulum $kurikulum): array
    {
        $headerRow = $rows[2] ?? [];
        $bkByColumn = [];

        foreach ($headerRow as $columnLetter => $value) {
            if ($columnLetter === 'A') {
                continue;
            }

            $header = trim((string) ($value ?? ''));
            if ($header === '') {
                continue;
            }

            $kodeBk = trim((string) Str::before($header, "\n"));
            $bk = Bk::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeBk)->first();
            if (!$bk) {
                throw new \RuntimeException('BK tidak ditemukan pada header: ' . $kodeBk);
            }

            $bkByColumn[$columnLetter] = $bk;
        }

        if (empty($bkByColumn)) {
            throw new \RuntimeException('Header BK tidak ditemukan pada template interaksi.');
        }

        $desiredPairs = [];
        $scopeMkIds = [];
        $scopeBkIds = collect($bkByColumn)->pluck('id')->unique()->values()->all();

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex <= 2) {
                continue;
            }

            $mkCell = trim((string) ($row['A'] ?? ''));
            if ($mkCell === '') {
                continue;
            }

            $kodeMk = trim((string) Str::before($mkCell, "\n"));
            $mk = Mk::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeMk)->first();
            if (!$mk) {
                throw new \RuntimeException('MK tidak ditemukan pada baris ' . $rowIndex . ': ' . $kodeMk);
            }

            $scopeMkIds[] = $mk->id;

            foreach ($bkByColumn as $columnLetter => $bk) {
                $raw = trim((string) ($row[$columnLetter] ?? ''));
                if ($raw === '') {
                    continue;
                }
                if (Str::upper($raw) !== 'V') {
                    throw new \RuntimeException('Nilai tidak valid pada baris ' . $rowIndex . ', kolom ' . $columnLetter . '. Gunakan huruf V atau kosong.');
                }

                $desiredPairs[$mk->id . '_' . $bk->id] = [
                    'kurikulum_id' => $kurikulum->id,
                    'mk_id' => $mk->id,
                    'bk_id' => $bk->id,
                ];
            }
        }

        $scopeMkIds = array_values(array_unique($scopeMkIds));
        if (empty($scopeMkIds)) {
            throw new \RuntimeException('Tidak ada baris MK pada template interaksi.');
        }

        $joinCplBkRows = JoinCplBk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('bk_id', $scopeBkIds)
            ->get(['id', 'bk_id']);

        $joinCplBkIdsByBk = $joinCplBkRows
            ->groupBy('bk_id')
            ->map(fn ($items) => $items->pluck('id')->values());

        $lockedPairMap = JoinCplCpmk::query()
            ->whereIn('mk_id', $scopeMkIds)
            ->whereIn('join_cpl_bk_id', $joinCplBkRows->pluck('id'))
            ->with('joinCplBk:id,bk_id')
            ->get()
            ->mapWithKeys(function ($row) {
                $bkId = optional($row->joinCplBk)->bk_id;

                return $bkId
                    ? [($row->mk_id . '_' . $bkId) => true]
                    : [];
            });

        $existingRows = JoinCplMk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('mk_id', $scopeMkIds)
            ->whereIn('join_cpl_bk_id', $joinCplBkRows->pluck('id'))
            ->with('joinCplBk:id,bk_id')
            ->get();

        $desiredKeys = array_keys($desiredPairs);
        $removed = 0;
        $lockedSkipped = 0;
        foreach ($existingRows as $existingRow) {
            $bkId = optional($existingRow->joinCplBk)->bk_id;
            if (!$bkId) {
                continue;
            }

            $key = $existingRow->mk_id . '_' . $bkId;
            if (!in_array($key, $desiredKeys, true)) {
                if ($lockedPairMap->has($key)) {
                    $lockedSkipped++;
                    continue;
                }

                $existingRow->delete();
                $removed++;
            }
        }

        foreach ($desiredPairs as $pair) {
            $joinCplBkIds = $joinCplBkIdsByBk->get($pair['bk_id'], collect());

            foreach ($joinCplBkIds as $joinCplBkId) {
                JoinCplMk::updateOrCreate(
                    [
                        'kurikulum_id' => $pair['kurikulum_id'],
                        'mk_id' => $pair['mk_id'],
                        'join_cpl_bk_id' => $joinCplBkId,
                    ],
                    []
                );
            }
        }

        return [
            'linked' => count($desiredPairs),
            'removed' => $removed,
            'locked_skipped' => $lockedSkipped,
        ];
    }

    private function saveJoinCplMkMatrix(array $rows, Kurikulum $kurikulum): array
    {
        $topHeaderRow = $rows[1] ?? [];
        $subHeaderRow = $rows[2] ?? [];
        $joinCplBkByColumn = [];
        $activeCplKode = '';

        foreach ($subHeaderRow as $columnLetter => $value) {
            if ($columnLetter === 'A') {
                continue;
            }

            $topHeader = trim((string) ($topHeaderRow[$columnLetter] ?? ''));
            if ($topHeader !== '') {
                $activeCplKode = trim((string) Str::before($topHeader, "\n"));
            }

            $subHeader = trim((string) ($value ?? ''));
            if ($subHeader === '') {
                continue;
            }

            if ($activeCplKode === '') {
                throw new \RuntimeException('Header CPL tidak dikenali pada kolom ' . $columnLetter . '.');
            }

            $kodeBk = trim((string) Str::after($subHeader, 'BK:'));
            if ($kodeBk === '') {
                throw new \RuntimeException('Header BK tidak valid pada kolom ' . $columnLetter . '.');
            }

            $cpl = Cpl::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $activeCplKode)
                ->first();
            if (!$cpl) {
                throw new \RuntimeException('CPL tidak ditemukan pada header: ' . $activeCplKode);
            }

            $bk = Bk::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kodeBk)
                ->first();
            if (!$bk) {
                throw new \RuntimeException('BK tidak ditemukan pada header: ' . $kodeBk);
            }

            $joinCplBk = JoinCplBk::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('cpl_id', $cpl->id)
                ->where('bk_id', $bk->id)
                ->first();

            if (!$joinCplBk) {
                throw new \RuntimeException('Relasi CPL-BK tidak ditemukan untuk header: ' . $activeCplKode . ' / ' . $kodeBk);
            }

            $joinCplBkByColumn[$columnLetter] = $joinCplBk;
        }

        if (empty($joinCplBkByColumn)) {
            throw new \RuntimeException('Header interaksi CPL >< MK tidak ditemukan pada template.');
        }

        $desiredPairs = [];
        $scopeMkIds = [];
        $scopeJoinCplBkIds = collect($joinCplBkByColumn)->pluck('id')->unique()->values()->all();
        $skippedUnavailableInputs = 0;

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex <= 2) {
                continue;
            }

            $mkCell = trim((string) ($row['A'] ?? ''));
            if ($mkCell === '') {
                continue;
            }

            $kodeMk = trim((string) Str::before($mkCell, "\n"));
            $mk = Mk::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kodeMk)
                ->first();
            if (!$mk) {
                throw new \RuntimeException('MK tidak ditemukan pada baris ' . $rowIndex . ': ' . $kodeMk);
            }

            $scopeMkIds[] = $mk->id;

            foreach ($joinCplBkByColumn as $columnLetter => $joinCplBk) {
                $raw = trim((string) ($row[$columnLetter] ?? ''));
                if ($raw === '') {
                    continue;
                }

                $normalizedValue = str_replace(',', '.', $raw);
                if (!is_numeric($normalizedValue)) {
                    throw new \RuntimeException('Nilai tidak valid pada baris ' . $rowIndex . ', kolom ' . $columnLetter . '. Gunakan angka 0-100 atau kosong.');
                }

                $bobot = (float) $normalizedValue;
                if ($bobot < 0 || $bobot > 100) {
                    throw new \RuntimeException('Nilai bobot harus di antara 0 sampai 100 pada baris ' . $rowIndex . ', kolom ' . $columnLetter . '.');
                }

                $desiredPairs[$mk->id . '_' . $joinCplBk->id] = [
                    'kurikulum_id' => $kurikulum->id,
                    'mk_id' => $mk->id,
                    'join_cpl_bk_id' => $joinCplBk->id,
                    'bobot' => $bobot,
                ];
            }
        }

        $scopeMkIds = array_values(array_unique($scopeMkIds));
        if (empty($scopeMkIds)) {
            throw new \RuntimeException('Tidak ada baris MK pada template interaksi.');
        }

        $lockedMap = JoinCplCpmk::query()
            ->whereIn('mk_id', $scopeMkIds)
            ->whereIn('join_cpl_bk_id', $scopeJoinCplBkIds)
            ->get()
            ->mapWithKeys(fn ($row) => [($row->mk_id . '_' . $row->join_cpl_bk_id) => true]);

        $existingRows = JoinCplMk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('mk_id', $scopeMkIds)
            ->whereIn('join_cpl_bk_id', $scopeJoinCplBkIds)
            ->get();

        $desiredKeys = array_keys($desiredPairs);
        $removed = 0;
        $lockedSkipped = 0;

        foreach ($existingRows as $existingRow) {
            $key = $existingRow->mk_id . '_' . $existingRow->join_cpl_bk_id;
            if (in_array($key, $desiredKeys, true)) {
                continue;
            }

            if ($lockedMap->has($key)) {
                $lockedSkipped++;
                continue;
            }

            $existingRow->delete();
            $removed++;
        }

        foreach ($desiredPairs as $pair) {
            JoinCplMk::updateOrCreate(
                [
                    'kurikulum_id' => $pair['kurikulum_id'],
                    'mk_id' => $pair['mk_id'],
                    'join_cpl_bk_id' => $pair['join_cpl_bk_id'],
                ],
                [
                    'bobot' => $pair['bobot'],
                ]
            );
        }

        return [
            'linked' => count($desiredPairs),
            'removed' => $removed,
            'locked_skipped' => $lockedSkipped,
            'skipped_unavailable_inputs' => $skippedUnavailableInputs,
        ];
    }

    private function buildKurikulumBundleTemplate(Kurikulum $kurikulum): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $sheetDefinitions = [
            [
                'name' => 'Profil',
                'columns' => ['kode', 'nama', 'deskripsi'],
                'required' => ['kode', 'nama'],
                'rows' => $kurikulum->profils()->orderBy('kode')->orderBy('nama')->get(['kode', 'nama', 'deskripsi'])
                    ->map(fn ($item) => [
                        'kode' => (string) ($item->kode ?? ''),
                        'nama' => (string) ($item->nama ?? ''),
                        'deskripsi' => (string) ($item->deskripsi ?? ''),
                    ])->all(),
                'examples' => [
                    ['kode' => 'P1', 'nama' => 'Profesional', 'deskripsi' => 'Mampu bekerja sesuai etika profesi.'],
                ],
            ],
            [
                'name' => 'CPL',
                'columns' => ['kode', 'nama', 'cakupan'],
                'required' => ['kode', 'nama', 'cakupan'],
                'rows' => $kurikulum->cpls()->orderBy('kode')->get(['kode', 'nama', 'cakupan'])
                    ->map(fn ($item) => [
                        'kode' => (string) ($item->kode ?? ''),
                        'nama' => (string) ($item->nama ?? ''),
                        'cakupan' => (string) ($item->cakupan ?? ''),
                    ])->all(),
                'examples' => [
                    ['kode' => 'CPL-01', 'nama' => 'Sikap Profesional', 'cakupan' => 'S'],
                ],
            ],
            [
                'name' => 'BK',
                'columns' => ['kode', 'nama', 'deskripsi'],
                'required' => ['kode', 'nama'],
                'rows' => $kurikulum->bks()->orderBy('kode')->get(['kode', 'nama', 'deskripsi'])
                    ->map(fn ($item) => [
                        'kode' => (string) ($item->kode ?? ''),
                        'nama' => (string) ($item->nama ?? ''),
                        'deskripsi' => (string) ($item->deskripsi ?? ''),
                    ])->all(),
                'examples' => [
                    ['kode' => 'BK-01', 'nama' => 'Dasar Komputasi', 'deskripsi' => 'Konsep dasar komputasi modern.'],
                ],
            ],
            [
                'name' => 'MK',
                'columns' => ['kode', 'nama', 'semester', 'sks_teori', 'sks_praktik', 'sks_lapangan', 'deskripsi'],
                'required' => ['kode', 'nama', 'semester', 'sks_teori', 'sks_praktik', 'sks_lapangan'],
                'rows' => $kurikulum->mks()->orderBy('kode')->get(['kode', 'nama', 'semester', 'sks_teori', 'sks_praktik', 'sks_lapangan', 'deskripsi'])
                    ->map(fn ($item) => [
                        'kode' => (string) ($item->kode ?? ''),
                        'nama' => (string) ($item->nama ?? ''),
                        'semester' => (string) ($item->semester ?? ''),
                        'sks_teori' => (string) ($item->sks_teori ?? 0),
                        'sks_praktik' => (string) ($item->sks_praktik ?? 0),
                        'sks_lapangan' => (string) ($item->sks_lapangan ?? 0),
                        'deskripsi' => (string) ($item->deskripsi ?? ''),
                    ])->all(),
                'examples' => [
                    ['kode' => 'MK-101', 'nama' => 'Algoritma dan Pemrograman', 'semester' => '1', 'sks_teori' => '2', 'sks_praktik' => '1', 'sks_lapangan' => '0', 'deskripsi' => 'Pengenalan logika dan pemrograman.'],
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
                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1);
                $columnLetters[$columnName] = $letter;
                $sheet->setCellValue($letter . '1', $columnName);
                $sheet->getStyle($letter . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($letter)->setWidth(max(16, strlen($columnName) + 3));
            }

            $rowIndex = 2;
            foreach ($rows as $row) {
                foreach ($columns as $columnName) {
                    $letter = $columnLetters[$columnName];
                    $sheet->setCellValue($letter . $rowIndex, (string) ($row[$columnName] ?? ''));
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
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildJoinKurikulumBundleTemplate(Kurikulum $kurikulum): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $sheetProfilCpl = $spreadsheet->createSheet(0);
        $sheetProfilCpl->setTitle('JOIN_PROFIL_CPL');
        $this->fillJoinProfilCplSheet($sheetProfilCpl, $kurikulum, true);

        $sheetCplBk = $spreadsheet->createSheet(1);
        $sheetCplBk->setTitle('JOIN_CPL_BK');
        $this->fillJoinCplBkSheet($sheetCplBk, $kurikulum, true);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function commitJoinKurikulumBundle(Spreadsheet $spreadsheet, Kurikulum $kurikulum): array
    {
        $sheetProfilCpl = $spreadsheet->getSheetByName('JOIN_PROFIL_CPL');
        $sheetCplBk = $spreadsheet->getSheetByName('JOIN_CPL_BK');

        if (!$sheetProfilCpl || !$sheetCplBk) {
            throw new \RuntimeException('Template gabungan tidak valid. Pastikan sheet JOIN_PROFIL_CPL dan JOIN_CPL_BK tersedia.');
        }

        return [
            'join_profil_cpls' => $this->saveJoinProfilCplMatrix($sheetProfilCpl->toArray(null, true, true, true), $kurikulum),
            'join_cpl_bks' => $this->saveJoinCplBkMatrix($sheetCplBk->toArray(null, true, true, true), $kurikulum),
        ];
    }

    private function fillJoinProfilCplSheet($sheet, Kurikulum $kurikulum, bool $withValidation): void
    {
        $profils = $kurikulum->profils()->orderBy('kode')->get();
        $cpls = $kurikulum->cpls()->orderBy('kode')->get();

        $linkedMap = JoinProfilCpl::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('profil_id', $profils->pluck('id'))
            ->whereIn('cpl_id', $cpls->pluck('id'))
            ->get()
            ->keyBy(fn ($row) => $row->cpl_id . '_' . $row->profil_id);

        $sheet->setCellValue('A1', 'CPL');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $lastColumn = Coordinate::stringFromColumnIndex(1 + $profils->count());
        $sheet->mergeCells('B1:' . $lastColumn . '1');
        $sheet->setCellValue('B1', 'PROFIL LULUSAN');
        $sheet->getStyle('B1')->getFont()->setBold(true);

        $columnIndex = 2;
        foreach ($profils as $profil) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($column . '2', trim((string) ($profil->nama . "\n" . ($profil->deskripsi ?? ''))));
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
            $sheet->getStyle($column . '2')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setWidth(28);
            $columnIndex++;
        }

        $sheet->getColumnDimension('A')->setWidth(36);

        $rowIndex = 3;
        foreach ($cpls as $cpl) {
            $sheet->setCellValue('A' . $rowIndex, trim((string) ($cpl->kode . "\n" . $cpl->nama)));
            $sheet->getStyle('A' . $rowIndex)->getAlignment()->setWrapText(true);

            $columnIndex = 2;
            foreach ($profils as $profil) {
                $column = Coordinate::stringFromColumnIndex($columnIndex);
                $isLinked = $linkedMap->has($cpl->id . '_' . $profil->id);
                $sheet->setCellValue($column . $rowIndex, $isLinked ? 'V' : '');
                $columnIndex++;
            }

            $rowIndex++;
        }

        if ($withValidation && $profils->count() > 0 && $cpls->count() > 0) {
            $this->applyInteractionValidation($sheet, 2, 1 + $profils->count(), 3, 2 + $cpls->count());
        }
    }

    private function fillJoinCplBkSheet($sheet, Kurikulum $kurikulum, bool $withValidation): void
    {
        $bks = $kurikulum->bks()->orderBy('kode')->get();
        $cpls = $kurikulum->cpls()->orderBy('kode')->get();

        $linkedMap = JoinCplBk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('bk_id', $bks->pluck('id'))
            ->whereIn('cpl_id', $cpls->pluck('id'))
            ->get()
            ->keyBy(fn ($row) => $row->cpl_id . '_' . $row->bk_id);

        $sheet->setCellValue('A1', 'CAPAIAN PEMBELAJARAN LULUSAN');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $lastColumn = Coordinate::stringFromColumnIndex(1 + $bks->count());
        $sheet->mergeCells('B1:' . $lastColumn . '1');
        $sheet->setCellValue('B1', 'BAHAN KAJIAN');
        $sheet->getStyle('B1')->getFont()->setBold(true);

        $columnIndex = 2;
        foreach ($bks as $bk) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($column . '2', trim((string) ($bk->kode . "\n" . $bk->nama)));
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
            $sheet->getStyle($column . '2')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setWidth(20);
            $columnIndex++;
        }

        $sheet->getColumnDimension('A')->setWidth(36);

        $rowIndex = 3;
        foreach ($cpls as $cpl) {
            $sheet->setCellValue('A' . $rowIndex, trim((string) ($cpl->kode . "\n" . $cpl->nama)));
            $sheet->getStyle('A' . $rowIndex)->getAlignment()->setWrapText(true);

            $columnIndex = 2;
            foreach ($bks as $bk) {
                $column = Coordinate::stringFromColumnIndex($columnIndex);
                $isLinked = $linkedMap->has($cpl->id . '_' . $bk->id);
                $sheet->setCellValue($column . $rowIndex, $isLinked ? 'V' : '');
                $columnIndex++;
            }

            $rowIndex++;
        }

        if ($withValidation && $bks->count() > 0 && $cpls->count() > 0) {
            $this->applyInteractionValidation($sheet, 2, 1 + $bks->count(), 3, 2 + $cpls->count());
        }
    }

    private function fillJoinCplMkSheet($sheet, Kurikulum $kurikulum, bool $withValidation): void
    {
        $cpls = $kurikulum->cpls()
            ->with(['joinCplBks.bk'])
            ->orderBy('kode')
            ->get();

        $mks = $kurikulum->mks()
            ->orderBy('semester')
            ->orderBy('kode')
            ->get();

        $cplHeaderGroups = collect();
        $cplBkColumns = collect();

        foreach ($cpls as $cpl) {
            $bkColumns = $cpl->joinCplBks
                ->filter(fn ($join) => $join->bk)
                ->sortBy(fn ($join) => (string) $join->bk->kode)
                ->values();

            if ($bkColumns->isEmpty()) {
                continue;
            }

            $cplHeaderGroups->push([
                'cpl_kode' => $cpl->kode,
                'cpl_nama' => $cpl->nama,
                'colspan' => $bkColumns->count(),
            ]);

            foreach ($bkColumns as $join) {
                $cplBkColumns->push([
                    'join_cpl_bk_id' => $join->id,
                    'bk_kode' => $join->bk->kode,
                    'bk_nama' => $join->bk->nama,
                ]);
            }
        }

        if ($cplBkColumns->isEmpty()) {
            throw new \RuntimeException('Belum ada relasi CPL >< BK. Tambahkan relasi CPL >< BK terlebih dahulu sebelum mengunduh template interaksi CPL >< MK.');
        }

        $linkedRows = JoinCplMk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('mk_id', $mks->pluck('id'))
            ->whereIn('join_cpl_bk_id', $cplBkColumns->pluck('join_cpl_bk_id'))
            ->get()
            ->keyBy(fn ($row) => $row->mk_id . '_' . $row->join_cpl_bk_id);

        $sheet->setCellValue('A1', 'MATA KULIAH');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(36);

        $columnIndex = 2;
        foreach ($cplHeaderGroups as $group) {
            $startColumn = Coordinate::stringFromColumnIndex($columnIndex);
            $endColumn = Coordinate::stringFromColumnIndex($columnIndex + $group['colspan'] - 1);

            $sheet->mergeCells($startColumn . '1:' . $endColumn . '1');
            $sheet->setCellValue($startColumn . '1', trim((string) ($group['cpl_kode'] . "\n" . $group['cpl_nama'])));
            $sheet->getStyle($startColumn . '1')->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($startColumn . '1')->getFont()->setBold(true);

            $columnIndex += $group['colspan'];
        }

        $columnIndex = 2;
        foreach ($cplBkColumns as $column) {
            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($columnLetter . '2', $column['bk_kode']);
            $sheet->getStyle($columnLetter . '2')->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($columnLetter . '2')->getFont()->setBold(true);
            $sheet->getColumnDimension($columnLetter)->setWidth(14);
            $columnIndex++;
        }

        $rowIndex = 3;
        foreach ($mks as $mk) {
            $sheet->setCellValue('A' . $rowIndex, trim((string) ($mk->kode . "\n" . $mk->nama)));
            $sheet->getStyle('A' . $rowIndex)->getAlignment()->setWrapText(true);

            $columnIndex = 2;
            foreach ($cplBkColumns as $column) {
                $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                $pairKey = $mk->id . '_' . $column['join_cpl_bk_id'];
                $bobot = $linkedRows->has($pairKey) ? $linkedRows->get($pairKey)->bobot : null;
                $sheet->setCellValue($columnLetter . $rowIndex, $bobot !== null ? (float) $bobot : '');
                $columnIndex++;
            }

            $rowIndex++;
        }

        if ($withValidation && $mks->count() > 0 && $cplBkColumns->count() > 0) {
            $this->applyNumericRangeValidation($sheet, 2, 1 + $cplBkColumns->count(), 3, 2 + $mks->count());
        }
    }

    private function applyNumericRangeValidation($sheet, int $startColumn, int $endColumn, int $startRow, int $endRow): void
    {
        if ($endColumn < $startColumn || $endRow < $startRow) {
            return;
        }

        $startColumnLetter = Coordinate::stringFromColumnIndex($startColumn);
        $endColumnLetter = Coordinate::stringFromColumnIndex($endColumn);

        $sheet->getStyle($startColumnLetter . $startRow . ':' . $endColumnLetter . $endRow)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFFFFF00');

        $sheet->getStyle($startColumnLetter . $startRow . ':' . $endColumnLetter . $endRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('FF000000'));

        $sheet->getStyle($startColumnLetter . $startRow . ':' . $endColumnLetter . $endRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $validationTemplate = new DataValidation();
        $validationTemplate->setType(DataValidation::TYPE_DECIMAL);
        $validationTemplate->setErrorStyle(DataValidation::STYLE_STOP);
        $validationTemplate->setOperator(DataValidation::OPERATOR_BETWEEN);
        $validationTemplate->setFormula1('0');
        $validationTemplate->setFormula2('100');
        $validationTemplate->setAllowBlank(true);
        $validationTemplate->setShowInputMessage(true);
        $validationTemplate->setShowErrorMessage(true);
        $validationTemplate->setErrorTitle('Input tidak valid');
        $validationTemplate->setError('Isi dengan angka pada rentang 0 sampai 100.');
        $validationTemplate->setPromptTitle('Input bobot interaksi CPL >< MK');
        $validationTemplate->setPrompt('Isi angka 0 sampai 100, atau kosongkan sel untuk menghapus interaksi.');

        for ($row = $startRow; $row <= $endRow; $row++) {
            for ($column = $startColumn; $column <= $endColumn; $column++) {
                $columnLetter = Coordinate::stringFromColumnIndex($column);
                $sheet->getCell($columnLetter . $row)->setDataValidation(clone $validationTemplate);
            }
        }
    }

    private function applyInteractionValidation($sheet, int $startColumn, int $endColumn, int $startRow, int $endRow): void
    {
        if ($endColumn < $startColumn || $endRow < $startRow) {
            return;
        }

        $startColumnLetter = Coordinate::stringFromColumnIndex($startColumn);
        $endColumnLetter = Coordinate::stringFromColumnIndex($endColumn);

        $sheet->getStyle($startColumnLetter . $startRow . ':' . $endColumnLetter . $endRow)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFFFFF00');

        $sheet->getStyle($startColumnLetter . $startRow . ':' . $endColumnLetter . $endRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('FF000000'));

        $sheet->getStyle($startColumnLetter . $startRow . ':' . $endColumnLetter . $endRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $validationTemplate = new DataValidation();
        $validationTemplate->setType(DataValidation::TYPE_LIST);
        $validationTemplate->setErrorStyle(DataValidation::STYLE_STOP);
        $validationTemplate->setAllowBlank(true);
        $validationTemplate->setShowDropDown(true);
        $validationTemplate->setShowInputMessage(true);
        $validationTemplate->setShowErrorMessage(true);
        $validationTemplate->setErrorTitle('Input tidak valid');
        $validationTemplate->setError('Pilih hanya "V" atau kosongkan sel.');
        $validationTemplate->setPromptTitle('Pilih interaksi');
        $validationTemplate->setPrompt('Pilih "V" untuk interaksi aktif.');
        $validationTemplate->setFormula1('"V"');

        for ($row = $startRow; $row <= $endRow; $row++) {
            for ($column = $startColumn; $column <= $endColumn; $column++) {
                $columnLetter = Coordinate::stringFromColumnIndex($column);
                $sheet->getCell($columnLetter . $row)->setDataValidation(clone $validationTemplate);
            }
        }
    }

    private function commitKurikulumBundle(Spreadsheet $spreadsheet, Kurikulum $kurikulum): array
    {
        $summary = [
            'profils' => 0,
            'cpls' => 0,
            'bks' => 0,
            'mks' => 0,
            'mks_skipped_duplicate' => 0,
        ];

        $this->importBundleSheet($spreadsheet, 'Profil', ['kode', 'nama', 'deskripsi'], ['nama'], function (array $row) use ($kurikulum, &$summary) {
            $nama = $this->required($row['nama'] ?? null, 'nama');
            $kode = trim((string) ($row['kode'] ?? ''));

            if ($kode !== '') {
                Profil::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'kode' => $kode],
                    ['nama' => $nama, 'deskripsi' => $row['deskripsi'] ?? null]
                );
            } else {
                Profil::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'nama' => $nama],
                    ['kode' => null, 'deskripsi' => $row['deskripsi'] ?? null]
                );
            }

            $summary['profils']++;
        });

        $this->importBundleSheet($spreadsheet, 'CPL', ['kode', 'nama', 'cakupan'], ['kode', 'nama', 'cakupan'], function (array $row) use ($kurikulum, &$summary) {
            Cpl::updateOrCreate(
                [
                    'kurikulum_id' => $kurikulum->id,
                    'kode' => $this->required($row['kode'] ?? null, 'kode'),
                ],
                [
                    'nama' => $this->required($row['nama'] ?? null, 'nama'),
                    'cakupan' => $this->required($row['cakupan'] ?? null, 'cakupan'),
                ]
            );

            $summary['cpls']++;
        });

        $this->importBundleSheet($spreadsheet, 'BK', ['kode', 'nama', 'deskripsi'], ['kode', 'nama'], function (array $row) use ($kurikulum, &$summary) {
            Bk::updateOrCreate(
                [
                    'kurikulum_id' => $kurikulum->id,
                    'kode' => $this->required($row['kode'] ?? null, 'kode'),
                ],
                [
                    'nama' => $this->required($row['nama'] ?? null, 'nama'),
                    'deskripsi' => $row['deskripsi'] ?? null,
                ]
            );

            $summary['bks']++;
        });

        $this->importBundleSheet(
            $spreadsheet,
            'MK',
            ['kode', 'nama', 'semester', 'sks_teori', 'sks_praktik', 'sks_lapangan', 'deskripsi'],
            ['kode', 'nama', 'semester', 'sks_teori', 'sks_praktik', 'sks_lapangan'],
            function (array $row) use ($kurikulum, &$summary) {
                $kode = $this->required($row['kode'] ?? null, 'kode');
                $hasDuplicateKode = Mk::query()
                    ->whereRaw('LOWER(TRIM(kode)) = ?', [Str::lower($kode)])
                    ->exists();

                if ($hasDuplicateKode) {
                    $summary['mks_skipped_duplicate']++;
                    return;
                }

                $sksTeori = (int) $this->required($row['sks_teori'] ?? null, 'sks_teori');
                $sksPraktik = (int) $this->required($row['sks_praktik'] ?? null, 'sks_praktik');
                $sksLapangan = (int) $this->required($row['sks_lapangan'] ?? null, 'sks_lapangan');

                Mk::create(
                    [
                        'kurikulum_id' => $kurikulum->id,
                        'kode' => $kode,
                        'nama' => $this->required($row['nama'] ?? null, 'nama'),
                        'semester' => (int) $this->required($row['semester'] ?? null, 'semester'),
                        'sks_teori' => $sksTeori,
                        'sks_praktik' => $sksPraktik,
                        'sks_lapangan' => $sksLapangan,
                        'sks' => $sksTeori + $sksPraktik + $sksLapangan,
                        'deskripsi' => $row['deskripsi'] ?? null,
                    ]
                );

                $summary['mks']++;
            }
        );

        return $summary;
    }

    private function importBundleSheet(Spreadsheet $spreadsheet, string $sheetName, array $columns, array $requiredColumns, callable $handler): void
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
}
