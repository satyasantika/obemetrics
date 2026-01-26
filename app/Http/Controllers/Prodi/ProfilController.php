<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Profil;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kurikulum;

class ProfilController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read profils', ['only' => ['index','show']]);
        $this->middleware('permission:create profils', ['only' => ['create','store']]);
        $this->middleware('permission:update profils', ['only' => ['edit','update']]);
        $this->middleware('permission:delete profils', ['only' => ['destroy']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $profils = Profil::where('kurikulum_id',$kurikulum->id)->get();
        return view('obe.kurikulum', compact('kurikulum','profils'));
    }

    public function create(Kurikulum $kurikulum)
    {
        $profil = new Profil();
        return view('setting.profil-form', compact('kurikulum','profil'));
    }

    public function store(Request $request, Kurikulum $kurikulum, Profil $profil)
    {
        $name = strtoupper($request->name);
        Profil::create($request->all());

        return to_route('kurikulums.profils.index', $kurikulum)->with('success','profil '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum, Profil $profil)
    {
        return view('setting.profil-form', compact('kurikulum','profil'));
    }

    public function update(Request $request, Kurikulum $kurikulum, Profil $profil)
    {
        $name = strtoupper($profil->nama);
        $data = $request->all();
        $profil->fill($data)->save();

        return to_route('kurikulums.profils.index', $kurikulum)->with('success','Profil '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Profil $profil)
    {
        $name = strtoupper($profil->nama);
        $profil->delete();
        return to_route('kurikulums.profils.index', $kurikulum)->with('warning','Profil '.$name.' telah dihapus');
    }

}
