<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Mk;
use App\Models\User;
use App\Models\Kurikulum;
use App\Models\JoinMkUser;
use Illuminate\Http\Request;
use App\Models\JoinProdiUser;
use App\Http\Controllers\Controller;

class JoinMkUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:update join mk users', ['only' => ['edit','update']]);
    }

    public function index(MK $mk)
    {
        $kurikulum = Kurikulum::findOrFail($mk->kurikulum_id);
        $join_prodi_users = JoinProdiUser::where('prodi_id', $mk->kurikulum->prodi_id)->get();
        return view('obe.mk-user')
                ->with('mk', $mk)
                ->with('kurikulum', $kurikulum)
                ->with('mks', $kurikulum->mks)
                ->with('join_prodi_users', $join_prodi_users);
    }

    public function update(Request $request, Mk $mk, User $user)
    {
        $joinbkmk = JoinMkUser::where('mk_id', $mk->id)
                                        ->where('user_id', $user->id)
                                        ->first();

        if ($request->has('is_linked'))
        {
            if (!$request->has('is_koordinator')) {
                if (!$joinbkmk) {
                    JoinMkUser::create([
                        'mk_id' => $mk->id,
                        'user_id' => $user->id,
                        'kurikulum_id' => $request->kurikulum_id,
                    ]);
                    return to_route('mks.users.index',$request->mk_id)
                        ->with('success', $user->name . ' menjadi dosen pengampu mata kuliah ' . $mk->nama);
                } else {
                    $joinbkmk->update([
                        'koordinator' => false,
                    ]);
                    return to_route('mks.users.index',$request->mk_id)
                        ->with('warning', $user->name . ' tetap menjadi dosen pengampu mata kuliah ' . $mk->nama);
                }
            } else {
                JoinMkUser::where('mk_id', $mk->id)->update(
                    ['koordinator' => false]
                );
                $joinbkmk->update([
                        'koordinator' => true,
                    ]);
                return to_route('mks.users.index',$request->mk_id)
                        ->with('success', $user->name . ' menjadi koordinator mata kuliah ' . $mk->nama);
            }
        } else {
            if ($request->has('is_koordinator')) {
                JoinMkUser::where('mk_id', $mk->id)->update(
                    ['koordinator' => false]
                );

                if (!$joinbkmk) {
                    JoinMkUser::create([
                        'mk_id' => $mk->id,
                        'user_id' => $user->id,
                        'kurikulum_id' => $request->kurikulum_id,
                        'koordinator' => true,
                    ]);
                return to_route('mks.users.index',$request->mk_id)
                    ->with('success', $user->name . ' menjadi koordinator mata kuliah ' . $mk->nama);
                } else{
                    $joinbkmk->delete();
                    return to_route('mks.users.index',$request->mk_id)
                        ->with('warning', $user->name . ' sudah tidak menjadi pengampu mata kuliah ' . $mk->nama);
                }
            }else{
                $joinbkmk->delete();
                return to_route('mks.users.index',$request->mk_id)
                    ->with('warning', $user->name . ' sudah tidak menjadi pengampu mata kuliah ' . $mk->nama);
            }
        }


    }
}
