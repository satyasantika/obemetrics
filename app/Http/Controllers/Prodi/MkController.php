<?php

namespace App\Http\Controllers\Prodi;

use App\Actions\SyncKurikulumState;
use App\Models\Mk;
use App\Models\Kurikulum;
use App\Models\JoinMkUser;
use App\Models\KurikulumMk;
use App\Models\KontrakMk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read mks', ['only' => ['index','show']]);
        $this->middleware('permission:create mks', ['only' => ['create','store']]);
        $this->middleware('permission:update mks', ['only' => ['edit','update']]);
        $this->middleware('permission:delete mks', ['only' => ['destroy']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $mks = $kurikulum->mks()
            ->orderBy('kurikulum_mks.semester_ke')
            ->orderBy('kurikulum_mks.kode_mk')
            ->withCount(['cplMks', 'joinMkUsers', 'kontrakMks', 'cpmks', 'penugasans'])
            ->get();

        $prodiUsers = $kurikulum->prodi->prodiUsers()->with('user')->get();

        $joinMkUsers = JoinMkUser::with('user')
            ->where('kurikulum_id', $kurikulum->id)
            ->get();

        $assignedByMk = $joinMkUsers->groupBy('mk_id');
        $linkedByMkUser = $assignedByMk->map(function ($rows) {
            return $rows->keyBy('user_id');
        });

        $lockedByMk = KontrakMk::query()
            ->whereIn('mk_id', $mks->pluck('id'))
            ->whereNotNull('user_id')
            ->get(['mk_id', 'user_id'])
            ->groupBy('mk_id')
            ->map(function ($rows) {
                return $rows->pluck('user_id')->filter()->unique()->flip();
            });

        $canDeleteByMk = [];
        foreach ($mks as $mk) {
            $canDeleteByMk[$mk->id] =
                (int) $mk->cpl_mks_count === 0 &&
                (int) $mk->join_mk_users_count === 0 &&
                (int) $mk->kontrak_mks_count === 0 &&
                (int) $mk->cpmks_count === 0 &&
                (int) $mk->penugasans_count === 0;
        }

        return view('obe.mk', compact(
            'kurikulum',
            'mks',
            'prodiUsers',
            'assignedByMk',
            'linkedByMkUser',
            'lockedByMk',
            'canDeleteByMk'
        ));
    }

    public function create(Kurikulum $kurikulum)
    {
        return to_route('kurikulums.mks.index', $kurikulum)
            ->with('warning', 'Gunakan tombol Tambah Mata Kuliah (modal) pada halaman MK.');
    }

    public function store(Request $request, Kurikulum $kurikulum, Mk $mk)
    {
        $kode = trim((string) $request->kode);
        $conflictMk = $this->findKodeConflict($kode, $kurikulum->id);
        if ($conflictMk) {
            $conflictKurikulum = $conflictMk->kurikulum;
            $conflictProdi = $conflictKurikulum?->prodi;

            return to_route('kurikulums.mks.index', $kurikulum)
                ->withInput()
                ->with('warning', 'Kode mata kuliah "' . $kode . '" sudah dipakai pada kurikulum "' . ($conflictKurikulum->nama ?? '-') . '" (' . ($conflictProdi->jenjang ?? '-') . ' ' . ($conflictProdi->nama ?? '-') . '). Gunakan kode lain.');
        }

        $name = $request->nama;
        $mk = Mk::create([
            'nama' => $request->nama,
            'sks_teori' => $request->sks_teori,
            'sks_praktik' => $request->sks_praktik,
            'sks_lapangan' => $request->sks_lapangan,
            'sks' => $request->sks_teori + $request->sks_praktik + $request->sks_lapangan,
            'deskripsi' => $request->deskripsi,
            'status' => $request->status ?? 'draft',
        ]);

        KurikulumMk::create([
            'kurikulum_id' => $kurikulum->id,
            'mk_id' => $mk->id,
            'kode_mk' => $kode,
            'semester_ke' => $request->semester,
        ]);

        SyncKurikulumState::sync($kurikulum);

        return to_route('kurikulums.mks.index', $kurikulum)->with('success','Mata Kuliah: '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum, Mk $mk)
    {
        return to_route('kurikulums.mks.index', $kurikulum)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar MK.');
    }

    public function update(Request $request, Kurikulum $kurikulum, Mk $mk)
    {
        if (!$kurikulum->mks()->whereKey($mk->id)->exists()) {
            abort(404);
        }

        $kode = trim((string) $request->kode);
        $conflictMk = $this->findKodeConflict($kode, $kurikulum->id, $mk->id);
        if ($conflictMk) {
            $conflictKurikulum = $conflictMk->kurikulum;
            $conflictProdi = $conflictKurikulum?->prodi;

            return to_route('kurikulums.mks.index', $kurikulum)
                ->withInput()
                ->with('warning', 'Kode mata kuliah "' . $kode . '" sudah dipakai pada kurikulum "' . ($conflictKurikulum->nama ?? '-') . '" (' . ($conflictProdi->jenjang ?? '-') . ' ' . ($conflictProdi->nama ?? '-') . '). Gunakan kode lain.');
        }

        $name = $mk->nama;
        KurikulumMk::where('kurikulum_id', $kurikulum->id)
            ->where('mk_id', $mk->id)
            ->update([
                'kode_mk' => $kode,
                'semester_ke' => $request->semester,
            ]);

        $mk->fill([
            'nama' => $request->nama,
            'sks_teori' => $request->sks_teori,
            'sks_praktik' => $request->sks_praktik,
            'sks_lapangan' => $request->sks_lapangan,
            'sks' => $request->sks_teori + $request->sks_praktik + $request->sks_lapangan,
            'deskripsi' => $request->deskripsi,
            'status' => $request->status ?? $mk->status,
        ])->save();

        return to_route('kurikulums.mks.index', $kurikulum)->with('success','Mata Kuliah: '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Mk $mk)
    {
        if (!$kurikulum->mks()->whereKey($mk->id)->exists()) {
            abort(404);
        }

        $name = $mk->nama;
        if (
            $mk->cplMks()->exists() ||
            $mk->joinMkUsers()->exists() ||
            $mk->kontrakMks()->exists() ||
            $mk->cpmks()->exists() ||
            $mk->penugasans()->exists()
        ) {
            return to_route('kurikulums.mks.index', $kurikulum)
                ->with('error','Mata Kuliah: '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $kurikulum->mks()->detach($mk->id);
        if (!$mk->kurikulums()->exists()) {
            $mk->delete();
        }

        SyncKurikulumState::sync($kurikulum);
        return to_route('kurikulums.mks.index', $kurikulum)->with('warning','Mata Kuliah: '.$name.' telah dihapus');
    }

    private function findKodeConflict(string $kode, string $kurikulumId, ?string $excludeMkId = null): ?KurikulumMk
    {
        if ($kode === '') {
            return null;
        }

        return KurikulumMk::query()
            ->with('kurikulum.prodi')
            ->when($excludeMkId, function ($query) use ($excludeMkId, $kurikulumId) {
                $query->where(function ($nested) use ($excludeMkId, $kurikulumId) {
                    $nested->where('mk_id', '!=', $excludeMkId)
                        ->orWhere('kurikulum_id', '!=', $kurikulumId);
                });
            })
            ->whereRaw('LOWER(TRIM(kode_mk)) = ?', [mb_strtolower($kode)])
            ->first();
    }

}
