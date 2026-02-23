<?php

namespace App\Http\Controllers\Setting;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RolePermissionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:update roles', ['only' => ['edit','update']]);
    }

    public function edit(Role $rolepermission)
    {
        return to_route('roles.index')->with('warning', 'Gunakan tombol SET Permission (modal) pada daftar Role.');
    }

    public function update(Request $request, Role $rolepermission)
    {
        $name = strtoupper($rolepermission->name);
        $rolepermission->syncPermissions($request->permission);

        return to_route('roles.index')->with('success','permission untuk role '.$name.' telah diperbarui');
    }

}
