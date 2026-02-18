<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Mk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AchievementController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read achievement-mks', ['only' => ['index','show']]);
    }

    public function index(Mk $mk)
    {
        return view('obe.report.mk-achievement', $this->buildNilaiPageData($mk));
    }

    private function buildNilaiPageData(Mk $mk): array
    {
        $semesters = $mk->kontrakMks()->with('semester')->get()->pluck('semester')->unique('id')->sortBy('kode');
        $activeSemester = $semesters->firstWhere('status_aktif', true) ?? $semesters->first();
        $defaultSemesterId = $activeSemester?->id;

        $kontrakMks = $mk->kontrakMks()
            ->with(['mahasiswa', 'semester'])
            ->whereNotNull('mahasiswa_id')
            ->whereNotNull('semester_id')
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

        $gradeOrder = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'E'];
        $targetKelulusan = $mk->kurikulum->target_capaian_lulusan;

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

        $achievementRows = DB::table('kontrak_mks as km')
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
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, km.semester_id, pcm.cpl_id, AVG(n.nilai) as avg_capaian")
            ->groupBy('kelas_key', 'km.semester_id', 'pcm.cpl_id')
            ->get();

        $achievementRowsAllClass = DB::table('kontrak_mks as km')
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
            ->selectRaw('km.semester_id, pcm.cpl_id, AVG(n.nilai) as avg_capaian')
            ->groupBy('km.semester_id', 'pcm.cpl_id')
            ->get();

        $achievementData = [];
        foreach ($achievementRows as $row) {
            $achievementData[$row->kelas_key][$row->semester_id][$row->cpl_id] = round((float) $row->avg_capaian, 2);
        }
        foreach ($achievementRowsAllClass as $row) {
            $achievementData['__SEMUA_KELAS__'][$row->semester_id][$row->cpl_id] = round((float) $row->avg_capaian, 2);
        }

        $componentRows = DB::table('penugasans as p')
            ->join('evaluasis as e', 'e.id', '=', 'p.evaluasi_id')
            ->join('join_subcpmk_penugasans as jsp', function ($join) {
                $join->on('jsp.penugasan_id', '=', 'p.id');
            })
            ->join('subcpmks as s', 's.id', '=', 'jsp.subcpmk_id')
            ->join('join_cpl_cpmks as jcc', 'jcc.id', '=', 's.join_cpl_cpmk_id')
            ->join('join_cpl_bks as jcb', 'jcb.id', '=', 'jcc.join_cpl_bk_id')
            ->join('cpls as c', 'c.id', '=', 'jcb.cpl_id')
            ->where('p.mk_id', $mk->id)
            ->selectRaw("COALESCE(p.semester_id, jsp.semester_id) as semester_id, c.id as cpl_id, COALESCE(NULLIF(TRIM(e.workcloud), ''), NULLIF(TRIM(e.kategori), ''), NULLIF(TRIM(e.kode), '')) as workcloud, COALESCE(SUM(COALESCE(jsp.bobot,0) * COALESCE(p.bobot,0)),0) as total_bobot")
            ->whereNotNull(DB::raw("COALESCE(NULLIF(TRIM(e.workcloud), ''), NULLIF(TRIM(e.kategori), ''), NULLIF(TRIM(e.kode), ''))"))
            ->groupBy(DB::raw('COALESCE(p.semester_id, jsp.semester_id)'), 'c.id', DB::raw("COALESCE(NULLIF(TRIM(e.workcloud), ''), NULLIF(TRIM(e.kategori), ''), NULLIF(TRIM(e.kode), ''))"))
            ->orderBy(DB::raw("COALESCE(NULLIF(TRIM(e.workcloud), ''), NULLIF(TRIM(e.kategori), ''), NULLIF(TRIM(e.kode), ''))"))
            ->get();

        $componentsDataByCpl = [];
        foreach ($componentRows as $row) {
            $semesterKey = (string) ($row->semester_id ?? 'all');

            if (!isset($componentsDataByCpl[$semesterKey][$row->cpl_id])) {
                $componentsDataByCpl[$semesterKey][$row->cpl_id] = [];
            }
            $componentsDataByCpl[$semesterKey][$row->cpl_id][] = [
                'workcloud' => $row->workcloud,
                'bobot' => round((float) $row->total_bobot/100, 2),
            ];
        }

        $componentsDataByCpl['all'] = [];
        foreach ($componentRows as $row) {
            if (!isset($componentsDataByCpl['all'][$row->cpl_id])) {
                $componentsDataByCpl['all'][$row->cpl_id] = [];
            }

            $existingIndex = collect($componentsDataByCpl['all'][$row->cpl_id])
                ->search(fn ($item) => ($item['workcloud'] ?? null) === $row->workcloud);

            if ($existingIndex === false) {
                $componentsDataByCpl['all'][$row->cpl_id][] = [
                    'workcloud' => $row->workcloud,
                    'bobot' => round((float) $row->total_bobot, 2),
                ];
            } else {
                $componentsDataByCpl['all'][$row->cpl_id][$existingIndex]['bobot'] = round(
                    (float) $componentsDataByCpl['all'][$row->cpl_id][$existingIndex]['bobot'] + (float) $row->total_bobot,
                    2
                );
            }
        }

        $totalStudentRows = DB::table('kontrak_mks as km')
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, km.semester_id, COUNT(*) as total_mahasiswa")
            ->groupBy('kelas_key', 'km.semester_id')
            ->get();

        $totalStudentRowsAllClass = DB::table('kontrak_mks as km')
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->selectRaw('km.semester_id, COUNT(*) as total_mahasiswa')
            ->groupBy('km.semester_id')
            ->get();

        $totalsByClassSemester = [];
        foreach ($totalStudentRows as $row) {
            $totalsByClassSemester[$row->kelas_key][$row->semester_id] = (int) $row->total_mahasiswa;
        }
        foreach ($totalStudentRowsAllClass as $row) {
            $totalsByClassSemester['__SEMUA_KELAS__'][$row->semester_id] = (int) $row->total_mahasiswa;
        }

        $gradeCountRows = DB::table('kontrak_mks as km')
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->whereNotNull('km.nilai_huruf')
            ->whereIn('km.nilai_huruf', $gradeOrder)
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, km.semester_id, km.nilai_huruf, COUNT(*) as jumlah")
            ->groupBy('kelas_key', 'km.semester_id', 'km.nilai_huruf')
            ->get();

        $gradeCountRowsAllClass = DB::table('kontrak_mks as km')
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->whereNotNull('km.nilai_huruf')
            ->whereIn('km.nilai_huruf', $gradeOrder)
            ->selectRaw('km.semester_id, km.nilai_huruf, COUNT(*) as jumlah')
            ->groupBy('km.semester_id', 'km.nilai_huruf')
            ->get();

        $gradeDistributionData = [];
        foreach ($kelasList as $kelas) {
            foreach ($semesters as $semester) {
                $semesterId = (string) $semester->id;
                $total = (int) ($totalsByClassSemester[$kelas][$semesterId] ?? 0);

                $gradeDistributionData[$kelas][$semesterId] = [
                    'total' => $total,
                    'counts' => array_fill_keys($gradeOrder, 0),
                ];
            }
        }

        foreach ($gradeCountRows as $row) {
            $kelas = $row->kelas_key;
            $semesterId = (string) $row->semester_id;
            $grade = (string) $row->nilai_huruf;
            if (isset($gradeDistributionData[$kelas][$semesterId]['counts'][$grade])) {
                $gradeDistributionData[$kelas][$semesterId]['counts'][$grade] = (int) $row->jumlah;
            }
        }

        foreach ($gradeCountRowsAllClass as $row) {
            $semesterId = (string) $row->semester_id;
            $grade = (string) $row->nilai_huruf;
            if (isset($gradeDistributionData['__SEMUA_KELAS__'][$semesterId]['counts'][$grade])) {
                $gradeDistributionData['__SEMUA_KELAS__'][$semesterId]['counts'][$grade] = (int) $row->jumlah;
            }
        }

        return compact(
            'mk',
            'semesters',
            'kelasList',
            'defaultSemesterId',
            'targetKelulusan',
            'gradeOrder',
            'cplRows',
            'achievementData',
            'componentsDataByCpl',
            'gradeDistributionData'
        );
    }
}
