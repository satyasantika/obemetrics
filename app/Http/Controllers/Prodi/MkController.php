<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Mk;
use App\Models\Kurikulum;
use App\Models\JoinMkUser;
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
        $mks = Mk::where('kurikulum_id', $kurikulum->id)
            ->withCount(['joinCplMks', 'joinMkUsers', 'kontrakMks', 'cpmks', 'penugasans'])
            ->get();

        $joinProdiUsers = $kurikulum->prodi->joinProdiUsers()->with('user')->get();

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
                (int) $mk->join_cpl_mks_count === 0 &&
                (int) $mk->join_mk_users_count === 0 &&
                (int) $mk->kontrak_mks_count === 0 &&
                (int) $mk->cpmks_count === 0 &&
                (int) $mk->penugasans_count === 0;
        }

        return view('obe.mk', compact(
            'kurikulum',
            'mks',
            'joinProdiUsers',
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
        $conflictMk = $this->findKodeConflict($kode);
        if ($conflictMk) {
            $conflictKurikulum = $conflictMk->kurikulum;
            $conflictProdi = $conflictKurikulum?->prodi;

            return to_route('kurikulums.mks.index', $kurikulum)
                ->withInput()
                ->with('warning', 'Kode mata kuliah "' . $kode . '" sudah dipakai pada kurikulum "' . ($conflictKurikulum->nama ?? '-') . '" (' . ($conflictProdi->jenjang ?? '-') . ' ' . ($conflictProdi->nama ?? '-') . '). Gunakan kode lain.');
        }

        $name = $request->nama;
        $data = $request->all();
        $data['sks'] = $request->sks_teori + $request->sks_praktik + $request->sks_lapangan;
        Mk::create($data);

        return to_route('kurikulums.mks.index', $kurikulum)->with('success','Mata Kuliah: '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum, Mk $mk)
    {
        return to_route('kurikulums.mks.index', $kurikulum)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar MK.');
    }

    public function update(Request $request, Kurikulum $kurikulum, Mk $mk)
    {
        $kode = trim((string) $request->kode);
        $conflictMk = $this->findKodeConflict($kode, $mk->id);
        if ($conflictMk) {
            $conflictKurikulum = $conflictMk->kurikulum;
            $conflictProdi = $conflictKurikulum?->prodi;

            return to_route('kurikulums.mks.index', $kurikulum)
                ->withInput()
                ->with('warning', 'Kode mata kuliah "' . $kode . '" sudah dipakai pada kurikulum "' . ($conflictKurikulum->nama ?? '-') . '" (' . ($conflictProdi->jenjang ?? '-') . ' ' . ($conflictProdi->nama ?? '-') . '). Gunakan kode lain.');
        }

        $name = $mk->nama;
        $data = $request->all();
        $data['sks'] = $request->sks_teori + $request->sks_praktik + $request->sks_lapangan;
        $mk->fill($data)->save();

        return to_route('kurikulums.mks.index', $kurikulum)->with('success','Mata Kuliah: '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Mk $mk)
    {
        $name = $mk->nama;
        if (
            $mk->joinCplMks()->exists() ||
            $mk->joinMkUsers()->exists() ||
            $mk->kontrakMks()->exists() ||
            $mk->cpmks()->exists() ||
            $mk->penugasans()->exists()
        ) {
            return to_route('kurikulums.mks.index', $kurikulum)
                ->with('error','Mata Kuliah: '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }
        $mk->delete();
        return to_route('kurikulums.mks.index', $kurikulum)->with('warning','Mata Kuliah: '.$name.' telah dihapus');
    }

    private function findKodeConflict(string $kode, ?string $excludeMkId = null): ?Mk
    {
        if ($kode === '') {
            return null;
        }

        return Mk::query()
            ->with('kurikulum.prodi')
            ->when($excludeMkId, function ($query) use ($excludeMkId) {
                $query->where('id', '!=', $excludeMkId);
            })
            ->whereRaw('LOWER(TRIM(kode)) = ?', [mb_strtolower($kode)])
            ->first();
    }

}
