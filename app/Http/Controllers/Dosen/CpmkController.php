<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Cpmk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CpmkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read cpmks', ['only' => ['index','show']]);
        $this->middleware('permission:create cpmks', ['only' => ['create','store']]);
        $this->middleware('permission:update cpmks', ['only' => ['edit','update']]);
        $this->middleware('permission:delete cpmks', ['only' => ['destroy']]);
    }

    public function index(Mk $mk)
    {
        $cpmks = Cpmk::where('mk_id',$mk->id)->get();
        return view('obe.cpmk', compact('mk','cpmks'));
    }

    public function create(Mk $mk)
    {
        $cpmk = new Cpmk();
        return view('setting.cpmk-form', compact('mk','cpmk'));
    }

    public function store(Request $request, Mk $mk, Cpmk $cpmk)
    {
        $name = $request->nama;
        $data = $request->all();
        Cpmk::create($data);

        return to_route('mks.cpmks.index', $mk)->with('success','CPMK: '.$name.' telah ditambahkan');
    }

    public function edit(Mk $mk, Cpmk $cpmk)
    {
        return view('setting.cpmk-form', compact('mk','cpmk'));
    }

    public function update(Request $request, Mk $mk, Cpmk $cpmk)
    {
        $name = $cpmk->nama;
        $data = $request->all();
        $cpmk->fill($data)->save();

        return to_route('mks.cpmks.index', $mk)->with('success','CPMK: '.$name.' telah diperbarui');
    }

    public function destroy(Mk $mk, Cpmk $cpmk)
    {
        $name = $cpmk->nama;
        $cpmk->delete();
        return to_route('mks.cpmks.index', $mk)->with('warning','CPMK: '.$name.' telah dihapus');
    }
}
