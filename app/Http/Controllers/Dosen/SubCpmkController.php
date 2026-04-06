<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Cpmk;
use App\Models\Subcpmk;
use App\Models\JoinCplCpmk;
use Illuminate\Http\Request;
use App\Actions\SyncMkState;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SubCpmkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read subcpmks', ['only' => ['index','show']]);
        $this->middleware('permission:create subcpmks', ['only' => ['create','store']]);
        $this->middleware('permission:update subcpmks', ['only' => ['edit','update']]);
        $this->middleware('permission:delete subcpmks', ['only' => ['destroy']]);
    }

    public function index(Mk $mk)
    {
        $join_cpl_cpmks = JoinCplCpmk::where('mk_id',$mk->id)->get();

        $total_bobot = DB::table('join_subcpmk_penugasans')
            ->join('subcpmks', 'subcpmks.id', '=', 'join_subcpmk_penugasans.subcpmk_id')
            ->join('join_cpl_cpmks', 'join_cpl_cpmks.id', '=', 'subcpmks.join_cpl_cpmk_id')
            ->leftJoin('penugasans', 'penugasans.id', '=', 'join_subcpmk_penugasans.penugasan_id')
            ->where('join_cpl_cpmks.mk_id', $mk->id)
            ->sum(DB::raw('COALESCE(penugasans.bobot,0) * COALESCE(join_subcpmk_penugasans.bobot,0) / 100'));

        $cpmks = Cpmk::where('mk_id',$mk->id)->get();
        return view('obe.subcpmk', compact('mk','cpmks','total_bobot'));
    }

    public function create(Mk $mk)
    {
        return to_route('mks.subcpmks.index', $mk)
            ->with('warning', 'Gunakan tombol Tambah Sub CPMK (modal) pada halaman Sub CPMK.');
    }

    public function store(Request $request, Mk $mk, Subcpmk $subcpmk)
    {
        $kode = $request->kode;
        $name = $request->nama;
        $subcpmk_data = $kode.' - '.$name;
        $data = $request->all();
        Subcpmk::create($data);
        SyncMkState::sync($mk->fresh());
        return to_route('mks.subcpmks.index', $mk)->with('success',$subcpmk_data.' telah ditambahkan');
    }

    public function edit(Mk $mk, Subcpmk $subcpmk)
    {
        return to_route('mks.subcpmks.index', $mk)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar Sub CPMK.');
    }

    public function update(Request $request, Mk $mk, Subcpmk $subcpmk)
    {
        $subcpmk_data = $request->kode.' - '.$request->nama;
        $data = $request->all();
        $subcpmk->fill($data)->save();
        SyncMkState::sync($mk->fresh());
        return to_route('mks.subcpmks.index', $mk)->with('success',$subcpmk_data.' telah diperbarui');
    }

    public function destroy(Mk $mk, Subcpmk $subcpmk)
    {
        $subcpmk_data = $subcpmk->kode.' - '.$subcpmk->nama;
        if ($subcpmk->joinSubcpmkPenugasans()->exists()) {
            return to_route('mks.subcpmks.index', $mk)
                ->with('error', $subcpmk_data.' tidak dapat dihapus karena sudah digunakan pada relasi SubCPMK >< Penugasan.');
        }
        $subcpmk->delete();
        SyncMkState::sync($mk->fresh());
        return to_route('mks.subcpmks.index', $mk)->with('warning',$subcpmk_data.' telah dihapus');
    }
}
