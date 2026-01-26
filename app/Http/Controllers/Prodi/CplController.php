<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Cpl;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kurikulum;

class CplController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read cpls', ['only' => ['index','show']]);
        $this->middleware('permission:create cpls', ['only' => ['create','store']]);
        $this->middleware('permission:update cpls', ['only' => ['edit','update']]);
        $this->middleware('permission:delete cpls', ['only' => ['destroy']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $cpls = Cpl::where('kurikulum_id',$kurikulum->id)->get();
        return view('obe.cpl', compact('kurikulum','cpls'));
    }

    public function create(Kurikulum $kurikulum)
    {
        $cpl = new Cpl();
        return view('setting.cpl-form', compact('kurikulum','cpl'));
    }

    public function store(Request $request, Kurikulum $kurikulum, Cpl $cpl)
    {
        $name = $request->name;
        Cpl::create($request->all());

        return to_route('kurikulums.cpls.index', $kurikulum)->with('success','CPL: '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum, Cpl $cpl)
    {
        return view('setting.cpl-form', compact('kurikulum','cpl'));
    }

    public function update(Request $request, Kurikulum $kurikulum, Cpl $cpl)
    {
        $name = $cpl->nama;
        $data = $request->all();
        $cpl->fill($data)->save();

        return to_route('kurikulums.cpls.index', $kurikulum)->with('success','CPL: '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Cpl $cpl)
    {
        $name = $cpl->nama;
        $cpl->delete();
        return to_route('kurikulums.cpls.index', $kurikulum)->with('warning','CPL: '.$name.' telah dihapus');
    }

}
