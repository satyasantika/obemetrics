<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Profil;
use Illuminate\Http\Request;
use App\Models\ProfilIndikator;
use App\Http\Controllers\Controller;
use App\DataTables\ProfilIndikatorsDataTable;

class ProfilIndikatorController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:create profil indikators', ['only' => ['create','store']]);
        $this->middleware('permission:update profil indikators', ['only' => ['edit','update']]);
        $this->middleware('permission:delete profil indikators', ['only' => ['destroy']]);
    }

    public function create(Profil $profil)
    {
        $profilindikator = new ProfilIndikator();
        return view('setting.profilindikator-form', compact('profil','profilindikator'));
    }

    public function store(Request $request, Profil $profil, ProfilIndikator $profilindikator)
    {
        $name_profil = strtoupper($profil->nama);
        ProfilIndikator::create($request->all());

        return to_route('kurikulums.profils.index', $profil->kurikulum)->with('success','Indikator telah ditambahkan untuk Profil '.$name_profil);
    }

    public function edit(Profil $profil, ProfilIndikator $profilindikator)
    {
        return view('setting.profilindikator-form', compact('profil','profilindikator'));
    }

    public function update(Request $request, Profil $profil, ProfilIndikator $profilindikator)
    {
        $name_indikator = $profilindikator->nama;
        $name_profil = strtoupper($profil->nama);
        $data = $request->all();
        $profilindikator->fill($data)->save();

        return to_route('kurikulums.profils.index', $profil->kurikulum)->with('success','Indikator: '.$name_indikator.' dari Profil '.$name_profil.' telah diperbarui');
    }

    public function destroy(Profil $profil, ProfilIndikator $profilindikator)
    {
        $name_indikator = $profilindikator->nama;
        $name_profil = strtoupper($profil->nama);
        $profilindikator->delete();
        return to_route('kurikulums.profils.index', $profil->kurikulum)->with('warning','Indikator: '.$name_indikator.' dari Profil '.$name_profil.' telah dihapus');
    }
}
