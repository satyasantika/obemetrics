<?php

namespace App\Http\Controllers\Setting;

use App\Models\Prodi;
use App\Models\ProdiUser;
use App\Actions\SyncProdiState;
use App\States\Prodi\Aktif as ProdiAktif;
use App\States\Prodi\Draft as ProdiDraft;
use Illuminate\Http\Request;
use App\DataTables\ProdisDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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
        return $dataTable->render('layouts.setting', $this->_dataSelection(new Prodi()));
    }

    public function create()
    {
        return to_route('prodis.index')->with('warning', 'Gunakan tombol tambah (modal) pada halaman Prodi.');
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        $data = $request->all();
        $data['status'] = ProdiDraft::$name;
        Prodi::create($data);

        return to_route('prodis.index')->with('success','prodi '.$name.' telah ditambahkan');
    }

    public function edit(Prodi $prodi)
    {
        return to_route('prodis.index')->with('warning', 'Gunakan tombol edit (modal) pada daftar Prodi.');
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

        $isUsed = $prodi->kurikulums()->exists()
            || $prodi->mahasiswas()->exists()
            || ProdiUser::query()->where('prodi_id', $prodi->id)->exists();

        if ($isUsed) {
            return to_route('prodis.index')->with('error','Prodi '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $prodi->delete();
        return to_route('prodis.index')->with('warning','Prodi '.$name.' telah dihapus');
    }

    private function _dataSelection($prodi)
    {
        $usedProdiIds = collect()
            ->merge(DB::table('kurikulums')->pluck('prodi_id'))
            ->merge(DB::table('mahasiswas')->pluck('prodi_id'))
            ->merge(DB::table('prodi_users')->pluck('prodi_id'))
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        return [
            'prodi' => $prodi,
            'prodis' => Prodi::orderBy('jenjang')->orderBy('nama')->get(),
            'nonDeletableProdiIds' => array_fill_keys($usedProdiIds->all(), true),
            'header' => 'Data Program Studi',
            'title' => 'Prodi',
        ];
    }
}
