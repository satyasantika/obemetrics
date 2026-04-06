<?php

namespace App\Http\Controllers\Setting;

use App\Actions\SyncProdiState;
use App\DataTables\AddJoinProdiUsersDataTable;
use App\DataTables\JoinProdiUsersDataTable;
use App\Http\Controllers\Controller;
use App\Models\JoinProdiUser;
use App\Models\KontrakMk;
use App\Models\JoinMkUser;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use App\States\Prodi\Aktif as ProdiAktif;
use App\States\Prodi\Draft as ProdiDraft;
use Illuminate\Http\Request;
use Spatie\Permission\Guard;

class JoinProdiUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join prodi users', ['only' => ['index','show']]);
        $this->middleware('permission:create join prodi users', ['only' => ['create','store']]);
        $this->middleware('permission:update join prodi users', ['only' => ['edit','update']]);
        $this->middleware('permission:delete join prodi users', ['only' => ['destroy']]);
    }

    public function index(JoinProdiUsersDataTable $dataTable, Prodi $prodi)
    {
        // rute tombol kembali
        $back_route = 'prodis.index';
        return $dataTable->with('prodi_id', $prodi->id)->render('layouts.setting', $this->_dataSelection($prodi,''),compact('back_route'));
    }

    public function create(AddJoinProdiUsersDataTable $dataTable, Prodi $prodi)
    {
        // $back_route = 'prodis.joinprodiusers.index'.$prodi;
        return $dataTable->with('prodi_id', $prodi->id)->render('layouts.setting', $this->_dataSelection($prodi,''));
    }

    public function store(Request $request, Prodi $prodi)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'prodi_id' => 'required|exists:prodis,id',
        ]);

        JoinProdiUser::create([
            'user_id' => $validated['user_id'],
            'prodi_id' => $validated['prodi_id'],
            'status_pimpinan' => false,
        ]);

        // Prodi sudah memiliki anggota — sync state
        SyncProdiState::sync($prodi);

        $namaUser = User::find($request->user_id)->name;
        $namaProdi = strtoupper($prodi->nama);
        return to_route('prodis.joinprodiusers.create',$prodi)
                ->with('success', 'User ' . $namaUser . ' pada Prodi ' . $namaProdi . ' telah ditambahkan');
    }

    public function edit(Prodi $prodi, JoinProdiUser $joinprodiuser)
    {
        return to_route('prodis.joinprodiusers.index', $prodi)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar User Prodi.');
    }

    public function update(Request $request, Prodi $prodi, JoinProdiUser $joinprodiuser)
    {
        $validated = $request->validate([
            'status_pimpinan' => 'nullable|boolean',
        ]);

        $statusPimpinan = (bool) ($validated['status_pimpinan'] ?? false);

        $namaProdi = strtoupper($joinprodiuser->prodi->nama);
        $namaUser = strtoupper($joinprodiuser->user->name);
        $joinprodiuser->fill([
            'status_pimpinan' => $statusPimpinan,
        ])->save();

        $roleName = 'pimpinan prodi';
        $user = $joinprodiuser->user;
        $role = Role::findOrCreate($roleName, Guard::getDefaultName($user));

        if ($statusPimpinan) {
            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }
        } else {
            $stillPimpinanInOtherProdi = JoinProdiUser::query()
                ->where('user_id', $user->id)
                ->where('status_pimpinan', true)
                ->where('id', '!=', $joinprodiuser->id)
                ->exists();

            if (!$stillPimpinanInOtherProdi && $user->hasRole($role)) {
                $user->removeRole($role);
            }
        }

        return to_route('prodis.joinprodiusers.index',$prodi)->with('success','User '.$namaUser.' pada Prodi '.$namaProdi.' telah diperbarui');
    }

    public function destroy(Prodi $prodi, JoinProdiUser $joinprodiuser)
    {
        $kurikulumIds = $prodi->kurikulums()->pluck('id');
        $isUsedInJoinMkUser = JoinMkUser::query()
            ->where('user_id', $joinprodiuser->user_id)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->exists();

        $isUsedInKontrak = KontrakMk::query()
            ->where('user_id', $joinprodiuser->user_id)
            ->whereIn('mk_id', $prodi->kurikulums()->with('mks:id,kurikulum_id')->get()->pluck('mks')->flatten()->pluck('id'))
            ->exists();

        if ($isUsedInJoinMkUser || $isUsedInKontrak) {
            return to_route('prodis.joinprodiusers.index',$prodi)
                ->with('error','Relasi user-prodi tidak dapat dihapus karena sudah digunakan pada data lainnya.');
        }

        $namaProdi = strtoupper($joinprodiuser->prodi->nama);
        $namaUser = strtoupper($joinprodiuser->user->name);
        $joinprodiuser->delete();

        // Jika tidak ada anggota tersisa — sync state kembali ke Draft
        SyncProdiState::sync($prodi);

        return to_route('prodis.joinprodiusers.index',$prodi)->with('warning','User '.$namaUser.' pada Prodi '.$namaProdi.' telah dihapus');
    }

    private function _dataSelection($prodi,$joinprodiuser)
    {
        return [
            'users' => User::role('dosen')->orderBy('name')->get(),
            'header' => 'Data Pengelola Program Studi '.$prodi->jenjang.' '.$prodi->nama,
            'prodi' => $prodi,
            'joinprodiuser'=> $joinprodiuser,
            'title' => 'JoinProdiUser',
        ];
    }
}
