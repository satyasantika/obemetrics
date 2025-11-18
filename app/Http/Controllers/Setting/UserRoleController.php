<?php

namespace App\Http\Controllers\Setting;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserRoleController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:update roles|update users', ['only' => ['edit','update']]);
    }

    public function edit(User $userrole)
    {
        $roles = Role::orderBy('name')->get();
        $userRoles = DB::table("model_has_roles")->where("model_has_roles.model_id",$userrole->id)
            ->pluck('model_has_roles.role_id','model_has_roles.role_id')
            ->all();

        return view('setting.userrole-form',compact('userrole','roles','userRoles'));
    }

    public function update(Request $request, User $userrole)
    {
        DB::table('model_has_roles')->where('model_id',$userrole->id)->delete();
        $name = strtoupper($userrole->name);
        $userrole->assignRole($request->roles);

        return back()->with('success','role untuk user '.$name.' telah diperbarui');
    }

}
