<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Evaluasi;
use App\Models\Penugasan;
use App\Models\JoinCplCpmk;
use Illuminate\Http\Request;
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
        $pertemuans = $mk->pertemuans;
        $penugasans = $mk->penugasans;
        $joinCplCpmks = JoinCplCpmk::where('mk_id', $mk->id)->get();
        $subcpmks = $joinCplCpmks->pluck('subcpmks')->flatten()->unique('id')->values();

        return view('obe.penugasan', compact('mk', 'evaluasis', 'penugasans', 'subcpmks', 'pertemuans'));
    }

    public function create(Mk $mk)
    {
        $penugasan = New Penugasan();
        $pertemuans = $mk->pertemuans;
        $evaluasis = Evaluasi::all();
        return view('setting.penugasan-form', compact('mk', 'penugasan','pertemuans','evaluasis'));
    }

    public function store(Request $request, Mk $mk)
    {
        $nama = $request->input('nama');
        $mk->penugasans()->create($request->all());

        return to_route('mks.penugasans.index', $mk->id)->with('success', 'Tugas: ' . $nama . ' telah dibuat.');
    }

    public function edit(Mk $mk, Penugasan $penugasan)
    {
        $evaluasis = Evaluasi::all();
        $pertemuans = $mk->pertemuans;
        return view('setting.penugasan-form', compact('mk', 'penugasan','evaluasis','pertemuans'));
    }

    public function update(Request $request, Mk $mk, Penugasan $penugasan)
    {
        $penugasan->update($request->all());
        $nama = $request->input('nama');

        return to_route('mks.penugasans.index', $mk->id)->with('success', 'Tugas: ' . $nama . ' telah diperbarui.');
    }

    public function destroy(Mk $mk, Penugasan $penugasan)
    {
        $nama = $penugasan->nama;
        $penugasan->delete();

        return to_route('mks.penugasans.index', $mk->id)->with('warning', 'Tugas: ' . $nama . ' telah dihapus.');
    }
}
