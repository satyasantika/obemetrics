<?php

namespace App\Http\Controllers\Setting;

use App\Models\Semester;
use App\Models\Subcpmk;
use App\Models\Penugasan;
use App\Models\JoinSubcpmkPenugasan;
use App\Models\Nilai;
use Illuminate\Http\Request;
use App\DataTables\SemestersDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SemesterController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read semesters', ['only' => ['index','show']]);
        $this->middleware('permission:create semesters', ['only' => ['create','store']]);
        $this->middleware('permission:update semesters', ['only' => ['edit','update']]);
        $this->middleware('permission:delete semesters', ['only' => ['destroy']]);
    }

    public function index(SemestersDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting', $this->_dataSelection(new Semester()));
    }

    public function create()
    {
        return to_route('semesters.index')->with('warning', 'Gunakan tombol tambah (modal) pada halaman Semester.');
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->nama);
        Semester::create($request->all());

        return to_route('semesters.index')->with('success','semester '.$name.' telah ditambahkan');
    }

    public function edit(Semester $semester)
    {
        return to_route('semesters.index')->with('warning', 'Gunakan tombol edit (modal) pada daftar Semester.');
    }

    public function update(Request $request, Semester $semester)
    {
        $name = strtoupper($semester->nama);
        $data = $request->all();
        $semester->fill($data)->save();

        return to_route('semesters.index')->with('success','Semester '.$name.' telah diperbarui');
    }

    public function destroy(Semester $semester)
    {
        $name = strtoupper($semester->nama);

        $isUsed = $semester->kontrakmks()->exists()
            || Subcpmk::query()->where('semester_id', $semester->id)->exists()
            || Penugasan::query()->where('semester_id', $semester->id)->exists()
            || JoinSubcpmkPenugasan::query()->where('semester_id', $semester->id)->exists()
            || Nilai::query()->where('semester_id', $semester->id)->exists();

        if ($isUsed) {
            return to_route('semesters.index')->with('error','Semester '.$name.' tidak dapat dihapus karena sudah digunakan pada tabel relasi.');
        }

        $semester->delete();
        return to_route('semesters.index')->with('warning','Semester '.$name.' telah dihapus');
    }

    private function _dataSelection($semester)
    {
        $usedSemesterIds = collect()
            ->merge(DB::table('kontrak_mks')->pluck('semester_id'))
            ->merge(DB::table('subcpmks')->pluck('semester_id'))
            ->merge(DB::table('penugasans')->pluck('semester_id'))
            ->merge(DB::table('join_subcpmk_penugasans')->pluck('semester_id'))
            ->merge(DB::table('nilais')->pluck('semester_id'))
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        return [
            'semester' => $semester,
            'semesters' => Semester::orderByDesc('kode')->get(),
            'nonDeletableSemesterIds' => array_fill_keys($usedSemesterIds->all(), true),
            'header' => 'Data Semester',
            'title' => 'Semester',
        ];
    }
}
