<?php

namespace App\Http\Controllers\Prodi;

use App\Actions\SyncKurikulumState;
use App\Models\Cpl;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kurikulum;

class CplController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read cpls', ['only' => ['index','show']]);
        $this->middleware('permission:create cpls', ['only' => ['create','store']]);
        $this->middleware('permission:update cpls', ['only' => ['edit','update']]);
        $this->middleware('permission:delete cpls', ['only' => ['destroy']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $cpls = Cpl::where('kurikulum_id',$kurikulum->id)->get();
        return view('obe.cpl', compact('kurikulum','cpls'));
    }

    public function create(Kurikulum $kurikulum)
    {
        return to_route('kurikulums.cpls.index', $kurikulum)
            ->with('warning', 'Gunakan tombol Tambah CPL (modal) pada halaman CPL.');
    }

    public function store(Request $request, Kurikulum $kurikulum, Cpl $cpl)
    {
        $name = $request->name;
        Cpl::create($request->all());
        SyncKurikulumState::sync($kurikulum);

        return to_route('kurikulums.cpls.index', $kurikulum)->with('success','CPL: '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum, Cpl $cpl)
    {
        return to_route('kurikulums.cpls.index', $kurikulum)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar CPL.');
    }

    public function update(Request $request, Kurikulum $kurikulum, Cpl $cpl)
    {
        $name = $cpl->nama;
        $data = $request->all();
        $cpl->fill($data)->save();

        return to_route('kurikulums.cpls.index', $kurikulum)->with('success','CPL: '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Cpl $cpl)
    {
        $name = $cpl->nama;
        if ($cpl->joinProfilCpls()->exists() || $cpl->joinCplBks()->exists()) {
            return to_route('kurikulums.cpls.index', $kurikulum)
                ->with('error','CPL: '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }
        $cpl->delete();
        SyncKurikulumState::sync($kurikulum);
        return to_route('kurikulums.cpls.index', $kurikulum)->with('warning','CPL: '.$name.' telah dihapus');
    }

}
