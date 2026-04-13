<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Prodi;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Models\ProdiUser;
use App\Http\Controllers\Controller;
use App\DataTables\KurikulumsDataTable;

class KurikulumController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read kurikulums', ['only' => ['index','show']]);
        $this->middleware('permission:create kurikulums', ['only' => ['create','store']]);
        $this->middleware('permission:update kurikulums', ['only' => ['edit','update']]);
        $this->middleware('permission:delete kurikulums', ['only' => ['destroy']]);
    }

    public function index(KurikulumsDataTable $dataTable)
    {
        return $dataTable->with($this->_dataSelection('',''))->render('layouts.setting');
    }

    public function create(Prodi $prodi)
    {
        return to_route('home')->with('warning', 'Gunakan tombol tambah (modal) pada halaman Dashboard Prodi.');
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        Kurikulum::create($request->all());

        return to_route('ruang.prodi')->with('success','kurikulum '.$name.' telah ditambahkan');
    }

    public function edit(Prodi $prodi, Kurikulum $kurikulum)
    {
        return to_route('home')->with('warning', 'Gunakan tombol edit (modal) pada daftar Kurikulum.');
    }

    public function update(Request $request, Prodi $prodi, Kurikulum $kurikulum)
    {
        $name = strtoupper($kurikulum->prodi->nama);
        $data = $request->all();
        $kurikulum->fill($data)->save();

        return to_route('ruang.prodi')->with('success','Kurikulum '.$name.' telah diperbarui');
    }

    public function destroy(Prodi $prodi, Kurikulum $kurikulum)
    {
        $name = strtoupper($kurikulum->name);

        $isUsed = $kurikulum->profils()->exists()
            || $kurikulum->cpls()->exists()
            || $kurikulum->bks()->exists()
            || $kurikulum->mks()->exists()
            || $kurikulum->profilCpls()->exists()
            || $kurikulum->joinCplBks()->exists()
            || $kurikulum->joinCplMks()->exists()
            || $kurikulum->joinMkUsers()->exists();

        if ($isUsed) {
            return to_route('home')->with('error','Kurikulum '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $kurikulum->delete();
        return to_route('home')->with('warning','Kurikulum '.$name.' telah dihapus');
    }

    private function _dataSelection($prodi, $kurikulum)
    {
        $prodi_ids = ProdiUser::where('user_id', auth()->id())->pluck('prodi_id');
        $prodis = Prodi::whereIn('id', $prodi_ids)->get();
        return [
            'header' => 'Data Kurikulum Program Studi '.Prodi::find($prodi->id)->nama,
            'kurikulum' => $kurikulum,
            'prodi_ids' => $prodi_ids,
            'prodis' => $prodis,
            'prodi' => $prodi,
        ];
    }
}
