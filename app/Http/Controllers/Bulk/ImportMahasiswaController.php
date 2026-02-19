<?php

namespace App\Http\Controllers\Bulk;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportMahasiswaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read bulk-import mahasiswas', ['only' => ['importMahasiswaForm','downloadTemplate']]);
        $this->middleware('permission:create bulk-import mahasiswas', ['only' => ['importMahasiswa', 'commitMahasiswa']]);
        $this->middleware('permission:delete bulk-import mahasiswas', ['only' => ['clearPreview']]);
    }

    public function importMahasiswaForm()
    {
        $prodis = Prodi::all();
        $preview = session('import_mahasiswa_preview', []);
        $returnUrl = $this->resolveReturnUrl(request(), $preview);
        return view('setting.bulk-import.mahasiswa', compact('prodis', 'preview', 'returnUrl'));
    }

    public function importMahasiswa(Request $request)
    {
        try {
            // Validate and process the uploaded file
            $request->validate([
                'prodi_id' => 'required|exists:prodis,id',
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
                'nim' => array_search('nim', $header, true),
                'nama' => array_search('nama', $header, true),
                'angkatan' => array_search('angkatan', $header, true),
                'email' => array_search('email', $header, true),
                'phone' => array_search('phone', $header, true),
            ];

            if ($headerMap['nim'] === false) {
                return back()->with('error', 'File harus memiliki kolom "nim".');
            }

            $previewRows = [];
            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue;
                }

                $nimCol = $headerMap['nim'] ?? null;
                if (!$nimCol) {
                    continue;
                }

                $nim = trim((string) ($row[$nimCol] ?? ''));
                if ($nim === '') {
                    continue;
                }

                $nama = trim((string) ($row[$headerMap['nama']] ?? ''));
                $angkatan = trim((string) ($row[$headerMap['angkatan']] ?? ''));
                $email = trim((string) ($row[$headerMap['email']] ?? ''));
                $phone = trim((string) ($row[$headerMap['phone']] ?? ''));

                $existing = Mahasiswa::where('nim', $nim)->first();

                $previewRows[] = [
                    'nim' => $nim,
                    'nama' => $nama,
                    'angkatan' => $angkatan,
                    'email' => $email,
                    'phone' => $phone,
                    'exists' => (bool) $existing,
                    'existing_id' => $existing?->id,
                ];
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data yang valid di file.');
            }

            session([
                'import_mahasiswa_preview' => [
                    'prodi_id' => $request->prodi_id,
                    'rows' => $previewRows,
                    'filename' => $file->getClientOriginalName(),
                    'return_url' => $this->resolveReturnUrl($request),
                ],
            ]);

            // Return view directly with all required data
            $prodis = Prodi::all();
            $preview = session('import_mahasiswa_preview', []);
            $returnUrl = $this->resolveReturnUrl($request, $preview);
            return view('setting.bulk-import.mahasiswa', compact('prodis', 'preview', 'returnUrl'))
                            ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membaca file: ' . $e->getMessage());
        }
    }

    public function commitMahasiswa(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodis,id',
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $preview = session('import_mahasiswa_preview', []);
        $rows = $preview['rows'] ?? [];

        if (empty($rows)) {
            return redirect()->route('setting.import.mahasiswas')
                            ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        $selectedIndexes = $request->input('selected', []);
        $savedCount = 0;

        foreach ($selectedIndexes as $idx) {
            if (!isset($rows[$idx])) {
                continue;
            }

            $row = $rows[$idx];
            Mahasiswa::updateOrCreate(
                ['nim' => $row['nim']],
                [
                    'nama' => $row['nama'],
                    'angkatan' => $row['angkatan'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'prodi_id' => $request->prodi_id,
                ]
            );
            $savedCount++;
        }

        session()->forget('import_mahasiswa_preview');

        return redirect()->to($this->resolveReturnUrl($request, $preview))
                        ->with('success', "{$savedCount} data mahasiswa berhasil disimpan.");
    }

    public function downloadTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['nim', 'nama', 'angkatan', 'email', 'phone'];
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
        $sheet->setCellValue('C2', '2023');
        $sheet->setCellValue('D2', 'ahmad@example.com');
        $sheet->setCellValue('E2', '081234567890');

        $sheet->setCellValue('A3', '2301002');
        $sheet->setCellValue('B3', 'Budi Santoso');
        $sheet->setCellValue('C3', '2023');
        $sheet->setCellValue('D3', 'budi@example.com');
        $sheet->setCellValue('E3', '082234567890');

        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Get prodi code if provided
        $fileName = 'template-import-mahasiswa';
        if ($request->prodi_id) {
            $prodi = Prodi::find($request->prodi_id);
            if ($prodi && $prodi->kode) {
                $fileName .= '-' . $prodi->kode;
            }
        }
        $fileName .= '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clearPreview()
    {
        session()->forget('import_mahasiswa_preview');
        return redirect()->route('setting.import.mahasiswas')
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

        return $candidate !== '' ? $candidate : route('setting.import.mahasiswas');
    }
}
