<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Bk;
use App\Models\Cpl;
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
        return view('obe.cpl-bk')
                ->with('kurikulum', $kurikulum)
                ->with('cpls', $kurikulum->cpls)
                ->with('bks', $kurikulum->bks);
    }

    public function update(Request $request, Cpl $cpl, Bk $bk)
    {
        $joincplbk = JoinCplBk::where('cpl_id', $cpl->id)
                                        ->where('bk_id', $bk->id)
                                        ->first();

        if ($request->has('is_linked')) {
            if (!$joincplbk) {
                JoinCplBk::create([
                    'cpl_id' => $cpl->id,
                    'bk_id' => $bk->id,
                    'kurikulum_id' => $request->kurikulum_id,
                ]);
            }
            return to_route('kurikulums.joincplbks.index',$request->kurikulum_id)
                    ->with('success', $bk->kode . ' telah diinteraksi dengan ' . $cpl->kode);
        } else {
            if ($joincplbk) {
                $joincplbk->delete();
                }
            return to_route('kurikulums.joincplbks.index',$request->kurikulum_id)
                    ->with('warning', $bk->kode . ' sudah tidak berinteraksi dengan ' . $cpl->kode);
        }

    }
}
