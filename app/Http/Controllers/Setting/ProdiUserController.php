<?php

namespace App\Http\Controllers\Setting;

use App\Models\User;
use App\Models\Prodi;
use App\Models\ProdiUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\ProdiUsersDataTable;

class ProdiUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read prodiusers', ['only' => ['index','show']]);
        $this->middleware('permission:create prodiusers', ['only' => ['create','store']]);
        $this->middleware('permission:update prodiusers', ['only' => ['edit','update']]);
        $this->middleware('permission:delete prodiusers', ['only' => ['destroy']]);
    }

    public function index(ProdiUsersDataTable $dataTable, Prodi $prodi)
    {
        $header = 'Data Pengelola Program Studi '.$prodi->nama;
        return $dataTable->with('prodi_id', $prodi->id)->render('layouts.setting', compact('header'));
    }

    public function create(Prodi $prodi)
    {
        $header = 'Data Pengelola Program Studi '.$prodi->nama;
        $prodiuser = new ProdiUser();
        return view('setting.prodiuser-form', array_merge(
            [
                'header'=> $header,
                'prodiuser'=> $prodiuser,
                'prodi'=> $prodi,
            ],
            $this->_dataSelection(),
        ));
    }

    public function store(Request $request)
    {
        $prodi = Prodi::find($request->prodi_id);
        ProdiUser::create($request->all());
        $username = User::find($request->user_id)->name;
        $prodiname = strtoupper($prodi->nama);


        return to_route('prodis.prodiusers.index',$request->prodi_id)
                ->with('success', 'User ' . $username . ' pada Prodi ' . $prodiname . ' telah ditambahkan');

    }

    public function edit(ProdiUser $prodiuser)
    {
        $prodi = Prodi::find($prodiuser->prodi_id);
        $header = 'Data Pengelola Program Studi '.$prodi->nama;
        return view('setting.prodiuser-form', array_merge(
            [
                'header'=> $header,
                'prodiuser'=> $prodiuser,
                'prodi'=> $prodi,
            ],
            $this->_dataSelection(),
        ));
    }

    public function update(Request $request, ProdiUser $prodiuser)
    {
        $name = strtoupper($prodiuser->prodi->nama);
        $data = $request->all();
        $prodiuser->fill($data)->save();

        return to_route('prodis.prodiusers.index',$prodiuser->prodi_id)->with('success','Prodi '.$name.' telah diperbarui');
    }

    public function destroy(ProdiUser $prodiuser)
    {
        $username = strtoupper($prodiuser->user->name);
        $prodiname = strtoupper($prodiuser->prodi->nama);
        $prodiuser->delete();
        return to_route('prodis.prodiusers.index',$prodiuser->prodi_id)->with('warning','User '.$username.'pada Prodi '.$prodiname.' telah dihapus');
    }

    private function _dataSelection()
    {
        return [
            'users' => User::role('dosen')->orderBy('name')->get(),
        ];
    }
}
