<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Profil;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\SyncKurikulumState;
use App\Models\Kurikulum;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProfilController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read profils', ['only' => ['index','show']]);
        $this->middleware('permission:create profils', ['only' => ['create','store']]);
        $this->middleware('permission:update profils', ['only' => ['edit','update']]);
        $this->middleware('permission:delete profils', ['only' => ['destroy']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $profils = Profil::where('kurikulum_id', $kurikulum->id)->get();

        $nonDeletableProfilIds = Profil::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->where(function ($query) {
                $query->whereHas('profil_indikators')
                    ->orWhereHas('profilCpls');
            })
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $nonDeletableProfilIndikatorIds = $this->collectNonDeletableProfilIndikatorIds();

        return view('obe.profil', compact(
            'kurikulum',
            'profils',
            'nonDeletableProfilIds',
            'nonDeletableProfilIndikatorIds'
        ));
    }

    private function collectNonDeletableProfilIndikatorIds(): array
    {
        try {
            $references = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('REFERENCED_TABLE_SCHEMA', DB::getDatabaseName())
                ->where('REFERENCED_TABLE_NAME', 'profil_indikators')
                ->whereNotNull('TABLE_NAME')
                ->whereNotNull('COLUMN_NAME')
                ->get(['TABLE_NAME', 'COLUMN_NAME']);

            $usedIds = collect();
            foreach ($references as $reference) {
                $usedIds = $usedIds->merge(
                    DB::table($reference->TABLE_NAME)
                        ->whereNotNull($reference->COLUMN_NAME)
                        ->pluck($reference->COLUMN_NAME)
                        ->map(fn ($id) => (string) $id)
                        ->all()
                );
            }

            return $usedIds->unique()->values()->all();
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function create(Kurikulum $kurikulum)
    {
        return to_route('kurikulums.profils.index', $kurikulum)
            ->with('warning', 'Gunakan tombol Tambah Profil Lulusan (modal) pada halaman Profil.');
    }

    public function store(Request $request, Kurikulum $kurikulum, Profil $profil)
    {
        $name = strtoupper($request->name);
        Profil::create($request->all());
        SyncKurikulumState::sync($kurikulum);

        return to_route('kurikulums.profils.index', $kurikulum)->with('success','profil '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum, Profil $profil)
    {
        return to_route('kurikulums.profils.index', $kurikulum)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar Profil.');
    }

    public function update(Request $request, Kurikulum $kurikulum, Profil $profil)
    {
        $name = strtoupper($profil->nama);
        $data = $request->all();
        $profil->fill($data)->save();

        return to_route('kurikulums.profils.index', $kurikulum)->with('success','Profil '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Profil $profil)
    {
        $name = strtoupper($profil->nama);
        if ($profil->profil_indikators()->exists() || $profil->profilCpls()->exists()) {
            return to_route('kurikulums.profils.index', $kurikulum)
                ->with('error','Profil '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }
        $profil->delete();
        SyncKurikulumState::sync($kurikulum);
        return to_route('kurikulums.profils.index', $kurikulum)->with('warning','Profil '.$name.' telah dihapus');
    }

}
