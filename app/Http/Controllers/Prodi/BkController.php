<?php

namespace App\Http\Controllers\Prodi;

use App\Actions\SyncKurikulumState;
use App\Models\Bk;
use App\Models\KurikulumBk;
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
        $bks = $kurikulum->bks()->orderBy('kurikulum_bks.kode_bk')->get();
        return view('obe.bk', compact('kurikulum','bks'));
    }

    public function create(Kurikulum $kurikulum)
    {
        return to_route('kurikulums.bks.index', $kurikulum)
            ->with('warning', 'Gunakan tombol Tambah Bahan Kajian (modal) pada halaman BK.');
    }

    public function store(Request $request, Kurikulum $kurikulum)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255',
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        $kode = $validated['kode'];

        // Find by kode_bk within this kurikulum
        $pivot = KurikulumBk::where('kurikulum_id', $kurikulum->id)
            ->where('kode_bk', $kode)
            ->with('bk')
            ->first();

        if ($pivot) {
            $bk = $pivot->bk;
            $bk->fill([
                'nama' => $validated['nama'],
                'deskripsi' => $validated['deskripsi'] ?? null,
            ])->save();
        } else {
            $bk = Bk::make([
                'nama' => $validated['nama'],
                'deskripsi' => $validated['deskripsi'] ?? null,
            ]);
            $bk->save();

            KurikulumBk::create([
                'kurikulum_id' => $kurikulum->id,
                'bk_id' => $bk->id,
                'kode_bk' => $kode,
            ]);
        }

        $name = $bk->nama;
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
        if (!$kurikulum->bks()->whereKey($bk->id)->exists()) {
            abort(404);
        }

        $request->validate([
            'kode' => 'required|string|max:255',
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        $name = $bk->nama;

        // Update kode_bk on the pivot
        KurikulumBk::where('kurikulum_id', $kurikulum->id)
            ->where('bk_id', $bk->id)
            ->update(['kode_bk' => $request->input('kode')]);

        // Update BK entity (nama and deskripsi only)
        $bk->fill([
            'nama' => $request->input('nama'),
            'deskripsi' => $request->input('deskripsi'),
        ])->save();

        return to_route('kurikulums.bks.index', $kurikulum)->with('success','BK: '.$name.' telah diperbarui');
    }

    public function destroy(Kurikulum $kurikulum, Bk $bk)
    {
        if (!$kurikulum->bks()->whereKey($bk->id)->exists()) {
            abort(404);
        }

        $name = $bk->nama;
        if ($bk->joinCplBks()->exists() || $bk->joinCplMks()->exists()) {
            return to_route('kurikulums.bks.index', $kurikulum)
                ->with('error','BK: '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $kurikulum->bks()->detach($bk->id);
        if (!$bk->kurikulums()->exists()) {
            $bk->delete();
        }

        SyncKurikulumState::sync($kurikulum);
        return to_route('kurikulums.bks.index', $kurikulum)->with('warning','BK: '.$name.' telah dihapus');
    }

}
