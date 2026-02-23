<?php

namespace App\Http\Controllers\Setting;

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
        return to_route('users.index')->with('warning', 'Gunakan tombol SET Role (modal) pada daftar User.');
    }

    public function update(Request $request, User $userrole)
    {
        DB::table('model_has_roles')->where('model_id',$userrole->id)->delete();
        $name = strtoupper($userrole->name);
        $userrole->assignRole($request->roles);

        return back()->with('success','role untuk user '.$name.' telah diperbarui');
    }

}
