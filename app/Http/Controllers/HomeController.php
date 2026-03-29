<?php

namespace App\Http\Controllers;

use App\Models\Evaluasi;
use App\Models\KontrakMk;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\Permission;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        $managedProdiIds = $this->getManagedPimpinanProdiIds($user);
        $managedKurikulumIds = Kurikulum::whereIn('prodi_id', $managedProdiIds)->pluck('id');
        $taughtKurikulumIds = $user->joinMkUsers()->pluck('kurikulum_id')->filter()->unique();

        $adminStats = [
            'users' => User::count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
            'prodis' => Prodi::count(),
            'mahasiswas' => Mahasiswa::count(),
            'semesters' => Semester::count(),
            'evaluasis' => Evaluasi::count(),
            'kontrakmks' => KontrakMk::count(),
        ];

        $prodiStats = [
            'prodis' => $managedProdiIds->count(),
            'kurikulums' => $managedKurikulumIds->count(),
            'mahasiswas' => Mahasiswa::whereIn('prodi_id', $managedProdiIds)->count(),
            'kontrakmks' => KontrakMk::whereHas('mahasiswa', function ($query) use ($managedProdiIds) {
                $query->whereIn('prodi_id', $managedProdiIds);
            })->count(),
        ];

        $dosenStats = [
            'prodis' => Kurikulum::whereIn('id', $taughtKurikulumIds)->distinct('prodi_id')->count('prodi_id'),
            'kurikulums' => $taughtKurikulumIds->count(),
            'mks' => $user->joinMkUsers()->distinct('mk_id')->count('mk_id'),
            'kontrakmks' => KontrakMk::where('user_id', $user->id)->count(),
        ];

        return view('home', compact('adminStats', 'prodiStats', 'dosenStats'));
    }

    public function ruangProdi(Request $request)
    {
        $user = auth()->user();
        $prodiIds = $this->getManagedPimpinanProdiIds($user);

        $managedProdis = $user->joinProdiUsers()
            ->where('status_pimpinan', true)
            ->whereHas('user', function ($query) {
                $query->role('pimpinan prodi');
            })
            ->with('prodi.kurikulums')
            ->get()
            ->pluck('prodi')
            ->filter()
            ->unique('id')
            ->values();

        $kurikulums = Kurikulum::whereIn('prodi_id', $prodiIds)
            ->with('prodi')
            ->orderBy('nama')
            ->get();

        $selectedId = $request->query('kurikulum_id');
        if ($selectedId !== null) {
            $selectedId = (int) $selectedId;
            if ($kurikulums->contains('id', $selectedId)) {
                session(['selected_kurikulum_id' => $selectedId]);
            }
        }

        $sessionSelectedId = (int) session('selected_kurikulum_id');
        $selectedKurikulum = $kurikulums->firstWhere('id', $sessionSelectedId);

        return view('dashboard.prodi-space', compact('kurikulums', 'selectedKurikulum', 'managedProdis'));
    }

    public function ruangDosen()
    {
        $user = auth()->user();

        $joinProdiUsers = $user->joinProdiUsers()
            ->with('prodi')
            ->get();

        $joinMkUsers = $user->joinMkUsers()
            ->with(['mk', 'kurikulum.prodi'])
            ->get()
            ->filter(fn ($item) => $item->mk && $item->kurikulum)
            ->values();

        $mkByProdiKurikulum = $joinMkUsers
            ->groupBy(fn ($item) => $item->kurikulum->prodi_id)
            ->map(function ($prodiRows) {
                return $prodiRows
                    ->groupBy('kurikulum_id')
                    ->map(fn ($kurikulumRows) => $kurikulumRows->unique('mk_id')->values());
            });

        return view('dashboard.dosen-space', compact('joinProdiUsers', 'mkByProdiKurikulum'));
    }

    private function getManagedPimpinanProdiIds(User $user)
    {
        return $user->joinProdiUsers()
            ->where('status_pimpinan', true)
            ->whereHas('user', function ($query) {
                $query->role('pimpinan prodi');
            })
            ->pluck('prodi_id')
            ->filter()
            ->unique()
            ->values();
    }
}
