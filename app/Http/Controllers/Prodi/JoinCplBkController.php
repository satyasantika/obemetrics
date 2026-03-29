<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Bk;
use App\Models\Cpl;
use App\Models\JoinCplMk;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Models\JoinCplBk;
use App\Http\Controllers\Controller;

class JoinCplBkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join cpl bks', ['only' => ['index']]);
        $this->middleware('permission:update join cpl bks', ['only' => ['update']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $linkedCplBks = JoinCplBk::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->get(['id', 'cpl_id', 'bk_id']);

        $lockedJoinCplBkIds = JoinCplMk::query()
            ->whereIn('join_cpl_bk_id', $linkedCplBks->pluck('id'))
            ->pluck('join_cpl_bk_id')
            ->unique()
            ->flip();

        $linkedPairMap = $linkedCplBks
            ->mapWithKeys(fn ($row) => [($row->cpl_id.'|'.$row->bk_id) => true])
            ->all();

        $lockedPairMap = $linkedCplBks
            ->filter(fn ($row) => $lockedJoinCplBkIds->has($row->id))
            ->mapWithKeys(fn ($row) => [($row->cpl_id.'|'.$row->bk_id) => true])
            ->all();

        return view('obe.cpl-bk')
                ->with('kurikulum', $kurikulum)
                ->with('cpls', $kurikulum->cpls)
                ->with('bks', $kurikulum->bks)
                ->with('linkedPairMap', $linkedPairMap)
                ->with('lockedPairMap', $lockedPairMap);
    }

    public function update(Request $request, Kurikulum $kurikulum, Cpl $cpl, Bk $bk)
    {
        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulums,id',
        ]);

        if ((string) $validated['kurikulum_id'] !== (string) $kurikulum->id) {
            return response()->json([
                'status' => 'error',
                'linked' => false,
                'message' => 'Kurikulum tidak valid.',
            ], 422);
        }

        $joincplbk = JoinCplBk::where('kurikulum_id', $kurikulum->id)
                                        ->where('cpl_id', $cpl->id)
                                        ->where('bk_id', $bk->id)
                                        ->first();

        $expectsJson = $request->expectsJson() || $request->ajax();

        if ($request->has('is_linked')) {
            if (!$joincplbk) {
                JoinCplBk::create([
                    'cpl_id' => $cpl->id,
                    'bk_id' => $bk->id,
                    'kurikulum_id' => $kurikulum->id,
                ]);
            }

            if ($expectsJson) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => true,
                    'message' => $bk->kode . ' telah diinteraksi dengan ' . $cpl->kode,
                ]);
            }

                return to_route('kurikulums.joincplbks.index', $kurikulum->id)
                    ->with('success', $bk->kode . ' telah diinteraksi dengan ' . $cpl->kode);
        } else {
            if ($joincplbk) {
                if (JoinCplMk::query()->where('join_cpl_bk_id', $joincplbk->id)->exists()) {
                    if ($expectsJson) {
                        return response()->json([
                            'status' => 'error',
                            'linked' => true,
                            'message' => 'Interaksi dikunci karena sudah digunakan pada relasi CPL >< MK.',
                        ], 422);
                    }

                    return to_route('kurikulums.joincplbks.index', $kurikulum->id)
                        ->with('error', 'Interaksi dikunci karena sudah digunakan pada relasi CPL >< MK.');
                }
                $joincplbk->delete();
                }

            if ($expectsJson) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => false,
                    'message' => $bk->kode . ' sudah tidak berinteraksi dengan ' . $cpl->kode,
                ]);
            }

                return to_route('kurikulums.joincplbks.index', $kurikulum->id)
                    ->with('warning', $bk->kode . ' sudah tidak berinteraksi dengan ' . $cpl->kode);
        }

    }
}
