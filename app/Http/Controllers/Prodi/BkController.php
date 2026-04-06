<?php

namespace App\Http\Controllers\Prodi;

use App\Actions\SyncKurikulumState;
use App\Models\Bk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kurikulum;

class BkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read bks', ['only' => ['index','show']]);
        $this->middleware('permission:create bks', ['only' => ['create','store']]);
        $this->middleware('permission:update bks', ['only' => ['edit','update']]);
        $this->middleware('permission:delete bks', ['only' => ['destroy']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        $bks = Bk::where('kurikulum_id',$kurikulum->id)->get();
        return view('obe.bk', compact('kurikulum','bks'));
    }

    public function create(Kurikulum $kurikulum)
    {
        return to_route('kurikulums.bks.index', $kurikulum)
            ->with('warning', 'Gunakan tombol Tambah Bahan Kajian (modal) pada halaman BK.');
    }

    public function store(Request $request, Kurikulum $kurikulum, Bk $bk)
    {
        $name = $request->name;
        Bk::create($request->all());
        SyncKurikulumState::sync($kurikulum);

        return to_route('kurikulums.bks.index', $kurikulum)->with('success','BK: '.$name.' telah ditambahkan');
    }

    public function edit(Kurikulum $kurikulum, Bk $bk)
    {
        return to_route('kurikulums.bks.index', $kurikulum)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar BK.');
    }

    public function update(Request $request, Kurikulum $kurikulum, Bk $bk)
    {
        $name = $bk->nama;
        $data = $request->all();
        $bk->fill($data)->save();

        return to_route('kurikulums.bks.index', $kurikulum)->with('success','BK: '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Bk $bk)
    {
        $name = $bk->nama;
        if ($bk->joinCplBks()->exists() || $bk->joinCplMks()->exists()) {
            return to_route('kurikulums.bks.index', $kurikulum)
                ->with('error','BK: '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }
        $bk->delete();
        SyncKurikulumState::sync($kurikulum);
        return to_route('kurikulums.bks.index', $kurikulum)->with('warning','BK: '.$name.' telah dihapus');
    }

}
