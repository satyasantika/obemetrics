<?php

namespace App\Http\Controllers\Prodi;

use App\Actions\SyncKurikulumState;
use App\Models\Cpl;
use App\Models\JoinCplCpmk;
use App\Models\JoinCplMk;
use App\Models\Kurikulum;
use App\Models\Mk;
use Illuminate\Http\Request;
use App\Models\CplBk;
use App\Http\Controllers\Controller;

class JoinCplMkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join cpl mks', ['only' => ['index']]);
        $this->middleware('permission:update join cpl mks', ['only' => ['update']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $cpls = $kurikulum->cpls()
            ->orderBy('kode')
            ->get();

        $bkIds = $kurikulum->bks()->pluck('bks.id');
        $cplBkByCpl = CplBk::query()
            ->with('bk')
            ->whereIn('cpl_id', $cpls->pluck('id'))
            ->whereIn('bk_id', $bkIds)
            ->get()
            ->groupBy('cpl_id');

        $mks = $kurikulum->mks()
            ->orderBy('semester')
            ->orderBy('kurikulum_mks.kode_mk')
            ->get();

        $cplHeaderGroups = collect();
        $cplBkColumns = collect();

        foreach ($cpls as $cpl) {
            $bkColumns = ($cplBkByCpl->get($cpl->id, collect()))
                ->filter(fn ($join) => $join->bk)
                ->sortBy(fn ($join) => (string) $join->bk->kode)
                ->values()
                ->map(function ($join) use ($cpl) {
                    return [
                        'type' => 'bk',
                        'cpl_id' => $cpl->id,
                        'cpl_kode' => $cpl->kode,
                        'cpl_nama' => $cpl->nama,
                        'cpl_bk_id' => $join->id,
                        'bk_kode' => $join->bk->kode,
                        'bk_nama' => $join->bk->nama,
                    ];
                })
                ->values();

            if ($bkColumns->isEmpty()) {
                $bkColumns = collect([
                    [
                        'type' => 'placeholder',
                        'cpl_id' => $cpl->id,
                        'cpl_kode' => $cpl->kode,
                        'cpl_nama' => $cpl->nama,
                        'cpl_bk_id' => null,
                        'bk_kode' => '-',
                        'bk_nama' => null,
                    ],
                ]);
            }

            $cplHeaderGroups->push([
                'cpl_id' => $cpl->id,
                'cpl_kode' => $cpl->kode,
                'cpl_nama' => $cpl->nama,
                'colspan' => $bkColumns->count(),
            ]);

            $cplBkColumns = $cplBkColumns->merge($bkColumns);
        }

        $cplBkOrderMap = $cplBkColumns
            ->filter(fn ($column) => ($column['type'] ?? null) === 'bk' && !empty($column['cpl_bk_id']))
            ->pluck('cpl_bk_id')
            ->values()
            ->flip();

        $linkedRows = JoinCplMk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('mk_id', $mks->pluck('id'))
            ->get();

        $availablePairMap = collect();

        foreach ($cplBkColumns->pluck('cpl_bk_id')->filter()->unique() as $cplBkId) {
            foreach ($mks as $mk) {
                $availablePairMap->put($cplBkId . '|' . $mk->id, true);
            }
        }

        $linkedPairMap = collect();
        $bobotPairMap = collect();
        $mkTotalBobotMap = collect();

        foreach ($linkedRows as $row) {
            if (!$row->cpl_bk_id) {
                continue;
            }

            $pairKey = $row->cpl_bk_id . '|' . $row->mk_id;
            $linkedPairMap->put($pairKey, true);

            if (!$bobotPairMap->has($pairKey) && $row->bobot !== null) {
                $bobotPairMap->put($pairKey, (float) $row->bobot);
            }

            $mkTotalBobotMap->put(
                $row->mk_id,
                (float) ($mkTotalBobotMap->get($row->mk_id, 0) + ((float) ($row->bobot ?? 0)))
            );
        }

        $lockedPairMap = JoinCplCpmk::query()
            ->whereIn('mk_id', $mks->pluck('id'))
            ->get()
            ->mapWithKeys(function ($row) {
                return $row->cpl_bk_id
                    ? [($row->cpl_bk_id . '|' . $row->mk_id) => true]
                    : [];
            });

        $mkOrderByBkMap = $linkedRows
                ->filter(fn ($row) => !empty($row->cpl_bk_id) && $cplBkOrderMap->has($row->cpl_bk_id))
                ->groupBy('mk_id')
                ->map(function ($rows) use ($cplBkOrderMap) {
                return $rows
                    ->map(fn ($row) => (int) $cplBkOrderMap->get($row->cpl_bk_id))
                    ->min();
            });

        $mks = $mks->sortBy(function ($mk) use ($mkOrderByBkMap) {
            $bkOrder = $mkOrderByBkMap->has($mk->id) ? (int) $mkOrderByBkMap->get($mk->id) : \PHP_INT_MAX;
            $nama = mb_strtolower((string) ($mk->nama ?? ''));
            $kode = mb_strtolower((string) ($mk->kode ?? ''));

            return sprintf('%010d|%s|%s', $bkOrder, $nama, $kode);
        })->values();

        return view('obe.cpl-mk')
                ->with('kurikulum', $kurikulum)
                ->with('cpls', $cpls)
                ->with('mks', $mks)
                ->with('cplHeaderGroups', $cplHeaderGroups)
                ->with('cplBkColumns', $cplBkColumns)
                ->with('linkedPairMap', $linkedPairMap)
                ->with('bobotPairMap', $bobotPairMap)
                ->with('availablePairMap', $availablePairMap)
                ->with('mkTotalBobotMap', $mkTotalBobotMap)
                ->with('lockedPairMap', $lockedPairMap);
    }

    public function update(Request $request, Kurikulum $kurikulum, Cpl $cpl, Mk $mk)
    {
        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'cpl_bk_id' => 'nullable|exists:cpl_bks,id',
            'bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        $kurikulumId = (string) $kurikulum->id;

        if ((string) $validated['kurikulum_id'] !== $kurikulumId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kurikulum tidak valid.',
            ], 422);
        }

        $selectedCplBkId = $validated['cpl_bk_id'] ?? null;

        if ($selectedCplBkId) {
            $cplBk = CplBk::query()
                ->where('id', $selectedCplBkId)
                ->where('cpl_id', $cpl->id)
                ->whereIn('bk_id', $kurikulum->bks()->pluck('bks.id'))
                ->first();

            if (!$cplBk) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Relasi CPL-BK tidak valid.',
                ], 422);
            }

            $relationQuery = JoinCplMk::query()
                ->where('mk_id', $mk->id)
                ->where('kurikulum_id', $kurikulumId)
                ->where('cpl_bk_id', $selectedCplBkId);

            $existingRows = $relationQuery
                ->orderBy('created_at')
                ->get();

            $bobotValue = $request->input('bobot');
            $hasBobotValue = $bobotValue !== null && trim((string) $bobotValue) !== '';
            $bobot = $hasBobotValue ? (float) $bobotValue : null;

            if ($hasBobotValue && ($bobot < 0 || $bobot > 100)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bobot harus di antara 0 sampai 100.',
                ], 422);
            }

            $isLocked = JoinCplCpmk::query()
                ->where('mk_id', $mk->id)
                ->where('cpl_bk_id', $selectedCplBkId)
                ->exists();

            if ($isLocked && !$hasBobotValue) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'bobot tidak boleh kosong saat status dikunci.',
                ], 422);
            }

            if (!$hasBobotValue) {
                if ($existingRows->isNotEmpty()) {
                    JoinCplMk::query()
                        ->whereIn('id', $existingRows->pluck('id'))
                        ->delete();
                }
                SyncKurikulumState::sync($kurikulum);

                return response()->json([
                    'status' => 'ok',
                    'linked' => false,
                    'bobot' => null,
                    'state' => class_basename($kurikulum->fresh()->status),
                ]);
            }

            if ($existingRows->isNotEmpty()) {
                $row = $existingRows->first();
                $row->bobot = $bobot;
                $row->save();

                $duplicateIds = $existingRows->skip(1)->pluck('id');
                if ($duplicateIds->isNotEmpty()) {
                    JoinCplMk::query()->whereIn('id', $duplicateIds)->delete();
                }
            } else {
                $row = JoinCplMk::create([
                    'cpl_bk_id' => $selectedCplBkId,
                    'mk_id' => $mk->id,
                    'kurikulum_id' => $kurikulumId,
                    'bobot' => $bobot,
                ]);
            }

            SyncKurikulumState::sync($kurikulum);

            return response()->json([
                'status' => 'ok',
                'linked' => true,
                'bobot' => (float) $row->bobot,
                'state' => class_basename($kurikulum->fresh()->status),
            ]);
        }

        $eligibleCplBkIds = CplBk::query()
            ->where('cpl_id', $cpl->id)
            ->whereIn('bk_id', $kurikulum->bks()->pluck('bks.id'))
            ->pluck('id');

        $existingRows = JoinCplMk::query()
            ->where('mk_id', $mk->id)
            ->where('kurikulum_id', $kurikulumId)
                ->whereIn('cpl_bk_id', function ($query) use ($cpl, $kurikulum) {
                $query->select('id')
                    ->from('cpl_bks')
                    ->where('cpl_id', $cpl->id)
                    ->whereIn('bk_id', $kurikulum->bks()->pluck('bks.id'));
            })
            ->get();

        if ($request->has('is_linked')) {
            if ($eligibleCplBkIds->isEmpty()) {
                return to_route('kurikulums.joincplmks.index', $kurikulumId)
                    ->with('error', 'Interaksi CPL >< MK tidak dapat dibuat karena belum ada jalur CPL >< BK >< MK.');
            }

            $bobotValue = $request->filled('bobot') ? (float) $request->input('bobot') : null;

            foreach ($eligibleCplBkIds as $cplBkId) {
                JoinCplMk::updateOrCreate(
                    [
                        'cpl_bk_id' => $cplBkId,
                        'mk_id' => $mk->id,
                        'kurikulum_id' => $kurikulumId,
                    ],
                    [
                        'bobot' => $bobotValue,
                    ]
                );
            }

            JoinCplMk::query()
                ->where('mk_id', $mk->id)
                ->where('kurikulum_id', $kurikulumId)
                ->whereIn('cpl_bk_id', function ($query) use ($cpl, $kurikulum) {
                    $query->select('id')
                        ->from('cpl_bks')
                    ->where('cpl_id', $cpl->id)
                    ->whereIn('bk_id', $kurikulum->bks()->pluck('bks.id'));
                })
                ->whereNotIn('cpl_bk_id', $eligibleCplBkIds)
                ->delete();

            SyncKurikulumState::sync($kurikulum);

            return to_route('kurikulums.joincplmks.index', $kurikulumId)
                ->with('success', $mk->kode . ' telah diinteraksi dengan ' . $cpl->kode);
        } else {
            if ($existingRows->isNotEmpty()) {
                $isUsed = JoinCplCpmk::query()
                    ->where('mk_id', $mk->id)
                    ->whereIn('cpl_bk_id', $existingRows->pluck('cpl_bk_id'))
                    ->exists();

                if ($isUsed) {
                    return to_route('kurikulums.joincplmks.index', $kurikulumId)
                        ->with('error', 'Interaksi dikunci karena sudah digunakan pada relasi CPL >< CPMK.');
                }

                JoinCplMk::query()
                    ->whereIn('id', $existingRows->pluck('id'))
                    ->delete();
            }
            SyncKurikulumState::sync($kurikulum);

            return to_route('kurikulums.joincplmks.index', $kurikulumId)
                ->with('warning', $mk->kode . ' sudah tidak berinteraksi dengan ' . $cpl->kode);
        }
    }
}
