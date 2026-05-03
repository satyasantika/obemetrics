<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Cpmk;
use App\Models\Subcpmk;
use App\Models\Penugasan;
use App\Models\JoinCplCpmk;
use App\Models\JoinSubcpmkPenugasan;
use Illuminate\Http\Request;
use App\Actions\SyncMkState;
use App\Actions\ResolveMkSemester;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SubCpmkController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read subcpmks', ['only' => ['index', 'show']]);
        $this->middleware('permission:create subcpmks', ['only' => ['create', 'store', 'copyFromSemester']]);
        $this->middleware('permission:update subcpmks', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete subcpmks', ['only' => ['destroy']]);
    }

    public function index(Mk $mk, Request $request)
    {
        $currentUserId = auth()->id();

        $semesterOptions = $mk->kontrakMks()
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

        [$selectedSemester, $selectedSemesterId] = ResolveMkSemester::resolve($mk, $request->query('semester_id'), $semesterOptions);

        $cpmks = Cpmk::where('mk_id', $mk->id)
            ->with([
                'joinCplCpmks' => fn ($q) => $q->with([
                    'subcpmks' => function ($q) use ($selectedSemesterId) {
                        if ($selectedSemesterId) {
                            $q->where('semester_id', $selectedSemesterId);
                        }
                        $q->with(['joinSubcpmkPenugasans.penugasan.evaluasi']);
                    },
                ]),
            ])
            ->get();

        $total_bobot = DB::table('join_subcpmk_penugasans')
            ->join('subcpmks', 'subcpmks.id', '=', 'join_subcpmk_penugasans.subcpmk_id')
            ->join('join_cpl_cpmks', 'join_cpl_cpmks.id', '=', 'subcpmks.join_cpl_cpmk_id')
            ->leftJoin('penugasans', 'penugasans.id', '=', 'join_subcpmk_penugasans.penugasan_id')
            ->where('join_cpl_cpmks.mk_id', $mk->id)
            ->when($selectedSemesterId, fn ($q) => $q->where('subcpmks.semester_id', $selectedSemesterId))
            ->sum(DB::raw('COALESCE(penugasans.bobot,0) * COALESCE(join_subcpmk_penugasans.bobot,0) / 100'));

        $hasSubcpmksForSemester = $cpmks
            ->flatMap(fn ($c) => $c->joinCplCpmks->flatMap(fn ($jcc) => $jcc->subcpmks))
            ->isNotEmpty();

        $semesterIdsWithSubcpmks = Subcpmk::whereHas('joinCplCpmk', fn ($q) => $q->where('mk_id', $mk->id))
            ->whereNotNull('semester_id')
            ->when($selectedSemesterId, fn ($q) => $q->where('semester_id', '!=', $selectedSemesterId))
            ->distinct()
            ->pluck('semester_id');
        $semestersWithSubcpmks = $semesterOptions
            ->whereIn('id', $semesterIdsWithSubcpmks->toArray())
            ->values();

        $joinCplCpmkOptions = JoinCplCpmk::where('mk_id', $mk->id)->with('cpmk')->get();

        return view('obe.subcpmk', compact(
            'mk', 'cpmks', 'total_bobot',
            'semesterOptions', 'selectedSemesterId', 'selectedSemester',
            'hasSubcpmksForSemester', 'semestersWithSubcpmks',
            'joinCplCpmkOptions'
        ));
    }

    public function create(Mk $mk)
    {
        return to_route('mks.subcpmks.index', $mk)
            ->with('warning', 'Gunakan tombol Tambah Sub CPMK (modal) pada halaman Sub CPMK.');
    }

    public function store(Request $request, Mk $mk)
    {
        $kode = $request->kode;
        $name = $request->nama;
        Subcpmk::create($request->all());
        SyncMkState::sync($mk->fresh());
        $semesterId = $request->semester_id;
        $params = $semesterId ? '?' . http_build_query(['semester_id' => $semesterId]) : '';
        return redirect(route('mks.subcpmks.index', $mk) . $params)
            ->with('success', $kode . ' - ' . $name . ' telah ditambahkan');
    }

    public function edit(Mk $mk, Subcpmk $subcpmk)
    {
        return to_route('mks.subcpmks.index', $mk)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar Sub CPMK.');
    }

    public function update(Request $request, Mk $mk, Subcpmk $subcpmk)
    {
        $subcpmk->fill($request->all())->save();
        SyncMkState::sync($mk->fresh());
        $semesterId = $request->semester_id ?? $subcpmk->semester_id;
        $params = $semesterId ? '?' . http_build_query(['semester_id' => $semesterId]) : '';
        return redirect(route('mks.subcpmks.index', $mk) . $params)
            ->with('success', $request->kode . ' - ' . $request->nama . ' telah diperbarui');
    }

    public function destroy(Mk $mk, Subcpmk $subcpmk)
    {
        $subcpmk_data = $subcpmk->kode . ' - ' . $subcpmk->nama;
        $semesterId = $subcpmk->semester_id;
        $params = $semesterId ? '?' . http_build_query(['semester_id' => $semesterId]) : '';
        if ($subcpmk->joinSubcpmkPenugasans()->exists()) {
            return redirect(route('mks.subcpmks.index', $mk) . $params)
                ->with('error', $subcpmk_data . ' tidak dapat dihapus karena sudah digunakan pada relasi SubCPMK >< Penugasan.');
        }
        $subcpmk->delete();
        SyncMkState::sync($mk->fresh());
        return redirect(route('mks.subcpmks.index', $mk) . $params)
            ->with('warning', $subcpmk_data . ' telah dihapus');
    }

    public function copyFromSemester(Request $request, Mk $mk)
    {
        $request->validate([
            'source_semester_id' => 'required|exists:semesters,id',
            'target_semester_id' => 'required|exists:semesters,id|different:source_semester_id',
        ]);

        $sourceSemesterId = $request->source_semester_id;
        $targetSemesterId = $request->target_semester_id;

        $targetAlreadyHas = Subcpmk::whereHas('joinCplCpmk', fn ($q) => $q->where('mk_id', $mk->id))
            ->where('semester_id', $targetSemesterId)
            ->exists();

        if ($targetAlreadyHas) {
            return redirect(route('mks.subcpmks.index', $mk) . '?' . http_build_query(['semester_id' => $targetSemesterId]))
                ->with('error', 'Semester tujuan sudah memiliki SubCPMK. Hapus terlebih dahulu sebelum menyalin.');
        }

        $sourceSubcpmks = Subcpmk::whereHas('joinCplCpmk', fn ($q) => $q->where('mk_id', $mk->id))
            ->where('semester_id', $sourceSemesterId)
            ->with('joinSubcpmkPenugasans.penugasan')
            ->get();

        if ($sourceSubcpmks->isEmpty()) {
            return redirect(route('mks.subcpmks.index', $mk) . '?' . http_build_query(['semester_id' => $targetSemesterId]))
                ->with('error', 'Tidak ada SubCPMK pada semester sumber yang dipilih.');
        }

        DB::transaction(function () use ($mk, $sourceSubcpmks, $targetSemesterId) {
            // Collect unique source penugasans and copy them to the target semester
            $sourcePenugasanIds = $sourceSubcpmks
                ->flatMap(fn ($s) => $s->joinSubcpmkPenugasans->pluck('penugasan_id'))
                ->filter()
                ->unique()
                ->values();

            $penugasanMap = [];
            foreach (Penugasan::whereIn('id', $sourcePenugasanIds)->get() as $source) {
                $new = $source->replicate();
                $new->semester_id = $targetSemesterId;
                $new->save();
                $penugasanMap[(string) $source->id] = (string) $new->id;
            }

            // Copy each subcpmk and re-link to new penugasans
            foreach ($sourceSubcpmks as $sourceSubcpmk) {
                $newSubcpmk = $sourceSubcpmk->replicate();
                $newSubcpmk->semester_id = $targetSemesterId;
                $newSubcpmk->save();

                foreach ($sourceSubcpmk->joinSubcpmkPenugasans as $join) {
                    $newPenugasanId = $penugasanMap[(string) $join->penugasan_id] ?? null;
                    if (!$newPenugasanId) continue;

                    JoinSubcpmkPenugasan::create([
                        'subcpmk_id'   => $newSubcpmk->id,
                        'penugasan_id' => $newPenugasanId,
                        'mk_id'        => $mk->id,
                        'semester_id'  => $targetSemesterId,
                        'bobot'        => $join->bobot,
                    ]);
                }
            }
        });

        SyncMkState::sync($mk->fresh());

        return redirect(route('mks.subcpmks.index', $mk) . '?' . http_build_query(['semester_id' => $targetSemesterId]))
            ->with('success', count($sourceSubcpmks) . ' SubCPMK beserta tagihan tugas dan interaksinya berhasil disalin.');
    }
}
