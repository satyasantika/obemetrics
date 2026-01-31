<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Subcpmk;
use App\Models\Semester;
use App\Models\Pertemuan;
use App\Models\JoinCplCpmk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PertemuanController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read pertemuans', ['only' => ['index','show']]);
        $this->middleware('permission:create pertemuans', ['only' => ['create','store']]);
        $this->middleware('permission:update pertemuans', ['only' => ['edit','update']]);
        $this->middleware('permission:delete pertemuans', ['only' => ['destroy']]);
    }

    public function index(Mk $mk)
    {
        $join_cpl_cpmks = JoinCplCpmk::where('mk_id', $mk->id)->get();
        $subcpmks = Subcpmk::whereIn('join_cpl_cpmk_id',$join_cpl_cpmks->pluck('id'))->get();
        $pertemuans = Pertemuan::where('mk_id', $mk->id)->get();
        return view('obe.pertemuan', compact('mk','subcpmks','pertemuans'));
    }

    public function create(Mk $mk)
    {
        $join_cpl_cpmks = JoinCplCpmk::where('mk_id', $mk->id)->get();
        $subcpmks = Subcpmk::whereIn('join_cpl_cpmk_id',$join_cpl_cpmks->pluck('id'))->get();
        $semesters = Semester::all();
        $pertemuan = new Pertemuan();
        return view('setting.pertemuan-form', compact('mk','pertemuan','subcpmks','semesters'));
    }

    public function store(Request $request, Mk $mk, Pertemuan $pertemuan)
    {
        $ke = $request->ke;
        $data = $request->all();
        Pertemuan::create($data);
        return to_route('mks.pertemuans.index', $mk)->with('success','Pertemuan ke-'.$ke.' telah ditambahkan');
    }

    public function edit(Mk $mk, Pertemuan $pertemuan)
    {
        $join_cpl_cpmks = JoinCplCpmk::where('mk_id', $mk->id)->get();
        $subcpmks = Subcpmk::whereIn('join_cpl_cpmk_id',$join_cpl_cpmks->pluck('id'))->get();
        return view('setting.pertemuan-form', compact('mk','pertemuan','subcpmks'));
    }

    public function update(Request $request, Mk $mk, Pertemuan $pertemuan)
    {
        $ke = $request->ke;
        $data = $request->all();
        $pertemuan->fill($data)->save();
        return to_route('mks.pertemuans.index', $mk)->with('success','Pertemuan ke-'.$ke.' telah diperbarui');
    }

    public function destroy(Mk $mk, Pertemuan $pertemuan)
    {
        $ke = $pertemuan->ke;
        $pertemuan->delete();
        return to_route('mks.pertemuans.index', $mk)->with('warning','Pertemuan ke-'.$ke.' telah dihapus');
    }
}
