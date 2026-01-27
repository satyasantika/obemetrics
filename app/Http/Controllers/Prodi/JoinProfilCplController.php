<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Cpl;
use App\Models\Profil;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Models\JoinProfilCpl;
use App\Http\Controllers\Controller;

class JoinProfilCplController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join profil cpls', ['only' => ['index']]);
        $this->middleware('permission:update join profil cpls', ['only' => ['update']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $back_route = 'home.prodis';
        return view('obe.profil-cpl', compact('back_route'))
                ->with('kurikulum', $kurikulum)
                ->with('profils', $kurikulum->profils)
                ->with('cpls', $kurikulum->cpls);
    }

    public function update(Request $request, Profil $profil, Cpl $cpl)
    {
        $joinprofilcpl = JoinProfilCpl::where('profil_id', $profil->id)
                                        ->where('cpl_id', $cpl->id)
                                        ->first();

        if ($request->has('is_linked')) {
            if (!$joinprofilcpl) {
                JoinProfilCpl::create([
                    'profil_id' => $profil->id,
                    'cpl_id' => $cpl->id,
                    'kurikulum_id' => $request->kurikulum_id,
                ]);
            }
            return to_route('kurikulums.joinprofilcpls.index',$request->kurikulum_id)
                    ->with('success', $cpl->kode . ' telah diset untuk profil ' . $profil->nama);
        } else {
            if ($joinprofilcpl) {
                $joinprofilcpl->delete();
                }
            return to_route('kurikulums.joinprofilcpls.index',$request->kurikulum_id)
                    ->with('warning', $cpl->kode . ' telah dihapus dari profil ' . $profil->nama);
        }

    }
}
