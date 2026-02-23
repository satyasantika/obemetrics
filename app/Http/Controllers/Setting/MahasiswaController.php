<?php

namespace App\Http\Controllers\Setting;

use App\Models\Prodi;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\MahasiswasDataTable;

class MahasiswaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read mahasiswas', ['only' => ['index','show']]);
        $this->middleware('permission:create mahasiswas', ['only' => ['create','store']]);
        $this->middleware('permission:update mahasiswas', ['only' => ['edit','update']]);
        $this->middleware('permission:delete mahasiswas', ['only' => ['destroy']]);
    }

    public function index(MahasiswasDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting', $this->_dataSelection(new Mahasiswa()));
    }

    public function create()
    {
        return to_route('mahasiswas.index')->with('warning', 'Gunakan tombol tambah (modal) pada halaman Mahasiswa.');
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        Mahasiswa::create($request->all());
        return to_route('mahasiswas.index')->with('success','mahasiswa '.$name.' telah ditambahkan');
    }

    public function edit(Mahasiswa $mahasiswa)
    {
        return to_route('mahasiswas.index')->with('warning', 'Gunakan tombol edit (modal) pada daftar Mahasiswa.');
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $name = strtoupper($mahasiswa->name);
        $data = $request->all();
        $mahasiswa->fill($data)->save();

        return to_route('mahasiswas.index')->with('success','mahasiswa '.$name.' telah diperbarui');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        $name = strtoupper($mahasiswa->name);
        $mahasiswa->delete();
        return to_route('mahasiswas.index')->with('danger','mahasiswa '.$name.' telah dihapus');
    }

    private function _dataSelection($mahasiswa)
    {
        return [
            'prodis' => Prodi::all(),
            'mahasiswa' => $mahasiswa,
            'mahasiswas' => Mahasiswa::with('prodi')->orderBy('nama')->get(),
            'header' => 'Data Mahasiswa',
            'title' => 'Mahasiswa',
        ];
    }
}
