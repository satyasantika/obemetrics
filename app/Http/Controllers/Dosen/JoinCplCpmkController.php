<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Cpmk;
use App\Models\JoinCplBk;
use App\Models\JoinCplCpmk;
use Illuminate\Http\Request;
use App\Actions\SyncMkState;
use App\Http\Controllers\Controller;

class JoinCplCpmkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join cpl cpmks', ['only' => ['index']]);
        $this->middleware('permission:update join cpl cpmks', ['only' => ['update']]);
    }

    public function index(Mk $mk)
    {
        $joincplbks = $mk->joinCplMks()
            ->with('joinCplBk')
            ->get()
            ->pluck('joinCplBk')
            ->filter()
            ->unique('id')
            ->values();

        $linkedPairMap = JoinCplCpmk::query()
            ->where('mk_id', $mk->id)
            ->get(['join_cpl_bk_id', 'cpmk_id'])
            ->mapWithKeys(fn ($row) => [($row->join_cpl_bk_id.'|'.$row->cpmk_id) => true])
            ->all();

        $lockedPairMap = JoinCplCpmk::query()
            ->where('mk_id', $mk->id)
            ->whereHas('subcpmks')
            ->get(['join_cpl_bk_id', 'cpmk_id'])
            ->mapWithKeys(fn ($row) => [($row->join_cpl_bk_id.'|'.$row->cpmk_id) => true])
            ->all();

        return view('obe.cpl-cpmk')
                ->with('mk', $mk)
                ->with('joincplbks', $joincplbks)
                ->with('cpmks', $mk->cpmks)
                ->with('linkedPairMap', $linkedPairMap)
                ->with('lockedPairMap', $lockedPairMap);
    }

    public function update(Request $request, Mk $mk, JoinCplBk $joincplbk, Cpmk $cpmk)
    {
        $joincplcpmk = JoinCplCpmk::where('mk_id', $mk->id)
                                        ->where('join_cpl_bk_id', $joincplbk->id)
                                        ->where('cpmk_id', $cpmk->id)
                                        ->first();
        $expectsJson = $request->expectsJson() || $request->ajax();

        if ($request->has('is_linked')) {
            if (!$joincplcpmk) {
                JoinCplCpmk::create([
                    'join_cpl_bk_id' => $joincplbk->id,
                    'cpmk_id' => $cpmk->id,
                    'mk_id' => $mk->id,
                ]);
            }

            SyncMkState::sync($mk->fresh());
            if ($expectsJson) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => true,
                    'message' => $cpmk->kode . ' telah diinteraksi dengan ' . $joincplbk->cpl->kode,
                ]);
            }

            return to_route('mks.joincplcpmks.index', $mk->id)
                    ->with('success', $cpmk->kode . ' telah diinteraksi dengan ' . $joincplbk->cpl->kode);
        } else {
            if ($joincplcpmk) {
                if ($joincplcpmk->subcpmks()->exists()) {
                    if ($expectsJson) {
                        return response()->json([
                            'status' => 'error',
                            'linked' => true,
                            'message' => 'Interaksi dikunci karena sudah digunakan pada data SubCPMK.',
                        ], 422);
                    }

                    return to_route('mks.joincplcpmks.index', $mk->id)
                            ->with('error', 'Interaksi dikunci karena sudah digunakan pada data SubCPMK.');
                }
                $joincplcpmk->delete();
                SyncMkState::sync($mk->fresh());
            }

            if ($expectsJson) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => false,
                    'message' => $cpmk->kode . ' sudah tidak berinteraksi dengan ' . $joincplbk->cpl->kode,
                ]);
            }

            return to_route('mks.joincplcpmks.index', $mk->id)
                    ->with('warning', $cpmk->kode . ' sudah tidak berinteraksi dengan ' . $joincplbk->cpl->kode);
        }
    }
}
