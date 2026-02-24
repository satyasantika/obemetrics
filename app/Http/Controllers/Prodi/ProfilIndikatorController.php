<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Profil;
use Illuminate\Http\Request;
use App\Models\ProfilIndikator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProfilIndikatorController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:create profil indikators', ['only' => ['create','store']]);
        $this->middleware('permission:update profil indikators', ['only' => ['edit','update']]);
        $this->middleware('permission:delete profil indikators', ['only' => ['destroy']]);
    }

    public function create(Profil $profil)
    {
        return to_route('kurikulums.profils.index', $profil->kurikulum)
            ->with('warning', 'Gunakan tombol Tambah Indikator (modal) pada halaman Profil.');
    }

    public function store(Request $request, Profil $profil, ProfilIndikator $profilindikator)
    {
        $name_profil = strtoupper($profil->nama);
        ProfilIndikator::create($request->all());

        return to_route('kurikulums.profils.index', $profil->kurikulum)->with('success','Indikator telah ditambahkan untuk Profil '.$name_profil);
    }

    public function edit(Profil $profil, ProfilIndikator $profilindikator)
    {
        return to_route('kurikulums.profils.index', $profil->kurikulum)
            ->with('warning', 'Gunakan tombol edit indikator (modal) pada halaman Profil.');
    }

    public function update(Request $request, Profil $profil, ProfilIndikator $profilindikator)
    {
        $name_indikator = $profilindikator->nama;
        $name_profil = strtoupper($profil->nama);
        $data = $request->all();
        $profilindikator->fill($data)->save();

        return to_route('kurikulums.profils.index', $profil->kurikulum)->with('success','Indikator: '.$name_indikator.' dari Profil '.$name_profil.' telah diperbarui');
    }

    public function destroy(Profil $profil, ProfilIndikator $profilindikator)
    {
        $name_indikator = $profilindikator->nama;
        $name_profil = strtoupper($profil->nama);

        $isUsedAsForeignKey = $this->isReferencedAsForeignKey($profilindikator->id);

        if ($isUsedAsForeignKey) {
            return to_route('kurikulums.profils.index', $profil->kurikulum)
                ->with('error', 'Indikator: '.$name_indikator.' dari Profil '.$name_profil.' tidak dapat dihapus karena sudah digunakan sebagai foreign key.');
        }

        $profilindikator->delete();
        return to_route('kurikulums.profils.index', $profil->kurikulum)->with('warning','Indikator: '.$name_indikator.' dari Profil '.$name_profil.' telah dihapus');
    }

    private function isReferencedAsForeignKey(string $profilIndikatorId): bool
    {
        try {
            $references = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('REFERENCED_TABLE_SCHEMA', DB::getDatabaseName())
                ->where('REFERENCED_TABLE_NAME', 'profil_indikators')
                ->whereNotNull('TABLE_NAME')
                ->whereNotNull('COLUMN_NAME')
                ->get(['TABLE_NAME', 'COLUMN_NAME']);

            foreach ($references as $reference) {
                $isUsed = DB::table($reference->TABLE_NAME)
                    ->where($reference->COLUMN_NAME, $profilIndikatorId)
                    ->exists();

                if ($isUsed) {
                    return true;
                }
            }

            return false;
        } catch (Throwable $exception) {
            return false;
        }
    }
}
