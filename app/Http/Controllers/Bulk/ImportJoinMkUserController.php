<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use App\Models\JoinMkUser;
use App\Models\Mk;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportJoinMkUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read bulk-import joinmkusers', ['only' => ['importJoinMkUserForm','downloadTemplate']]);
        $this->middleware('permission:create bulk-import joinmkusers', ['only' => ['importJoinMkUser', 'commitJoinMkUser']]);
        $this->middleware('permission:delete bulk-import joinmkusers', ['only' => ['clearPreview']]);
    }

    public function importJoinMkUserForm()
    {
        $preview = session('import_joinmkuser_preview', []);
        $returnUrl = $this->resolveReturnUrl(request(), $preview);
        return view('setting.bulk-import.joinmkuser', compact('preview', 'returnUrl'));
    }

    public function importJoinMkUser(Request $request)
    {
        try {
            // Validate and process the uploaded file
            $request->validate([
                'file' => 'required|mimes:xlsx,csv,ods',
            ]);

            $file = $request->file('file');

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $header = array_map(function ($value) {
                return Str::lower(trim((string) $value));
            }, $rows[1] ?? []);

            $headerMap = [
                'kode_mk' => array_search('kode mata kuliah', $header, true),
                'nama_mata_kuliah' => array_search('nama mata kuliah', $header, true),
                'nidn' => array_search('nidn dosen', $header, true),
                'nama_dosen' => array_search('nama dosen', $header, true),
                'kode_semester' => array_search('kode semester', $header, true),
                'koordinator' => array_search('koordinator', $header, true),
            ];

            if ($headerMap['kode_semester'] === false || $headerMap['kode_mk'] === false || $headerMap['nidn'] === false) {
                return back()->with('error', 'File harus memiliki minimal kolom "kode semester", "kode mata kuliah", dan "nidn dosen".');
            }

            $previewRows = [];
            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue;
                }

                $kodeSemesterCol = $headerMap['kode_semester'] ?? null;
                $kodeMkCol = $headerMap['kode_mk'] ?? null;
                $nidnCol = $headerMap['nidn'] ?? null;

                if (!$kodeSemesterCol || !$kodeMkCol || !$nidnCol) {
                    continue;
                }

                $kodeSemester = trim((string) ($row[$kodeSemesterCol] ?? ''));
                $kodeMk = trim((string) ($row[$kodeMkCol] ?? ''));
                $nidn = trim((string) ($row[$nidnCol] ?? ''));

                if ($kodeSemester === '' || $kodeMk === '' || $nidn === '') {
                    continue;
                }

                $namaMataKuliah = trim((string) ($row[$headerMap['nama_mata_kuliah']] ?? ''));
                $namaDosen = trim((string) ($row[$headerMap['nama_dosen']] ?? ''));
                $koordinator = trim((string) ($row[$headerMap['koordinator']] ?? ''));

                // Check semester
                $semester = Semester::where('kode', $kodeSemester)->first();
                // Check mk
                $mk = Mk::where('kode', $kodeMk)->first();
                // Check dosen (user dengan nidn)
                $dosen = User::where('nidn', $nidn)->first();

                // Check if kontrak already exists
                $existing = null;
                if ($semester && $mk && $dosen) {
                    $existing = JoinMkUser::where('semester_id', $semester->id)
                                        ->where('mk_id', $mk->id)
                                        ->where('user_id', $dosen->id)
                                        ->first();
                }

                $previewRows[] = [
                    'kode_semester' => $kodeSemester,
                    'kode_mk' => $kodeMk,
                    'nama_mata_kuliah' => $namaMataKuliah,
                    'nidn' => $nidn,
                    'nama_dosen' => $namaDosen,
                    'koordinator' => $koordinator,
                    'semester_exists' => (bool) $semester,
                    'semester_id' => $semester?->id,
                    'mk_exists' => (bool) $mk,
                    'mk_id' => $mk?->id,
                    'dosen_exists' => (bool) $dosen,
                    'dosen_id' => $dosen?->id,
                    'exists' => (bool) $existing,
                    'existing_id' => $existing?->id,
                    'can_save' => $semester && $mk && $dosen,
                ];
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data yang valid di file.');
            }

            session([
                'import_joinmkuser_preview' => [
                    'rows' => $previewRows,
                    'filename' => $file->getClientOriginalName(),
                    'return_url' => $this->resolveReturnUrl($request),
                ],
            ]);

            // Return view directly with all required data
            $preview = session('import_joinmkuser_preview', []);
            $returnUrl = $this->resolveReturnUrl($request, $preview);
            return view('setting.bulk-import.joinmkuser', compact('preview', 'returnUrl'))
                            ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membaca file: ' . $e->getMessage());
        }
    }

    public function commitJoinMkUser(Request $request)
    {
        $request->validate([
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $preview = session('import_joinmkuser_preview', []);
        $rows = $preview['rows'] ?? [];

        if (empty($rows)) {
            return redirect()->route('setting.import.joinmkusers')
                            ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        $selectedIndexes = $request->input('selected', []);
        $savedCount = 0;
        $errorCount = 0;

        foreach ($selectedIndexes as $idx) {
            if (!isset($rows[$idx])) {
                continue;
            }

            $row = $rows[$idx];

            if (!$row['can_save']) {
                $errorCount++;
                continue;
            }

            $koordinatorValue = in_array(strtolower($row['koordinator']), ['ya', 'yes', '1', 'true']) ? 1 : 0;

            JoinMkUser::updateOrCreate(
                [
                    'semester_id' => $row['semester_id'],
                    'mk_id' => $row['mk_id'],
                    'kurikulum_id' => Mk::where('id', $row['mk_id'])->first()?->kurikulum_id,
                    'user_id' => $row['dosen_id'],
                ],
                [
                    'koordinator' => $koordinatorValue,
                ]
            );
            $savedCount++;
        }

        session()->forget('import_joinmkuser_preview');

        $message = "{$savedCount} data kontrak MK berhasil disimpan.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} data gagal disimpan karena data mahasiswa/mk/dosen tidak ditemukan.";
        }

        return redirect()->to($this->resolveReturnUrl($request, $preview))
                        ->with('success', $message);
    }

    public function downloadTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['kode semester', 'kode mata kuliah', 'nama mata kuliah', 'nidn dosen', 'nama dosen', 'koordinator'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        // Add sample data
        $sheet->setCellValue('A2', '20251');
        $sheet->setCellValue('B2', 'MK001');
        $sheet->setCellValue('C2', 'Pemrograman Web');
        $sheet->setCellValue('D2', '0123456789');
        $sheet->setCellValue('E2', 'Dr. Budi Santoso');
        $sheet->setCellValue('F2', 'Ya');

        $sheet->setCellValue('A3', '20251');
        $sheet->setCellValue('B3', 'MK002');
        $sheet->setCellValue('C3', 'Basis Data');
        $sheet->setCellValue('D3', '0123456790');
        $sheet->setCellValue('E3', 'Prof. Ahmad Rizki');
        $sheet->setCellValue('F3', 'Tidak');

        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $fileName = 'template-import-pengampu-mata-kuliah.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clearPreview()
    {
        session()->forget('import_joinmkuser_preview');
        return redirect()->route('setting.import.joinmkusers')
                        ->with('success', 'Data preview berhasil dihapus.');
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

        return $candidate !== '' ? $candidate : route('setting.import.joinmkusers');
    }
}
