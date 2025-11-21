<?php

namespace App\Http\Controllers\Setting;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserPermissionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:update permissions|update users', ['only' => ['edit','update']]);
    }

    public function edit(User $userpermission)
    {
        $getPermissionViaRoles = $userpermission->getPermissionsViaRoles()->pluck('id')->all();
        $permissions = Permission::whereNotIn('id',$getPermissionViaRoles)->orderBy('name')->pluck('name','id');
        $userPermissions = $userpermission->permissions->pluck('id','id')->all();

        return view('setting.userpermission-form',compact('userpermission','permissions','userPermissions'));
    }

    public function update(Request $request, User $userpermission)
    {
        DB::table('model_has_permissions')->where('model_id',$userpermission->id)->delete();
        $name = strtoupper($userpermission->name);
        $userpermission->givePermissionTo($request->permission);

        return back()->with('success','permission untuk user '.$name.' telah diperbarui');
    }
}
