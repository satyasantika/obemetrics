<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class NilaiController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read nilais', ['only' => ['index','show']]);
        $this->middleware('permission:create nilais', ['only' => ['create','store']]);
        $this->middleware('permission:update nilais', ['only' => ['edit','update']]);
        $this->middleware('permission:delete nilais', ['only' => ['destroy']]);
    }

    public function index(Mk $mk)
    {
        return view('obe.nilai', $this->buildNilaiPageData($mk));
    }

    public function create(Mk $mk)
    {
        $nilai = New Nilai();
        return view('setting.nilai-form', compact('mk', 'nilai'));
    }

    public function store(Request $request, Mk $mk)
    {
        $payload = $request->validate([
            'penugasan_id' => 'required|exists:penugasans,id',
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'semester_id' => 'required|exists:semesters,id',
            'nilai' => 'nullable|numeric|min:0|max:100',
            'komentar' => 'nullable|string',
        ]);

        $mk->nilais()->updateOrCreate(
            [
                'mk_id' => $mk->id,
                'penugasan_id' => $payload['penugasan_id'],
                'mahasiswa_id' => $payload['mahasiswa_id'],
                'semester_id' => $payload['semester_id'],
            ],
            [
                'nilai' => $payload['nilai'],
                'komentar' => $payload['komentar'] ?? null,
            ]
        );

        return to_route('mks.nilais.index', $mk->id)->with('success', 'Nilai berhasil ditambahkan.');
    }

    public function edit(Mk $mk, Nilai $nilai)
    {
        return view('setting.nilai-form', compact('mk', 'nilai'));
    }

    public function update(Request $request, Mk $mk, Nilai $nilai)
    {
        $payload = $request->validate([
            'nilai' => 'nullable|numeric|min:0|max:100',
            'komentar' => 'nullable|string',
        ]);

        $nilai->update($payload);

        return to_route('mks.nilais.index', $mk->id)->with('success', 'Nilai berhasil diperbarui.');
    }

    public function liveUpdate(Request $request, Mk $mk)
    {
        $payload = $request->validate([
            'penugasan_id' => 'required|exists:penugasans,id',
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'semester_id' => 'required|exists:semesters,id',
            'nilai' => 'nullable|numeric|min:0|max:100',
            'komentar' => 'nullable|string',
        ]);

        $hasKontrak = $mk->kontrakMks()
            ->where('mahasiswa_id', $payload['mahasiswa_id'])
            ->where('semester_id', $payload['semester_id'])
            ->exists();

        $hasPenugasan = $mk->penugasans()->where('id', $payload['penugasan_id'])->exists();

        if (!$hasKontrak || !$hasPenugasan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mahasiswa/penugasan tidak sesuai dengan mata kuliah ini.',
            ], 422);
        }

        if ($payload['nilai'] === null || $payload['nilai'] === '') {
            Nilai::where('mk_id', $mk->id)
                ->where('penugasan_id', $payload['penugasan_id'])
                ->where('mahasiswa_id', $payload['mahasiswa_id'])
                ->where('semester_id', $payload['semester_id'])
                ->delete();

            return response()->json([
                'status' => 'ok',
                'message' => 'Nilai dikosongkan.',
            ]);
        }

        $nilai = Nilai::updateOrCreate(
            [
                'mk_id' => $mk->id,
                'penugasan_id' => $payload['penugasan_id'],
                'mahasiswa_id' => $payload['mahasiswa_id'],
                'semester_id' => $payload['semester_id'],
            ],
            [
                'nilai' => $payload['nilai'],
                'komentar' => $payload['komentar'] ?? null,
            ]
        );

        $namaMahasiswa = $nilai->mahasiswa->nama ?? 'Mahasiswa';
        $namaTagihan = $nilai->penugasan->nama ?? 'Penugasan';

        return to_route('mks.nilais.index', $mk->id)->with('success', 'Nilai '.$namaTagihan.' untuk ' . $namaMahasiswa . ' berhasil diperbarui.');
    }

    private function buildNilaiPageData(Mk $mk): array
    {
        $penugasans = $mk->penugasans()->orderBy('kode')->get();

        $kontrakMks = $mk->kontrakMks()
            ->with(['mahasiswa', 'semester'])
            ->whereNotNull('mahasiswa_id')
            ->whereNotNull('semester_id')
            ->get()
            ->filter(fn ($kontrakMk) => $kontrakMk->mahasiswa !== null)
            ->sortBy(fn ($kontrakMk) => Str::lower((string) ($kontrakMk->mahasiswa->nama ?? '')))
            ->values();

        $mahasiswaIds = $kontrakMks->pluck('mahasiswa_id')->filter()->unique()->values();
        $semesterIds = $kontrakMks->pluck('semester_id')->filter()->unique()->values();
        $penugasanIds = $penugasans->pluck('id')->values();

        $nilaisByKey = $mk->nilais()
            ->whereIn('mahasiswa_id', $mahasiswaIds)
            ->whereIn('semester_id', $semesterIds)
            ->whereIn('penugasan_id', $penugasanIds)
            ->get()
            ->keyBy(fn ($nilai) => $nilai->mahasiswa_id . '_' . $nilai->penugasan_id . '_' . $nilai->semester_id)
            ->all();

        return compact('mk', 'penugasans', 'kontrakMks', 'nilaisByKey');
    }

    public function destroy(Mk $mk, Nilai $nilai)
    {
        $nilai->delete();

        return to_route('mks.nilais.index', $mk->id)->with('warning', 'Nilai telah dihapus.');
    }
}
