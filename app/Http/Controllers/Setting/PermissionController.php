<?php

namespace App\Http\Controllers\Setting;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\PermissionsDataTable;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read permissions', ['only' => ['index','show']]);
        $this->middleware('permission:create permissions', ['only' => ['create','store']]);
        $this->middleware('permission:update permissions', ['only' => ['edit','update']]);
        $this->middleware('permission:delete permissions', ['only' => ['destroy']]);
    }


    public function index(PermissionsDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting', $this->_dataSelection(new Permission()));
    }

    public function create()
    {
        return to_route('permissions.index')->with('warning', 'Gunakan tombol tambah (modal) pada halaman Permission.');
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        Permission::create($request->all());
        return to_route('permissions.index')->with('success','permission '.$name.' telah ditambahkan');
    }

    public function edit(Permission $permission)
    {
        return to_route('permissions.index')->with('warning', 'Gunakan tombol edit (modal) pada daftar Permission.');
    }

    public function update(Request $request, Permission $permission)
    {
        $name = strtoupper($permission->name);
        $data = $request->all();
        $permission->fill($data)->save();

        return to_route('permissions.index')->with('success','permission '.$name.' telah diperbarui');
    }

    public function destroy(Permission $permission)
    {
        $name = strtoupper($permission->name);

        $isUsed = DB::table('role_has_permissions')->where('permission_id', $permission->id)->exists()
            || DB::table('model_has_permissions')->where('permission_id', $permission->id)->exists();

        if ($isUsed) {
            return to_route('permissions.index')->with('error','permission '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $permission->delete();
        return to_route('permissions.index')->with('warning','permission '.$name.' telah dihapus');
    }

    private function _dataSelection($permission)
    {
        $usedPermissionIds = collect()
            ->merge(DB::table('role_has_permissions')->pluck('permission_id'))
            ->merge(DB::table('model_has_permissions')->pluck('permission_id'))
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        return [
            'header' => 'Data Permission',
            'permission' => $permission,
            'permissions' => Permission::orderBy('name')->get(),
            'nonDeletablePermissionIds' => array_fill_keys($usedPermissionIds->all(), true),
            'title' => 'Permission',
        ];
    }
}
