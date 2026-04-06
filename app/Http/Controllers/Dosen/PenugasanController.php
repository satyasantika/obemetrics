<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Evaluasi;
use App\Models\Penugasan;
use Illuminate\Http\Request;
use App\Actions\SyncMkState;
use App\Http\Controllers\Controller;

class PenugasanController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read penugasans', ['only' => ['index','show']]);
        $this->middleware('permission:create penugasans', ['only' => ['create','store']]);
        $this->middleware('permission:update penugasans', ['only' => ['edit','update']]);
        $this->middleware('permission:delete penugasans', ['only' => ['destroy']]);
    }

    public function index(Mk $mk)
    {
        $evaluasis = Evaluasi::all();
        $penugasans = $mk->penugasans->sortBy('kode');
        $subcpmks = $mk->joinCplCpmks->pluck('subcpmks')->flatten()->unique('id')->values();

        return view('obe.penugasan', compact('mk', 'evaluasis', 'penugasans', 'subcpmks'));
    }

    public function create(Mk $mk)
    {
        return to_route('mks.penugasans.index', $mk->id)
            ->with('warning', 'Gunakan tombol Tambah Tagihan (modal) pada halaman Rancangan Tugas.');
    }

    public function store(Request $request, Mk $mk)
    {
        $nama = $request->input('nama');
        $newPenugasan = $mk->penugasans()->create($request->all());
        SyncMkState::sync($mk->fresh());

        return to_route('mks.penugasans.index', $mk->id)->with('success', 'Tugas: ' . $nama . ' telah dibuat.');
    }

    public function edit(Mk $mk, Penugasan $penugasan)
    {
        return to_route('mks.penugasans.index', $mk->id)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar Tagihan.');
    }

    public function update(Request $request, Mk $mk, Penugasan $penugasan)
    {
        $penugasan->update($request->all());
        $nama = $request->input('nama');
        SyncMkState::sync($mk->fresh());

        return to_route('mks.penugasans.index', $mk->id)->with('success', 'Tugas: ' . $nama . ' telah diperbarui.');
    }

    public function destroy(Mk $mk, Penugasan $penugasan)
    {
        $nama = $penugasan->nama;
        if ($penugasan->joinSubcpmkPenugasans()->exists() || $penugasan->nilais()->exists()) {
            return to_route('mks.penugasans.index', $mk->id)
                ->with('error', 'Tugas: ' . $nama . ' tidak dapat dihapus karena sudah digunakan pada tabel relasi/nilai.');
        }
        $penugasan->delete();
        SyncMkState::sync($mk->fresh());

        return to_route('mks.penugasans.index', $mk->id)->with('warning', 'Tugas: ' . $nama . ' telah dihapus.');
    }
}
