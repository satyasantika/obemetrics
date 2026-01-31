<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Cpmk;
use App\Models\SubCpmk;
use App\Models\JoinCplCpmk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $cpmks = Cpmk::where('mk_id',$mk->id)->get();
        return view('obe.subcpmk', compact('mk','cpmks'));
    }

    public function create(Mk $mk)
    {
        $join_cpl_cpmks = JoinCplCpmk::where('mk_id', $mk->id)->get();
        $subcpmk = new SubCpmk();
        return view('setting.subcpmk-form', compact('mk','subcpmk','join_cpl_cpmks'));
    }

    public function store(Request $request, Mk $mk, SubCpmk $subcpmk)
    {
        $kode = $request->kode;
        $name = $request->nama;
        $subcpmk_data = $kode.' - '.$name;
        $data = $request->all();
        SubCpmk::create($data);
        return to_route('mks.subcpmks.index', $mk)->with('success',$subcpmk_data.' telah ditambahkan');
    }

    public function edit(Mk $mk, SubCpmk $subcpmk)
    {
        $join_cpl_cpmks = JoinCplCpmk::where('mk_id', $mk->id)->get();
        return view('setting.subcpmk-form', compact('mk','subcpmk','join_cpl_cpmks'));
    }

    public function update(Request $request, Mk $mk, SubCpmk $subcpmk)
    {
        $subcpmk_data = $request->kode.' - '.$request->nama;
        $data = $request->all();
        $subcpmk->fill($data)->save();
        return to_route('mks.subcpmks.index', $mk)->with('success',$subcpmk_data.' telah diperbarui');
    }

    public function destroy(Mk $mk, SubCpmk $subcpmk)
    {
        $subcpmk_data = $subcpmk->kode.' - '.$subcpmk->nama;
        $subcpmk->delete();
        return to_route('mks.subcpmks.index', $mk)->with('warning',$subcpmk_data.' telah dihapus');
    }
}
