<?php

namespace App\Http\Controllers\Bulk;

use App\Models\JoinProdiUser;
use App\Models\Semester;
use App\Models\Mk;
use App\Models\User;
use App\Models\Prodi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportJoinProdiUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read bulk-import joinprodiusers', ['only' => ['importJoinProdiUserForm','downloadTemplate']]);
        $this->middleware('permission:create bulk-import joinprodiusers', ['only' => ['importJoinProdiUser', 'commitJoinProdiUser']]);
        $this->middleware('permission:delete bulk-import joinprodiusers', ['only' => ['clearPreview']]);
    }

    public function importJoinProdiUserForm()
    {
        $preview = session('import_joinprodiuser_preview', []);
        return view('setting.bulk-import.joinprodiuser', compact('preview'));
    }

    public function importJoinProdiUser(Request $request)
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
                'prodi_id' => array_search('kode program studi', $header, true),
                'nama_prodi' => array_search('nama program studi', $header, true),
                'nidn' => array_search('nidn dosen', $header, true),
                'nama_dosen' => array_search('nama dosen', $header, true),
                'status' => array_search('posisi dalam prodi', $header, true),
            ];

            if ($headerMap['prodi_id'] === false || $headerMap['nidn'] === false) {
                return back()->with('error', 'File harus memiliki minimal kolom "kode program studi" dan "nidn dosen".');
            }

            $previewRows = [];
            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue;
                }

                $kodeProdiCol = $headerMap['prodi_id'] ?? null;
                $nidnCol = $headerMap['nidn'] ?? null;

                if (!$kodeProdiCol || !$nidnCol) {
                    continue;
                }

                $kodeProdi = trim((string) ($row[$kodeProdiCol] ?? ''));
                $nidn = trim((string) ($row[$nidnCol] ?? ''));

                if ($kodeProdi === '' || $nidn === '') {
                    continue;
                }

                $namaProdi = trim((string) ($row[$headerMap['nama_prodi']] ?? ''));
                $namaDosen = trim((string) ($row[$headerMap['nama_dosen']] ?? ''));
                $status = trim((string) ($row[$headerMap['status']] ?? ''));

                // Check prodi
                $prodi = Prodi::where('kode_unsil', $kodeProdi)->first();
                // Check dosen (user dengan nidn)
                $dosen = User::where('nidn', $nidn)->first();

                // Check if kontrak already exists
                $existing = null;
                if ($prodi && $dosen) {
                    $existing = JoinProdiUser::where('prodi_id', $prodi->id)
                                        ->where('user_id', $dosen->id)
                                        ->first();
                }

                $previewRows[] = [
                    'kode_prodi' => $kodeProdi,
                    'nama_prodi' => $namaProdi,
                    'nidn' => $nidn,
                    'nama_dosen' => $namaDosen,
                    'status' => $status,
                    'prodi_exists' => (bool) $prodi,
                    'prodi_actual_id' => $prodi?->id,
                    'dosen_exists' => (bool) $dosen,
                    'dosen_id' => $dosen?->id,
                    'exists' => (bool) $existing,
                    'existing_id' => $existing?->id,
                    'can_save' => $prodi && $dosen,
                ];
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data yang valid di file.');
            }

            session([
                'import_joinprodiuser_preview' => [
                    'rows' => $previewRows,
                    'filename' => $file->getClientOriginalName(),
                ],
            ]);

            // Return view directly with all required data
            $preview = session('import_joinprodiuser_preview', []);
            return view('setting.bulk-import.joinprodiuser', compact('preview'))
                            ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membaca file: ' . $e->getMessage());
        }
    }

    public function commitJoinProdiUser(Request $request)
    {
        $request->validate([
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $preview = session('import_joinprodiuser_preview', []);
        $rows = $preview['rows'] ?? [];

        if (empty($rows)) {
            return redirect()->route('setting.import.joinprodiusers')
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

            JoinProdiUser::updateOrCreate(
                [
                    'prodi_id' => $row['prodi_actual_id'],
                    'user_id' => $row['dosen_id'],
                ],
                [
                    'status' => $row['status'] ?: null,
                ]
            );
            $savedCount++;
        }

        session()->forget('import_joinprodiuser_preview');

        $message = "{$savedCount} data user prodi berhasil disimpan.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} data gagal disimpan karena data prodi/dosen tidak ditemukan.";
        }

        return redirect()->route('setting.import.joinprodiusers')
                        ->with('success', $message);
    }

    public function downloadTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['kode program studi', 'nama program studi', 'nidn dosen', 'nama dosen', 'posisi dalam prodi'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        // Add sample data
        $sheet->setCellValue('A2', '2155');
        $sheet->setCellValue('B2', 'Pendidikan X');
        $sheet->setCellValue('C2', '0123456789');
        $sheet->setCellValue('D2', 'Dr. Budi Santoso');
        $sheet->setCellValue('E2', 'Ketua Prodi');

        $sheet->setCellValue('A3', '2156');
        $sheet->setCellValue('B3', 'Pendidikan Y');
        $sheet->setCellValue('C3', '0123456790');
        $sheet->setCellValue('D3', 'Prof. Ahmad Rizki');
        $sheet->setCellValue('E3', 'Dosen');

        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $fileName = 'template-import-prodiuser.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clearPreview()
    {
        session()->forget('import_joinprodiuser_preview');
        return redirect()->route('setting.import.joinprodiusers')
                        ->with('success', 'Data preview berhasil dihapus.');
    }
}
