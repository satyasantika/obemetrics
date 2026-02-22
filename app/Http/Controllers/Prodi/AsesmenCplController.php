<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Kurikulum;
use App\Http\Controllers\Controller;

class AsesmenCplController extends Controller
{
    function __construct()
    {
        // $this->middleware('permission:read join cpl mks', ['only' => ['rencanaAsesmen']]);
        // $this->middleware('permission:update join cpl mks', ['only' => ['update']]);
    }

    public function rencanaAsesmen(Kurikulum $kurikulum)
    {
        return view('obe.report.rencana-asesmen')
                ->with('kurikulum', $kurikulum)
                ->with('cpls', $kurikulum->cpls)
                ->with('mks', $kurikulum->mks);
    }
    public function analisisAsesmen(Kurikulum $kurikulum)
    {
        $angkatan = $kurikulum->mks->pluck('kontrakMks')
            ->flatten()
            ->pluck('mahasiswa.angkatan')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $nilais = $kurikulum->mks
            ->flatMap(function ($mk) {
                return $mk->kontrakMks()
                    ->whereHas('mk.joinCplCpmks.joinCplBk', function ($q) {
                        $q->whereNotNull('cpl_id');
                    })
                    ->with([
                        'mk.joinCplCpmks.joinCplBk.cpl',
                        'mk.kurikulum',
                        'mahasiswa',
                    ])
                    ->get();
            })
            ->flatMap(function ($kontrakMk) {
                $nilai   = $kontrakMk->nilai_angka;
                $target  = $kontrakMk->mk->kurikulum->target_capaian_lulusan ?? null;
                $angk    = $kontrakMk->mahasiswa->angkatan ?? null;

                // Ambil semua cpl_id yang terkait dengan MK ini
                $cplIds = $kontrakMk->mk->joinCplCpmks
                    ->pluck('joinCplBk')
                    ->flatten()
                    ->pluck('cpl_id')
                    ->filter()        // buang null
                    ->unique();

                // Kembalikan satu baris per cpl_id
                return $cplIds->map(function ($cplId) use ($kontrakMk, $nilai, $target, $angk) {
                    return [
                        'nilai'         => $nilai,
                        'cpl_id'        => $cplId,
                        'mk_id'         => $kontrakMk->mk->id,
                        'angkatan'      => $angk,
                        'cpl_tercapai'  => $target !== null ? ($nilai >= $target) : null,
                    ];
                });
            })
            ->groupBy('cpl_id');
            // Hasil: Collection $nilais keyed by cpl_id -> Collection of baris-baris nilai terkait CPL itu
            // dd($nilais['a11a1a70-4a3e-49a5-a862-868398ffc2b2'] ?? null);

        $target = $kurikulum->target_capaian_lulusan;

        // Hasil: Collection keyed by cpl_id -> mk_id -> angkatan
        $statPerCplMkAngkatan = $nilais->map(function ($itemsPerCpl) use ($target) {
            // $itemsPerCpl: Collection item2 dalam satu cpl_id
            return $itemsPerCpl
                ->groupBy('mk_id')                // turunan level MK
                ->map(function ($itemsPerMk) use ($target) {
                    // $itemsPerMk: semua item (nilai) pada cpl_id tertentu untuk mk_id tertentu
                    return $itemsPerMk
                        ->groupBy('angkatan')     // turunan level angkatan
                        ->map(function ($itemsPerAngkatan) use ($target) {
                            $avg = round($itemsPerAngkatan->avg('nilai'), 2);
                            $n1  = $itemsPerAngkatan->where('nilai', '<',  $target)->count();
                            $n2  = $itemsPerAngkatan->where('nilai', '>=', $target)->count();
                            $tot = $itemsPerAngkatan->count();

                            return [
                                'rerata'   => $avg,
                                'n1'       => $n1,
                                'n2'       => $n2,
                                'total'    => $tot,
                                'p_tidaktercapai'   => $tot ? round($n1 / $tot * 100, 2) : 0,
                                'p_tercapai'  => $tot ? round($n2 / $tot * 100, 2) : 0,
                            ];
                        });
                });
        });

        // Hasil: Collection keyed by cpl_id -> mk_id
        $statPerCplMk = $nilais->map(function ($itemsPerCpl) use ($target) {
            return $itemsPerCpl
                ->groupBy('mk_id')
                ->map(function ($itemsPerMk) use ($target) {
                    $avg = round($itemsPerMk->avg('nilai'), 2);
                    $n1  = $itemsPerMk->where('nilai', '<',  $target)->count();
                    $n2  = $itemsPerMk->where('nilai', '>=', $target)->count();
                    $tot = $itemsPerMk->count();

                    return [
                        'rerata'         => $avg,
                        'n1'             => $n1,
                        'n2'             => $n2,
                        'total'          => $tot,
                        'p_tidaktercapai'=> $tot ? round($n1 / $tot * 100, 2) : 0,
                        'p_tercapai'     => $tot ? round($n2 / $tot * 100, 2) : 0,
                    ];
                });
        });

        // MK per CPL (unik) + total SKS per CPL
        $mkPerCpl = collect($kurikulum->cpls)->mapWithKeys(function ($cpl) {
            $mks = $cpl->joinCplBks
                ->pluck('bk.joinBkMks')->flatten()
                ->pluck('mk')->unique('id');

            $totalSks = $mks->sum('sks');

            return [$cpl->id => [
                'mks'       => $mks,          // Collection of MK models (unik)
                'total_sks' => (float) $totalSks,
            ]];
        });

        $bobotFraksiPerCplMk = $mkPerCpl->map(function ($bag) {
            $mks = $bag['mks'];
            $totalSks = $bag['total_sks'];

            return $mks->mapWithKeys(function ($mk) use ($totalSks) {
                $w = $totalSks > 0 ? ($mk->sks / $totalSks) : 0.0; // fraksi 0..1
                return [$mk->id => $w];
            });
        });
        // Akses: $bobotFraksiPerCplMk[cpl_id][mk_id] = w (0..1)

        $avgPerCplPerMk = $statPerCplMk
            ->map(function ($statPerMk) {
                return $statPerMk
                    ->map(fn ($statMk) => (float) ($statMk['rerata'] ?? 0));
            });
        // Akses: $avgPerCplPerMk[cpl_id][mk_id] = rerata MK
        // dd($avgPerCplPerMk['a11a1a70-4a3e-49a5-a862-868398ffc2b2']['a11adaac-7d62-4a5d-a6eb-8c2127b26f2c'] ?? null);

        $nilaiCplTertimbang = $avgPerCplPerMk->map(function ($avgPerMk, $cplId) use ($bobotFraksiPerCplMk) {
            $bobotMk = $bobotFraksiPerCplMk->get($cplId) ?? collect();

            $sum = 0.0;
            foreach ($avgPerMk as $mkId => $avg) {
                $w = (float) ($bobotMk[$mkId] ?? 0.0); // fraksi
                $sum += $w * (float) $avg;
            }
            return round($sum, 2);
        });
        // Akses: $nilaiCplTertimbang[cpl_id] = nilai CPL (0..100 tipikal)
        // dd($nilaiCplTertimbang['a11a1a70-4a3e-49a5-a862-868398ffc2b2'] ?? null);

        $ketercapaianCpl = $statPerCplMk->map(function ($statPerMk, $cplId) use ($bobotFraksiPerCplMk, $nilaiCplTertimbang, $target) {
            $bobotMk = $bobotFraksiPerCplMk->get($cplId) ?? collect();

            $pTercapai = 0.0;
            $pTidakTercapai = 0.0;
            foreach ($statPerMk as $mkId => $statMk) {
                $w = (float) ($bobotMk[$mkId] ?? 0.0);
                $pTercapai += $w * (float) ($statMk['p_tercapai'] ?? 0);
                $pTidakTercapai += $w * (float) ($statMk['p_tidaktercapai'] ?? 0);
            }

            $nilaiCpl = (float) ($nilaiCplTertimbang->get($cplId) ?? 0);

            return [
                'p_tercapai' => round($pTercapai, 2),
                'p_tidaktercapai' => round($pTidakTercapai, 2),
                'tercapai' => $nilaiCpl >= $target,
            ];
        });

        return view('obe.report.analisis-asesmen')
            ->with('kurikulum', $kurikulum)
            ->with('cpls', $kurikulum->cpls)
            ->with('mks', $kurikulum->mks)
            ->with('nilais', $nilais)
            ->with('angkatan', $angkatan)
            ->with('statPerCplMkAngkatan', $statPerCplMkAngkatan)
            ->with('statPerCplMk', $statPerCplMk)
            ->with('avgPerCplPerMk', $avgPerCplPerMk)
            ->with('ketercapaianCpl', $ketercapaianCpl)
            ->with('mkPerCpl', $mkPerCpl)
            ->with('bobotFraksiPerCplMk', $bobotFraksiPerCplMk)
            ->with('nilaiCplTertimbang', $nilaiCplTertimbang);
    }

    public function spyderwebCpl(Kurikulum $kurikulum)
    {
        $cpls = $kurikulum->cpls()
            ->with('joinCplBks.bk.joinBkMks.mk.kontrakMks')
            ->get();

        $mks = $kurikulum->mks;

        $chartPerCpl = $cpls->mapWithKeys(function ($cpl) use ($kurikulum) {
            $matkuls = $cpl->joinCplBks
                ->pluck('bk.joinBkMks')
                ->flatten()
                ->pluck('mk')
                ->filter(fn ($mk) => (string) $mk->kurikulum_id === (string) $kurikulum->id)
                ->unique('id')
                ->sortBy('nama')
                ->values();

            $labels = $matkuls->map(fn ($mk) => $mk->nama)->values();
            $data = $matkuls
                ->map(function ($mk) {
                    $rerata = $mk->kontrakMks
                        ->whereNotNull('nilai_angka')
                        ->avg('nilai_angka');

                    return $rerata !== null ? round((float) $rerata, 2) : 0;
                })
                ->values();

            return [$cpl->id => [
                'kode' => $cpl->kode,
                'nama' => $cpl->nama,
                'labels' => $labels,
                'data' => $data,
            ]];
        });

        return view('obe.report.spyderweb-cpl')
            ->with('kurikulum', $kurikulum)
            ->with('cpls', $cpls)
            ->with('mks', $mks)
            ->with('chartPerCpl', $chartPerCpl);
    }
}
