<?php

namespace App\Http\Controllers\Setting;

use App\Models\Metode;
use Illuminate\Http\Request;
use App\DataTables\MetodesDataTable;
use App\Http\Controllers\Controller;

class MetodeController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read metodes', ['only' => ['index','show']]);
        $this->middleware('permission:create metodes', ['only' => ['create','store']]);
        $this->middleware('permission:update metodes', ['only' => ['edit','update']]);
        $this->middleware('permission:delete metodes', ['only' => ['destroy']]);
    }

    public function index(MetodesDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting', $this->_dataSelection(''));
    }

    public function create()
    {
        $metode = new Metode();
        return view('setting.metode-form', $this->_dataSelection($metode));
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->nama);
        Metode::create($request->all());

        return to_route('metodes.index')->with('success','metode '.$name.' telah ditambahkan');
    }

    public function edit(Metode $metode)
    {
        return view('setting.metode-form', $this->_dataSelection($metode));
    }

    public function update(Request $request, Metode $metode)
    {
        $name = strtoupper($metode->nama);
        $data = $request->all();
        $metode->fill($data)->save();

        return to_route('metodes.index')->with('success','Metode '.$name.' telah diperbarui');
    }

    public function destroy(Metode $metode)
    {
        $name = strtoupper($metode->nama);
        $metode->delete();
        return to_route('metodes.index')->with('warning','Metode '.$name.' telah dihapus');
    }

    private function _dataSelection($metode)
    {
        return [
            'metode' => $metode,
            'header' => 'Data Metode Perkuliahan',
            'title' => 'Metode',
        ];
    }
}
