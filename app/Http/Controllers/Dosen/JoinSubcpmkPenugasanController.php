<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Penugasan;
use App\Models\Subcpmk;
use App\Models\JoinSubcpmkPenugasan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JoinSubcpmkPenugasanController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join subcpmk penugasans', ['only' => ['index']]);
        $this->middleware('permission:update join subcpmk penugasans', ['only' => ['update']]);
    }

    public function index(Mk $mk)
    {
        $subcpmks = Subcpmk::all();
        $penugasans = Penugasan::where('mk_id', $mk->id)->orderBy('kode')->get();

        return view('obe.subcpmk-penugasan')
                ->with('mk', $mk)
                ->with('subcpmks', $subcpmks)
                ->with('penugasans', $penugasans);
    }

    public function update(Request $request, Subcpmk $subcpmk, Penugasan $penugasan)
    {
        $subcpmkpenugasan = JoinSubcpmkPenugasan::where('subcpmk_id', $subcpmk->id)
                                        ->where('penugasan_id', $penugasan->id)
                                        ->first();
        if ($request->has('is_linked')) {
            if (!$subcpmkpenugasan) {
            JoinSubcpmkPenugasan::create([
                'subcpmk_id' => $subcpmk->id,
                'penugasan_id' => $penugasan->id,
                'mk_id' => $request->mk_id,
            ]);
            }
            return to_route('mks.joinsubcpmkpenugasans.index',$request->mk_id)
                    ->with('success', $subcpmk->kode . ' telah dihubungkan dengan ' . $penugasan->nama . '.');
        } elseif ($request->has('bobot')) {
            // Update bobot
            if ($subcpmkpenugasan) {
                $request->validate([
                    'bobot' => 'required|numeric|min:0|max:100',
                ]);

                $subcpmkpenugasan->update([
                    'bobot' => $request->bobot,
                ]);

                return to_route('mks.joinsubcpmkpenugasans.index', $request->mk_id)
                        ->with('success', 'Bobot ' . $subcpmk->kode . ' untuk ' . $penugasan->nama . ' berhasil diperbarui.');
            }
            return to_route('mks.joinsubcpmkpenugasans.index', $request->mk_id)
                    ->with('error', 'Data tidak ditemukan.');
        } else {
            if ($subcpmkpenugasan) {
                $subcpmkpenugasan->delete();
                }
            return to_route('mks.joinsubcpmkpenugasans.index',$request->mk_id)
                    ->with('warning', $subcpmk->kode . ' telah dihapus dari ' . $penugasan->nama . '.');
        }

    }
}
