<?php

namespace App\Http\Controllers\Setting;

use App\Models\Prodi;
use Illuminate\Http\Request;
use App\DataTables\ProdisDataTable;
use App\Http\Controllers\Controller;

class ProdiController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read prodis', ['only' => ['index','show']]);
        $this->middleware('permission:create prodis', ['only' => ['create','store']]);
        $this->middleware('permission:update prodis', ['only' => ['edit','update']]);
        $this->middleware('permission:delete prodis', ['only' => ['destroy']]);
    }

    public function index(ProdisDataTable $dataTable)
    {
        $header = 'Data Program Studi';
        return $dataTable->render('layouts.setting', compact('header'));
    }

    public function create()
    {
        $prodi = new Prodi();
        return view('setting.prodi-form', array_merge(
            [
                'prodi'=> $prodi,
            ],
        ));
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        Prodi::create($request->all());

        return to_route('prodis.index')->with('success','prodi '.$name.' telah ditambahkan');
    }

    public function edit(Prodi $prodi)
    {
        return view('setting.prodi-form', array_merge(
            [
                'prodi'=> $prodi,
            ],
        ));
    }

    public function update(Request $request, Prodi $prodi)
    {
        $name = strtoupper($prodi->name);
        $data = $request->all();
        $prodi->fill($data)->save();

        return to_route('prodis.index')->with('success','Prodi '.$name.' telah diperbarui');
    }

    public function destroy(Prodi $prodi)
    {
        $name = strtoupper($prodi->name);
        $prodi->delete();
        return to_route('prodis.index')->with('warning','Prodi '.$name.' telah dihapus');
    }
}
