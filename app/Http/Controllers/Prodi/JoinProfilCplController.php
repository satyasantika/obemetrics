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
        return view('obe.profil-cpl')
                ->with('kurikulum', $kurikulum)
                ->with('profils', $kurikulum->profils)
                ->with('cpls', $kurikulum->cpls);
    }

    public function update(Request $request, Kurikulum $kurikulum, Profil $profil, Cpl $cpl)
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

        $joinprofilcpl = JoinProfilCpl::where('kurikulum_id', $kurikulum->id)
                                        ->where('profil_id', $profil->id)
                                        ->where('cpl_id', $cpl->id)
                                        ->first();
        $expectsJson = $request->expectsJson() || $request->ajax();

        if ($request->has('is_linked')) {
            if (!$joinprofilcpl) {
                JoinProfilCpl::create([
                    'profil_id' => $profil->id,
                    'cpl_id' => $cpl->id,
                    'kurikulum_id' => $kurikulum->id,
                ]);
            }

            if ($expectsJson) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => true,
                    'message' => $cpl->kode . ' telah diset untuk profil ' . $profil->nama,
                ]);
            }

            return back()
                    ->with('success', $cpl->kode . ' telah diset untuk profil ' . $profil->nama);
        } else {
            if ($joinprofilcpl) {
                $joinprofilcpl->delete();
                }

            if ($expectsJson) {
                return response()->json([
                    'status' => 'ok',
                    'linked' => false,
                    'message' => $cpl->kode . ' telah dihapus dari profil ' . $profil->nama,
                ]);
            }

                return to_route('kurikulums.joinprofilcpls.index', $kurikulum->id)
                    ->with('warning', $cpl->kode . ' telah dihapus dari profil ' . $profil->nama);
        }

    }
}
