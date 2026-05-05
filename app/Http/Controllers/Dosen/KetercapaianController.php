<?php

namespace App\Http\Controllers\Dosen;

use App\Actions\ResolveMkSemester;
use App\Http\Controllers\Controller;
use App\Models\KontrakMk;
use App\Models\Mk;
use App\Models\Nilai;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
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

        $semestersForFilter = $mk->kontrakMks()
            ->whereNotNull('semester_id')
            ->when($currentUserId, fn ($q) => $q->where('user_id', $currentUserId))
            ->with('semester')
            ->get()
            ->pluck('semester')
            ->filter()
            ->unique('id')
            ->sortByDesc('status_aktif')
            ->sortByDesc('kode')
            ->values();

        [$selectedSemester, $selectedSemesterId] = ResolveMkSemester::resolve($mk, request()->query('semester_id'), $semestersForFilter);

        $kontrakQuery = KontrakMk::query()
            ->with(['mahasiswa', 'mk'])
            ->where('mk_id', $mk->id)
            ->whereNotNull('mahasiswa_id');

        if ($currentUserId) {
            $kontrakQuery->where('user_id', $currentUserId);
        } else {
            $kontrakQuery->whereRaw('1 = 0');
        }

        if ($selectedSemesterId) {
            $kontrakQuery->where('semester_id', $selectedSemesterId);
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

        $baselinePenugasanAgg = $mk->penugasans()
            ->select('id', 'kode', 'nama')
            ->get()
            ->mapWithKeys(function ($penugasan) {
                return [
                    (string) $penugasan->id => [
                        'kode' => $penugasan->kode,
                        'nama' => $penugasan->nama,
                        'total' => 0.0,
                        'count' => 0,
                    ],
                ];
            })
            ->all();

        $baselineSubcpmkAgg = DB::table('subcpmks as s')
            ->join('join_cpl_cpmks as jcc', 'jcc.id', '=', 's.join_cpl_cpmk_id')
            ->where('jcc.mk_id', $mk->id)
            ->select('s.id', 's.kode', 's.nama')
            ->orderBy('s.kode')
            ->get()
            ->mapWithKeys(function ($subcpmk) {
                return [
                    (string) $subcpmk->id => [
                        'kode' => $subcpmk->kode,
                        'nama' => $subcpmk->nama,
                        'total' => 0.0,
                        'count' => 0,
                    ],
                ];
            })
            ->all();

        $baselineCpmkAgg = $mk->cpmks()
            ->select('id', 'kode', 'nama')
            ->get()
            ->mapWithKeys(function ($cpmk) {
                return [
                    (string) $cpmk->id => [
                        'kode' => $cpmk->kode,
                        'nama' => $cpmk->nama,
                        'total' => 0.0,
                        'count' => 0,
                    ],
                ];
            })
            ->all();

        $byMahasiswa = $kontrakMks->groupBy('mahasiswa_id');

        $detailPerMahasiswa = $byMahasiswa->map(function ($kontraks, $mahasiswaId) use ($resolveHuruf, $angkaToHuruf, $nilaiByMahasiswa, $baselinePenugasanAgg, $baselineSubcpmkAgg, $baselineCpmkAgg) {
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

            $penugasanAgg = $baselinePenugasanAgg;
            $subcpmkAgg = $baselineSubcpmkAgg;
            $cpmkAgg = $baselineCpmkAgg;

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
                        $isAssessed = ((int) ($item['count'] ?? 0)) > 0;
                        $avg = $isAssessed
                            ? round(((float) $item['total']) / ((int) $item['count']), 2)
                            : null;

                        return [
                            'kode' => $item['kode'] ?? '-',
                            'nama' => $item['nama'] ?? '-',
                            'nilai' => $avg,
                            'dinilai' => $isAssessed,
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
            ->with('semesters', $semestersForFilter)
            ->with('selectedSemesterId', (string) ($selectedSemesterId ?? ''))
            ->with('mahasiswas', $mahasiswas)
            ->with('detailPerMahasiswa', $detailPerMahasiswa);
    }

    private function buildNilaiPageData(Mk $mk): array
    {
        $currentUserId = auth()->id();

        $semesters = $mk->kontrakMks()
            ->whereNotNull('semester_id')
            ->when($currentUserId, fn ($q) => $q->where('user_id', $currentUserId))
            ->with('semester')
            ->get()
            ->pluck('semester')
            ->filter()
            ->unique('id')
            ->sortByDesc('status_aktif')
            ->sortByDesc('kode')
            ->values();
        [, $defaultSemesterId] = ResolveMkSemester::resolve($mk, null, $semesters);

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

        $kelasPerSemester = $kontrakMks
            ->groupBy('semester_id')
            ->mapWithKeys(function ($items, $semId) {
                $kelas = $items
                    ->map(fn ($item) => trim((string) ($item->kelas ?? '')) ?: 'Tanpa Kelas')
                    ->unique()->sort()->values();
                return [(string) $semId => collect(['__SEMUA_KELAS__'])->merge($kelas)->values()];
            })
            ->all();

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
                'jsp.bobot as indikator_bobot',
                's.semester_id as subcpmk_semester_id'
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
                'semester_id' => $row->subcpmk_semester_id ? (string) $row->subcpmk_semester_id : null,
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
            'kelasPerSemester',
            'defaultSemesterId',
            'hierarchyData',
            'rnData'
        );
    }

    public function laporan(Mk $mk)
    {
        $baseData = $this->buildNilaiPageData($mk);
        $currentUserId = auth()->id();

        $semesters = $mk->kontrakMks()
            ->whereNotNull('semester_id')
            ->when($currentUserId, fn ($q) => $q->where('user_id', $currentUserId))
            ->with('semester')
            ->get()
            ->pluck('semester')
            ->filter()
            ->unique('id')
            ->sortByDesc('status_aktif')
            ->sortByDesc('kode')
            ->values();

        [$semester, $semesterId] = ResolveMkSemester::resolve($mk, request()->query('semester_id'), $semesters);

        $targetKelulusan = (float) ($mk->kurikulum->target_capaian_lulusan ?? 100);
        $gradeOrder = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'E'];

        $assessmentPlanRowsQuery = DB::table('penugasans as p')
            ->leftJoin('evaluasis as e', 'e.id', '=', 'p.evaluasi_id')
            ->leftJoin('join_subcpmk_penugasans as jsp', function ($join) {
                $join->on('jsp.penugasan_id', '=', 'p.id')
                    ->on('jsp.mk_id', '=', 'p.mk_id');
            })
            ->leftJoin('subcpmks as s', 's.id', '=', 'jsp.subcpmk_id')
            ->leftJoin('join_cpl_cpmks as jcc', 'jcc.id', '=', 's.join_cpl_cpmk_id')
            ->leftJoin('cpmks as cpmk', 'cpmk.id', '=', 'jcc.cpmk_id')
            ->leftJoin('join_cpl_bks as jcb', 'jcb.id', '=', 'jcc.join_cpl_bk_id')
            ->leftJoin('cpls as cpl', 'cpl.id', '=', 'jcb.cpl_id')
            ->where('p.mk_id', $mk->id)
            ->selectRaw("p.id as penugasan_id, p.kode as penugasan_kode, p.nama as penugasan_nama, COALESCE(p.bobot, 0) as bobot, COALESCE(NULLIF(TRIM(e.workcloud), ''), NULLIF(TRIM(e.kategori), ''), NULLIF(TRIM(e.kode), ''), p.kode) as workcloud, GROUP_CONCAT(DISTINCT CONCAT(cpl.kode, ' - ', cpl.nama) ORDER BY cpl.kode SEPARATOR '||') as cpl_items, GROUP_CONCAT(DISTINCT CONCAT(cpmk.kode, ' - ', cpmk.nama) ORDER BY cpmk.kode SEPARATOR '||') as cpmk_items")
            ->groupBy('p.id', 'p.kode', 'p.nama', 'p.bobot', 'e.workcloud', 'e.kategori', 'e.kode')
            ->orderBy('p.kode');

        if ($semesterId) {
            $assessmentPlanRowsQuery->where(function ($query) use ($semesterId) {
                $query->where('p.semester_id', $semesterId)
                    ->orWhereNull('p.semester_id');
            });
        }

        $assessmentPlan = $assessmentPlanRowsQuery
            ->get()
            ->map(function ($row) {
                return [
                    'penugasan_id' => (string) $row->penugasan_id,
                    'workcloud' => (string) $row->workcloud,
                    'penugasan' => trim((string) ($row->penugasan_kode . ' - ' . $row->penugasan_nama)),
                    'bobot' => round((float) $row->bobot, 2),
                    'cpl_items' => collect(explode('||', (string) ($row->cpl_items ?? '')))->filter()->unique()->values()->all(),
                    'cpmk_items' => collect(explode('||', (string) ($row->cpmk_items ?? '')))->filter()->unique()->values()->all(),
                ];
            })
            ->values();

        $nilaiColumns = $assessmentPlan
            ->map(function ($item) {
                return [
                    'penugasan_id' => $item['penugasan_id'],
                    'label' => $item['workcloud'] ?: '-',
                    'asesmen' => $item['penugasan'] ?? '-',
                    'cpl_label' => collect($item['cpl_items'])->map(function ($text) {
                        return explode(' - ', (string) $text)[0] ?? '-';
                    })->filter()->unique()->implode(', '),
                    'bobot' => $item['bobot'],
                ];
            })
            ->values();

        $kontrakRowsQuery = DB::table('kontrak_mks as km')
            ->join('mahasiswas as m', 'm.id', '=', 'km.mahasiswa_id')
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, km.mahasiswa_id, m.nim, m.nama, km.nilai_angka, km.nilai_huruf")
            ->orderBy('m.nim');

        if ($semesterId) {
            $kontrakRowsQuery->where('km.semester_id', $semesterId);
        }
        if ($currentUserId) {
            $kontrakRowsQuery->where('km.user_id', $currentUserId);
        }

        $kontrakRows = collect($kontrakRowsQuery->get());

        $kelasList = $kontrakRows->pluck('kelas_key')->unique()->sort()->values();

        $nilaiRowsQuery = DB::table('kontrak_mks as km')
            ->join('nilais as n', function ($join) {
                $join->on('n.mk_id', '=', 'km.mk_id')
                    ->on('n.mahasiswa_id', '=', 'km.mahasiswa_id')
                    ->on('n.semester_id', '=', 'km.semester_id');
            })
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, km.mahasiswa_id, n.penugasan_id, AVG(n.nilai) as avg_nilai")
            ->groupBy('kelas_key', 'km.mahasiswa_id', 'n.penugasan_id');

        if ($semesterId) {
            $nilaiRowsQuery->where('km.semester_id', $semesterId);
        }
        if ($currentUserId) {
            $nilaiRowsQuery->where('km.user_id', $currentUserId);
        }

        $nilaiRows = $nilaiRowsQuery->get();
        $nilaiByClassMahasiswa = [];
        foreach ($nilaiRows as $row) {
            $kelasKey = (string) $row->kelas_key;
            $mahasiswaId = (string) $row->mahasiswa_id;
            $penugasanId = (string) $row->penugasan_id;
            $nilaiByClassMahasiswa[$kelasKey][$mahasiswaId][$penugasanId] = round((float) $row->avg_nilai, 2);
        }

        $penugasanCplMapQuery = DB::table('join_subcpmk_penugasans as jsp')
            ->join('subcpmks as s', 's.id', '=', 'jsp.subcpmk_id')
            ->join('join_cpl_cpmks as jcc', 'jcc.id', '=', 's.join_cpl_cpmk_id')
            ->join('join_cpl_bks as jcb', 'jcb.id', '=', 'jcc.join_cpl_bk_id')
            ->join('cpls as c', 'c.id', '=', 'jcb.cpl_id')
            ->where('jsp.mk_id', $mk->id)
            ->select('jsp.penugasan_id', 'c.id as cpl_id')
            ->distinct();

        $cplRows = DB::table('cpls as c')
            ->joinSub(
                DB::query()->fromSub($penugasanCplMapQuery, 'pcm')
                    ->select('pcm.cpl_id')
                    ->distinct(),
                'used_cpl',
                function ($join) {
                    $join->on('used_cpl.cpl_id', '=', 'c.id');
                }
            )
            ->select('c.id', 'c.kode', 'c.nama')
            ->orderBy('c.kode')
            ->get();

        $achievementRowsQuery = DB::table('kontrak_mks as km')
            ->join('nilais as n', function ($join) {
                $join->on('n.mk_id', '=', 'km.mk_id')
                    ->on('n.mahasiswa_id', '=', 'km.mahasiswa_id')
                    ->on('n.semester_id', '=', 'km.semester_id');
            })
            ->joinSub($penugasanCplMapQuery, 'pcm', function ($join) {
                $join->on('pcm.penugasan_id', '=', 'n.penugasan_id');
            })
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, pcm.cpl_id, AVG(n.nilai) as avg_capaian")
            ->groupBy('kelas_key', 'pcm.cpl_id');

        if ($semesterId) {
            $achievementRowsQuery->where('km.semester_id', $semesterId);
        }
        if ($currentUserId) {
            $achievementRowsQuery->where('km.user_id', $currentUserId);
        }

        $achievementRows = $achievementRowsQuery->get();
        $achievementData = [];
        foreach ($achievementRows as $row) {
            $achievementData[(string) $row->kelas_key][(string) $row->cpl_id] = round((float) $row->avg_capaian, 2);
        }

        $componentRowsQuery = DB::table('penugasans as p')
            ->join('evaluasis as e', 'e.id', '=', 'p.evaluasi_id')
            ->join('join_subcpmk_penugasans as jsp', function ($join) {
                $join->on('jsp.penugasan_id', '=', 'p.id');
            })
            ->join('subcpmks as s', 's.id', '=', 'jsp.subcpmk_id')
            ->join('join_cpl_cpmks as jcc', 'jcc.id', '=', 's.join_cpl_cpmk_id')
            ->join('join_cpl_bks as jcb', 'jcb.id', '=', 'jcc.join_cpl_bk_id')
            ->join('cpls as c', 'c.id', '=', 'jcb.cpl_id')
            ->where('p.mk_id', $mk->id)
            ->selectRaw("c.id as cpl_id, COALESCE(NULLIF(TRIM(e.workcloud), ''), NULLIF(TRIM(e.kategori), ''), NULLIF(TRIM(e.kode), '')) as workcloud, COALESCE(SUM(COALESCE(jsp.bobot,0) * COALESCE(p.bobot,0)),0) as total_bobot")
            ->whereNotNull(DB::raw("COALESCE(NULLIF(TRIM(e.workcloud), ''), NULLIF(TRIM(e.kategori), ''), NULLIF(TRIM(e.kode), ''))"))
            ->groupBy('c.id', 'e.workcloud', 'e.kategori', 'e.kode')
            ->orderBy(DB::raw("COALESCE(NULLIF(TRIM(e.workcloud), ''), NULLIF(TRIM(e.kategori), ''), NULLIF(TRIM(e.kode), ''))"));

        if ($semesterId) {
            $componentRowsQuery->where(function ($query) use ($semesterId) {
                $query->where('p.semester_id', $semesterId)
                    ->orWhereNull('p.semester_id');
            });
        }

        $componentsDataByCpl = [];
        foreach ($componentRowsQuery->get() as $row) {
            $componentsDataByCpl[(string) $row->cpl_id][] = [
                'workcloud' => (string) $row->workcloud,
                'bobot' => round((float) $row->total_bobot / 100, 2),
            ];
        }

        $totalsQuery = DB::table('kontrak_mks as km')
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, COUNT(*) as total_mahasiswa")
            ->groupBy('kelas_key');

        if ($semesterId) {
            $totalsQuery->where('km.semester_id', $semesterId);
        }
        if ($currentUserId) {
            $totalsQuery->where('km.user_id', $currentUserId);
        }

        $totalsByClass = [];
        foreach ($totalsQuery->get() as $row) {
            $totalsByClass[(string) $row->kelas_key] = (int) $row->total_mahasiswa;
        }

        $gradeCountQuery = DB::table('kontrak_mks as km')
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->whereNotNull('km.nilai_huruf')
            ->whereIn('km.nilai_huruf', $gradeOrder)
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, km.nilai_huruf, COUNT(*) as jumlah")
            ->groupBy('kelas_key', 'km.nilai_huruf');

        if ($semesterId) {
            $gradeCountQuery->where('km.semester_id', $semesterId);
        }
        if ($currentUserId) {
            $gradeCountQuery->where('km.user_id', $currentUserId);
        }

        $gradeCountsByClass = [];
        foreach ($gradeCountQuery->get() as $row) {
            $gradeCountsByClass[(string) $row->kelas_key][(string) $row->nilai_huruf] = (int) $row->jumlah;
        }

        $hierarchyData = collect($baseData['hierarchyData'] ?? [])
            ->map(function ($cpl) use ($semesterId) {
                $cpl['cpmks'] = collect($cpl['cpmks'] ?? [])
                    ->map(function ($cpmk) use ($semesterId) {
                        $cpmk['subcpmks'] = collect($cpmk['subcpmks'] ?? [])
                            ->map(function ($subcpmk) use ($semesterId) {
                                $subcpmk['sources'] = collect($subcpmk['sources'] ?? [])
                                    ->filter(fn ($src) => !$semesterId || ($src['semester_id'] ?? null) === (string) $semesterId)
                                    ->values()
                                    ->all();
                                return $subcpmk;
                            })
                            ->filter(fn ($s) => count($s['sources']) > 0)
                            ->values()
                            ->all();
                        return $cpmk;
                    })
                    ->filter(fn ($c) => count($c['subcpmks']) > 0)
                    ->values()
                    ->all();
                return $cpl;
            })
            ->filter(fn ($cpl) => count($cpl['cpmks']) > 0)
            ->values()
            ->all();

        $assessmentBobotByPenugasan = $assessmentPlan
            ->mapWithKeys(function ($item) {
                return [(string) ($item['penugasan_id'] ?? '') => (float) ($item['bobot'] ?? 0)];
            })
            ->all();

        $angkaToHuruf = function ($nilaiAngka) {
            if ($nilaiAngka === null) {
                return null;
            }

            $nilai = (float) $nilaiAngka;
            return match (true) {
                $nilai >= 85 => 'A',
                $nilai >= 77 => 'A-',
                $nilai >= 68.5 => 'B+',
                $nilai >= 61 => 'B',
                $nilai >= 53 => 'B-',
                $nilai >= 45 => 'C+',
                $nilai >= 37 => 'C',
                $nilai >= 29 => 'C-',
                $nilai >= 21 => 'D',
                default => 'E',
            };
        };

        $reportByClass = [];
        foreach ($kelasList as $kelas) {
            $kelasKey = (string) $kelas;

            $studentRows = $kontrakRows
                ->where('kelas_key', $kelasKey)
                ->values()
                ->map(function ($item) use ($nilaiColumns, $nilaiByClassMahasiswa, $kelasKey, $assessmentBobotByPenugasan, $angkaToHuruf) {
                    $mahasiswaId = (string) $item->mahasiswa_id;
                    $scores = [];
                    $weightedSum = 0.0;
                    $weightedDenom = 0.0;

                    foreach ($nilaiColumns as $column) {
                        $penugasanId = (string) $column['penugasan_id'];
                        $nilai = $nilaiByClassMahasiswa[$kelasKey][$mahasiswaId][$penugasanId] ?? null;
                        $scores[$penugasanId] = $nilai;

                        if ($nilai !== null) {
                            $bobot = (float) ($assessmentBobotByPenugasan[$penugasanId] ?? 0);
                            if ($bobot > 0) {
                                $weightedSum += ((float) $nilai) * $bobot;
                                $weightedDenom += $bobot;
                            }
                        }
                    }

                    $nilaiAkhir = $weightedDenom > 0 ? round($weightedSum / $weightedDenom, 2) : null;

                    return [
                        'nim' => $item->nim,
                        'nama' => $item->nama,
                        'nilai_akhir' => $nilaiAkhir,
                        'nilai_huruf' => $angkaToHuruf($nilaiAkhir),
                        'scores' => $scores,
                    ];
                })
                ->all();

            $avgPerColumn = [];
            foreach ($nilaiColumns as $column) {
                $penugasanId = (string) $column['penugasan_id'];
                $values = collect($studentRows)
                    ->map(fn ($row) => $row['scores'][$penugasanId] ?? null)
                    ->filter(fn ($value) => $value !== null)
                    ->values();

                $avgPerColumn[$penugasanId] = $values->isNotEmpty() ? round((float) $values->avg(), 2) : null;
            }

            $averageFinalScore = collect($studentRows)
                ->map(fn ($row) => $row['nilai_akhir'])
                ->filter(fn ($value) => $value !== null)
                ->avg();

            $rnMap = $avgPerColumn;
            $ketercapaianDetailRows = collect($hierarchyData)
                ->flatMap(function ($cpl) use ($rnMap) {
                    $cpmks = collect($cpl['cpmks'] ?? []);

                    return $cpmks->flatMap(function ($cpmk) use ($cpl, $rnMap) {
                        $subcpmks = collect($cpmk['subcpmks'] ?? []);

                        return $subcpmks->flatMap(function ($subcpmk) use ($cpl, $cpmk, $rnMap) {
                            $sources = collect($subcpmk['sources'] ?? []);

                            return $sources->map(function ($source) use ($cpl, $cpmk, $subcpmk, $rnMap) {
                                $pk = ((float) ($source['pk'] ?? 0)) / 100;
                                $rn = (float) ($rnMap[(string) ($source['penugasan_id'] ?? '')] ?? 0);
                                $pkrn = ($pk * $rn) / 100;

                                $sourceLabel = trim((string) ($source['kode'] ?? '-'));
                                $sourceKategori = trim((string) ($source['kategori'] ?? ''));
                                if ($sourceKategori !== '' && $sourceKategori !== '-') {
                                    $sourceLabel .= ' - ' . $sourceKategori;
                                }

                                $cplCode = (string) ($cpl['kode'] ?? '-');
                                $cpmkCode = (string) ($cpmk['kode'] ?? '-');
                                $subcpmkCode = (string) ($subcpmk['kode'] ?? '-');

                                $cplLabel = $cplCode . ' - ' . (string) ($cpl['nama'] ?? '-');
                                $cpmkLabel = $cpmkCode . ' - ' . (string) ($cpmk['nama'] ?? '-');
                                $subcpmkLabel = $subcpmkCode . ' - ' . (string) ($subcpmk['nama'] ?? '-');

                                return [
                                    'cpl_key' => (string) ($cpl['kode'] ?? '-') . '|' . (string) ($cpl['nama'] ?? '-'),
                                    'cpl_code' => $cplCode,
                                    'cpl' => $cplLabel,
                                    'cpmk_key' => (string) ($cpmk['kode'] ?? '-') . '|' . (string) ($cpmk['nama'] ?? '-'),
                                    'cpmk_code' => $cpmkCode,
                                    'cpmk' => $cpmkLabel,
                                    'subcpmk_key' => (string) ($subcpmk['kode'] ?? '-') . '|' . (string) ($subcpmk['nama'] ?? '-'),
                                    'subcpmk_code' => $subcpmkCode,
                                    'subcpmk' => $subcpmkLabel,
                                    'indikator' => (string) ($subcpmk['indikator'] ?? '-'),
                                    'source' => $sourceLabel !== '' ? $sourceLabel : '-',
                                    'pk_raw' => $pk,
                                    'pk' => round($pk, 2),
                                    'rn_raw' => $rn,
                                    'rn' => round($rn, 2),
                                    'pkrn_raw' => $pkrn,
                                    'pkrn' => round($pkrn, 2),
                                ];
                            });
                        });
                    });
                })
                ->values()
                ->all();

            $detailCollection = collect($ketercapaianDetailRows);

            $cplStats = $detailCollection
                ->groupBy('cpl_code')
                ->map(function ($rows) {
                    $pk = (float) $rows->sum('pk_raw');
                    $pkrn = (float) $rows->sum('pkrn_raw');
                    $ratio = $pk > 0 ? ($pkrn / $pk) * 100 : null;

                    return [
                        'ratio' => $ratio !== null ? round($ratio, 2) : null,
                    ];
                })
                ->all();

            $cpmkStats = $detailCollection
                ->groupBy(function ($row) {
                    return ($row['cpl_code'] ?? '-') . '||' . ($row['cpmk_code'] ?? '-');
                })
                ->map(function ($rows) {
                    $pk = (float) $rows->sum('pk_raw');
                    $pkrn = (float) $rows->sum('pkrn_raw');
                    $ratio = $pk > 0 ? ($pkrn / $pk) * 100 : null;
                    return $ratio !== null ? round($ratio, 2) : null;
                })
                ->all();

            $subcpmkStats = $detailCollection
                ->groupBy(function ($row) {
                    return ($row['cpl_code'] ?? '-') . '||' . ($row['cpmk_code'] ?? '-') . '||' . ($row['subcpmk_code'] ?? '-');
                })
                ->map(function ($rows) {
                    $pk = (float) $rows->sum('pk_raw');
                    $pkrn = (float) $rows->sum('pkrn_raw');
                    $ratio = $pk > 0 ? ($pkrn / $pk) * 100 : null;
                    return $ratio !== null ? round($ratio, 2) : null;
                })
                ->all();

            $ketercapaianDetailRows = $detailCollection
                ->map(function ($row) use ($cplStats, $cpmkStats, $subcpmkStats) {
                    $cplCode = (string) ($row['cpl_code'] ?? '-');
                    $cpmkKey = (string) ($row['cpl_code'] ?? '-') . '||' . (string) ($row['cpmk_code'] ?? '-');
                    $subcpmkKey = (string) ($row['cpl_code'] ?? '-') . '||' . (string) ($row['cpmk_code'] ?? '-') . '||' . (string) ($row['subcpmk_code'] ?? '-');

                    $row['cpl_ratio'] = $cplStats[$cplCode]['ratio'] ?? null;
                    $row['cpmk_ratio'] = $cpmkStats[$cpmkKey] ?? null;
                    $row['subcpmk_ratio'] = $subcpmkStats[$subcpmkKey] ?? null;

                    return $row;
                })
                ->values()
                ->all();

            $ketercapaianRows = $cplRows->map(function ($cpl) use ($cplStats, $targetKelulusan) {
                $kode = (string) ($cpl->kode ?? '');
                $ratio = $cplStats[$kode]['ratio'] ?? null;

                return [
                    'kode' => $cpl->kode,
                    'nama' => $cpl->nama,
                    'pk_total' => null,
                    'pk_rn' => null,
                    'ratio' => $ratio,
                    'status' => $ratio === null ? null : ((float) $ratio >= $targetKelulusan),
                ];
            })->all();

            $achievementTableRows = $cplRows->map(function ($cpl) use ($componentsDataByCpl, $targetKelulusan, $cplStats) {
                $kode = (string) ($cpl->kode ?? '');
                $avg = $cplStats[$kode]['ratio'] ?? null;
                $components = collect($componentsDataByCpl[(string) $cpl->id] ?? [])
                    ->map(function ($item) {
                        return trim(($item['workcloud'] ?? '-') . ' (' . number_format((float) ($item['bobot'] ?? 0), 2) . '%)');
                    })
                    ->values()
                    ->all();

                return [
                    'kode' => $cpl->kode,
                    'nama' => $cpl->nama,
                    'components' => $components,
                    'avg' => $avg,
                    'status' => $avg === null ? null : ((float) $avg >= $targetKelulusan),
                ];
            })->all();

            $counts = array_fill_keys($gradeOrder, 0);
            foreach ($studentRows as $studentRow) {
                $grade = (string) ($studentRow['nilai_huruf'] ?? '');
                if (isset($counts[$grade])) {
                    $counts[$grade]++;
                }
            }

            $reportByClass[$kelasKey] = [
                'assessment_plan' => $assessmentPlan->all(),
                'nilai_columns' => $nilaiColumns->all(),
                'nilai_rows' => $studentRows,
                'avg_per_column' => $avgPerColumn,
                'avg_final_score' => $averageFinalScore !== null ? round((float) $averageFinalScore, 2) : null,
                'achievement_rows' => $achievementTableRows,
                'ketercapaian_rows' => $ketercapaianRows,
                'ketercapaian_detail_rows' => $ketercapaianDetailRows,
                'grade_distribution' => [
                    'total' => count($studentRows),
                    'counts' => $counts,
                ],
            ];
        }

        return view('obe.report.pdf-workcloud-mk', [
            'mk' => $mk,
            'semester' => $semester,
            'semesters' => $semesters,
            'selectedSemesterId' => (string) ($semesterId ?? ''),
            'kelasList' => $kelasList,
            'targetKelulusan' => $targetKelulusan,
            'gradeOrder' => $gradeOrder,
            'reportByClass' => $reportByClass,
        ]);
    }

    public function downloadLaporanPdf(Mk $mk)
    {
        $kelasRequested = trim((string) request()->query('kelas', ''));
        $mode = trim((string) request()->query('mode', 'view'));
        if ($kelasRequested === '') {
            abort(404);
        }

        $view = $this->laporan($mk);
        $data = $view->getData();

        $kelasList = collect($data['kelasList'] ?? []);
        if (!$kelasList->contains($kelasRequested)) {
            abort(404);
        }

        $reportByClass = $data['reportByClass'] ?? [];
        $classReport = $reportByClass[$kelasRequested] ?? null;
        if ($classReport === null) {
            abort(404);
        }

        $pdf = Pdf::loadView('obe.report.pdf-workcloud-mk-download', [
            'mk' => $mk,
            'semester' => $data['semester'] ?? null,
            'targetKelulusan' => $data['targetKelulusan'] ?? 100,
            'gradeOrder' => $data['gradeOrder'] ?? [],
            'kelas' => $kelasRequested,
            'data' => $classReport,
            'downloadedAt' => now()->format('d-m-Y H:i:s'),
        ])
            ->setOption('isPhpEnabled', true)
            ->setPaper('a4', 'landscape');

        $filename = 'laporan-mk-' . Str::slug((string) $mk->kode, '-') . '-kelas-' . Str::slug($kelasRequested, '-') . '.pdf';

        if ($mode === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
