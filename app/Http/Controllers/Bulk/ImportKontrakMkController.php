<?php

namespace App\Http\Controllers\Bulk;

use App\Models\KontrakMk;
use App\Models\Mahasiswa;
use App\Models\Mk;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Semester;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportKontrakMkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read bulk-import kontrakmks', ['only' => ['importKontrakMkForm','downloadTemplate']]);
        $this->middleware('permission:create bulk-import kontrakmks', ['only' => ['importKontrakMk', 'commitKontrakMk']]);
        $this->middleware('permission:delete bulk-import kontrakmks', ['only' => ['clearPreview']]);
    }

    public function importKontrakMkForm()
    {
        $semesters = Semester::all();
        $preview = session('import_kontrakmk_preview', []);
        return view('setting.bulk-import.kontrakmk', compact('preview', 'semesters'));
    }

    public function importKontrakMk(Request $request)
    {
        try {
            // Validate and process the uploaded file
            $request->validate([
                'file' => 'required|mimes:xlsx,csv,ods',
                'semester_id' => 'required|uuid',
            ]);

            $file = $request->file('file');
            $semesterId = $request->semester_id;
            $semester = Semester::find($semesterId);

            if (!$semester) {
                return back()->with('error', 'Semester tidak ditemukan.');
            }

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $header = array_map(function ($value) {
                return Str::lower(trim((string) $value));
            }, $rows[1] ?? []);

            $headerMap = [
                'nim' => array_search('nim', $header, true),
                'nama_mahasiswa' => array_search('nama mahasiswa', $header, true),
                'kode_mk' => array_search('kode mata kuliah', $header, true),
                'nidn' => array_search('nidn dosen', $header, true),
                'nama_dosen' => array_search('nama dosen', $header, true),
                'kelas' => array_search('kelas', $header, true),
            ];

            if ($headerMap['nim'] === false || $headerMap['kode_mk'] === false || $headerMap['nidn'] === false) {
                return back()->with('error', 'File harus memiliki kolom "nim", "kode mata kuliah", dan "nidn dosen".');
            }

            $previewRows = [];
            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue;
                }

                $nimCol = $headerMap['nim'] ?? null;
                $kodeMkCol = $headerMap['kode_mk'] ?? null;
                $nidnCol = $headerMap['nidn'] ?? null;

                if (!$nimCol || !$kodeMkCol || !$nidnCol) {
                    continue;
                }

                $nim = trim((string) ($row[$nimCol] ?? ''));
                $kodeMk = trim((string) ($row[$kodeMkCol] ?? ''));
                $nidn = trim((string) ($row[$nidnCol] ?? ''));

                if ($nim === '' || $kodeMk === '' || $nidn === '') {
                    continue;
                }

                $namaMahasiswa = trim((string) ($row[$headerMap['nama_mahasiswa']] ?? ''));
                $namaDosen = trim((string) ($row[$headerMap['nama_dosen']] ?? ''));
                $kelas = trim((string) ($row[$headerMap['kelas']] ?? ''));

                // Check mahasiswa
                $mahasiswa = Mahasiswa::where('nim', $nim)->first();
                // Check mk
                $mk = Mk::where('kodemk', $kodeMk)->first();
                // Check dosen (user dengan nidn)
                $dosen = User::where('nidn', $nidn)->first();

                // Check if kontrak already exists
                $existing = null;
                if ($mahasiswa && $mk && $dosen) {
                    $existing = KontrakMk::where('mahasiswa_id', $mahasiswa->id)
                                        ->where('mk_id', $mk->id)
                                        ->where('user_id', $dosen->id)
                                        ->first();
                }

                $previewRows[] = [
                    'nim' => $nim,
                    'nama_mahasiswa' => $namaMahasiswa,
                    'kode_mk' => $kodeMk,
                    'nidn' => $nidn,
                    'nama_dosen' => $namaDosen,
                    'kelas' => $kelas,
                    'kode_semester' => $semester->kode,
                    'mahasiswa_exists' => (bool) $mahasiswa,
                    'mahasiswa_id' => $mahasiswa?->id,
                    'mk_exists' => (bool) $mk,
                    'mk_id' => $mk?->id,
                    'dosen_exists' => (bool) $dosen,
                    'dosen_id' => $dosen?->id,
                    'exists' => (bool) $existing,
                    'existing_id' => $existing?->id,
                    'can_save' => $mahasiswa && $mk && $dosen,
                ];
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data yang valid di file.');
            }

            session([
                'import_kontrakmk_preview' => [
                    'rows' => $previewRows,
                    'filename' => $file->getClientOriginalName(),
                    'semester_id' => $semesterId,
                    'semester_kode' => $semester->kode,
                ],
            ]);

            // Return view directly with all required data
            $preview = session('import_kontrakmk_preview', []);
            $semesters = Semester::all();
            return view('setting.bulk-import.kontrakmk', compact('preview', 'semesters'))
                            ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membaca file: ' . $e->getMessage());
        }
    }

    public function commitKontrakMk(Request $request)
    {
        $request->validate([
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $preview = session('import_kontrakmk_preview', []);
        $rows = $preview['rows'] ?? [];
        $semesterId = $preview['semester_id'] ?? null;

        if (empty($rows)) {
            return redirect()->route('setting.import.kontrakmks')
                            ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        if (!$semesterId) {
            return redirect()->route('setting.import.kontrakmks')
                            ->with('error', 'Semester tidak ditemukan dalam preview.');
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

            KontrakMk::updateOrCreate(
                [
                    'mahasiswa_id' => $row['mahasiswa_id'],
                    'mk_id' => $row['mk_id'],
                    'user_id' => $row['dosen_id'],
                    'semester_id' => $semesterId,
                ],
                [
                    'kelas' => $row['kelas'],
                ]
            );
            $savedCount++;
        }

        session()->forget('import_kontrakmk_preview');

        $message = "{$savedCount} data kontrak MK berhasil disimpan.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} data gagal disimpan karena data mahasiswa/mk/dosen tidak ditemukan.";
        }

        return redirect()->route('setting.import.kontrakmks')
                        ->with('success', $message);
    }

    public function downloadTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['nim', 'nama mahasiswa', 'kode mata kuliah', 'nidn dosen', 'nama dosen', 'kelas'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        // Add sample data
        $sheet->setCellValue('A2', '2301001');
        $sheet->setCellValue('B2', 'Ahmad Rizki');
        $sheet->setCellValue('C2', 'MK001');
        $sheet->setCellValue('D2', '0123456789');
        $sheet->setCellValue('E2', 'Dr. Budi Santoso');
        $sheet->setCellValue('F2', 'A');

        $sheet->setCellValue('A3', '2301002');
        $sheet->setCellValue('B3', 'Citra Dewi');
        $sheet->setCellValue('C3', 'MK001');
        $sheet->setCellValue('D3', '0123456789');
        $sheet->setCellValue('E3', 'Dr. Budi Santoso');
        $sheet->setCellValue('F3', 'B');

        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $fileName = 'template-import-kontrakmk.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clearPreview()
    {
        session()->forget('import_kontrakmk_preview');
        return redirect()->route('setting.import.kontrakmks')
                        ->with('success', 'Data preview berhasil dihapus.');
    }
}
