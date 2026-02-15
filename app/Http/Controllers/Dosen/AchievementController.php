<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Mk;
use App\Models\Semester;
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
        return view('obe.report.achievement-per-mk', $this->buildNilaiPageData($mk));
    }

    private function buildNilaiPageData(Mk $mk): array
    {
        $semesters = Semester::query()->orderBy('kode')->get();
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

        $gradeOrder = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'E'];
        $targetKelulusan = 60;

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

        $achievementData = [];
        foreach ($achievementRows as $row) {
            $achievementData[$row->kelas_key][$row->semester_id][$row->cpl_id] = round((float) $row->avg_capaian, 2);
        }

        $componentRows = DB::table('penugasans as p')
            ->join('evaluasis as e', 'e.id', '=', 'p.evaluasi_id')
            ->joinSub($penugasanCplMapQuery, 'pcm', function ($join) {
                $join->on('pcm.penugasan_id', '=', 'p.id');
            })
            ->where('p.mk_id', $mk->id)
            ->whereNotNull('e.workcloud')
            ->where('e.workcloud', '!=', '')
            ->selectRaw('p.semester_id, pcm.cpl_id, e.workcloud, COALESCE(SUM(p.bobot),0) as total_bobot')
            ->groupBy('p.semester_id', 'pcm.cpl_id', 'e.workcloud')
            ->orderBy('e.workcloud')
            ->get();

        $componentsData = [];
        foreach ($componentRows as $row) {
            $semesterKey = $row->semester_id ?: 'all';
            if (!isset($componentsData[$semesterKey][$row->cpl_id])) {
                $componentsData[$semesterKey][$row->cpl_id] = [];
            }
            $componentsData[$semesterKey][$row->cpl_id][] = [
                'workcloud' => $row->workcloud,
                'bobot' => round((float) $row->total_bobot, 2),
            ];
        }

        $totalStudentRows = DB::table('kontrak_mks as km')
            ->where('km.mk_id', $mk->id)
            ->whereNotNull('km.mahasiswa_id')
            ->whereNotNull('km.semester_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(km.kelas), ''), 'Tanpa Kelas') as kelas_key, km.semester_id, COUNT(*) as total_mahasiswa")
            ->groupBy('kelas_key', 'km.semester_id')
            ->get();

        $totalsByClassSemester = [];
        foreach ($totalStudentRows as $row) {
            $totalsByClassSemester[$row->kelas_key][$row->semester_id] = (int) $row->total_mahasiswa;
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

        return compact(
            'mk',
            'semesters',
            'kelasList',
            'defaultSemesterId',
            'targetKelulusan',
            'gradeOrder',
            'cplRows',
            'achievementData',
            'componentsData',
            'gradeDistributionData'
        );
    }
}
