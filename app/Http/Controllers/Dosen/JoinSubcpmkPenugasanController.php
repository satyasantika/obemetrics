<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Penugasan;
use App\Models\Subcpmk;
use App\Models\JoinSubcpmkPenugasan;
use App\Models\Semester;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JoinSubcpmkPenugasanController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join subcpmk penugasans', ['only' => ['index']]);
        $this->middleware('permission:update join subcpmk penugasans', ['only' => ['update']]);
    }

    public function index(Mk $mk)
    {
        $semesterOptions = $mk->kontrakMks()
            ->whereNotNull('semester_id')
            ->with('semester')
            ->get()
            ->pluck('semester')
            ->filter()
            ->unique('id')
            ->sortByDesc('status_aktif')
            ->sortByDesc('kode')
            ->values();

        if ($semesterOptions->isEmpty()) {
            $semesterOptions = Semester::query()
                ->orderByDesc('status_aktif')
                ->orderByDesc('kode')
                ->get();
        }

        $selectedSemesterId = (string) (request()->query('semester_id')
            ?: ($semesterOptions->firstWhere('status_aktif', true)?->id ?? $semesterOptions->first()?->id ?? ''));

        $subcpmks = Subcpmk::query()
            ->where('semester_id', $selectedSemesterId)
            ->whereHas('joinCplCpmk', function ($query) use ($mk) {
                $query->where('mk_id', $mk->id);
            })
            ->orderBy('kode')
            ->get();

        $penugasans = Penugasan::query()
            ->where('mk_id', $mk->id)
            ->where('semester_id', $selectedSemesterId)
            ->orderBy('kode')
            ->get();

        $links = JoinSubcpmkPenugasan::query()
            ->where('mk_id', $mk->id)
            ->where('semester_id', $selectedSemesterId)
            ->whereIn('subcpmk_id', $subcpmks->pluck('id'))
            ->whereIn('penugasan_id', $penugasans->pluck('id'))
            ->get();

        $linkByKey = $links->keyBy(function ($item) {
            return $item->penugasan_id . '_' . $item->subcpmk_id;
        });

        $bobotTotalByPenugasan = $links
            ->groupBy('penugasan_id')
            ->map(fn ($items) => (float) $items->sum('bobot'))
            ->all();

        return view('obe.subcpmk-penugasan')
                ->with('mk', $mk)
                ->with('semesterOptions', $semesterOptions)
                ->with('selectedSemesterId', $selectedSemesterId)
                ->with('subcpmks', $subcpmks)
                ->with('penugasans', $penugasans)
                ->with('linkByKey', $linkByKey)
                ->with('bobotTotalByPenugasan', $bobotTotalByPenugasan);
    }

    public function update(Request $request, Subcpmk $subcpmk, Penugasan $penugasan)
    {
        $payload = $request->validate([
            'mk_id' => 'required|exists:mks,id',
            'semester_id' => 'required|exists:semesters,id',
            'bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        $mkId = (string) $payload['mk_id'];
        $semesterId = (string) $payload['semester_id'];

        if ((string) $penugasan->mk_id !== $mkId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Penugasan tidak sesuai dengan mata kuliah.',
            ], 422);
        }

        $existing = JoinSubcpmkPenugasan::query()
            ->where('mk_id', $mkId)
            ->where('semester_id', $semesterId)
            ->where('subcpmk_id', $subcpmk->id)
            ->where('penugasan_id', $penugasan->id)
            ->first();

        $bobotValue = $request->input('bobot');
        $hasBobotValue = $bobotValue !== null && trim((string) $bobotValue) !== '';

        if (!$hasBobotValue) {
            if ($existing) {
                $existing->delete();
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => false,
                    'bobot' => null,
                ]);
            }

            return to_route('mks.joinsubcpmkpenugasans.index', ['mk' => $mkId, 'semester_id' => $semesterId])
                ->with('warning', $subcpmk->kode . ' dihapus dari ' . $penugasan->nama . '.');
        }

        $bobot = (float) $bobotValue;
        $link = JoinSubcpmkPenugasan::updateOrCreate(
            [
                'mk_id' => $mkId,
                'semester_id' => $semesterId,
                'subcpmk_id' => $subcpmk->id,
                'penugasan_id' => $penugasan->id,
            ],
            [
                'bobot' => $bobot,
            ]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'linked' => true,
                'bobot' => (float) $link->bobot,
            ]);
        }

        return to_route('mks.joinsubcpmkpenugasans.index', ['mk' => $mkId, 'semester_id' => $semesterId])
            ->with('success', 'Bobot ' . $subcpmk->kode . ' untuk ' . $penugasan->nama . ' berhasil diperbarui.');

    }
}
