<?php

namespace App\Http\Controllers\Setting;

use App\Models\User;
use App\Models\Prodi;
use App\Models\JoinProdiUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\JoinProdiUsersDataTable;

class JoinProdiUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join prodi users', ['only' => ['index','show']]);
        $this->middleware('permission:create join prodi users', ['only' => ['create','store']]);
        $this->middleware('permission:update join prodi users', ['only' => ['edit','update']]);
        $this->middleware('permission:delete join prodi users', ['only' => ['destroy']]);
    }

    public function index(JoinProdiUsersDataTable $dataTable, Prodi $prodi)
    {
        $back_route = 'prodis.index';
        return $dataTable->with('prodi_id', $prodi->id)->render('layouts.setting', $this->_dataSelection($prodi,''),compact('back_route'));
    }

    public function create(Prodi $prodi)
    {
        $joinprodiuser = new JoinProdiUser();
        return view('setting.joinprodiuser-form', $this->_dataSelection($prodi,$joinprodiuser));
    }

    public function store(Request $request)
    {
        $prodi = Prodi::find($request->prodi_id);
        JoinProdiUser::create($request->all());
        $username = User::find($request->user_id)->name;
        $prodiname = strtoupper($prodi->nama);


        return to_route('prodis.joinprodiusers.index',$request->prodi_id)
                ->with('success', 'User ' . $username . ' pada Prodi ' . $prodiname . ' telah ditambahkan');

    }

    public function edit(JoinProdiUser $joinprodiuser)
    {
        $prodi = Prodi::find($joinprodiuser->prodi_id);
        return view('setting.joinprodiuser-form', $this->_dataSelection($prodi,$joinprodiuser));
    }

    public function update(Request $request, JoinProdiUser $joinprodiuser)
    {
        $name = strtoupper($joinprodiuser->prodi->nama);
        $data = $request->all();
        $joinprodiuser->fill($data)->save();

        return to_route('prodis.joinprodiusers.index',$joinprodiuser->prodi_id)->with('success','Prodi '.$name.' telah diperbarui');
    }

    public function destroy(JoinProdiUser $joinprodiuser)
    {
        $username = strtoupper($joinprodiuser->user->name);
        $prodiname = strtoupper($joinprodiuser->prodi->nama);
        $joinprodiuser->delete();
        return to_route('prodis.joinprodiusers.index',$joinprodiuser->prodi_id)->with('warning','User '.$username.' pada Prodi '.$prodiname.' telah dihapus');
    }

    private function _dataSelection($prodi,$joinprodiuser)
    {
        return [
            'users' => User::role('dosen')->orderBy('name')->get(),
            'header' => 'Data Pengelola Program Studi '.$prodi->nama,
            'prodi' => $prodi,
            'joinprodiuser'=> $joinprodiuser,
        ];
    }
}
