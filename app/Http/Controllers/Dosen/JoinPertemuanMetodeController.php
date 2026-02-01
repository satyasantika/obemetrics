<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Metode;
use App\Models\Pertemuan;
use App\Models\JoinPertemuanMetode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JoinPertemuanMetodeController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join pertemuan metodes', ['only' => ['index']]);
        $this->middleware('permission:update join pertemuan metodes', ['only' => ['update']]);
    }

    public function index(Mk $mk)
    {
        $pertemuans = Pertemuan::where('mk_id', $mk->id)->get();
        $metodes = Metode::all();

        return view('obe.pertemuan-metode')
                ->with('mk', $mk)
                ->with('pertemuans', $pertemuans)
                ->with('metodes', $metodes);
    }

    public function update(Request $request, Pertemuan $pertemuan, Metode $metode)
    {
        $pertemuanmetode = JoinPertemuanMetode::where('pertemuan_id', $pertemuan->id)
                                        ->where('metode_id', $metode->id)
                                        ->first();
        if ($request->has('is_linked')) {
            if (!$pertemuanmetode) {
            JoinPertemuanMetode::create([
                'pertemuan_id' => $pertemuan->id,
                'metode_id' => $metode->id,
                'mk_id' => $request->mk_id,
            ]);
            }
            return to_route('mks.joinpertemuanmetodes.index',$request->mk_id)
                    ->with('success', $metode->nama . ' dipakai pada pertemuan ke-' . $pertemuan->ke);
        } else {
            if ($pertemuanmetode) {
                $pertemuanmetode->delete();
                }
            return to_route('mks.joinpertemuanmetodes.index',$request->mk_id)
                    ->with('warning', $metode->nama . ' tidak dipakai pada pertemuan ke-' . $pertemuan->ke);
        }

    }
}
