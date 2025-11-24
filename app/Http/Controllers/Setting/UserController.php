<?php

namespace App\Http\Controllers\Setting;

use App\Models\User;
use Illuminate\Http\Request;
use App\DataTables\UsersDataTable;
use Spatie\Permission\Models\Role;
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
        return $dataTable->render('layouts.setting', $this->_dataSelection(''));
    }

    public function create()
    {
        $user = new User();
        return view('setting.user-form', $this->_dataSelection($user));
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
        return view('setting.user-form', $this->_dataSelection($user));
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
        return [
            'roles' =>  Role::all()->pluck('name')->sort(),
            'user' => $user,
            'header' => 'Data User',
            'title' => 'User',
        ];
    }
}
