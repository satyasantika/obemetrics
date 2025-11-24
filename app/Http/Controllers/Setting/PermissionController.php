<?php

namespace App\Http\Controllers\Setting;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\PermissionsDataTable;

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
        return $dataTable->render('layouts.setting', $this->_dataSelection(''));
    }

    public function create()
    {
        $permission = new Permission();
        return view('setting.permission-form', $this->_dataSelection($permission));
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        Permission::create($request->all());
        return to_route('permissions.index')->with('success','permission '.$name.' telah ditambahkan');
    }

    public function edit(Permission $permission)
    {
        return view('setting.permission-form', $this->_dataSelection($permission));
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
        $permission->delete();
        return to_route('permissions.index')->with('warning','permission '.$name.' telah dihapus');
    }

    private function _dataSelection($permission)
    {
        return [
            'header' => 'Data Permission',
            'permission' => $permission,
            'title' => 'Permission',
        ];
    }
}
