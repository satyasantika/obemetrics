<?php

namespace App\Http\Controllers\Setting;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\KontrakMk;
use App\Models\JoinMkUser;
use App\Models\ProdiUser;
use Illuminate\Http\Request;
use App\DataTables\UsersDataTable;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read users', ['only' => ['index','show']]);
        $this->middleware('permission:create users', ['only' => ['create','store']]);
        $this->middleware('permission:update users', ['only' => ['edit','update']]);
        $this->middleware('permission:delete users', ['only' => ['destroy']]);
    }

    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting', $this->_dataSelection(new User()));
    }

    public function create()
    {
        return to_route('users.index')->with('warning', 'Gunakan tombol tambah (modal) pada halaman User.');
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        $data = $request->merge([
            'password'=> bcrypt($request->password),
        ]);
        User::create($data->all())->assignRole($request->role);
        return to_route('users.index')->with('success','user '.$name.' telah ditambahkan');
    }

    public function edit(User $user)
    {
        return to_route('users.index')->with('warning', 'Gunakan tombol edit (modal) pada daftar User.');
    }

    public function update(Request $request, User $user)
    {
        $name = strtoupper($user->name);
        $data = $request->all();
        $user->fill($data)->save();

        return to_route('users.index')->with('success','user '.$name.' telah diperbarui');
    }

    public function destroy(User $user)
    {
        $name = strtoupper($user->name);
        $isUsed = $user->prodiUsers()->exists()
            || $user->joinMkUsers()->exists()
            || KontrakMk::query()->where('user_id', $user->id)->exists();

        if ($isUsed) {
            return to_route('users.index')->with('error', 'user '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $user->delete();
        return to_route('users.index')->with('danger','user '.$name.' telah dihapus');
    }

    public function activation(User $user)
    {
        $name = strtoupper($user->name);
        $user->hasRole('active-user') ? $user->removeRole('active-user') : $user->assignRole('active-user');
        return redirect()->back();
    }

    private function _dataSelection($user)
    {
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email', 'password', 'phone', 'nidn']);

        $roles = Role::all()->pluck('name')->sort();
        $roleModels = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        $userRolesMap = DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->select('model_id', 'role_id')
            ->get()
            ->groupBy('model_id')
            ->map(fn ($items) => $items->pluck('role_id')->map(fn ($id) => (string) $id)->all())
            ->toArray();

        $userPermissionsMap = DB::table('model_has_permissions')
            ->where('model_type', User::class)
            ->select('model_id', 'permission_id')
            ->get()
            ->groupBy('model_id')
            ->map(fn ($items) => $items->pluck('permission_id')->map(fn ($id) => (string) $id)->all())
            ->toArray();

        $userRoleDerivedPermissionsMap = DB::table('model_has_roles as mhr')
            ->join('role_has_permissions as rhp', 'mhr.role_id', '=', 'rhp.role_id')
            ->where('mhr.model_type', User::class)
            ->select('mhr.model_id', 'rhp.permission_id')
            ->get()
            ->groupBy('model_id')
            ->map(function ($items) {
                return $items
                    ->pluck('permission_id')
                    ->map(fn ($id) => (string) $id)
                    ->unique()
                    ->values()
                    ->all();
            })
            ->toArray();

        $usedUserIds = collect()
            ->merge(ProdiUser::query()->pluck('user_id'))
            ->merge(JoinMkUser::query()->pluck('user_id'))
            ->merge(KontrakMk::query()->pluck('user_id'))
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $nonDeletableUserIds = array_fill_keys($usedUserIds->all(), true);

        return [
            'roles' =>  $roles,
            'users' => $users,
            'roleModels' => $roleModels,
            'permissions' => $permissions,
            'userRolesMap' => $userRolesMap,
            'userPermissionsMap' => $userPermissionsMap,
            'userRoleDerivedPermissionsMap' => $userRoleDerivedPermissionsMap,
            'nonDeletableUserIds' => $nonDeletableUserIds,
            'user' => $user,
            'header' => 'Data User',
            'title' => 'User',
        ];
    }
}
