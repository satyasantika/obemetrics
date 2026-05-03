<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Mk;
use App\Models\Nilai;
use App\Models\KontrakMk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Actions\SyncMkState;
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

    public function index(Request $request, Mk $mk)
    {
        return view('obe.nilai', $this->buildNilaiPageData($mk, $request));
    }

    public function create(Mk $mk)
    {
        return to_route('mks.nilais.index', $mk->id)
            ->with('warning', 'Gunakan input nilai langsung pada tabel penilaian.');
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

        SyncMkState::sync($mk->fresh());
        return to_route('mks.nilais.index', $mk->id)->with('success', 'Nilai berhasil ditambahkan.');
    }

    public function edit(Mk $mk, Nilai $nilai)
    {
        return to_route('mks.nilais.index', $mk->id)
            ->with('warning', 'Gunakan input nilai langsung pada tabel penilaian.');
    }

    public function update(Request $request, Mk $mk, Nilai $nilai)
    {
        $payload = $request->validate([
            'nilai' => 'nullable|numeric|min:0|max:100',
            'komentar' => 'nullable|string',
        ]);

        $nilai->update($payload);

        SyncMkState::sync($mk->fresh());
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

            $this->syncKontrakMkScore($mk, $payload['mahasiswa_id'], $payload['semester_id']);
            SyncMkState::sync($mk->fresh());

            $kontrakMk = KontrakMk::query()
                ->where('mk_id', $mk->id)
                ->where('mahasiswa_id', $payload['mahasiswa_id'])
                ->where('semester_id', $payload['semester_id'])
                ->first();

            $responsePayload = [
                'status' => 'ok',
                'message' => 'Nilai dikosongkan.',
                'kontrak_nilai' => [
                    'nilai_angka' => $kontrakMk?->nilai_angka !== null ? round((float) $kontrakMk->nilai_angka, 2) : null,
                    'nilai_huruf' => $kontrakMk?->nilai_huruf,
                ],
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($responsePayload);
            }

            return to_route('mks.nilais.index', $mk->id)->with('success', 'Nilai berhasil dikosongkan.');
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

        $this->syncKontrakMkScore($mk, $payload['mahasiswa_id'], $payload['semester_id']);
        SyncMkState::sync($mk->fresh());
        $nama_mahasiswa = $nilai->mahasiswa->nama ?? 'N/A';
        $tugas = $nilai->penugasan->kode. '-' . $nilai->penugasan->nama ?? 'N/A';

        $kontrakMk = KontrakMk::query()
            ->where('mk_id', $mk->id)
            ->where('mahasiswa_id', $payload['mahasiswa_id'])
            ->where('semester_id', $payload['semester_id'])
            ->first();

        $responsePayload = [
            'status' => 'ok',
            'message' => 'Nilai '.$nama_mahasiswa.' untuk '.$tugas.' berhasil diperbarui.',
            'kontrak_nilai' => [
                'nilai_angka' => $kontrakMk?->nilai_angka !== null ? round((float) $kontrakMk->nilai_angka, 2) : null,
                'nilai_huruf' => $kontrakMk?->nilai_huruf,
            ],
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($responsePayload);
        }

        return to_route('mks.nilais.index', $mk->id)->with('success', 'Nilai '.$nama_mahasiswa.' untuk '.$tugas.' berhasil diperbarui.');
    }

    private function syncKontrakMkScore(Mk $mk, string $mahasiswaId, string $semesterId): void
    {
        $kontrakMk = KontrakMk::query()
            ->where('mk_id', $mk->id)
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('semester_id', $semesterId)
            ->first();

        if (!$kontrakMk) {
            return;
        }

        $penugasans = $mk->penugasans()->select('id', 'bobot')->get();
        if ($penugasans->isEmpty()) {
            $kontrakMk->update([
                'nilai_angka' => null,
                'nilai_huruf' => null,
            ]);
            return;
        }

        $bobotByPenugasan = $penugasans->mapWithKeys(function ($item) {
            return [$item->id => (float) ($item->bobot ?? 0)];
        });

        $nilais = $mk->nilais()
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('semester_id', $semesterId)
            ->whereIn('penugasan_id', $bobotByPenugasan->keys())
            ->get(['penugasan_id', 'nilai']);

        $weightedSum = 0.0;
        $totalBobot = 0.0;
        foreach ($nilais as $item) {
            $bobot = (float) ($bobotByPenugasan[$item->penugasan_id] ?? 0);
            $score = (float) ($item->nilai ?? 0);
            $weightedSum += $score * $bobot;
            $totalBobot += $bobot;
        }

        if ($totalBobot <= 0) {
            $kontrakMk->update([
                'nilai_angka' => null,
                'nilai_huruf' => null,
            ]);
            return;
        }

        $nilaiAngka = $weightedSum / 100;
        $nilaiHuruf = $this->toNilaiHuruf($nilaiAngka);

        $kontrakMk->update([
            'nilai_angka' => round($nilaiAngka, 2),
            'nilai_huruf' => $nilaiHuruf,
        ]);
    }

    private function toNilaiHuruf(float $nilaiAngka): string
    {
        if ($nilaiAngka >= 85.0) {
            return 'A';
        }
        if ($nilaiAngka >= 77.0) {
            return 'A-';
        }
        if ($nilaiAngka >= 68.5) {
            return 'B+';
        }
        if ($nilaiAngka >= 61.0) {
            return 'B';
        }
        if ($nilaiAngka >= 53.0) {
            return 'B-';
        }
        if ($nilaiAngka >= 45.0) {
            return 'C+';
        }
        if ($nilaiAngka >= 37.0) {
            return 'C';
        }
        if ($nilaiAngka >= 29.0) {
            return 'C-';
        }
        if ($nilaiAngka >= 21.0) {
            return 'D';
        }

        return 'E';
    }

    private function buildNilaiPageData(Mk $mk, Request $request): array
    {
        $semesterOptions = $mk->kontrakMks()
            ->with('semester')
            ->whereNotNull('mahasiswa_id')
            ->whereNotNull('semester_id')
            ->get()
            ->pluck('semester')
            ->filter()
            ->unique('id')
            ->sortByDesc('status_aktif')
            ->sortByDesc('kode')
            ->values();

        $requestedSemesterId = $request->query('semester_id');
        $defaultSemesterId = $semesterOptions->firstWhere('status_aktif', true)?->id
            ?? $semesterOptions->first()?->id;
        $selectedSemesterId = ($requestedSemesterId ?: null) ?? $defaultSemesterId;

        $penugasans = $mk->penugasans()
            ->with('joinSubcpmkPenugasans.subcpmk.joinCplCpmk.joinCplBk.Cpl')
            ->when($selectedSemesterId, function ($q) use ($selectedSemesterId) {
                $q->where(function ($q2) use ($selectedSemesterId) {
                    $q2->where('semester_id', $selectedSemesterId)
                        ->orWhereNull('semester_id');
                });
            })
            ->orderBy('kode')
            ->get();

        $kontrakMks = $mk->kontrakMks()
            ->with(['mahasiswa', 'semester'])
            ->whereNotNull('mahasiswa_id')
            ->whereNotNull('semester_id')
            ->when($selectedSemesterId, function ($query) use ($selectedSemesterId) {
                $query->where('semester_id', $selectedSemesterId);
            })
            ->get()
            ->filter(fn ($kontrakMk) => $kontrakMk->mahasiswa !== null)
            ->sortBy(fn ($kontrakMk) => Str::lower((string) ($kontrakMk->mahasiswa->nama ?? '')))
            ->values();

        $kelasGroups = $kontrakMks
            ->groupBy(function ($item) {
                $kelas = trim((string) ($item->kelas ?? ''));
                return $kelas !== '' ? $kelas : 'Tanpa Kelas';
            })
            ->sortKeys();

        $kelasGroups = collect(['__SEMUA_KELAS__' => $kontrakMks])->merge($kelasGroups);
        $defaultKelas = $kelasGroups->keys()->first();

        $kelasRowsByKey = $kelasGroups
            ->map(function ($rows) {
                return $rows->values();
            })
            ->values();

        $cplLabelByPenugasanId = $penugasans->mapWithKeys(function ($penugasan) {
            $label = $penugasan->joinSubcpmkPenugasans
                ->pluck('subcpmk.joinCplCpmk.joinCplBk.Cpl.kode')
                ->flatten()
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->whenEmpty(fn () => collect(['-']))
                ->implode(', ');

            return [$penugasan->id => $label];
        });

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

        return compact(
            'mk',
            'penugasans',
            'kontrakMks',
            'nilaisByKey',
            'semesterOptions',
            'selectedSemesterId',
            'cplLabelByPenugasanId',
            'kelasGroups',
            'kelasRowsByKey',
            'defaultKelas'
        );
    }

    public function destroy(Mk $mk, Nilai $nilai)
    {
        $nilai->delete();

        return to_route('mks.nilais.index', $mk->id)->with('warning', 'Nilai telah dihapus.');
    }
}
