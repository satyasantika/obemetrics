<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Mk;
use App\Models\Bk;
use App\Models\JoinCplBk;
use App\Models\JoinCplMk;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Models\JoinCplCpmk;
use App\Http\Controllers\Controller;

class JoinBkMkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join bk mks', ['only' => ['index']]);
        $this->middleware('permission:update join bk mks', ['only' => ['update']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $bks = $kurikulum->bks()->orderBy('kode')->get();
        $mks = $kurikulum->mks()->orderBy('semester')->orderBy('kode')->get();

        $joinCplBkRows = JoinCplBk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('bk_id', $bks->pluck('id'))
            ->get(['id', 'bk_id']);

        $joinCplBkIdsByBk = $joinCplBkRows
            ->groupBy('bk_id')
            ->map(fn ($rows) => $rows->pluck('id')->values());

        $linkedRows = JoinCplMk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->whereIn('mk_id', $mks->pluck('id'))
            ->whereIn('join_cpl_bk_id', $joinCplBkRows->pluck('id'))
            ->get(['mk_id', 'join_cpl_bk_id']);

        $linkedPairMap = collect();
        foreach ($bks as $bk) {
            $joinIds = $joinCplBkIdsByBk->get($bk->id, collect());
            if ($joinIds->isEmpty()) {
                continue;
            }

            foreach ($mks as $mk) {
                $isLinked = $linkedRows
                    ->where('mk_id', $mk->id)
                    ->pluck('join_cpl_bk_id')
                    ->intersect($joinIds)
                    ->isNotEmpty();

                if ($isLinked) {
                    $linkedPairMap->put($bk->id . '|' . $mk->id, true);
                }
            }
        }

        $lockedPairMap = JoinCplCpmk::query()
            ->whereIn('mk_id', $mks->pluck('id'))
            ->with('joinCplBk:id,bk_id')
            ->get()
            ->mapWithKeys(function ($row) {
                $bkId = optional($row->joinCplBk)->bk_id;

                return $bkId
                    ? [($bkId . '|' . $row->mk_id) => true]
                    : [];
            });

        return view('obe.bk-mk')
                ->with('kurikulum', $kurikulum)
                ->with('bks', $bks)
                ->with('mks', $mks)
                ->with('linkedPairMap', $linkedPairMap)
                ->with('lockedPairMap', $lockedPairMap);
    }

    public function update(Request $request, Bk $bk, Mk $mk)
    {
        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulums,id',
        ]);

        $kurikulumId = $validated['kurikulum_id'];

        $joinCplBkIds = JoinCplBk::query()
            ->where('kurikulum_id', $kurikulumId)
            ->where('bk_id', $bk->id)
            ->pluck('id');

        if ($joinCplBkIds->isEmpty()) {
            return to_route('kurikulums.joinbkmks.index', $kurikulumId)
                ->with('error', 'Interaksi BK >< MK tidak dapat diubah karena belum ada relasi CPL >< BK untuk BK ini.');
        }

        $existingRows = JoinCplMk::query()
            ->where('kurikulum_id', $kurikulumId)
            ->where('mk_id', $mk->id)
            ->whereIn('join_cpl_bk_id', $joinCplBkIds)
            ->get();

        if ($request->has('is_linked')) {
            foreach ($joinCplBkIds as $joinCplBkId) {
                JoinCplMk::updateOrCreate(
                    [
                        'kurikulum_id' => $kurikulumId,
                        'mk_id' => $mk->id,
                        'join_cpl_bk_id' => $joinCplBkId,
                    ],
                    []
                );
            }

            return to_route('kurikulums.joinbkmks.index', $kurikulumId)
                    ->with('success', $mk->kode . ' telah diinteraksi dengan ' . $bk->kode);
        } else {
            $isUsed = JoinCplCpmk::query()
                ->where('mk_id', $mk->id)
                ->whereIn('join_cpl_bk_id', $joinCplBkIds)
                ->exists();

            if ($isUsed) {
                return to_route('kurikulums.joinbkmks.index', $kurikulumId)
                    ->with('error', 'Interaksi tidak dapat diubah karena sudah dipakai pada relasi CPL >< CPMK.');
            }

            if ($existingRows->isNotEmpty()) {
                JoinCplMk::query()
                    ->whereIn('id', $existingRows->pluck('id'))
                    ->delete();
            }

            return to_route('kurikulums.joinbkmks.index', $kurikulumId)
                    ->with('warning', $mk->kode . ' sudah tidak berinteraksi dengan ' . $bk->kode);
        }

    }
}
