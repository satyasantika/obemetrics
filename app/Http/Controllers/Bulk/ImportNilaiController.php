<?php

namespace App\Http\Controllers\Bulk;

use App\Models\Mk;
use App\Models\Nilai;
use App\Models\KontrakMk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        $penugasans = $mk->penugasans()->orderBy('kode')->get();
        $preview = session($this->previewSessionKey($mk), []);

        return view('setting.bulk-import.nilai', compact('mk', 'penugasans', 'preview'));
    }

    public function importNilai(Mk $mk, Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,csv,ods',
            ]);

            $penugasans = $mk->penugasans()->orderBy('kode')->get();
            if ($penugasans->isEmpty()) {
                return to_route('setting.import.nilais', $mk->id)
                    ->with('error', 'Belum ada penugasan pada mata kuliah ini.');
            }

            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $headerRow = $rows[1] ?? [];
            $normalizedHeader = array_map(fn ($value) => Str::lower(trim((string) $value)), $headerRow);

            $nimCol = array_search('nim', $normalizedHeader, true);
            $namaCol = array_search('nama mahasiswa', $normalizedHeader, true);

            if ($nimCol === false) {
                return to_route('setting.import.nilais', $mk->id)
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
                return to_route('setting.import.nilais', $mk->id)
                    ->with('error', 'Header penugasan belum lengkap. Kolom yang belum ada: ' . implode(', ', $missingHeaders));
            }

            $kontrakByNim = $mk->kontrakMks()
                ->with(['mahasiswa', 'semester'])
                ->whereNotNull('mahasiswa_id')
                ->whereNotNull('semester_id')
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
                    $errors[] = 'Mahasiswa tidak terdaftar pada kontrak MK ini';
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
                return to_route('setting.import.nilais', $mk->id)
                    ->with('error', 'Tidak ada data valid di file yang diunggah.');
            }

            session([
                $this->previewSessionKey($mk) => [
                    'rows' => $previewRows,
                    'filename' => $request->file('file')->getClientOriginalName(),
                ],
            ]);

            return to_route('setting.import.nilais', $mk->id)
                ->with('success', 'Data nilai berhasil dibaca. Pilih baris yang ingin disimpan.');
        } catch (\Throwable $exception) {
            return to_route('setting.import.nilais', $mk->id)
                ->with('error', 'Terjadi kesalahan saat membaca file: ' . $exception->getMessage());
        }
    }

    public function commitNilai(Mk $mk, Request $request)
    {
        $request->validate([
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $preview = session($this->previewSessionKey($mk), []);
        $rows = $preview['rows'] ?? [];

        if (empty($rows)) {
            return to_route('setting.import.nilais', $mk->id)
                ->with('error', 'Tidak ada preview import untuk diproses.');
        }

        $selectedIndexes = $request->input('selected', []);
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

            $savedRows++;
            foreach (($row['scores'] ?? []) as $penugasanId => $score) {
                if ($score === null || $score === '') {
                    continue;
                }

                Nilai::updateOrCreate(
                    [
                        'mk_id' => $mk->id,
                        'penugasan_id' => $penugasanId,
                        'mahasiswa_id' => $row['mahasiswa_id'],
                        'semester_id' => $row['semester_id'],
                    ],
                    [
                        'nilai' => $score,
                        'komentar' => null,
                    ]
                );

                $savedScores++;
            }

            $this->syncKontrakMkScore($mk, $row['mahasiswa_id'], $row['semester_id']);
        }

        session()->forget($this->previewSessionKey($mk));

        return to_route('setting.import.nilais', $mk->id)
            ->with('success', "{$savedRows} baris diproses, {$savedScores} nilai berhasil disimpan.");
    }

    public function downloadTemplate(Mk $mk)
    {
        $penugasans = $mk->penugasans()->orderBy('kode')->get();
        if ($penugasans->isEmpty()) {
            return to_route('setting.import.nilais', $mk->id)
                ->with('error', 'Tidak bisa membuat template karena belum ada penugasan.');
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

        $sampleKontrakMks = $mk->kontrakMks()
            ->with('mahasiswa')
            ->whereNotNull('mahasiswa_id')
            ->get()
            ->sortBy('mahasiswa.nama');

        $rowNum = 2;
        foreach ($sampleKontrakMks as $kontrakMk) {
            $sheet->setCellValue('A' . $rowNum, $kontrakMk->mahasiswa?->nim ?? '');
            $sheet->setCellValue('B' . $rowNum, $kontrakMk->mahasiswa?->nama ?? '');
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $safeKode = Str::slug((string) ($mk->kode ?? 'mk'), '-');
        $fileName = 'template-import-nilai-' . $safeKode . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clearPreview(Mk $mk)
    {
        session()->forget($this->previewSessionKey($mk));

        return to_route('setting.import.nilais', $mk->id)
            ->with('success', 'Preview import nilai berhasil dikosongkan.');
    }

    private function previewSessionKey(Mk $mk): string
    {
        return 'import_nilai_preview_' . $mk->id;
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

        $penugasans = $mk->penugasans()->select('id', 'bobot')->get();
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
