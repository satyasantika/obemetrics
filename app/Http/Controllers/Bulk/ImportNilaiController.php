<?php

namespace App\Http\Controllers\Bulk;

use App\Models\Mk;
use App\Models\Nilai;
use App\Models\KontrakMk;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Actions\ResolveMkSemester;
use App\Actions\SyncMkState;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportNilaiController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read nilais', ['only' => ['importNilaiForm', 'downloadTemplate']]);
        $this->middleware('permission:create nilais', ['only' => ['importNilai', 'commitNilai']]);
        $this->middleware('permission:delete nilais', ['only' => ['clearPreview']]);
    }

    public function importNilaiForm(Mk $mk)
    {
        $request = request();
        $kelasFilter = $this->resolveKelasScope($request);
        [$semesterOptions, $selectedSemesterId] = $this->resolveImportSemester($mk, $request);
        $penugasans = $selectedSemesterId
            ? $this->penugasanQueryForImportSemester($mk, $selectedSemesterId)->get()
            : $mk->penugasans()->orderBy('kode')->get();
        $preview = session($this->previewSessionKey($mk, $kelasFilter, $selectedSemesterId), []);

        $kelasLabel = $kelasFilter === null ? 'Semua Kelas' : 'Kelas ' . $kelasFilter;
        $selectedSemester = $selectedSemesterId
            ? $semesterOptions->firstWhere('id', $selectedSemesterId)
            : null;
        $semesterLabel = $selectedSemester
            ? ($selectedSemester->kode . ' — ' . $selectedSemester->nama)
            : '—';

        $returnUrl = $this->resolveReturnUrl($request, $preview);

        return view('setting.bulk-import.nilai', compact(
            'mk',
            'penugasans',
            'preview',
            'kelasFilter',
            'kelasLabel',
            'returnUrl',
            'semesterOptions',
            'selectedSemesterId',
            'semesterLabel'
        ));
    }

    public function importNilai(Mk $mk, Request $request)
    {
        try {
            $kelasFilter = $this->resolveKelasScope($request);
            [$semesterOptions, $selectedSemesterId] = $this->resolveImportSemester($mk, $request);

            if ($semesterOptions->isEmpty() || !$selectedSemesterId) {
                return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                    ->with('error', 'Belum ada kontrak MK dengan semester, atau semester tidak dapat ditentukan.');
            }

            $request->validate([
                'file' => 'required|mimes:xlsx,csv,ods',
            ]);

            $penugasans = $this->penugasanQueryForImportSemester($mk, $selectedSemesterId)->get();
            if ($penugasans->isEmpty()) {
                return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                    ->with('error', 'Belum ada penugasan untuk semester ini (atau penugasan global) pada mata kuliah ini.');
            }

            $allowedPenugasanIds = $penugasans->pluck('id')->flip();

            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $headerRow = $rows[1] ?? [];
            $normalizedHeader = array_map(fn ($value) => Str::lower(trim((string) $value)), $headerRow);

            $nimCol = array_search('nim', $normalizedHeader, true);
            $namaCol = array_search('nama mahasiswa', $normalizedHeader, true);

            if ($nimCol === false) {
                return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                    ->with('error', 'Header wajib memiliki kolom "nim".');
            }

            $penugasanColumnMap = [];
            $missingHeaders = [];
            foreach ($penugasans as $penugasan) {
                $col = array_search(Str::lower(trim((string) $penugasan->kode)), $normalizedHeader, true);
                if ($col === false) {
                    $missingHeaders[] = $penugasan->kode;
                    continue;
                }
                $penugasanColumnMap[$penugasan->id] = $col;
            }

            if (!empty($missingHeaders)) {
                return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                    ->with('error', 'Header penugasan belum lengkap. Kolom yang belum ada: ' . implode(', ', $missingHeaders));
            }

            $kontrakQuery = $mk->kontrakMks()
                ->with(['mahasiswa', 'semester'])
                ->whereNotNull('mahasiswa_id')
                ->whereNotNull('semester_id')
                ->where('semester_id', $selectedSemesterId);

            if ($kelasFilter !== null) {
                $kontrakQuery->whereRaw("COALESCE(NULLIF(TRIM(kelas), ''), 'Tanpa Kelas') = ?", [$kelasFilter]);
            }

            $kontrakByNim = $kontrakQuery
                ->get()
                ->filter(fn ($kontrak) => $kontrak->mahasiswa !== null)
                ->keyBy(fn ($kontrak) => Str::lower(trim((string) $kontrak->mahasiswa->nim)));

            $previewRows = [];
            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex === 1) {
                    continue;
                }

                $nim = trim((string) ($row[$nimCol] ?? ''));
                if ($nim === '') {
                    continue;
                }

                $namaMahasiswa = $namaCol !== false ? trim((string) ($row[$namaCol] ?? '')) : '';
                $kontrakMk = $kontrakByNim->get(Str::lower($nim));

                $scores = [];
                $errors = [];
                $hasAnyScore = false;

                foreach ($penugasans as $penugasan) {
                    $columnKey = $penugasanColumnMap[$penugasan->id] ?? null;
                    $rawValue = $columnKey ? ($row[$columnKey] ?? null) : null;

                    if ($rawValue === null || trim((string) $rawValue) === '') {
                        $scores[$penugasan->id] = null;
                        continue;
                    }

                    if (!is_numeric($rawValue)) {
                        $errors[] = "{$penugasan->kode}: bukan angka";
                        $scores[$penugasan->id] = null;
                        continue;
                    }

                    $score = (float) $rawValue;
                    if ($score < 0 || $score > 100) {
                        $errors[] = "{$penugasan->kode}: di luar rentang 0-100";
                    }

                    $scores[$penugasan->id] = $score;
                    $hasAnyScore = true;
                }

                if (!$kontrakMk) {
                    $errors[] = 'Mahasiswa tidak terdaftar pada kontrak MK untuk semester ini';
                }

                if (!$hasAnyScore) {
                    $errors[] = 'Tidak ada nilai yang terisi';
                }

                $previewRows[] = [
                    'nim' => $nim,
                    'nama_mahasiswa' => $namaMahasiswa ?: ($kontrakMk?->mahasiswa?->nama ?? ''),
                    'mahasiswa_id' => $kontrakMk?->mahasiswa_id,
                    'semester_id' => $kontrakMk?->semester_id,
                    'semester_kode' => $kontrakMk?->semester?->kode,
                    'scores' => $scores,
                    'errors' => $errors,
                    'can_save' => empty($errors),
                ];
            }

            if (empty($previewRows)) {
                return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                    ->with('error', 'Tidak ada data valid di file yang diunggah.');
            }

            $savedRows = 0;
            $savedScores = 0;
            $skippedRows = 0;

            foreach ($previewRows as $row) {
                if (!($row['can_save'] ?? false)) {
                    $skippedRows++;
                    continue;
                }

                if ((string) ($row['semester_id'] ?? '') !== (string) $selectedSemesterId) {
                    $skippedRows++;
                    continue;
                }

                $mahasiswaId = (string) ($row['mahasiswa_id'] ?? '');
                if ($mahasiswaId === '' || !$this->kontrakExistsForImport($mk, $mahasiswaId, $selectedSemesterId, $kelasFilter)) {
                    $skippedRows++;
                    continue;
                }

                $savedRows++;
                foreach (($row['scores'] ?? []) as $penugasanId => $score) {
                    if ($score === null || $score === '') {
                        continue;
                    }

                    if (!$allowedPenugasanIds->has($penugasanId)) {
                        continue;
                    }

                    Nilai::updateOrCreate(
                        [
                            'mk_id' => $mk->id,
                            'penugasan_id' => $penugasanId,
                            'mahasiswa_id' => $mahasiswaId,
                            'semester_id' => $selectedSemesterId,
                        ],
                        [
                            'nilai' => $score,
                            'komentar' => null,
                        ]
                    );

                    $savedScores++;
                }

                $this->syncKontrakMkScore($mk, $mahasiswaId, $selectedSemesterId);
            }

            session()->forget($this->previewSessionKey($mk, $kelasFilter, $selectedSemesterId));

            if ($savedRows === 0) {
                return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                    ->with('error', 'Tidak ada nilai yang disimpan. Pastikan baris memiliki nilai valid dan mahasiswa berkontrak MK pada semester terpilih.');
            }

            $message = "{$savedRows} baris disimpan, {$savedScores} nilai berhasil ditulis.";
            if ($skippedRows > 0) {
                $message .= " {$skippedRows} baris dilewati (tidak valid atau bukan kontrak semester ini).";
            }

            SyncMkState::sync($mk->fresh());

            return redirect()->route('mks.nilais.index', [
                'mk' => $mk->id,
                'semester_id' => $selectedSemesterId,
            ])->with('success', $message);
        } catch (\Throwable $exception) {
            return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                ->with('error', 'Terjadi kesalahan saat membaca file: ' . $exception->getMessage());
        }
    }

    public function commitNilai(Mk $mk, Request $request)
    {
        $kelasFilter = $this->resolveKelasScope($request);
        [$semesterOptions, $selectedSemesterId] = $this->resolveImportSemester($mk, $request);

        if ($semesterOptions->isEmpty() || !$selectedSemesterId) {
            return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                ->with('error', 'Semester kontrak tidak valid; tidak dapat menyimpan nilai.');
        }

        $allowedPenugasanIds = $this->penugasanQueryForImportSemester($mk, $selectedSemesterId)
            ->pluck('id')
            ->flip();

        $request->validate([
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $preview = session($this->previewSessionKey($mk, $kelasFilter, $selectedSemesterId), []);
        $rows = $preview['rows'] ?? [];

        if (empty($rows)) {
            return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                ->with('error', 'Tidak ada preview import untuk diproses.');
        }

        $selectedIndexes = $request->input('selected', []);
        if (!is_array($selectedIndexes) || count($selectedIndexes) === 0) {
            return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                ->with('error', 'Pilih minimal satu baris yang siap disimpan sebelum commit.');
        }

        $savedRows = 0;
        $savedScores = 0;

        foreach ($selectedIndexes as $index) {
            if (!isset($rows[$index])) {
                continue;
            }

            $row = $rows[$index];
            if (!($row['can_save'] ?? false)) {
                continue;
            }

            if ((string) ($row['semester_id'] ?? '') !== (string) $selectedSemesterId) {
                continue;
            }

            $mahasiswaId = (string) ($row['mahasiswa_id'] ?? '');
            if ($mahasiswaId === '' || !$this->kontrakExistsForImport($mk, $mahasiswaId, $selectedSemesterId, $kelasFilter)) {
                continue;
            }

            $savedRows++;
            foreach (($row['scores'] ?? []) as $penugasanId => $score) {
                if ($score === null || $score === '') {
                    continue;
                }

                if (!$allowedPenugasanIds->has($penugasanId)) {
                    continue;
                }

                Nilai::updateOrCreate(
                    [
                        'mk_id' => $mk->id,
                        'penugasan_id' => $penugasanId,
                        'mahasiswa_id' => $mahasiswaId,
                        'semester_id' => $selectedSemesterId,
                    ],
                    [
                        'nilai' => $score,
                        'komentar' => null,
                    ]
                );

                $savedScores++;
            }

            $this->syncKontrakMkScore($mk, $mahasiswaId, $selectedSemesterId);
        }

        if ($savedRows === 0) {
            return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                ->with('error', 'Tidak ada nilai yang disimpan. Pastikan memilih baris dengan status siap disimpan (mahasiswa berkontrak MK pada semester terpilih).');
        }

        session()->forget($this->previewSessionKey($mk, $kelasFilter, $selectedSemesterId));

        SyncMkState::sync($mk->fresh());

        return redirect()->route('mks.nilais.index', [
            'mk' => $mk->id,
            'semester_id' => $selectedSemesterId,
        ])->with('success', "{$savedRows} baris diproses, {$savedScores} nilai berhasil disimpan.");
    }

    public function downloadTemplate(Mk $mk, Request $request)
    {
        $kelasFilter = $this->resolveKelasScope($request);
        [$semesterOptions, $selectedSemesterId] = $this->resolveImportSemester($mk, $request);

        if ($semesterOptions->isEmpty() || !$selectedSemesterId) {
            return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                ->with('error', 'Tidak bisa membuat template: semester kontrak tidak tersedia.');
        }

        $penugasans = $this->penugasanQueryForImportSemester($mk, $selectedSemesterId)->get();
        if ($penugasans->isEmpty()) {
            return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
                ->with('error', 'Tidak bisa membuat template: belum ada penugasan untuk semester ini.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['nim', 'nama mahasiswa'];
        foreach ($penugasans as $penugasan) {
            $headers[] = $penugasan->kode;
        }

        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        $kontrakQuery = $mk->kontrakMks()
            ->with('mahasiswa')
            ->whereNotNull('mahasiswa_id')
            ->whereNotNull('semester_id')
            ->where('semester_id', $selectedSemesterId);

        if ($kelasFilter !== null) {
            $kontrakQuery->whereRaw("COALESCE(NULLIF(TRIM(kelas), ''), 'Tanpa Kelas') = ?", [$kelasFilter]);
        }

        $sampleKontrakMks = $kontrakQuery
            ->get()
            ->sortBy('mahasiswa.nama');

        $mahasiswaIds = $sampleKontrakMks->pluck('mahasiswa_id')->filter()->unique()->values();
        $semesterIds = $sampleKontrakMks->pluck('semester_id')->filter()->unique()->values();
        $penugasanIds = $penugasans->pluck('id')->values();

        $nilaisByKey = $mk->nilais()
            ->whereIn('mahasiswa_id', $mahasiswaIds)
            ->whereIn('semester_id', $semesterIds)
            ->whereIn('penugasan_id', $penugasanIds)
            ->get(['mahasiswa_id', 'semester_id', 'penugasan_id', 'nilai'])
            ->keyBy(fn ($nilai) => $nilai->mahasiswa_id . '_' . $nilai->semester_id . '_' . $nilai->penugasan_id)
            ->all();

        $rowNum = 2;
        foreach ($sampleKontrakMks as $kontrakMk) {
            $sheet->setCellValue('A' . $rowNum, $kontrakMk->mahasiswa?->nim ?? '');
            $sheet->setCellValue('B' . $rowNum, $kontrakMk->mahasiswa?->nama ?? '');

            $columnIndex = 3;
            foreach ($penugasans as $penugasan) {
                $nilaiKey = $kontrakMk->mahasiswa_id . '_' . $kontrakMk->semester_id . '_' . $penugasan->id;
                $nilaiObj = $nilaisByKey[$nilaiKey] ?? null;
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($columnIndex) . $rowNum,
                    $nilaiObj?->nilai ?? null
                );
                $columnIndex++;
            }

            $rowNum++;
        }

        if ($rowNum > 2 && count($headers) > 2) {
            $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle('C2:' . $lastColumn . ($rowNum - 1))
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFFFF59D');
        }

        $writer = new Xlsx($spreadsheet);
        $safeKode = Str::slug((string) ($mk->kode ?? 'mk'), '-');
        $safeKelas = $kelasFilter !== null ? Str::slug($kelasFilter, '-') : 'semua-kelas';
        $safeSemester = Str::slug((string) ($semesterOptions->firstWhere('id', $selectedSemesterId)?->kode ?? 'semester'), '-');
        $fileName = 'template-import-nilai-' . $safeKode . '-' . ($safeKelas !== '' ? $safeKelas : 'tanpa-kelas') . '-' . ($safeSemester !== '' ? $safeSemester : 'semester') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clearPreview(Mk $mk)
    {
        $request = request();
        $kelasFilter = $this->resolveKelasScope($request);
        [, $selectedSemesterId] = $this->resolveImportSemester($mk, $request);
        session()->forget($this->previewSessionKey($mk, $kelasFilter, $selectedSemesterId));

        return to_route('settings.import.nilais', $this->importRedirectQuery($mk, $request))
            ->with('success', 'Preview import nilai berhasil dikosongkan.');
    }

    /**
     * Penugasan yang dipakai untuk nilai pada semester ini — selaras dengan NilaiController (semester atau global).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Eloquent\Builder
     */
    private function penugasanQueryForImportSemester(Mk $mk, string $semesterId)
    {
        return $mk->penugasans()
            ->where(function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->orWhereNull('semester_id');
            })
            ->orderBy('kode');
    }

    /**
     * Pastikan mahasiswa berkontrak MK pada semester terpilih (dan kelas bila difilter).
     */
    private function kontrakExistsForImport(Mk $mk, string $mahasiswaId, string $semesterId, ?string $kelasFilter): bool
    {
        $query = $mk->kontrakMks()
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('semester_id', $semesterId)
            ->whereNotNull('mahasiswa_id');

        if ($kelasFilter !== null) {
            $query->whereRaw("COALESCE(NULLIF(TRIM(kelas), ''), 'Tanpa Kelas') = ?", [$kelasFilter]);
        }

        return $query->exists();
    }

    /**
     * @return array{0: Collection<int, \App\Models\Semester>, 1: string|null}
     */
    private function resolveImportSemester(Mk $mk, Request $request): array
    {
        $options = $this->semesterOptionsForMk($mk);
        $requestedRaw = $request->input('semester_id');
        $requestedId = ($requestedRaw !== null && $requestedRaw !== '') ? (string) $requestedRaw : null;
        [, $id] = ResolveMkSemester::resolve($mk, $requestedId, $options);

        return [$options, $id];
    }

    /**
     * @return Collection<int, \App\Models\Semester>
     */
    private function semesterOptionsForMk(Mk $mk): Collection
    {
        return $mk->kontrakMks()
            ->with('semester')
            ->whereNotNull('mahasiswa_id')
            ->whereNotNull('semester_id')
            ->get()
            ->pluck('semester')
            ->filter()
            ->unique('id')
            ->sortByDesc('status_aktif')
            ->sortByDesc('kode')
            ->values();
    }

    /** @return array<string, mixed> */
    private function importRedirectQuery(Mk $mk, Request $request): array
    {
        $query = ['mk' => $mk->id];
        $kelas = $request->query('kelas', $request->input('kelas'));
        if ($kelas !== null && $kelas !== '' && $kelas !== '__SEMUA_KELAS__') {
            $query['kelas'] = $kelas;
        }
        $semesterId = $request->input('semester_id');
        if ($semesterId !== null && $semesterId !== '') {
            $query['semester_id'] = $semesterId;
        }

        return $query;
    }

    private function previewSessionKey(Mk $mk, ?string $kelasFilter, ?string $semesterId): string
    {
        $kelasKey = $kelasFilter === null ? 'all' : Str::slug($kelasFilter, '-');
        $semesterKey = $semesterId !== null && $semesterId !== '' ? Str::slug((string) $semesterId, '-') : 'no-semester';

        return 'import_nilai_preview_' . $mk->id . '_' . ($kelasKey !== '' ? $kelasKey : 'tanpa-kelas') . '_' . ($semesterKey !== '' ? $semesterKey : 'semester');
    }

    private function resolveKelasScope(Request $request): ?string
    {
        $kelas = trim((string) $request->query('kelas', $request->input('kelas', '')));
        if ($kelas === '' || $kelas === '__SEMUA_KELAS__') {
            return null;
        }

        return $kelas;
    }

    private function resolveReturnUrl(Request $request, array $preview = []): string
    {
        $candidate = (string) $request->query('return_url', '');
        if ($candidate === '') {
            $candidate = (string) $request->input('return_url', '');
        }
        if ($candidate === '' && isset($preview['return_url'])) {
            $candidate = (string) $preview['return_url'];
        }
        if ($candidate === '') {
            $candidate = (string) url()->previous();
        }

        return $candidate !== '' ? $candidate : route('mks.nilais.index', [$request->route('mk')]);
    }

    private function syncKontrakMkScore(Mk $mk, string $mahasiswaId, string $semesterId): void
    {
        $kontrakMk = KontrakMk::query()
            ->where('mk_id', $mk->id)
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('semester_id', $semesterId)
            ->first();

        if (!$kontrakMk) {
            return;
        }

        $penugasans = $this->penugasanQueryForImportSemester($mk, $semesterId)->get(['id', 'bobot']);
        if ($penugasans->isEmpty()) {
            $kontrakMk->update([
                'nilai_angka' => null,
                'nilai_huruf' => null,
            ]);
            return;
        }

        $bobotByPenugasan = $penugasans->mapWithKeys(function ($item) {
            return [$item->id => (float) ($item->bobot ?? 0)];
        });

        $nilais = $mk->nilais()
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('semester_id', $semesterId)
            ->whereIn('penugasan_id', $bobotByPenugasan->keys())
            ->get(['penugasan_id', 'nilai']);

        $weightedSum = 0.0;
        $totalBobot = 0.0;
        foreach ($nilais as $item) {
            $bobot = (float) ($bobotByPenugasan[$item->penugasan_id] ?? 0);
            $score = (float) ($item->nilai ?? 0);
            $weightedSum += $score * $bobot;
            $totalBobot += $bobot;
        }

        if ($totalBobot <= 0) {
            $kontrakMk->update([
                'nilai_angka' => null,
                'nilai_huruf' => null,
            ]);
            return;
        }

        $nilaiAngka = $weightedSum / 100;
        $nilaiHuruf = $this->toNilaiHuruf($nilaiAngka);

        $kontrakMk->update([
            'nilai_angka' => round($nilaiAngka, 2),
            'nilai_huruf' => $nilaiHuruf,
        ]);
    }

    private function toNilaiHuruf(float $nilaiAngka): string
    {
        if ($nilaiAngka >= 85.0) {
            return 'A';
        }
        if ($nilaiAngka >= 77.0) {
            return 'A-';
        }
        if ($nilaiAngka >= 68.5) {
            return 'B+';
        }
        if ($nilaiAngka >= 61.0) {
            return 'B';
        }
        if ($nilaiAngka >= 53.0) {
            return 'B-';
        }
        if ($nilaiAngka >= 45.0) {
            return 'C+';
        }
        if ($nilaiAngka >= 37.0) {
            return 'C';
        }
        if ($nilaiAngka >= 29.0) {
            return 'C-';
        }
        if ($nilaiAngka >= 21.0) {
            return 'D';
        }

        return 'E';
    }
}
