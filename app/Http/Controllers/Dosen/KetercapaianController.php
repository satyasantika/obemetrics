<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\KontrakMk;
use App\Models\Mk;
use App\Models\Nilai;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KetercapaianController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read ketercapaian-mks', ['only' => ['index','show']]);
    }

    public function index(Mk $mk)
    {
        return view('obe.report.mk-ketercapaian', $this->buildNilaiPageData($mk));
    }

    public function spyderWeb(Mk $mk)
    {
        $currentUserId = auth()->id();

        $angkaToHuruf = function ($nilaiAngka) {
            if ($nilaiAngka === null) {
                return null;
            }

            $nilai = (float) $nilaiAngka;
            return match (true) {
                $nilai >= 85 => 'A',
                $nilai >= 80 => 'A-',
                $nilai >= 75 => 'B+',
                $nilai >= 70 => 'B',
                $nilai >= 65 => 'B-',
                $nilai >= 60 => 'C+',
                $nilai >= 55 => 'C',
                $nilai >= 50 => 'C-',
                $nilai >= 40 => 'D',
                default => 'E',
            };
        };

        $normalizeHuruf = function ($huruf) {
            if ($huruf === null) {
                return null;
            }

            $grade = strtoupper(trim((string) $huruf));
            return $grade !== '' ? $grade : null;
        };

        $resolveHuruf = function ($kontrak) use ($angkaToHuruf, $normalizeHuruf) {
            $grade = $normalizeHuruf($kontrak->nilai_huruf ?? null);
            if ($grade !== null) {
                return $grade;
            }

            return $angkaToHuruf($kontrak->nilai_angka ?? null);
        };

        $kontrakQuery = KontrakMk::query()
            ->with(['mahasiswa', 'mk'])
            ->where('mk_id', $mk->id)
            ->whereNotNull('mahasiswa_id');

        if ($currentUserId) {
            $kontrakQuery->where('user_id', $currentUserId);
        } else {
            $kontrakQuery->whereRaw('1 = 0');
        }

        $kontrakMks = $kontrakQuery
            ->get()
            ->filter(fn ($item) => $item->mahasiswa !== null)
            ->values();

        $mahasiswaIds = $kontrakMks->pluck('mahasiswa_id')->filter()->unique()->values();

        $nilaiByMahasiswa = Nilai::query()
            ->where('mk_id', $mk->id)
            ->whereIn('mahasiswa_id', $mahasiswaIds)
            ->with([
                'penugasan.joinSubcpmkPenugasans.subcpmk.joinCplCpmk.cpmk',
            ])
            ->get()
            ->groupBy('mahasiswa_id');

        $byMahasiswa = $kontrakMks->groupBy('mahasiswa_id');

        $detailPerMahasiswa = $byMahasiswa->map(function ($kontraks, $mahasiswaId) use ($resolveHuruf, $angkaToHuruf, $nilaiByMahasiswa) {
            $mahasiswa = $kontraks->first()->mahasiswa;

            $totalSks = (int) $kontraks->sum(fn ($kontrak) => (int) optional($kontrak->mk)->sks);
            $nilaiAngkaRerata = $kontraks
                ->whereNotNull('nilai_angka')
                ->avg('nilai_angka');
            $nilaiAngkaRerata = $nilaiAngkaRerata !== null ? round((float) $nilaiAngkaRerata, 2) : 0;

            $detailMks = $kontraks
                ->sortBy(fn ($kontrak) => optional($kontrak->mk)->nama)
                ->values()
                ->map(function ($kontrak) use ($totalSks, $resolveHuruf) {
                    $sks = (int) optional($kontrak->mk)->sks;
                    $kontribusi = $totalSks > 0 ? round(($sks / $totalSks) * 100, 2) : 0;

                    return [
                        'kode' => optional($kontrak->mk)->kode,
                        'nama' => optional($kontrak->mk)->nama,
                        'sks' => $sks,
                        'nilai' => $kontrak->nilai_angka !== null ? round((float) $kontrak->nilai_angka, 2) : null,
                        'nilai_huruf' => $resolveHuruf($kontrak),
                        'kontribusi' => $kontribusi,
                    ];
                });

            $allowedSemesterIds = $kontraks->pluck('semester_id')->filter()->unique()->flip()->all();
            $nilaiMahasiswa = collect($nilaiByMahasiswa->get($mahasiswaId) ?? [])
                ->filter(function ($nilai) use ($allowedSemesterIds) {
                    return isset($allowedSemesterIds[(string) $nilai->semester_id]);
                })
                ->values();

            $penugasanAgg = [];
            $subcpmkAgg = [];
            $cpmkAgg = [];

            foreach ($nilaiMahasiswa as $nilai) {
                $nilaiAngka = $nilai->nilai;
                if ($nilaiAngka === null) {
                    continue;
                }

                $penugasan = $nilai->penugasan;
                if (!$penugasan) {
                    continue;
                }

                $penugasanId = (string) $penugasan->id;
                if (!isset($penugasanAgg[$penugasanId])) {
                    $penugasanAgg[$penugasanId] = [
                        'kode' => $penugasan->kode,
                        'nama' => $penugasan->nama,
                        'total' => 0.0,
                        'count' => 0,
                    ];
                }
                $penugasanAgg[$penugasanId]['total'] += (float) $nilaiAngka;
                $penugasanAgg[$penugasanId]['count']++;

                foreach ($penugasan->joinSubcpmkPenugasans as $jsp) {
                    $subcpmk = $jsp->subcpmk;
                    if (!$subcpmk) {
                        continue;
                    }

                    $subcpmkId = (string) $subcpmk->id;
                    if (!isset($subcpmkAgg[$subcpmkId])) {
                        $subcpmkAgg[$subcpmkId] = [
                            'kode' => $subcpmk->kode,
                            'nama' => $subcpmk->nama,
                            'total' => 0.0,
                            'count' => 0,
                        ];
                    }
                    $subcpmkAgg[$subcpmkId]['total'] += (float) $nilaiAngka;
                    $subcpmkAgg[$subcpmkId]['count']++;

                    $cpmk = optional($subcpmk->joinCplCpmk)->cpmk;
                    if (!$cpmk) {
                        continue;
                    }

                    $cpmkId = (string) $cpmk->id;
                    if (!isset($cpmkAgg[$cpmkId])) {
                        $cpmkAgg[$cpmkId] = [
                            'kode' => $cpmk->kode,
                            'nama' => $cpmk->nama,
                            'total' => 0.0,
                            'count' => 0,
                        ];
                    }
                    $cpmkAgg[$cpmkId]['total'] += (float) $nilaiAngka;
                    $cpmkAgg[$cpmkId]['count']++;
                }
            }

            $toScores = function ($agg) {
                return collect($agg)
                    ->map(function ($item) {
                        $avg = ($item['count'] ?? 0) > 0
                            ? round(((float) $item['total']) / ((int) $item['count']), 2)
                            : 0;

                        return [
                            'kode' => $item['kode'] ?? '-',
                            'nama' => $item['nama'] ?? '-',
                            'nilai' => $avg,
                        ];
                    })
                    ->sortBy('kode')
                    ->values();
            };

            return [
                'mahasiswa' => [
                    'id' => $mahasiswaId,
                    'nim' => $mahasiswa->nim,
                    'nama' => $mahasiswa->nama,
                    'nilai_angka' => $nilaiAngkaRerata,
                    'nilai_huruf' => $angkaToHuruf($nilaiAngkaRerata),
                ],
                'detail_mks' => $detailMks->values(),
                'cpmk_scores' => $toScores($cpmkAgg),
                'subcpmk_scores' => $toScores($subcpmkAgg),
                'penugasan_scores' => $toScores($penugasanAgg),
            ];
        });

        $mahasiswas = $detailPerMahasiswa
            ->pluck('mahasiswa')
            ->sortBy('nim')
            ->values();

        return view('obe.report.laporan-mk')
            ->with('mk', $mk)
            ->with('mahasiswas', $mahasiswas)
            ->with('detailPerMahasiswa', $detailPerMahasiswa);
    }

    private function buildNilaiPageData(Mk $mk): array
    {
        $currentUserId = auth()->id();

        $semesters = Semester::query()->orderBy('kode')->get();
        $activeSemester = $semesters->firstWhere('status_aktif', true) ?? $semesters->first();
        $defaultSemesterId = $activeSemester?->id;

        $kontrakMksQuery = $mk->kontrakMks()
            ->with(['mahasiswa', 'semester'])
            ->whereNotNull('mahasiswa_id')
            ->whereNotNull('semester_id');

        if ($currentUserId) {
            $kontrakMksQuery->where('user_id', $currentUserId);
        }

        $kontrakMks = $kontrakMksQuery
            ->get()
            ->filter(fn ($kontrakMk) => $kontrakMk->mahasiswa !== null)
            ->sortBy(fn ($kontrakMk) => Str::lower((string) ($kontrakMk->mahasiswa->nim ?? '')))
            ->values();

        $kelasList = $kontrakMks
            ->map(function ($item) {
                $kelas = trim((string) ($item->kelas ?? ''));
                return $kelas !== '' ? $kelas : 'Tanpa Kelas';
            })
            ->unique()
            ->sort()
            ->values();

        if ($kelasList->isNotEmpty()) {
            $kelasList = collect(['__SEMUA_KELAS__'])->merge($kelasList)->values();
        }

        $mappingRows = DB::table('join_subcpmk_penugasans as jsp')
            ->join('subcpmks as s', 's.id', '=', 'jsp.subcpmk_id')
            ->join('join_cpl_cpmks as jcc', 'jcc.id', '=', 's.join_cpl_cpmk_id')
            ->join('cpmks as cpmk', 'cpmk.id', '=', 'jcc.cpmk_id')
            ->join('join_cpl_bks as jcb', 'jcb.id', '=', 'jcc.join_cpl_bk_id')
            ->join('cpls as cpl', 'cpl.id', '=', 'jcb.cpl_id')
            ->join('penugasans as p', 'p.id', '=', 'jsp.penugasan_id')
            ->leftJoin('evaluasis as e', 'e.id', '=', 'p.evaluasi_id')
            ->where('jsp.mk_id', $mk->id)
            ->select(
                'cpl.id as cpl_id',
                'cpl.kode as cpl_kode',
                'cpl.nama as cpl_nama',
                'cpmk.id as cpmk_id',
                'cpmk.kode as cpmk_kode',
                'cpmk.nama as cpmk_nama',
                's.id as subcpmk_id',
                's.kode as subcpmk_kode',
                's.nama as subcpmk_nama',
                's.indikator as indikator',
                'p.id as penugasan_id',
                'p.kode as penugasan_kode',
                'p.bobot as penugasan_bobot',
                'e.kategori as evaluasi_kategori',
                'jsp.bobot as indikator_bobot'
            )
            ->orderBy('cpl.kode')
            ->orderBy('cpmk.kode')
            ->orderBy('s.kode')
            ->orderBy('p.kode')
            ->get();

        $hierarchyMap = [];
        foreach ($mappingRows as $row) {
            $cplId = (string) $row->cpl_id;
            $cpmkId = (string) $row->cpmk_id;
            $subcpmkId = (string) $row->subcpmk_id;

            if (!isset($hierarchyMap[$cplId])) {
                $hierarchyMap[$cplId] = [
                    'id' => $cplId,
                    'kode' => $row->cpl_kode,
                    'nama' => $row->cpl_nama,
                    'cpmks' => [],
                ];
            }

            if (!isset($hierarchyMap[$cplId]['cpmks'][$cpmkId])) {
                $hierarchyMap[$cplId]['cpmks'][$cpmkId] = [
                    'id' => $cpmkId,
                    'kode' => $row->cpmk_kode,
                    'nama' => $row->cpmk_nama,
                    'subcpmks' => [],
                ];
            }

            if (!isset($hierarchyMap[$cplId]['cpmks'][$cpmkId]['subcpmks'][$subcpmkId])) {
                $hierarchyMap[$cplId]['cpmks'][$cpmkId]['subcpmks'][$subcpmkId] = [
                    'id' => $subcpmkId,
                    'kode' => $row->subcpmk_kode,
                    'nama' => $row->subcpmk_nama,
                    'indikator' => $row->indikator,
                    'sources' => [],
                ];
            }

            $indikatorBobot = (float) ($row->indikator_bobot ?? 0);
            $penugasanBobot = (float) ($row->penugasan_bobot ?? 0);
            $pk = $indikatorBobot * $penugasanBobot;
            $hierarchyMap[$cplId]['cpmks'][$cpmkId]['subcpmks'][$subcpmkId]['sources'][] = [
                'penugasan_id' => (string) $row->penugasan_id,
                'kode' => $row->penugasan_kode,
                'kategori' => $row->evaluasi_kategori,
                'pk' => round($pk, 2),
            ];
        }

        $hierarchyData = collect($hierarchyMap)->map(function ($cpl) {
            $cpl['cpmks'] = collect($cpl['cpmks'])->map(function ($cpmk) {
                $cpmk['subcpmks'] = collect($cpmk['subcpmks'])->values()->all();
                return $cpmk;
            })->values()->all();
            return $cpl;
        })->values();

        $rnRows = DB::table('kontrak_mks as km')
            ->join('nilais as n', function ($join) {
                $join->on('n.mk_id', '=', 'km.mk_id')
                    ->on('n.mahasiswa_id', '=', 'km.mahasiswa_id')
                    ->on('n.semester_id', '=', 'km.semester_id');
            })
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, km.semester_id, n.penugasan_id, AVG(n.nilai) as avg_nilai")
            ->groupBy('kelas_key', 'km.semester_id', 'n.penugasan_id')
            ->get();

        if ($currentUserId) {
            $rnRows = DB::table('kontrak_mks as km')
                ->join('nilais as n', function ($join) {
                    $join->on('n.mk_id', '=', 'km.mk_id')
                        ->on('n.mahasiswa_id', '=', 'km.mahasiswa_id')
                        ->on('n.semester_id', '=', 'km.semester_id');
                })
                ->where('km.mk_id', $mk->id)
                ->where('km.user_id', $currentUserId)
                ->whereNotNull('km.mahasiswa_id')
                ->whereNotNull('km.semester_id')
                ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, km.semester_id, n.penugasan_id, AVG(n.nilai) as avg_nilai")
                ->groupBy('kelas_key', 'km.semester_id', 'n.penugasan_id')
                ->get();
        }

        $rnData = [];
        foreach ($rnRows as $row) {
            $kelasKey = (string) $row->kelas_key;
            $semesterKey = (string) $row->semester_id;
            $penugasanKey = (string) $row->penugasan_id;
            $avgNilai = round((float) $row->avg_nilai, 2);

            $rnData[$kelasKey][$semesterKey][$penugasanKey] = $avgNilai;
        }

        $rnAllQuery = DB::table('kontrak_mks as km')
            ->join('nilais as n', function ($join) {
                $join->on('n.mk_id', '=', 'km.mk_id')
                    ->on('n.mahasiswa_id', '=', 'km.mahasiswa_id')
                    ->on('n.semester_id', '=', 'km.semester_id');
            })
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id');

        if ($currentUserId) {
            $rnAllQuery->where('km.user_id', $currentUserId);
        }

        $rnAllRows = $rnAllQuery
            ->selectRaw('km.semester_id, n.penugasan_id, AVG(n.nilai) as avg_nilai')
            ->groupBy('km.semester_id', 'n.penugasan_id')
            ->get();

        foreach ($rnAllRows as $row) {
            $semesterKey = (string) $row->semester_id;
            $penugasanKey = (string) $row->penugasan_id;
            $rnData['__SEMUA_KELAS__'][$semesterKey][$penugasanKey] = round((float) $row->avg_nilai, 2);
        }

        return compact(
            'mk',
            'semesters',
            'kelasList',
            'defaultSemesterId',
            'hierarchyData',
            'rnData'
        );
    }
}
