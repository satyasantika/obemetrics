<?php

namespace App\Http\Controllers\Setting;

use App\DataTables\AddJoinProdiUsersDataTable;
use App\DataTables\JoinProdiUsersDataTable;
use App\Http\Controllers\Controller;
use App\Models\JoinProdiUser;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;

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
        // rute tombol kembali
        $back_route = 'prodis.index';
        return $dataTable->with('prodi_id', $prodi->id)->render('layouts.setting', $this->_dataSelection($prodi,''),compact('back_route'));
    }

    public function create(AddJoinProdiUsersDataTable $dataTable, Prodi $prodi)
    {
        // $back_route = 'prodis.joinprodiusers.index'.$prodi;
        return $dataTable->with('prodi_id', $prodi->id)->render('layouts.setting', $this->_dataSelection($prodi,''));
    }

    public function store(Request $request, Prodi $prodi)
    {
        JoinProdiUser::create($request->all());
        $namaUser = User::find($request->user_id)->name;
        $namaProdi = strtoupper($prodi->nama);
        return to_route('prodis.joinprodiusers.create',$prodi)
                ->with('success', 'User ' . $namaUser . ' pada Prodi ' . $namaProdi . ' telah ditambahkan');
    }

    public function edit(Prodi $prodi, JoinProdiUser $joinprodiuser)
    {
        return view('setting.joinprodiuser-form', $this->_dataSelection($prodi,$joinprodiuser));
    }

    public function update(Request $request, Prodi $prodi, JoinProdiUser $joinprodiuser)
    {
        $namaProdi = strtoupper($joinprodiuser->prodi->nama);
        $namaUser = strtoupper($joinprodiuser->user->name);
        $data = $request->all();
        $joinprodiuser->fill($data)->save();

        return to_route('prodis.joinprodiusers.index',$prodi)->with('success','User '.$namaUser.' pada Prodi '.$namaProdi.' telah diperbarui');
    }

    public function destroy(Prodi $prodi, JoinProdiUser $joinprodiuser)
    {
        $namaProdi = strtoupper($joinprodiuser->prodi->nama);
        $namaUser = strtoupper($joinprodiuser->user->name);
        $joinprodiuser->delete();
        return to_route('prodis.joinprodiusers.index',$prodi)->with('warning','User '.$namaUser.' pada Prodi '.$namaProdi.' telah dihapus');
    }

    private function _dataSelection($prodi,$joinprodiuser)
    {
        return [
            'users' => User::role('dosen')->orderBy('name')->get(),
            'header' => 'Data Pengelola Program Studi '.$prodi->jenjang.' '.$prodi->nama,
            'prodi' => $prodi,
            'joinprodiuser'=> $joinprodiuser,
        ];
    }
}
