<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Mk;
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
