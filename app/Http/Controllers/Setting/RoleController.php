<?php

namespace App\Http\Controllers\Setting;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\DataTables\RolesDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read roles', ['only' => ['index','show']]);
        $this->middleware('permission:create roles', ['only' => ['create','store']]);
        $this->middleware('permission:update roles', ['only' => ['edit','update']]);
        $this->middleware('permission:delete roles', ['only' => ['destroy']]);
    }

    public function index(RolesDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting', $this->_dataSelection(new Role()));
    }

    public function create()
    {
        return to_route('roles.index')->with('warning', 'Gunakan tombol tambah (modal) pada halaman Role.');
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        Role::create($request->all());
        return to_route('roles.index')->with('success','role '.$name.' telah ditambahkan');
    }

    public function edit(Role $role)
    {
        return to_route('roles.index')->with('warning', 'Gunakan tombol edit (modal) pada daftar Role.');
    }

    public function update(Request $request, Role $role)
    {
        $name = strtoupper($role->name);
        $data = $request->all();
        $role->fill($data)->save();

        return to_route('roles.index')->with('success','role '.$name.' telah diperbarui');
    }

    public function destroy(Role $role)
    {
        $name = strtoupper($role->name);

        $isUsed = DB::table('model_has_roles')->where('role_id', $role->id)->exists()
            || DB::table('role_has_permissions')->where('role_id', $role->id)->exists();

        if ($isUsed) {
            return to_route('roles.index')->with('error','role '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $role->delete();
        return to_route('roles.index')->with('warning','role '.$name.' telah dihapus');
    }

    private function _dataSelection($role)
    {
        $roles = Role::with('permissions:id,name')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $usedRoleIds = collect()
            ->merge(DB::table('model_has_roles')->pluck('role_id'))
            ->merge(DB::table('role_has_permissions')->pluck('role_id'))
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        return [
            'header' => 'Data Role',
            'role' => $role,
            'roles' => $roles,
            'permissions' => $permissions,
            'nonDeletableRoleIds' => array_fill_keys($usedRoleIds->all(), true),
            'title' => 'Role',
        ];
    }

}
