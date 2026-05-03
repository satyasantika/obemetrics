<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Penugasan;
use App\Models\Subcpmk;
use App\Models\JoinSubcpmkPenugasan;
use Illuminate\Http\Request;
use App\Actions\SyncMkState;
use App\Actions\ResolveMkSemester;
use App\Http\Controllers\Controller;

class JoinSubcpmkPenugasanController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join subcpmk penugasans', ['only' => ['index']]);
        $this->middleware('permission:update join subcpmk penugasans', ['only' => ['update']]);
    }

    public function index(Mk $mk, Request $request)
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

        [, $selectedSemesterId] = ResolveMkSemester::resolve($mk, $request->query('semester_id'), $semesterOptions);
        $selectedSemesterId = (string) ($selectedSemesterId ?? '');

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

    public function update(Request $request, Mk $mk, Subcpmk $subcpmk, Penugasan $penugasan)
    {
        $payload = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        $mkId = $mk->id;
        $semesterId = (string) $payload['semester_id'];

        if ((string) $penugasan->mk_id !== $mkId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Penugasan tidak sesuai dengan mata kuliah.',
            ], 422);
        }

        $existingRows = JoinSubcpmkPenugasan::query()
            ->where('mk_id', $mkId)
            ->where('semester_id', $semesterId)
            ->where('subcpmk_id', $subcpmk->id)
            ->where('penugasan_id', $penugasan->id)
            ->orderBy('created_at')
            ->get();

        $existing = $existingRows->first();

        $bobotValue = $request->input('bobot');
        $hasBobotValue = $bobotValue !== null && trim((string) $bobotValue) !== '';
        $bobot = $hasBobotValue ? (float) $bobotValue : null;

        if (!$hasBobotValue) {
            if ($existingRows->isNotEmpty()) {
                JoinSubcpmkPenugasan::query()
                    ->whereIn('id', $existingRows->pluck('id'))
                    ->delete();
            }

            SyncMkState::sync($mk->fresh());
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

        if ($bobot <= 0) {
            if ($existingRows->isNotEmpty()) {
                JoinSubcpmkPenugasan::query()
                    ->whereIn('id', $existingRows->pluck('id'))
                    ->delete();

                SyncMkState::sync($mk->fresh());
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

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => false,
                    'bobot' => null,
                ]);
            }

            return to_route('mks.joinsubcpmkpenugasans.index', ['mk' => $mkId, 'semester_id' => $semesterId])
                ->with('warning', 'Tidak ada relasi yang diubah.');
        }

        if ($existing) {
            $existing->bobot = $bobot;
            $existing->save();

            $duplicateIds = $existingRows->skip(1)->pluck('id');
            if ($duplicateIds->isNotEmpty()) {
                JoinSubcpmkPenugasan::query()
                    ->whereIn('id', $duplicateIds)
                    ->delete();
            }

            $link = $existing;
        } else {
            $link = JoinSubcpmkPenugasan::create([
                'mk_id' => $mkId,
                'semester_id' => $semesterId,
                'subcpmk_id' => $subcpmk->id,
                'penugasan_id' => $penugasan->id,
                'bobot' => $bobot,
            ]);

            $latestRows = JoinSubcpmkPenugasan::query()
                ->where('mk_id', $mkId)
                ->where('semester_id', $semesterId)
                ->where('subcpmk_id', $subcpmk->id)
                ->where('penugasan_id', $penugasan->id)
                ->orderBy('created_at')
                ->get();

            $link = $latestRows->first() ?? $link;

            $duplicateIds = $latestRows->skip(1)->pluck('id');
            if ($duplicateIds->isNotEmpty()) {
                JoinSubcpmkPenugasan::query()
                    ->whereIn('id', $duplicateIds)
                    ->delete();
            }
        }

        SyncMkState::sync($mk->fresh());
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
