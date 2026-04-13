<?php

namespace App\Http\Controllers\Setting;

use App\Models\Prodi;
use App\Models\Mahasiswa;
use App\Models\KontrakMk;
use App\Models\Nilai;
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

        $isUsed = KontrakMk::query()->where('mahasiswa_id', $mahasiswa->id)->exists()
            || Nilai::query()->where('mahasiswa_id', $mahasiswa->id)->exists();

        if ($isUsed) {
            return to_route('mahasiswas.index')->with('error','mahasiswa '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $mahasiswa->delete();
        return to_route('mahasiswas.index')->with('danger','mahasiswa '.$name.' telah dihapus');
    }

    private function _dataSelection($mahasiswa)
    {
        $user = auth()->user();
        $managedProdiIds = collect();

        if ($user && $user->hasRole('pimpinan prodi')) {
            $managedProdiIds = $user->prodiUsers()
                ->where('status_pimpinan', true)
                ->pluck('prodi_id')
                ->filter()
                ->unique()
                ->values();
        }

        $usedMahasiswaIds = collect()
            ->merge(KontrakMk::query()->pluck('mahasiswa_id'))
            ->merge(Nilai::query()->pluck('mahasiswa_id'))
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $prodisQuery = Prodi::query();
        $mahasiswasQuery = Mahasiswa::with('prodi')->orderBy('nama');

        if ($user && $user->hasRole('pimpinan prodi')) {
            if ($managedProdiIds->isEmpty()) {
                $prodisQuery->whereRaw('1 = 0');
                $mahasiswasQuery->whereRaw('1 = 0');
            } else {
                $prodisQuery->whereIn('id', $managedProdiIds);
                $mahasiswasQuery->whereIn('prodi_id', $managedProdiIds);
            }
        }

        return [
            'prodis' => $prodisQuery->get(),
            'mahasiswa' => $mahasiswa,
            'mahasiswas' => $mahasiswasQuery->get(),
            'nonDeletableMahasiswaIds' => array_fill_keys($usedMahasiswaIds->all(), true),
            'header' => 'Data Mahasiswa',
            'title' => 'Mahasiswa',
        ];
    }
}
