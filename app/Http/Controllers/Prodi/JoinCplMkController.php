<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Mk;
use App\Models\Cpl;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Models\JoinCplBk;
use App\Http\Controllers\Controller;

class JoinCplMkController extends Controller
{
    function __construct()
    {
        // $this->middleware('permission:read join cpl mks', ['only' => ['index']]);
        // $this->middleware('permission:update join cpl mks', ['only' => ['update']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        return view('obe.cpl-mk')
                ->with('kurikulum', $kurikulum)
                ->with('cpls', $kurikulum->cpls)
                ->with('mks', $kurikulum->mks);
    }
}
