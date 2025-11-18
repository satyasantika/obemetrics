<?php

namespace App\Http\Controllers\Setting;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:update roles', ['only' => ['edit','update']]);
    }

    public function edit(Role $rolepermission)
    {
        $permissions = Permission::orderBy('name')->pluck('name','id');
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$rolepermission->id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();

        return view('setting.rolepermission-form',compact('rolepermission','permissions','rolePermissions'));
    }

    public function update(Request $request, Role $rolepermission)
    {
        $name = strtoupper($rolepermission->name);
        $rolepermission->syncPermissions($request->permission);

        return to_route('roles.index')->with('success','permission untuk role '.$name.' telah diperbarui');
    }

}
