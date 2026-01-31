<?php

namespace App\Http\Controllers\Setting;

use App\Models\Semester;
use Illuminate\Http\Request;
use App\DataTables\SemestersDataTable;
use App\Http\Controllers\Controller;

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
        return $dataTable->render('layouts.setting', $this->_dataSelection(''));
    }

    public function create()
    {
        $semester = new Semester();
        return view('setting.semester-form', $this->_dataSelection($semester));
    }

    public function store(Request $request)
    {
        $name = strtoupper($request->nama);
        Semester::create($request->all());

        return to_route('semesters.index')->with('success','semester '.$name.' telah ditambahkan');
    }

    public function edit(Semester $semester)
    {
        return view('setting.semester-form', $this->_dataSelection($semester));
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
        $semester->delete();
        return to_route('semesters.index')->with('warning','Semester '.$name.' telah dihapus');
    }

    private function _dataSelection($semester)
    {
        return [
            'semester' => $semester,
            'header' => 'Data Semester',
            'title' => 'Semester',
        ];
    }
}
