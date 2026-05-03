<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Evaluasi;
use App\Models\Penugasan;
use Illuminate\Http\Request;
use App\Actions\SyncMkState;
use App\Actions\ResolveMkSemester;
use App\Http\Controllers\Controller;

class PenugasanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read penugasans', ['only' => ['index', 'show']]);
        $this->middleware('permission:create penugasans', ['only' => ['create', 'store']]);
        $this->middleware('permission:update penugasans', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete penugasans', ['only' => ['destroy']]);
    }

    public function index(Mk $mk, Request $request)
    {
        $currentUserId = auth()->id();

        $semesterOptions = $mk->kontrakMks()
            ->whereNotNull('semester_id')
            ->when($currentUserId, fn ($q) => $q->where('user_id', $currentUserId))
            ->with('semester')
            ->get()
            ->pluck('semester')
            ->filter()
            ->unique('id')
            ->sortByDesc('status_aktif')
            ->sortByDesc('kode')
            ->values();

        [$selectedSemester, $selectedSemesterId] = ResolveMkSemester::resolve($mk, $request->query('semester_id'), $semesterOptions);

        $evaluasis = Evaluasi::all();

        $penugasans = $mk->penugasans()
            ->when($selectedSemesterId, fn ($q) => $q->where('semester_id', $selectedSemesterId))
            ->with([
                'evaluasi',
                'joinSubcpmkPenugasans.subcpmk.joinCplCpmk.joinCplBk.Cpl',
                'joinSubcpmkPenugasans.subcpmk.joinCplCpmk.cpmk',
            ])
            ->orderBy('kode')
            ->get();

        return view('obe.penugasan', compact(
            'mk', 'evaluasis', 'penugasans',
            'semesterOptions', 'selectedSemesterId', 'selectedSemester'
        ));
    }

    public function create(Mk $mk)
    {
        return to_route('mks.penugasans.index', $mk->id)
            ->with('warning', 'Gunakan tombol Tambah Tagihan (modal) pada halaman Rancangan Tugas.');
    }

    public function store(Request $request, Mk $mk)
    {
        $nama = $request->input('nama');
        $mk->penugasans()->create($request->all());
        SyncMkState::sync($mk->fresh());
        $semesterId = $request->input('semester_id');
        $params = $semesterId ? '?' . http_build_query(['semester_id' => $semesterId]) : '';
        return redirect(route('mks.penugasans.index', $mk->id) . $params)
            ->with('success', 'Tugas: ' . $nama . ' telah dibuat.');
    }

    public function edit(Mk $mk, Penugasan $penugasan)
    {
        return to_route('mks.penugasans.index', $mk->id)
            ->with('warning', 'Gunakan tombol edit (modal) pada daftar Tagihan.');
    }

    public function update(Request $request, Mk $mk, Penugasan $penugasan)
    {
        $penugasan->update($request->except('semester_id'));
        $nama = $request->input('nama');
        SyncMkState::sync($mk->fresh());
        $semesterId = $penugasan->semester_id ?? $request->input('semester_id');
        $params = $semesterId ? '?' . http_build_query(['semester_id' => $semesterId]) : '';
        return redirect(route('mks.penugasans.index', $mk->id) . $params)
            ->with('success', 'Tugas: ' . $nama . ' telah diperbarui.');
    }

    public function destroy(Mk $mk, Penugasan $penugasan)
    {
        $nama = $penugasan->nama;
        $semesterId = $penugasan->semester_id;
        $params = $semesterId ? '?' . http_build_query(['semester_id' => $semesterId]) : '';
        if ($penugasan->joinSubcpmkPenugasans()->exists() || $penugasan->nilais()->exists()) {
            return redirect(route('mks.penugasans.index', $mk->id) . $params)
                ->with('error', 'Tugas: ' . $nama . ' tidak dapat dihapus karena sudah digunakan pada tabel relasi/nilai.');
        }
        $penugasan->delete();
        SyncMkState::sync($mk->fresh());
        return redirect(route('mks.penugasans.index', $mk->id) . $params)
            ->with('warning', 'Tugas: ' . $nama . ' telah dihapus.');
    }
}
