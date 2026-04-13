<?php

namespace App\Http\Controllers\Setting;

use App\Models\Mk;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\KontrakMk;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
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
            $prodiIds = $user->prodiUsers->pluck('prodi_id')->toArray();
        }

        $kurikulumId = request()->query('kurikulum');
        $kurikulum = $kurikulumId ? Kurikulum::find($kurikulumId) : null;

        return $dataTable->with('prodi_ids', $prodiIds)
                        ->with('kurikulum_id', $kurikulumId)
                        ->render('layouts.setting', array_merge($this->_dataSelection(''), ['kurikulum' => $kurikulum]));
    }

    public function create()
    {
        return to_route('kontrakmks.index')->with('warning', 'Gunakan tombol tambah (modal) pada halaman KontrakMK.');
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
            $prodiIds = $user->prodiUsers->pluck('prodi_id')->toArray();
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
        return to_route('kontrakmks.index')->with('warning', 'Gunakan tombol edit (modal) pada daftar KontrakMK.');
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
            $prodiIds = $user->prodiUsers->pluck('prodi_id')->toArray();
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
            $prodiIds = $user->prodiUsers->pluck('prodi_id')->toArray();

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
            $prodiIds = $user->prodiUsers->pluck('prodi_id')->toArray();
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

        $prodis = empty($prodiIds) ? Prodi::all() : Prodi::whereIn('id', $prodiIds)->get();
        $mahasiswas = $mahasiswasQuery->get();
        $mks = $mksQuery->get();
        $dosens = User::role('dosen')->orderBy('name')->get();
        $semesters = Semester::orderBy('nama')->get();

        $kontrakmksQuery = KontrakMk::with(['mahasiswa.prodi', 'mk.kurikulum.prodi', 'user', 'semester']);
        if (!empty($prodiIds)) {
            $kontrakmksQuery->whereHas('mahasiswa', function ($query) use ($prodiIds) {
                $query->whereIn('prodi_id', $prodiIds);
            });
        }

        return [
            'prodis' => $prodis,
            'mahasiswas' => $mahasiswas,
            'mks' => $mks,
            'dosens' => $dosens,
            'semesters' => $semesters,
            'kontrakmk' => $kontrakmk,
            'kontrakmks' => $kontrakmksQuery->orderByDesc('updated_at')->get(),
            'header' => 'Data KontrakMk',
            'title' => 'KontrakMk',
        ];
    }
}
