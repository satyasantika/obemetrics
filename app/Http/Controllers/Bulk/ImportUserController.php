<?php

namespace App\Http\Controllers\Bulk;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read bulk-import users', ['only' => ['importUserForm','downloadTemplate']]);
        $this->middleware('permission:create bulk-import users', ['only' => ['importUser', 'commitUser']]);
        $this->middleware('permission:delete bulk-import users', ['only' => ['clearPreview']]);
    }

    public function importUserForm()
    {
        $preview = session('import_user_preview', []);
        return view('setting.bulk-import.users', compact('preview'));
    }

    public function importUser(Request $request)
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
                'name' => array_search('name', $header, true),
                'username' => array_search('username', $header, true),
                'nidn' => array_search('nidn', $header, true),
                'email' => array_search('email', $header, true),
                'password' => array_search('password', $header, true),
            ];

            if ($headerMap['username'] === false) {
                return back()->with('error', 'File harus memiliki kolom "username".');
            }

            $previewRows = [];
            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue;
                }

                $usernameCol = $headerMap['username'] ?? null;
                if (!$usernameCol) {
                    continue;
                }

                $username = trim((string) ($row[$usernameCol] ?? ''));
                if ($username === '') {
                    continue;
                }

                $name = trim((string) ($row[$headerMap['name']] ?? ''));
                $nidn = trim((string) ($row[$headerMap['nidn']] ?? ''));
                $email = trim((string) ($row[$headerMap['email']] ?? ''));
                $password = trim((string) ($row[$headerMap['password']] ?? ''));

                $existing = User::where('username', $username)->first();

                $previewRows[] = [
                    'name' => $name,
                    'username' => $username,
                    'nidn' => $nidn,
                    'email' => $email,
                    'password' => $password,
                    'exists' => (bool) $existing,
                    'existing_id' => $existing?->id,
                ];
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data yang valid di file.');
            }

            session([
                'import_user_preview' => [
                    'rows' => $previewRows,
                    'filename' => $file->getClientOriginalName(),
                ],
            ]);

            // Return view directly with all required data
            $preview = session('import_user_preview', []);
            return view('setting.bulk-import.users', compact('preview'))
                            ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membaca file: ' . $e->getMessage());
        }
    }

    public function commitUser(Request $request)
    {
        $request->validate([
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $preview = session('import_user_preview', []);
        $rows = $preview['rows'] ?? [];

        if (empty($rows)) {
            return redirect()->route('setting.import.users')
                            ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        $selectedIndexes = $request->input('selected', []);
        $savedCount = 0;

        foreach ($selectedIndexes as $idx) {
            if (!isset($rows[$idx])) {
                continue;
            }

            $row = $rows[$idx];

            // Set default password if not provided
            $password = !empty($row['password']) ? $row['password'] : 'password123';

            User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name'],
                    'nidn' => $row['nidn'],
                    'email' => $row['email'],
                    'password' => Hash::make($password),
                ]
            )->assignRole('dosen');
            $savedCount++;
        }

        session()->forget('import_user_preview');

        return redirect()->route('setting.import.users')
                        ->with('success', "{$savedCount} data user berhasil disimpan.");
    }

    public function downloadTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['name', 'username', 'nidn', 'email', 'password'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        // Add sample data
        $sheet->setCellValue('A2', 'Ahmad Rizki');
        $sheet->setCellValue('B2', 'ahmad.rizki');
        $sheet->setCellValue('C2', '0001234567');
        $sheet->setCellValue('D2', 'ahmad@example.com');
        $sheet->setCellValue('E2', 'password123');

        $sheet->setCellValue('A3', 'Budi Santoso');
        $sheet->setCellValue('B3', 'budi.santoso');
        $sheet->setCellValue('C3', '0001234568');
        $sheet->setCellValue('D3', 'budi@example.com');
        $sheet->setCellValue('E3', 'password123');

        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $fileName = 'template-import-users.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clearPreview()
    {
        session()->forget('import_user_preview');
        return redirect()->route('setting.import.users')
                        ->with('success', 'Data preview berhasil dihapus.');
    }
}
