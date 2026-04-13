<?php

namespace App\Http\Controllers\Bulk;

use App\Models\ProdiUser;
use App\Models\Role;
use App\Models\User;
use App\Models\Prodi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Guard;

class ImportProdiUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read bulk-import prodiusers', ['only' => ['importProdiUserForm','downloadTemplate']]);
        $this->middleware('permission:create bulk-import prodiusers', ['only' => ['importProdiUser', 'commitProdiUser']]);
        $this->middleware('permission:delete bulk-import prodiusers', ['only' => ['clearPreview']]);
    }

    public function importProdiUserForm()
    {
        $preview = session('import_prodiuser_preview', []);
        $returnUrl = $this->resolveReturnUrl(request(), $preview);
        return view('setting.bulk-import.prodiuser', compact('preview', 'returnUrl'));
    }

    public function importProdiUser(Request $request)
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
                'status_pimpinan' => array_search('status pimpinan', $header, true),
            ];

            if ($headerMap['status_pimpinan'] === false) {
                $headerMap['status_pimpinan'] = array_search('posisi dalam prodi', $header, true);
            }

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
                $rawStatusPimpinan = trim((string) ($row[$headerMap['status_pimpinan']] ?? ''));
                $normalizedStatus = Str::lower($rawStatusPimpinan);
                $statusPimpinan = in_array($normalizedStatus, ['ya', 'y', 'yes', '1', 'true', 'ketua prodi', 'kaprodi'], true);

                // Check prodi
                $prodi = Prodi::where('kode_prodi', $kodeProdi)->first();
                // Check dosen (user dengan nidn)
                $dosen = User::where('nidn', $nidn)->first();

                // Check if kontrak already exists
                $existing = null;
                if ($prodi && $dosen) {
                    $existing = ProdiUser::where('prodi_id', $prodi->id)
                                        ->where('user_id', $dosen->id)
                                        ->first();
                }

                $previewRows[] = [
                    'kode_prodi' => $kodeProdi,
                    'nama_prodi' => $namaProdi,
                    'nidn' => $nidn,
                    'nama_dosen' => $namaDosen,
                    'status_pimpinan' => $statusPimpinan,
                    'status_pimpinan_label' => $statusPimpinan ? 'Ya' : '-',
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
                'import_prodiuser_preview' => [
                    'rows' => $previewRows,
                    'filename' => $file->getClientOriginalName(),
                    'return_url' => $this->resolveReturnUrl($request),
                ],
            ]);

            // Return view directly with all required data
            $preview = session('import_prodiuser_preview', []);
            $returnUrl = $this->resolveReturnUrl($request, $preview);
            return view('setting.bulk-import.prodiuser', compact('preview', 'returnUrl'))
                            ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membaca file: ' . $e->getMessage());
        }
    }

    public function commitProdiUser(Request $request)
    {
        $request->validate([
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $preview = session('import_prodiuser_preview', []);
        $rows = $preview['rows'] ?? [];

        if (empty($rows)) {
            return redirect()->route('settings.import.prodiusers')
                            ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        $selectedIndexes = $request->input('selected', []);
        $savedCount = 0;
        $errorCount = 0;
        $affectedUserIds = [];

        foreach ($selectedIndexes as $idx) {
            if (!isset($rows[$idx])) {
                continue;
            }

            $row = $rows[$idx];

            if (!$row['can_save']) {
                $errorCount++;
                continue;
            }

            $prodiUser = ProdiUser::updateOrCreate(
                [
                    'prodi_id' => $row['prodi_actual_id'],
                    'user_id' => $row['dosen_id'],
                ],
                [
                    'status_pimpinan' => (bool) ($row['status_pimpinan'] ?? false),
                ]
            );

            $affectedUserIds[] = (int) $prodiUser->user_id;
            $this->syncPimpinanProdiRole((int) $prodiUser->user_id);
            $savedCount++;
        }

        foreach (array_unique($affectedUserIds) as $affectedUserId) {
            $this->syncPimpinanProdiRole((int) $affectedUserId);
        }

        session()->forget('import_prodiuser_preview');

        $message = "{$savedCount} data user prodi berhasil disimpan.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} data gagal disimpan karena data prodi/dosen tidak ditemukan.";
        }

        return to_route('prodis.index')
                ->with('success', $message);
    }

    public function downloadTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['kode program studi', 'nama program studi', 'nidn dosen', 'nama dosen', 'status pimpinan'];
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
        $sheet->setCellValue('E2', 'Ya');

        $sheet->setCellValue('A3', '2156');
        $sheet->setCellValue('B3', 'Pendidikan Y');
        $sheet->setCellValue('C3', '0123456790');
        $sheet->setCellValue('D3', 'Prof. Ahmad Rizki');
        $sheet->setCellValue('E3', '-');

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
        session()->forget('import_prodiuser_preview');
        return redirect()->route('settings.import.prodiusers')
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

        return $candidate !== '' ? $candidate : route('settings.import.prodiusers');
    }

    private function syncPimpinanProdiRole(int $userId): void
    {
        $roleName = 'pimpinan prodi';

        $user = User::query()->find($userId);
        if (!$user) {
            return;
        }

        $role = Role::findOrCreate($roleName, Guard::getDefaultName($user));

        $isPimpinan = ProdiUser::query()
            ->where('user_id', $userId)
            ->where('status_pimpinan', true)
            ->exists();

        if ($isPimpinan) {
            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }
            return;
        }

        if ($user->hasRole($role)) {
            $user->removeRole($role);
        }
    }
}
