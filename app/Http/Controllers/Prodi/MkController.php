<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Mk;
use App\Models\Kurikulum;
use App\Models\JoinMkUser;
use Illuminate\Http\Request;
use App\Models\JoinProdiUser;
use App\Http\Controllers\Controller;

class MkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read mks', ['only' => ['index','show']]);
        $this->middleware('permission:create mks', ['only' => ['create','store']]);
        $this->middleware('permission:update mks', ['only' => ['edit','update']]);
        $this->middleware('permission:delete mks', ['only' => ['destroy']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $mks = Mk::where('kurikulum_id',$kurikulum->id)->get();
        return view('obe.mk', compact('kurikulum','mks'));
    }

    public function create(Kurikulum $kurikulum)
    {
        $mk = new Mk();
        $users = JoinProdiUser::where('prodi_id', $kurikulum->prodi_id)->pluck('user_id')->toArray();
        return view('setting.mk-form', compact('kurikulum','mk','users'));
    }

    public function store(Request $request, Kurikulum $kurikulum, Mk $mk)
    {
        $name = $request->name;
        $data = $request->all();
        $data['sks'] = $request->sks_teori + $request->sks_praktik + $request->sks_lapangan;
        Mk::create($data);

        return to_route('kurikulums.mks.index', $kurikulum)->with('success','Mata Kuliah: '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum, Mk $mk)
    {
        $join_mk_users = $mk->joinMkUsers()->pluck('user_id')->toArray();
        $koordinator_user = JoinMkUser::where('koordinator',1)->pluck('user_id');
        $join_prodi_users = JoinProdiUser::where('prodi_id', $kurikulum->prodi_id)->get();
        return view('setting.mk-form', compact('kurikulum','mk','join_prodi_users','join_mk_users','koordinator_user'));
    }

    public function update(Request $request, Kurikulum $kurikulum, Mk $mk)
    {
        $name = $mk->nama;
        $data = $request->all();
        $data['sks'] = $request->sks_teori + $request->sks_praktik + $request->sks_lapangan;
        $mk->fill($data)->save();

        return to_route('kurikulums.mks.index', $kurikulum)->with('success','Mata Kuliah: '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Mk $mk)
    {
        $name = $mk->nama;
        $mk->delete();
        return to_route('kurikulums.mks.index', $kurikulum)->with('warning','Mata Kuliah: '.$name.' telah dihapus');
    }

}
