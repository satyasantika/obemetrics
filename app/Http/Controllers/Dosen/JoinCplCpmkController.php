<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\JoinBkMk;
use App\Models\JoinCplBk;
use App\Models\JoinCplCpmk;
use Illuminate\Http\Request;
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
        $bk_ids = JoinBkMk::where('mk_id', $mk->id)->pluck('bk_id');
        $joincplbks = JoinCplBk::whereIn('bk_id', $bk_ids)->get();

        return view('obe.cpl-cpmk')
                ->with('mk', $mk)
                ->with('joincplbks', $joincplbks)
                ->with('cpmks', $mk->cpmks);
    }

    public function update(Request $request, JoinCplBk $joincplbk, Cpmk $cpmk)
    {
        $joincplcpmk = JoinCplCpmk::where('join_cpl_bk_id', $joincplbk->id)
                                        ->where('cpmk_id', $cpmk->id)
                                        ->first();
        if ($request->has('is_linked')) {
            if (!$joincplcpmk) {
            JoinCplCpmk::create([
                'join_cpl_bk_id' => $joincplbk->id,
                'cpmk_id' => $cpmk->id,
                'mk_id' => $request->mk_id,
            ]);
            }
            return to_route('mks.joincplcpmks.index',$request->mk_id)
                    ->with('success', $cpmk->kode . ' telah diinteraksi dengan ' . $joincplbk->cpl->kode);
        } else {
            if ($joincplcpmk) {
                $joincplcpmk->delete();
                }
            return to_route('mks.joincplcpmks.index',$request->mk_id)
                    ->with('warning', $cpmk->kode . ' sudah tidak berinteraksi dengan ' . $joincplbk->cpl->kode);
        }

    }
}
