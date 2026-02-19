<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use App\Models\JoinProdiUser;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportAdminMasterController extends Controller
{
    private const TARGETS = [
        'prodis' => [
            'label' => 'Program Studi',
            'columns' => ['kode_prodi', 'nama', 'jenjang'],
            'required' => ['kode_prodi', 'nama', 'jenjang'],
        ],
        'users' => [
            'label' => 'User Dosen',
            'columns' => ['name', 'username', 'nidn', 'email', 'password'],
            'required' => ['username'],
        ],
        'joinprodiusers' => [
            'label' => 'Dosen ke Program Studi',
            'columns' => ['kode_prodi', 'nama_prodi', 'nidn', 'nama_dosen', 'status'],
            'required' => ['kode_prodi', 'nidn'],
        ],
    ];

    public function form(Request $request)
    {
        $target = $this->resolveTarget($request->query('target'));
        $preview = session($this->previewSessionKey($target), []);
        $returnUrl = $this->resolveReturnUrl($request);

        return view('setting.bulk-import.admin-master', [
            'targets' => self::TARGETS,
            'target' => $target,
            'preview' => $preview,
            'returnUrl' => $returnUrl,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'target' => 'required|string|in:' . implode(',', array_keys(self::TARGETS)),
            'file' => 'required|mimes:xlsx,csv,ods',
        ]);

        $target = $this->resolveTarget($request->input('target'));
        $meta = self::TARGETS[$target];

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

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

                $previewRows[] = $normalizedRow;
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data valid untuk dipreview.');
            }

            session([
                $this->previewSessionKey($target) => [
                    'target' => $target,
                    'filename' => $request->file('file')->getClientOriginalName(),
                    'rows' => $previewRows,
                ],
            ]);

            return to_route('setting.import.admin-master', $this->withReturnUrl([
                'target' => $target,
            ], $request))
                ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan diproses.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function commit(Request $request)
    {
        $request->validate([
            'target' => 'required|string|in:' . implode(',', array_keys(self::TARGETS)),
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $target = $this->resolveTarget($request->input('target'));
        $preview = session($this->previewSessionKey($target), []);
        $rows = $preview['rows'] ?? [];

        if (empty($rows)) {
            return to_route('setting.import.admin-master', $this->withReturnUrl([
                'target' => $target,
            ], $request))
                ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        $selectedIndexes = $request->input('selected', []);
        $saved = 0;
        $skipped = [];

        foreach ($selectedIndexes as $idx) {
            if (!isset($rows[$idx])) {
                continue;
            }

            try {
                $this->persistRow($target, $rows[$idx]);
                $saved++;
            } catch (\Throwable $e) {
                $skipped[] = 'Baris ' . ($idx + 2) . ': ' . $e->getMessage();
            }
        }

        session()->forget($this->previewSessionKey($target));

        $message = "{$saved} baris berhasil diproses.";
        if (!empty($skipped)) {
            $message .= ' Beberapa baris dilewati: ' . implode(' | ', array_slice($skipped, 0, 5));
        }

        return to_route('setting.import.admin-master', $this->withReturnUrl([
            'target' => $target,
        ], $request))
            ->with('success', $message);
    }

    public function template(Request $request)
    {
        $target = $this->resolveTarget($request->query('target'));
        $meta = self::TARGETS[$target];

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
        $fileName = 'import' . $waktu_download . '-' .Str::slug($meta['label'], '-') . '-by-admin.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clear(Request $request)
    {
        $target = $this->resolveTarget($request->input('target'));
        session()->forget($this->previewSessionKey($target));

        return to_route('setting.import.admin-master', $this->withReturnUrl([
            'target' => $target,
        ], $request))
            ->with('success', 'Preview berhasil dikosongkan.');
    }

    private function persistRow(string $target, array $row): void
    {
        switch ($target) {
            case 'prodis':
                $kodeProdi = $this->required($row['kode_prodi'] ?? null, 'kode_prodi');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $jenjang = $this->required($row['jenjang'] ?? null, 'jenjang');

                Prodi::updateOrCreate(
                    ['kode_prodi' => $kodeProdi],
                    ['nama' => $nama, 'jenjang' => $jenjang]
                );
                return;

            case 'users':
                $username = $this->required($row['username'] ?? null, 'username');
                $password = trim((string) ($row['password'] ?? ''));
                if ($password === '') {
                    $password = 'password123';
                }

                $user = User::updateOrCreate(
                    ['username' => $username],
                    [
                        'name' => $row['name'] ?? null,
                        'nidn' => $row['nidn'] ?? null,
                        'email' => $row['email'] ?? null,
                        'password' => Hash::make($password),
                    ]
                );
                $user->assignRole('dosen');
                return;

            case 'joinprodiusers':
                $kodeProdi = $this->required($row['kode_prodi'] ?? null, 'kode_prodi');
                $nidn = $this->required($row['nidn'] ?? null, 'nidn');

                $prodi = Prodi::query()->where('kode_prodi', $kodeProdi)->first();
                if (!$prodi) {
                    throw new \RuntimeException('Prodi tidak ditemukan: ' . $kodeProdi);
                }

                $user = User::query()->where('nidn', $nidn)->first();
                if (!$user) {
                    throw new \RuntimeException('User dosen tidak ditemukan untuk NIDN: ' . $nidn);
                }

                JoinProdiUser::updateOrCreate(
                    [
                        'prodi_id' => $prodi->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'status' => trim((string) ($row['status'] ?? '')) ?: null,
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

    private function resolveTarget(?string $target): string
    {
        return array_key_exists((string) $target, self::TARGETS)
            ? (string) $target
            : array_key_first(self::TARGETS);
    }

    private function previewSessionKey(string $target): string
    {
        return 'import_admin_master_' . $target;
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
}
