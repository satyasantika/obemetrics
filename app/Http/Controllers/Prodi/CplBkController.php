<?php

namespace App\Http\Controllers\Prodi;

use App\Actions\SyncKurikulumState;
use App\Models\Bk;
use App\Models\CplMk;
use App\Models\Cpl;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Models\CplBk;
use App\Http\Controllers\Controller;

class CplBkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join cpl bks', ['only' => ['index']]);
        $this->middleware('permission:update join cpl bks', ['only' => ['update']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $scopeCplIds = $kurikulum->cpls()->pluck('cpls.id');
        $scopeBkIds = $kurikulum->bks()->pluck('bks.id');

        $linkedCplBks = CplBk::query()
            ->whereIn('cpl_id', $scopeCplIds)
            ->whereIn('bk_id', $scopeBkIds)
            ->get(['id', 'cpl_id', 'bk_id']);

        $lockedCplBkIds = CplMk::query()
            ->whereIn('cpl_bk_id', $linkedCplBks->pluck('id'))
            ->pluck('cpl_bk_id')
            ->unique()
            ->flip();

        $linkedPairMap = $linkedCplBks
            ->mapWithKeys(fn ($row) => [($row->cpl_id.'|'.$row->bk_id) => true])
            ->all();

        $lockedPairMap = $linkedCplBks
            ->filter(fn ($row) => $lockedCplBkIds->has($row->id))
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

        if (!$kurikulum->cpls()->whereKey($cpl->id)->exists() || !$kurikulum->bks()->whereKey($bk->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'linked' => false,
                'message' => 'CPL/BK tidak berada pada kurikulum yang dipilih.',
            ], 422);
        }

        $cplBk = CplBk::where('cpl_id', $cpl->id)
            ->where('bk_id', $bk->id)
            ->first();

        $expectsJson = $request->expectsJson() || $request->ajax();

        if ($request->has('is_linked')) {
            if (!$cplBk) {
                CplBk::create([
                    'cpl_id' => $cpl->id,
                    'bk_id' => $bk->id,
                ]);
            }

            SyncKurikulumState::sync($kurikulum);

            if ($expectsJson) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => true,
                    'message' => $bk->kode . ' telah diinteraksi dengan ' . $cpl->kode,
                ]);
            }

                return to_route('kurikulums.cplbks.index', $kurikulum->id)
                    ->with('success', $bk->kode . ' telah diinteraksi dengan ' . $cpl->kode);
        } else {
            if ($cplBk) {
                if (CplMk::query()->where('cpl_bk_id', $cplBk->id)->exists()) {
                    if ($expectsJson) {
                        return response()->json([
                            'status' => 'error',
                            'linked' => true,
                            'message' => 'Interaksi dikunci karena sudah digunakan pada relasi CPL >< MK.',
                        ], 422);
                    }

                    return to_route('kurikulums.cplbks.index', $kurikulum->id)
                        ->with('error', 'Interaksi dikunci karena sudah digunakan pada relasi CPL >< MK.');
                }
                $cplBk->delete();
                }
            SyncKurikulumState::sync($kurikulum);

            if ($expectsJson) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => false,
                    'message' => $bk->kode . ' sudah tidak berinteraksi dengan ' . $cpl->kode,
                ]);
            }

                return to_route('kurikulums.cplbks.index', $kurikulum->id)
                    ->with('warning', $bk->kode . ' sudah tidak berinteraksi dengan ' . $cpl->kode);
        }

    }
}
