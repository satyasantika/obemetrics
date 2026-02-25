<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JoinBkMkController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join bk mks', ['only' => ['index']]);
        $this->middleware('permission:update join bk mks', ['only' => ['update']]);
    }

    public function index(Kurikulum $kurikulum)
    {
        return to_route('kurikulums.joincplmks.index', $kurikulum->id)
            ->with('warning', 'Halaman Interaksi BK >< MK sudah dihapus. Gunakan halaman Bobot CPL tiap MK.');
    }

    public function update(Request $request)
    {
        $kurikulumId = (string) $request->input('kurikulum_id');
        if ($kurikulumId === '') {
            return to_route('home')->with('warning', 'Halaman Interaksi BK >< MK sudah dihapus.');
        }

        return to_route('kurikulums.joincplmks.index', $kurikulumId)
            ->with('warning', 'Perubahan BK >< MK dinonaktifkan. Gunakan halaman Bobot CPL tiap MK.');
    }
}
