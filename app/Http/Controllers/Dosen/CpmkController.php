<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Cpmk;
use Illuminate\Http\Request;
use App\Actions\SyncMkState;
use App\Http\Controllers\Controller;

class CpmkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read cpmks', ['only' => ['index','show']]);
        $this->middleware('permission:create cpmks', ['only' => ['create','store']]);
        $this->middleware('permission:update cpmks', ['only' => ['edit','update']]);
        $this->middleware('permission:delete cpmks', ['only' => ['destroy']]);
    }

    public function index(Mk $mk)
    {
        $cpmks = Cpmk::where('mk_id',$mk->id)->get();
        return view('obe.cpmk', compact('mk','cpmks'));
    }

    public function create(Mk $mk)
    {
        return to_route('mks.cpmks.index', $mk)
            ->with('warning', 'Gunakan tombol Tambah CPMK (modal) pada halaman CPMK.');
    }

    public function store(Request $request, Mk $mk, Cpmk $cpmk)
    {
        $name = $request->nama;
        $data = $request->all();
        Cpmk::create($data);
        SyncMkState::sync($mk->fresh());

        return to_route('mks.cpmks.index', $mk)->with('success','CPMK: '.$name.' telah ditambahkan');
    }

    public function edit(Mk $mk, Cpmk $cpmk)
    {
        return to_route('mks.cpmks.index', $mk)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar CPMK.');
    }

    public function update(Request $request, Mk $mk, Cpmk $cpmk)
    {
        $name = $cpmk->nama;
        $data = $request->all();
        $cpmk->fill($data)->save();
        SyncMkState::sync($mk->fresh());

        return to_route('mks.cpmks.index', $mk)->with('success','CPMK: '.$name.' telah diperbarui');
    }

    public function destroy(Mk $mk, Cpmk $cpmk)
    {
        $name = $cpmk->nama;
        if ($cpmk->joinCplCpmks()->exists() || $cpmk->subcpmks()->exists()) {
            return to_route('mks.cpmks.index', $mk)
                ->with('error','CPMK: '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }
        $cpmk->delete();
        SyncMkState::sync($mk->fresh());
        return to_route('mks.cpmks.index', $mk)->with('warning','CPMK: '.$name.' telah dihapus');
    }
}
