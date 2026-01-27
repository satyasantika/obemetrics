<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Bk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kurikulum;

class BkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read bks', ['only' => ['index','show']]);
        $this->middleware('permission:create bks', ['only' => ['create','store']]);
        $this->middleware('permission:update bks', ['only' => ['edit','update']]);
        $this->middleware('permission:delete bks', ['only' => ['destroy']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $bks = Bk::where('kurikulum_id',$kurikulum->id)->get();
        return view('obe.bk', compact('kurikulum','bks'));
    }

    public function create(Kurikulum $kurikulum)
    {
        $bk = new Bk();
        return view('setting.bk-form', compact('kurikulum','bk'));
    }

    public function store(Request $request, Kurikulum $kurikulum, Bk $bk)
    {
        $name = $request->name;
        Bk::create($request->all());

        return to_route('kurikulums.bks.index', $kurikulum)->with('success','BK: '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum, Bk $bk)
    {
        return view('setting.bk-form', compact('kurikulum','bk'));
    }

    public function update(Request $request, Kurikulum $kurikulum, Bk $bk)
    {
        $name = $bk->nama;
        $data = $request->all();
        $bk->fill($data)->save();

        return to_route('kurikulums.bks.index', $kurikulum)->with('success','BK: '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Bk $bk)
    {
        $name = $bk->nama;
        $bk->delete();
        return to_route('kurikulums.bks.index', $kurikulum)->with('warning','BK: '.$name.' telah dihapus');
    }

}
