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
        return $dataTable->render('layouts.setting', $this->_dataSelection(new Evaluasi()));
    }

    public function create()
    {
        return to_route('evaluasis.index')->with('warning', 'Gunakan tombol tambah (modal) pada halaman Evaluasi.');
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->nama);
        Evaluasi::create($request->all());

        return to_route('evaluasis.index')->with('success','evaluasi '.$name.' telah ditambahkan');
    }

    public function edit(Evaluasi $evaluasi)
    {
        return to_route('evaluasis.index')->with('warning', 'Gunakan tombol edit (modal) pada daftar Evaluasi.');
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

        if ($evaluasi->penugasans()->exists()) {
            return to_route('evaluasis.index')->with('error','Evaluasi '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $evaluasi->delete();
        return to_route('evaluasis.index')->with('warning','Evaluasi '.$name.' telah dihapus');
    }

    private function _dataSelection($evaluasi)
    {
        return [
            'evaluasi' => $evaluasi,
            'evaluasis' => Evaluasi::orderBy('kode')->get(),
            'header' => 'Data Evaluasi Perkuliahan',
            'title' => 'Evaluasi',
        ];
    }
}
