<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\KontrakMk;
use App\Models\Mahasiswa;
use App\Models\Mk;
use App\Models\Nilai;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KontrakMKController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:access dosen dashboard');
    }

    public function index()
    {
        $mahasiswas = Mahasiswa::select('id', 'nim', 'nama')->orderBy('nama')->get();
        $mks = Mk::select('id', 'kode', 'nama')->orderBy('nama')->get();
        $semesters = Semester::select('id', 'kode', 'nama')->orderBy('kode')->get();

        return view('obe.kontrak-per-dosen', compact('mahasiswas', 'mks', 'semesters'));
    }

    public function data(): JsonResponse
    {
        $userId = (string) Auth::id();

        $rows = KontrakMk::query()
            ->leftJoin('mahasiswas', 'kontrak_mks.mahasiswa_id', '=', 'mahasiswas.id')
            ->leftJoin('prodis', 'mahasiswas.prodi_id', '=', 'prodis.id')
            ->leftJoin('mks', 'kontrak_mks.mk_id', '=', 'mks.id')
            ->leftJoin('semesters', 'kontrak_mks.semester_id', '=', 'semesters.id')
            ->where('kontrak_mks.user_id', $userId)
            ->select([
                'kontrak_mks.id',
                'kontrak_mks.mahasiswa_id',
                'kontrak_mks.mk_id',
                'kontrak_mks.semester_id',
                'mahasiswas.nim as mahasiswa_nim',
                'mahasiswas.nama as mahasiswa_nama',
                'prodis.jenjang as prodi_jenjang',
                'prodis.nama as prodi_nama',
                'mks.kode as mk_kode',
                'mks.nama as mk_nama',
                'semesters.kode as semester_kode',
                'semesters.nama as semester_nama',
                'kontrak_mks.kelas',
            ])
            ->orderBy('mahasiswas.nim')
            ->get()
            ->map(function ($row) {
                $used = $this->isKontrakUsedInPenilaianFromIds(
                    $row->mahasiswa_id,
                    $row->mk_id,
                    $row->semester_id
                );

                $row->can_delete = !$used;
                $row->lock_reason = $used
                    ? 'Kontrak tidak dapat dihapus karena sudah digunakan pada data penilaian.'
                    : null;

                return $row;
            });

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $userId = (string) Auth::id();

        $validated = $request->validate([
            'mahasiswa_id' => ['required', 'exists:mahasiswas,id'],
            'mk_id' => ['required', 'exists:mks,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'kelas' => ['nullable', 'string', 'max:10'],
        ]);

        $this->ensureUniqueCombination(
            $validated['mahasiswa_id'],
            $validated['mk_id'],
            $validated['semester_id'] ?? null
        );

        $validated['user_id'] = $userId;

        KontrakMk::create($validated);

        return redirect()
            ->route('dosen.kontrakmks.index')
            ->with('success', 'Kontrak MK berhasil ditambahkan');
    }

    public function update(Request $request, KontrakMk $kontrakMk)
    {
        $this->authorize('update', $kontrakMk);

        $validated = $request->validate([
            'mahasiswa_id' => ['required', 'exists:mahasiswas,id'],
            'mk_id' => ['required', 'exists:mks,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'kelas' => ['nullable', 'string', 'max:10'],
        ]);

        $this->ensureUniqueCombination(
            $validated['mahasiswa_id'],
            $validated['mk_id'],
            $validated['semester_id'] ?? null,
            $kontrakMk->id
        );

        $kontrakMk->update($validated);

        return redirect()
            ->route('dosen.kontrakmks.index')
            ->with('success', 'Kontrak MK berhasil diperbarui');
    }

    public function import()
    {
        $preview = session()->pull('dosen_kontrak_import_preview'); // Get and remove from session
        $semesters = Semester::select('id', 'kode', 'nama')->orderBy('kode')->get();

        return view('obe.kontrak-per-dosen-import', ['preview' => $preview, 'semesters' => $semesters]);
    }

    public function destroy(KontrakMk $kontrakMk)
    {
        $this->authorize('delete', $kontrakMk);

        if ($this->isKontrakUsedInPenilaian($kontrakMk)) {
            return redirect()
                ->route('dosen.kontrakmks.index')
                ->with('error', 'Kontrak tidak dapat dihapus karena sudah digunakan pada data penilaian.');
        }

        $kontrakMk->delete();

        return redirect()
            ->route('dosen.kontrakmks.index')
            ->with('success', 'Kontrak MK berhasil dihapus');
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,ods',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        try {
            $file = $request->file('file');
            $selectedSemester = Semester::select('id', 'kode', 'nama')->findOrFail($request->input('semester_id'));
            $dosenName = (string) (Auth::user()->name ?? '-');
            $spreadsheet = IOFactory::load($file->path());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $preview = [
                'rows' => [],
                'filename' => $file->getClientOriginalName(),
                'semester_id' => $selectedSemester->id,
                'semester_label' => trim(implode(' - ', array_filter([$selectedSemester->kode, $selectedSemester->nama]))),
            ];

            $seenCombinations = [];

            // Skip header row (row 1)
            for ($i = 2; $i <= count($rows); $i++) {
                $row = $rows[$i - 1];

                if (empty(array_filter($row))) {
                    continue;
                }

                $nim = $row[0] ?? '';
                $mahasiswaNama = $row[1] ?? '';
                $mkKode = $row[2] ?? '';
                $mkNama = $row[3] ?? '';
                $kelas = $row[5] ?? $row[4] ?? '';

                $previewRow = [
                    'nim' => $nim,
                    'mahasiswa_nama' => $mahasiswaNama,
                    'mk_kode' => $mkKode,
                    'mk_nama' => $mkNama,
                    'nama_dosen' => $dosenName,
                    'semester' => $preview['semester_label'],
                    'kelas' => $kelas,
                    'mahasiswa_id' => null,
                    'mk_id' => null,
                    'semester_id' => $selectedSemester->id,
                    'status' => 'success',
                    'error' => null,
                ];

                // Validate mahasiswa
                $mahasiswa = Mahasiswa::where('nim', $nim)->first();
                if (!$mahasiswa) {
                    $previewRow['status'] = 'error';
                    $previewRow['error'] = "Mahasiswa dengan NIM $nim tidak ditemukan";
                } else {
                    $previewRow['mahasiswa_id'] = $mahasiswa->id;
                }

                // Validate MK
                if ($previewRow['status'] === 'success') {
                    $mk = Mk::where('kode', $mkKode)->first();
                    if (!$mk) {
                        $previewRow['status'] = 'error';
                        $previewRow['error'] = "Mata Kuliah dengan kode $mkKode tidak ditemukan";
                    } else {
                        $previewRow['mk_id'] = $mk->id;
                    }
                }

                if ($previewRow['status'] === 'success') {
                    if ($this->hasDuplicateCombination($previewRow['mahasiswa_id'], $previewRow['mk_id'], $selectedSemester->id)) {
                        $previewRow['status'] = 'error';
                        $previewRow['error'] = 'Kombinasi NIM, kode MK, dan semester sudah terdaftar.';
                    }
                }

                if ($previewRow['status'] === 'success') {
                    $combinationKey = implode('|', [
                        $previewRow['mahasiswa_id'],
                        $previewRow['mk_id'],
                        $selectedSemester->id,
                    ]);

                    if (isset($seenCombinations[$combinationKey])) {
                        $previewRow['status'] = 'error';
                        $previewRow['error'] = 'Terdapat duplikasi kombinasi NIM, kode MK, dan semester di file import.';
                    } else {
                        $seenCombinations[$combinationKey] = true;
                    }
                }

                $preview['rows'][] = $previewRow;
            }

            session(['dosen_kontrak_import_preview' => $preview]);
            $semesters = Semester::select('id', 'kode', 'nama')->orderBy('kode')->get();

            return view('obe.kontrak-per-dosen-import', ['preview' => $preview, 'semesters' => $semesters]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function importCommit(Request $request)
    {
        $preview = session('dosen_kontrak_import_preview');
        if (!$preview) {
            return redirect()->route('dosen.kontrakmks.import')->with('error', 'Data preview tidak ditemukan');
        }

        $userId = (string) Auth::id();
        $successCount = 0;
        $errorCount = 0;

        foreach ($preview['rows'] as $row) {
            if ($row['status'] === 'success' && $row['mahasiswa_id'] && $row['mk_id']) {
                if ($this->hasDuplicateCombination($row['mahasiswa_id'], $row['mk_id'], $row['semester_id'] ?? null)) {
                    $errorCount++;
                    continue;
                }

                KontrakMk::create([
                    'mahasiswa_id' => $row['mahasiswa_id'],
                    'mk_id' => $row['mk_id'],
                    'semester_id' => $row['semester_id'],
                    'kelas' => $row['kelas'],
                    'user_id' => $userId,
                ]);
                $successCount++;
            }
        }

        session()->forget('dosen_kontrak_import_preview');

        $message = "Import berhasil! $successCount data kontrak telah ditambahkan";
        if (!empty($errorCount)) {
            $message .= ". $errorCount data dilewati karena kombinasi NIM, kode MK, dan semester sudah ada.";
        }

        return redirect()->route('dosen.kontrakmks.index')
            ->with('success', $message);
    }

    public function importClear()
    {
        session()->forget('dosen_kontrak_import_preview');
        return redirect()->route('dosen.kontrakmks.import');
    }

    public function importTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'NIM');
        $sheet->setCellValue('B1', 'Nama Mahasiswa');
        $sheet->setCellValue('C1', 'Kode MK');
        $sheet->setCellValue('D1', 'Nama MK');
        $sheet->setCellValue('E1', 'Kelas');

        // Example data
        $sheet->setCellValue('A2', '20210001');
        $sheet->setCellValue('B2', 'Rudi Hermawan');
        $sheet->setCellValue('C2', 'TI001');
        $sheet->setCellValue('D2', 'Algoritma Pemrograman');
        $sheet->setCellValue('E2', 'A');

        // Set column widths
        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set header style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        $semesterId = (string) $request->query('semester_id', data_get(session('dosen_kontrak_import_preview'), 'semester_id', ''));
        $semesterCode = (string) (Semester::query()->where('id', $semesterId)->value('kode') ?? 'SEMESTER');
        $semesterCode = trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $semesterCode), '-');
        if ($semesterCode === '') {
            $semesterCode = 'SEMESTER';
        }

        $filename = 'template-import-kontrak-mk-' . $semesterCode . '-' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\Writer\Xlsx::class;
        $writer = new $writer($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function ensureUniqueCombination(string $mahasiswaId, string $mkId, ?string $semesterId, ?string $ignoreId = null): void
    {
        if ($this->hasDuplicateCombination($mahasiswaId, $mkId, $semesterId, $ignoreId)) {
            throw ValidationException::withMessages([
                'mahasiswa_id' => 'Kombinasi mahasiswa, mata kuliah, dan semester sudah ada.',
            ]);
        }
    }

    private function hasDuplicateCombination(string $mahasiswaId, string $mkId, ?string $semesterId, ?string $ignoreId = null): bool
    {
        $query = KontrakMk::query()
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('mk_id', $mkId);

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        } else {
            $query->whereNull('semester_id');
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function isKontrakUsedInPenilaian(KontrakMk $kontrakMk): bool
    {
        return $this->isKontrakUsedInPenilaianFromIds(
            $kontrakMk->mahasiswa_id,
            $kontrakMk->mk_id,
            $kontrakMk->semester_id
        );
    }

    private function isKontrakUsedInPenilaianFromIds(?string $mahasiswaId, ?string $mkId, ?string $semesterId): bool
    {
        if (!$mahasiswaId || !$mkId) {
            return false;
        }

        $query = Nilai::query()
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('mk_id', $mkId);

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        } else {
            $query->whereNull('semester_id');
        }

        return $query->exists();
    }
}
