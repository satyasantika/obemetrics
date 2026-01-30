<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Mk;
use App\Models\Bk;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Models\JoinBkMk;
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
        return view('obe.bk-mk')
                ->with('kurikulum', $kurikulum)
                ->with('bks', $kurikulum->bks)
                ->with('mks', $kurikulum->mks);
    }

    public function update(Request $request, Bk $bk, Mk $mk)
    {
        $joinbkmk = JoinBkMk::where('bk_id', $bk->id)
                                        ->where('mk_id', $mk->id)
                                        ->first();

        if ($request->has('is_linked')) {
            if (!$joinbkmk) {
                JoinBkMk::create([
                    'bk_id' => $bk->id,
                    'mk_id' => $mk->id,
                    'kurikulum_id' => $request->kurikulum_id,
                ]);
            }
            return to_route('kurikulums.joinbkmks.index',$request->kurikulum_id)
                    ->with('success', $mk->kode . ' telah diinteraksi dengan ' . $bk->kode);
        } else {
            if ($joinbkmk) {
                $joinbkmk->delete();
                }
            return to_route('kurikulums.joinbkmks.index',$request->kurikulum_id)
                    ->with('warning', $mk->kode . ' sudah tidak berinteraksi dengan ' . $bk->kode);
        }

    }
}
