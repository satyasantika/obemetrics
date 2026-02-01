<?php

namespace App\Http\Controllers\Setting;

use App\Models\Evaluasi;
use Illuminate\Http\Request;
use App\DataTables\EvaluasisDataTable;
use App\Http\Controllers\Controller;

class EvaluasiController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read evaluasis', ['only' => ['index','show']]);
        $this->middleware('permission:create evaluasis', ['only' => ['create','store']]);
        $this->middleware('permission:update evaluasis', ['only' => ['edit','update']]);
        $this->middleware('permission:delete evaluasis', ['only' => ['destroy']]);
    }

    public function index(EvaluasisDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting', $this->_dataSelection(''));
    }

    public function create()
    {
        $evaluasi = new Evaluasi();
        return view('setting.evaluasi-form', $this->_dataSelection($evaluasi));
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->nama);
        Evaluasi::create($request->all());

        return to_route('evaluasis.index')->with('success','evaluasi '.$name.' telah ditambahkan');
    }

    public function edit(Evaluasi $evaluasi)
    {
        return view('setting.evaluasi-form', $this->_dataSelection($evaluasi));
    }

    public function update(Request $request, Evaluasi $evaluasi)
    {
        $name = strtoupper($evaluasi->nama);
        $data = $request->all();
        $evaluasi->fill($data)->save();
        return to_route('evaluasis.index')->with('success','Evaluasi '.$name.' telah diperbarui');
    }

    public function destroy(Evaluasi $evaluasi)
    {
        $name = strtoupper($evaluasi->nama);
        $evaluasi->delete();
        return to_route('evaluasis.index')->with('warning','Evaluasi '.$name.' telah dihapus');
    }

    private function _dataSelection($evaluasi)
    {
        return [
            'evaluasi' => $evaluasi,
            'header' => 'Data Evaluasi Perkuliahan',
            'title' => 'Evaluasi',
        ];
    }
}
