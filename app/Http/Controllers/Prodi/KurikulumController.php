<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Prodi;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Models\JoinProdiUser;
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
        $header = 'Data Kurikulum Program Studi';
        return $dataTable->render('layouts.setting', compact('header'));
    }

    public function create()
    {
        $prodi_id = JoinProdiUser::where('user_id', auth()->id())->first()->prodi_id;
        $kurikulum = new Kurikulum();
        return view('setting.kurikulum-form', $this->_dataSelection($prodi_id,$kurikulum));
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        Kurikulum::create($request->all());

        return to_route('kurikulums.index')->with('success','kurikulum '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum)
    {
        return view('setting.kurikulum-form', $this->_dataSelection($kurikulum->prodi_id,$kurikulum));
    }

    public function update(Request $request, Kurikulum $kurikulum)
    {
        $name = strtoupper($kurikulum->prodi->nama);
        $data = $request->all();
        $kurikulum->fill($data)->save();

        return to_route('kurikulums.index')->with('success','Kurikulum '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum)
    {
        $name = strtoupper($kurikulum->name);
        $kurikulum->delete();
        return to_route('kurikulums.index')->with('warning','Kurikulum '.$name.' telah dihapus');
    }

    private function _dataSelection($prodi_id,$kurikulum)
    {
        return [
            'header' => 'Data Kurikulum Program Studi'.Prodi::find($prodi_id)->nama,
            'kurikulum' => $kurikulum,
            'prodi_id' => $prodi_id,
        ];
    }
}
