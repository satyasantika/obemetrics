<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use App\Models\Bk;
use App\Models\Cpl;
use App\Models\JoinBkMk;
use App\Models\JoinCplBk;
use App\Models\JoinMkUser;
use App\Models\JoinProfilCpl;
use App\Models\KontrakMk;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\Mk;
use App\Models\Profil;
use App\Models\ProfilIndikator;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportKurikulumMasterController extends Controller
{
    private const TARGETS = [
        'profils' => [
            'label' => 'Profil Lulusan',
            'columns' => ['kode', 'nama', 'deskripsi'],
            'required' => ['nama'],
        ],
        'profil_indikators' => [
            'label' => 'Indikator Profil Lulusan',
            'columns' => ['nama', 'deskripsi', 'nama_profil'],
            'required' => ['nama'],
        ],
        'cpls' => [
            'label' => 'Capaian Pembelajaran Lulusan',
            'columns' => ['kode', 'nama', 'cakupan'],
            'required' => ['kode', 'nama', 'cakupan'],
        ],
        'join_profil_cpls' => [
            'label' => 'CPL untuk Profil Lulusan',
            'columns' => ['kode_profil', 'nama_profil', 'kode_cpl', 'nama_cpl'],
            'required' => ['kode_profil', 'kode_cpl'],
        ],
        'bks' => [
            'label' => 'Bahan Kajian',
            'columns' => ['kode', 'nama', 'deskripsi'],
            'required' => ['kode', 'nama'],
        ],
        'join_cpl_bks' => [
            'label' => 'Bahan Kajian untuk CPL',
            'columns' => ['kode_cpl', 'nama_cpl', 'kode_bk', 'nama_bk'],
            'required' => ['kode_cpl', 'kode_bk'],
        ],
        'mks' => [
            'label' => 'Mata Kuliah',
            'columns' => ['kode', 'nama', 'semester', 'sks_teori', 'sks_praktik', 'sks_lapangan', 'deskripsi'],
            'required' => ['kode', 'nama', 'semester', 'sks_teori', 'sks_praktik', 'sks_lapangan'],
            'requires_semester' => false,
        ],
        'join_bk_mks' => [
            'label' => 'Mata Kuliah untuk Bahan Kajian',
            'columns' => ['kode_bk', 'nama_bk', 'kode_mk', 'nama_mk'],
            'required' => ['kode_bk', 'kode_mk'],
            'requires_semester' => false,
        ],
        'mahasiswas' => [
            'label' => 'Mahasiswa Program Studi',
            'columns' => ['nim', 'nama', 'angkatan', 'email', 'phone'],
            'required' => ['nim'],
            'requires_semester' => false,
        ],
        'joinmkusers' => [
            'label' => 'Dosen Pengampu Mata Kuliah',
            'columns' => ['kode_semester', 'kode_mk', 'nama_mata_kuliah', 'nidn', 'nama_dosen', 'koordinator'],
            'required' => ['kode_semester', 'kode_mk', 'nidn'],
            'requires_semester' => false,
        ],
        'kontrakmks' => [
            'label' => 'Kontrak Mata Kuliah',
            'columns' => ['kode_semester', 'nim', 'nama_mahasiswa', 'kode_mk', 'nidn', 'nama_dosen', 'kelas'],
            'required' => ['kode_semester', 'nim', 'kode_mk', 'nidn'],
            'requires_semester' => false,
        ],
    ];

    public function form(Kurikulum $kurikulum, Request $request)
    {
        $target = $this->resolveTarget($request->query('target'));
        $preview = session($this->previewSessionKey($kurikulum, $target), []);
        $semesters = Semester::query()->orderBy('kode')->get();
        $returnUrl = $this->resolveReturnUrl($request);

        return view('setting.bulk-import.kurikulum-master', [
            'kurikulum' => $kurikulum,
            'targets' => self::TARGETS,
            'target' => $target,
            'preview' => $preview,
            'semesters' => $semesters,
            'returnUrl' => $returnUrl,
        ]);
    }

    public function import(Request $request, Kurikulum $kurikulum)
    {
        $request->validate([
            'target' => 'required|string|in:' . implode(',', array_keys(self::TARGETS)),
            'semester_id' => 'nullable|exists:semesters,id',
            'file' => 'required|mimes:xlsx,csv,ods',
        ]);

        $target = $this->resolveTarget($request->input('target'));
        $meta = self::TARGETS[$target];

        if (!empty($meta['requires_semester']) && empty($request->semester_id)) {
            return back()->with('error', 'Semester wajib dipilih untuk target import ini.');
        }

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

                $previewRows[] = $this->decoratePreviewRow($target, $normalizedRow, $kurikulum, $request->semester_id);
            }

            if (empty($previewRows)) {
                return back()->with('error', 'Tidak ada data valid untuk dipreview.');
            }

            session([
                $this->previewSessionKey($kurikulum, $target) => [
                    'target' => $target,
                    'semester_id' => $request->semester_id,
                    'filename' => $request->file('file')->getClientOriginalName(),
                    'rows' => $previewRows,
                ],
            ]);

            return to_route('setting.import.kurikulum-master', $this->withReturnUrl([
                'kurikulum' => $kurikulum->id,
                'target' => $target,
            ], $request))
                ->with('success', 'Data berhasil dibaca. Silakan pilih data yang akan diproses.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function commit(Request $request, Kurikulum $kurikulum)
    {
        $request->validate([
            'target' => 'required|string|in:' . implode(',', array_keys(self::TARGETS)),
            'semester_id' => 'nullable|exists:semesters,id',
            'selected' => 'array',
            'selected.*' => 'integer',
        ]);

        $target = $this->resolveTarget($request->input('target'));
        $meta = self::TARGETS[$target];
        $preview = session($this->previewSessionKey($kurikulum, $target), []);
        $rows = $preview['rows'] ?? [];
        $semesterId = $request->input('semester_id') ?: ($preview['semester_id'] ?? null);

        if (empty($rows)) {
            return to_route('setting.import.kurikulum-master', $this->withReturnUrl([
                'kurikulum' => $kurikulum->id,
                'target' => $target,
            ], $request))
                ->with('error', 'Tidak ada data preview untuk diproses.');
        }

        if (!empty($meta['requires_semester']) && empty($semesterId)) {
            return to_route('setting.import.kurikulum-master', $this->withReturnUrl([
                'kurikulum' => $kurikulum->id,
                'target' => $target,
            ], $request))
                ->with('error', 'Semester wajib dipilih untuk target import ini.');
        }

        $selectedIndexes = $request->input('selected', []);
        $saved = 0;
        $skipped = [];

        foreach ($selectedIndexes as $idx) {
            if (!isset($rows[$idx])) {
                continue;
            }

            if (($rows[$idx]['can_save'] ?? true) === false) {
                $skipped[] = 'Baris ' . ($idx + 2) . ': ' . ($rows[$idx]['status_message'] ?? 'Data tidak valid.');
                continue;
            }

            try {
                $this->persistRow($target, $rows[$idx], $kurikulum, $semesterId);
                $saved++;
            } catch (\Throwable $e) {
                $skipped[] = 'Baris ' . ($idx + 2) . ': ' . $e->getMessage();
            }
        }

        session()->forget($this->previewSessionKey($kurikulum, $target));

        $message = "{$saved} baris berhasil diproses.";
        if (!empty($skipped)) {
            $message .= ' Beberapa baris dilewati: ' . implode(' | ', array_slice($skipped, 0, 5));
        }

        return to_route('setting.import.kurikulum-master', $this->withReturnUrl([
            'kurikulum' => $kurikulum->id,
            'target' => $target,
        ], $request))
            ->with('success', $message);
    }

    public function template(Kurikulum $kurikulum, Request $request)
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
        $fileName = 'import' . $waktu_download . '-' . Str::slug($meta['label'], '-') . '-prodi-' . Str::slug((string) ($kurikulum->prodi->jenjang . '-' . $kurikulum->prodi->nama ?? 'kurikulum'), '-') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clear(Kurikulum $kurikulum, Request $request)
    {
        $target = $this->resolveTarget($request->input('target'));
        session()->forget($this->previewSessionKey($kurikulum, $target));

        return to_route('setting.import.kurikulum-master', $this->withReturnUrl([
            'kurikulum' => $kurikulum->id,
            'target' => $target,
        ], $request))
            ->with('success', 'Preview berhasil dikosongkan.');
    }

    private function persistRow(string $target, array $row, Kurikulum $kurikulum, ?string $semesterId): void
    {
        switch ($target) {
            case 'profils':
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $kode = trim((string) ($row['kode'] ?? ''));

                if ($kode !== '') {
                    Profil::updateOrCreate(
                        ['kurikulum_id' => $kurikulum->id, 'kode' => $kode],
                        [
                            'nama' => $nama,
                            'deskripsi' => $row['deskripsi'] ?? null,
                        ]
                    );
                } else {
                    Profil::updateOrCreate(
                        ['kurikulum_id' => $kurikulum->id, 'nama' => $nama],
                        [
                            'kode' => null,
                            'deskripsi' => $row['deskripsi'] ?? null,
                        ]
                    );
                }
                return;

            case 'profil_indikators':
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $profil = $this->findProfilByRow($kurikulum, $row);
                if (!$profil) {
                    throw new \RuntimeException('Profil tidak ditemukan dari kolom nama_profil/kode_profil.');
                }
                ProfilIndikator::updateOrCreate(
                    ['profil_id' => $profil->id, 'nama' => $nama],
                    ['deskripsi' => $row['deskripsi'] ?? null]
                );
                return;

            case 'cpls':
                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $cakupan = $this->required($row['cakupan'] ?? null, 'cakupan');
                Cpl::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'kode' => $kode],
                    ['nama' => $nama, 'cakupan' => $cakupan]
                );
                return;

            case 'join_profil_cpls':
                $profil = $this->findProfilByRow($kurikulum, $row);
                if (!$profil) {
                    throw new \RuntimeException('Profil tidak ditemukan dari kolom nama_profil/kode_profil.');
                }

                $kodeCpl = $this->required($row['kode_cpl'] ?? null, 'kode_cpl');
                $cpl = Cpl::query()
                    ->where('kurikulum_id', $kurikulum->id)
                    ->where('kode', $kodeCpl)
                    ->first();
                if (!$cpl) {
                    throw new \RuntimeException('CPL tidak ditemukan untuk kode_cpl: ' . $kodeCpl);
                }

                JoinProfilCpl::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'profil_id' => $profil->id, 'cpl_id' => $cpl->id],
                    []
                );
                return;

            case 'bks':
                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                Bk::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'kode' => $kode],
                    ['nama' => $nama, 'deskripsi' => $row['deskripsi'] ?? null]
                );
                return;

            case 'join_cpl_bks':
                $kodeCpl = $this->required($row['kode_cpl'] ?? null, 'kode_cpl');
                $kodeBk = $this->required($row['kode_bk'] ?? null, 'kode_bk');

                $cpl = Cpl::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeCpl)->first();
                $bk = Bk::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeBk)->first();
                if (!$cpl || !$bk) {
                    throw new \RuntimeException('CPL/BK tidak ditemukan untuk relasi.');
                }

                JoinCplBk::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'cpl_id' => $cpl->id, 'bk_id' => $bk->id],
                    []
                );
                return;

            case 'mks':
                $kode = $this->required($row['kode'] ?? null, 'kode');
                $nama = $this->required($row['nama'] ?? null, 'nama');
                $semester = (int) $this->required($row['semester'] ?? null, 'semester');
                $sksTeori = (int) $this->required($row['sks_teori'] ?? null, 'sks_teori');
                $sksPraktik = (int) $this->required($row['sks_praktik'] ?? null, 'sks_praktik');
                $sksLapangan = (int) $this->required($row['sks_lapangan'] ?? null, 'sks_lapangan');
                $sks = $sksTeori + $sksPraktik + $sksLapangan;

                Mk::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'kode' => $kode],
                    [
                        'nama' => $nama,
                        'semester' => $semester,
                        'sks_teori' => $sksTeori,
                        'sks_praktik' => $sksPraktik,
                        'sks_lapangan' => $sksLapangan,
                        'sks' => $sks,
                        'deskripsi' => $row['deskripsi'] ?? null,
                    ]
                );
                return;

            case 'join_bk_mks':
                $kodeBk = $this->required($row['kode_bk'] ?? null, 'kode_bk');
                $kodeMk = $this->required($row['kode_mk'] ?? null, 'kode_mk');

                $bk = Bk::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeBk)->first();
                $mk = Mk::query()->where('kurikulum_id', $kurikulum->id)->where('kode', $kodeMk)->first();
                if (!$bk || !$mk) {
                    throw new \RuntimeException('BK/MK tidak ditemukan untuk relasi.');
                }

                JoinBkMk::updateOrCreate(
                    ['kurikulum_id' => $kurikulum->id, 'bk_id' => $bk->id, 'mk_id' => $mk->id],
                    []
                );
                return;

            case 'mahasiswas':
                $nim = $this->required($row['nim'] ?? null, 'nim');
                Mahasiswa::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'nama' => $row['nama'] ?? null,
                        'angkatan' => $row['angkatan'] ?? null,
                        'email' => $row['email'] ?? null,
                        'phone' => $row['phone'] ?? null,
                        'prodi_id' => $kurikulum->prodi_id,
                    ]
                );
                return;

            case 'joinmkusers':
                $kodeSemester = $this->required($row['kode_semester'] ?? null, 'kode_semester');
                $kodeMk = $this->required($row['kode_mk'] ?? null, 'kode_mk');
                $nidn = $this->required($row['nidn'] ?? null, 'nidn');

                $semester = Semester::query()->where('kode', $kodeSemester)->first();
                if (!$semester) {
                    throw new \RuntimeException('Semester tidak ditemukan: ' . $kodeSemester);
                }

                $mk = Mk::query()
                    ->where('kurikulum_id', $kurikulum->id)
                    ->where('kode', $kodeMk)
                    ->first();
                if (!$mk) {
                    throw new \RuntimeException('MK tidak ditemukan pada kurikulum ini: ' . $kodeMk);
                }

                $user = User::query()->where('nidn', $nidn)->first();
                if (!$user) {
                    throw new \RuntimeException('User dosen tidak ditemukan untuk NIDN: ' . $nidn);
                }

                $isKoordinator = in_array(Str::lower(trim((string) ($row['koordinator'] ?? ''))), ['ya', 'yes', '1', 'true'], true);

                JoinMkUser::updateOrCreate(
                    [
                        'semester_id' => $semester->id,
                        'mk_id' => $mk->id,
                        'kurikulum_id' => $kurikulum->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'koordinator' => $isKoordinator,
                    ]
                );
                return;

            case 'kontrakmks':
                $kodeSemester = $this->required($row['kode_semester'] ?? null, 'kode_semester');
                $nim = $this->required($row['nim'] ?? null, 'nim');
                $kodeMk = $this->required($row['kode_mk'] ?? null, 'kode_mk');
                $nidn = $this->required($row['nidn'] ?? null, 'nidn');

                $semester = Semester::query()->where('kode', $kodeSemester)->first();
                if (!$semester) {
                    throw new \RuntimeException('Semester tidak ditemukan: ' . $kodeSemester);
                }

                $mahasiswa = Mahasiswa::query()->where('nim', $nim)->first();
                if (!$mahasiswa) {
                    throw new \RuntimeException('Mahasiswa tidak ditemukan: ' . $nim);
                }

                $mk = Mk::query()
                    ->where('kurikulum_id', $kurikulum->id)
                    ->where('kode', $kodeMk)
                    ->first();
                if (!$mk) {
                    throw new \RuntimeException('MK tidak ditemukan pada kurikulum ini: ' . $kodeMk);
                }

                $user = User::query()->where('nidn', $nidn)->first();
                if (!$user) {
                    throw new \RuntimeException('User dosen tidak ditemukan untuk NIDN: ' . $nidn);
                }

                KontrakMk::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa->id,
                        'mk_id' => $mk->id,
                        'user_id' => $user->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'kelas' => $row['kelas'] ?? null,
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

    private function decoratePreviewRow(string $target, array $row, Kurikulum $kurikulum, ?string $semesterId): array
    {
        if (!in_array($target, ['profils', 'profil_indikators', 'cpls', 'bks', 'mks', 'joinmkusers'], true)) {
            return $row;
        }

        $status = [
            'exists' => false,
            'can_save' => true,
            'status_message' => null,
        ];

        if ($target === 'profils') {
            $kode = trim((string) ($row['kode'] ?? ''));
            $nama = trim((string) ($row['nama'] ?? ''));

            $query = Profil::query()->where('kurikulum_id', $kurikulum->id);
            if ($kode !== '') {
                $query->where('kode', $kode);
            } else {
                $query->where('nama', $nama);
            }

            $status['exists'] = $query->exists();

            return array_merge($row, $status);
        }

        if ($target === 'profil_indikators') {
            $nama = trim((string) ($row['nama'] ?? ''));
            if ($nama === '') {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Nama indikator wajib diisi',
                ]);
            }

            $profil = $this->findProfilByRow($kurikulum, $row);
            if (!$profil) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Profil tidak ditemukan',
                ]);
            }

            $status['exists'] = ProfilIndikator::query()
                ->where('profil_id', $profil->id)
                ->where('nama', $nama)
                ->exists();

            return array_merge($row, $status);
        }

        if ($target === 'cpls') {
            $kode = trim((string) ($row['kode'] ?? ''));
            if ($kode === '') {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Kode CPL wajib diisi',
                ]);
            }

            $status['exists'] = Cpl::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kode)
                ->exists();

            return array_merge($row, $status);
        }

        if ($target === 'mks') {
            $kodeMk = trim((string) ($row['kode'] ?? ''));
            if ($kodeMk === '') {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Kode MK wajib diisi',
                ]);
            }

            $status['exists'] = Mk::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kodeMk)
                ->exists();

            return array_merge($row, $status);
        }

        if ($target === 'joinmkusers') {
            $kodeSemester = trim((string) ($row['kode_semester'] ?? ''));
            $kodeMk = trim((string) ($row['kode_mk'] ?? ''));
            $nidn = trim((string) ($row['nidn'] ?? ''));

            if ($kodeSemester === '' || $kodeMk === '' || $nidn === '') {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Kode semester, kode MK, dan NIDN wajib diisi',
                ]);
            }

            $semester = Semester::query()->where('kode', $kodeSemester)->first();
            if (!$semester) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Semester tidak ditemukan: ' . $kodeSemester,
                ]);
            }

            $mk = Mk::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kodeMk)
                ->first();
            if (!$mk) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'MK tidak ditemukan: ' . $kodeMk,
                ]);
            }

            $user = User::query()->where('nidn', $nidn)->first();
            if (!$user) {
                return array_merge($row, [
                    'exists' => false,
                    'can_save' => false,
                    'status_message' => 'Dosen tidak ditemukan: ' . $nidn,
                ]);
            }

            $status['exists'] = JoinMkUser::query()
                ->where('semester_id', $semester->id)
                ->where('mk_id', $mk->id)
                ->where('kurikulum_id', $kurikulum->id)
                ->where('user_id', $user->id)
                ->exists();

            return array_merge($row, $status);
        }

        $kodeBk = trim((string) ($row['kode'] ?? ''));
        if ($kodeBk === '') {
            return array_merge($row, [
                'exists' => false,
                'can_save' => false,
                'status_message' => 'Kode BK wajib diisi',
            ]);
        }

        $status['exists'] = Bk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->where('kode', $kodeBk)
            ->exists();

        return array_merge($row, $status);
    }

    private function resolveTarget(?string $target): string
    {
        return array_key_exists((string) $target, self::TARGETS)
            ? (string) $target
            : array_key_first(self::TARGETS);
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

    private function previewSessionKey(Kurikulum $kurikulum, string $target): string
    {
        return 'import_kurikulum_master_' . $kurikulum->id . '_' . $target;
    }

    private function findProfilByRow(Kurikulum $kurikulum, array $row): ?Profil
    {
        $namaProfil = trim((string) ($row['nama_profil'] ?? ''));
        $kodeProfil = trim((string) ($row['kode_profil'] ?? ''));

        if ($kodeProfil !== '') {
            $profil = Profil::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('kode', $kodeProfil)
                ->first();
            if ($profil) {
                return $profil;
            }
        }

        if ($namaProfil !== '') {
            $profil = Profil::query()
                ->where('kurikulum_id', $kurikulum->id)
                ->where('nama', $namaProfil)
                ->first();
            if ($profil) {
                return $profil;
            }
        }

        return null;
    }
}
