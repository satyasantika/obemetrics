<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Mk;
use App\Models\User;
use App\Models\Kurikulum;
use App\Models\JoinMkUser;
use Illuminate\Http\Request;
use App\Models\ProdiUser;
use App\Models\KontrakMk;
use App\Http\Controllers\Controller;

class JoinMkUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:update join mk users', ['only' => ['edit','update']]);
    }

    public function index(Mk $mk)
    {
        $kurikulum = Kurikulum::findOrFail($mk->kurikulum_id);
        $prodi_users = ProdiUser::query()
            ->where('prodi_id', $mk->kurikulum->prodi_id)
            ->with('user:id,name')
            ->get();

        $linkedMkUsers = JoinMkUser::query()
            ->where('mk_id', $mk->id)
            ->get(['user_id', 'koordinator']);

        $linkedUserMap = $linkedMkUsers
            ->pluck('user_id')
            ->unique()
            ->flip()
            ->all();

        $koordinatorUserMap = $linkedMkUsers
            ->where('koordinator', true)
            ->pluck('user_id')
            ->unique()
            ->flip()
            ->all();

        $lockedUserMap = KontrakMk::query()
            ->where('mk_id', $mk->id)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->flip()
            ->all();

        return view('obe.mk-user')
                ->with('mk', $mk)
                ->with('kurikulum', $kurikulum)
                ->with('mks', $kurikulum->mks)
                ->with('prodi_users', $prodi_users)
                ->with('linkedUserMap', $linkedUserMap)
                ->with('koordinatorUserMap', $koordinatorUserMap)
                ->with('lockedUserMap', $lockedUserMap);
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
                    $isUsed = KontrakMk::query()
                        ->where('mk_id', $mk->id)
                        ->where('user_id', $user->id)
                        ->exists();
                    if ($isUsed) {
                        return to_route('mks.users.index',$request->mk_id)
                            ->with('error', $user->name . ' tidak dapat dilepas karena data kontrak mata kuliah sudah digunakan.');
                    }
                    $joinbkmk->delete();
                    return to_route('mks.users.index',$request->mk_id)
                        ->with('warning', $user->name . ' sudah tidak menjadi pengampu mata kuliah ' . $mk->nama);
                }
            }else{
                $isUsed = KontrakMk::query()
                    ->where('mk_id', $mk->id)
                    ->where('user_id', $user->id)
                    ->exists();
                if ($isUsed) {
                    return to_route('mks.users.index',$request->mk_id)
                        ->with('error', $user->name . ' tidak dapat dilepas karena data kontrak mata kuliah sudah digunakan.');
                }
                $joinbkmk->delete();
                return to_route('mks.users.index',$request->mk_id)
                    ->with('warning', $user->name . ' sudah tidak menjadi pengampu mata kuliah ' . $mk->nama);
            }
        }


    }
}
