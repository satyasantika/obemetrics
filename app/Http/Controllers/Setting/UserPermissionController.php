<?php

namespace App\Http\Controllers\Setting;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserPermissionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:update permissions|update users', ['only' => ['edit','update']]);
    }

    public function edit(User $userpermission)
    {
        return to_route('users.index')->with('warning', 'Gunakan tombol SET Permission (modal) pada daftar User.');
    }

    public function update(Request $request, User $userpermission)
    {
        $requestedPermissions = collect($request->input('permission', []))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $roleDerivedPermissionIds = $userpermission->roles()
            ->with('permissions:id')
            ->get()
            ->flatMap(fn ($role) => $role->permissions->pluck('id'))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $directPermissionsToSync = $requestedPermissions
            ->diff($roleDerivedPermissionIds)
            ->values()
            ->all();

        $name = strtoupper($userpermission->name);
        $userpermission->syncPermissions($directPermissionsToSync);

        return back()->with('success','permission untuk user '.$name.' telah diperbarui');
    }
}
