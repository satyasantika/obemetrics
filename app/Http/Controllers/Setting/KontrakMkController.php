<?php

namespace App\Http\Controllers\Setting;

use App\Models\Mk;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\KontrakMk;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\DataTables\KontrakMksDataTable;

class KontrakMkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read kontrakmks', ['only' => ['index','show']]);
        $this->middleware('permission:create kontrakmks', ['only' => ['create','store']]);
        $this->middleware('permission:update kontrakmks', ['only' => ['edit','update']]);
        $this->middleware('permission:delete kontrakmks', ['only' => ['destroy']]);
    }

    public function index(KontrakMksDataTable $dataTable)
    {
        // Filter berdasarkan prodi jika user memiliki role prodi
        $user = auth()->user();
        $prodiIds = [];

        if ($user->hasRole('prodi')) {
            $prodiIds = $user->joinProdiUsers->pluck('prodi_id')->toArray();
        }

        return $dataTable->with('prodi_ids', $prodiIds)
                        ->render('layouts.setting', $this->_dataSelection(''));
    }

    public function create()
    {
        $kontrakmk = new KontrakMk();
        return view('setting.kontrakmk-form', $this->_dataSelection($kontrakmk));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'mk_id' => 'required|exists:mks,id',
            'user_id' => 'required|exists:users,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'kelas' => 'nullable|string|max:10',
        ]);

        // Validasi tambahan untuk role prodi
        $user = auth()->user();
        if ($user->hasRole('prodi')) {
            $prodiIds = $user->joinProdiUsers->pluck('prodi_id')->toArray();
            $mahasiswa = Mahasiswa::find($request->mahasiswa_id);

            if (!in_array($mahasiswa->prodi_id, $prodiIds)) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menambahkan kontrak untuk mahasiswa dari prodi ini.');
            }
        }

        KontrakMk::create($request->only([
            'mahasiswa_id',
            'mk_id',
            'user_id',
            'semester_id',
            'kelas',
        ]));

        return to_route('kontrakmks.index')->with('success','Kontrak mata kuliah berhasil ditambahkan');
    }

    public function edit(KontrakMk $kontrakmk)
    {
        return view('setting.kontrakmk-form', $this->_dataSelection($kontrakmk));
    }

    public function update(Request $request, KontrakMk $kontrakmk)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'mk_id' => 'required|exists:mks,id',
            'user_id' => 'required|exists:users,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'kelas' => 'nullable|string|max:10',
        ]);

        // Validasi tambahan untuk role prodi
        $user = auth()->user();
        if ($user->hasRole('prodi')) {
            $prodiIds = $user->joinProdiUsers->pluck('prodi_id')->toArray();
            $mahasiswa = Mahasiswa::find($request->mahasiswa_id);

            if (!in_array($mahasiswa->prodi_id, $prodiIds)) {
                return back()->with('error', 'Anda tidak memiliki akses untuk mengupdate kontrak untuk mahasiswa dari prodi ini.');
            }
        }

        $kontrakmk->update($request->only([
            'mahasiswa_id',
            'mk_id',
            'user_id',
            'semester_id',
            'kelas',
        ]));

        return to_route('kontrakmks.index')->with('success','Kontrak mata kuliah berhasil diperbarui');
    }

    public function destroy(KontrakMk $kontrakmk)
    {
        // Validasi tambahan untuk role prodi
        $user = auth()->user();
        if ($user->hasRole('prodi')) {
            $prodiIds = $user->joinProdiUsers->pluck('prodi_id')->toArray();

            if (!in_array($kontrakmk->mahasiswa->prodi_id, $prodiIds)) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menghapus kontrak untuk mahasiswa dari prodi ini.');
            }
        }

        $mahasiswa = $kontrakmk->mahasiswa->nama ?? 'Data';
        $kontrakmk->delete();
        return to_route('kontrakmks.index')->with('danger','Kontrak mata kuliah '.$mahasiswa.' telah dihapus');
    }

    private function _dataSelection($kontrakmk)
    {
        $user = auth()->user();
        $prodiIds = [];

        // Filter berdasarkan prodi jika user memiliki role prodi
        if ($user->hasRole('prodi')) {
            $prodiIds = $user->joinProdiUsers->pluck('prodi_id')->toArray();
        }

        // Query mahasiswa
        $mahasiswasQuery = Mahasiswa::with('prodi')->orderBy('nama');
        if (!empty($prodiIds)) {
            $mahasiswasQuery->whereIn('prodi_id', $prodiIds);
        }

        // Query mata kuliah
        $mksQuery = Mk::with('kurikulum.prodi')->orderBy('nama');
        if (!empty($prodiIds)) {
            $mksQuery->whereHas('kurikulum', function($q) use ($prodiIds) {
                $q->whereIn('prodi_id', $prodiIds);
            });
        }

        return [
            'prodis' => empty($prodiIds) ? Prodi::all() : Prodi::whereIn('id', $prodiIds)->get(),
            'mahasiswas' => $mahasiswasQuery->get(),
            'mks' => $mksQuery->get(),
            'dosens' => User::role('dosen')->orderBy('name')->get(),
            'semesters' => Semester::orderBy('nama')->get(),
            'kontrakmk' => $kontrakmk,
            'header' => 'Data KontrakMk',
            'title' => 'KontrakMk',
        ];
    }
}
