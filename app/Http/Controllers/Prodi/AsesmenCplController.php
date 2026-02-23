<?php

namespace App\Http\Controllers\Prodi;

use App\Models\KontrakMk;
use App\Models\Kurikulum;
use App\Http\Controllers\Controller;

class AsesmenCplController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:read join cpl mks', ['only' => ['rencanaAsesmen', 'analisisAsesmen', 'spyderwebCpl', 'laporanMahasiswa']]);
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

    public function laporanMahasiswa(Kurikulum $kurikulum)
    {
        $target = (float) ($kurikulum->target_capaian_lulusan ?? 0);
        $gradePointMap = [
            'A' => 4.0,
            'A-' => 3.7,
            'B+' => 3.4,
            'B' => 3.0,
            'B-' => 2.7,
            'C+' => 2.4,
            'C' => 2.0,
            'C-' => 1.7,
            'D' => 1.0,
            'E' => 0.0,
        ];

        $normalizeHuruf = function ($huruf) {
            if ($huruf === null) {
                return null;
            }

            $grade = strtoupper(trim((string) $huruf));
            return $grade !== '' ? $grade : null;
        };

        $angkaToHuruf = function ($nilaiAngka) {
            if ($nilaiAngka === null) {
                return null;
            }

            $nilai = (float) $nilaiAngka;
            return match (true) {
                $nilai >= 85 => 'A',
                $nilai >= 80 => 'A-',
                $nilai >= 75 => 'B+',
                $nilai >= 70 => 'B',
                $nilai >= 65 => 'B-',
                $nilai >= 60 => 'C+',
                $nilai >= 55 => 'C',
                $nilai >= 50 => 'C-',
                $nilai >= 40 => 'D',
                default => 'E',
            };
        };

        $poinToHuruf = function ($poin) use ($gradePointMap) {
            $nilai = (float) ($poin ?? 0);
            $terdekat = 'E';
            $jarakMin = INF;

            foreach ($gradePointMap as $huruf => $bobot) {
                $jarak = abs($nilai - $bobot);
                if ($jarak < $jarakMin) {
                    $jarakMin = $jarak;
                    $terdekat = $huruf;
                }
            }

            return $terdekat;
        };

        $hurufToBobot = function ($huruf) use ($gradePointMap, $normalizeHuruf) {
            $grade = $normalizeHuruf($huruf);
            return $grade !== null && array_key_exists($grade, $gradePointMap)
                ? (float) $gradePointMap[$grade]
                : null;
        };

        $resolveHuruf = function ($kontrak) use ($normalizeHuruf, $angkaToHuruf) {
            $grade = $normalizeHuruf($kontrak->nilai_huruf ?? null);
            if ($grade !== null) {
                return $grade;
            }

            return $angkaToHuruf($kontrak->nilai_angka ?? null);
        };

        $cpls = $kurikulum->cpls()
            ->with('joinCplBks.bk.joinBkMks.mk')
            ->get();

        $profils = $kurikulum->profils()->get();
        $joinProfilCplsByProfil = $kurikulum->joinProfilCpls()
            ->get()
            ->groupBy('profil_id');

        $mkIds = $kurikulum->mks()->pluck('id');

        $kontrakMks = KontrakMk::query()
            ->with([
                'mahasiswa',
                'mk.joinCplCpmks.joinCplBk',
            ])
            ->whereIn('mk_id', $mkIds)
            ->whereNotNull('mahasiswa_id')
            ->get()
            ->filter(fn ($item) => $item->mahasiswa !== null)
            ->values();

        $byMahasiswa = $kontrakMks->groupBy('mahasiswa_id');

        $detailPerMahasiswa = $byMahasiswa->map(function ($kontraks, $mahasiswaId) use ($cpls, $profils, $joinProfilCplsByProfil, $target, $resolveHuruf, $hurufToBobot, $poinToHuruf, $gradePointMap) {
            $mahasiswa = $kontraks->first()->mahasiswa;

            $totalSks = (int) $kontraks->sum(fn ($kontrak) => (int) optional($kontrak->mk)->sks);

            $totalSksIpk = 0;
            $totalMutu = 0.0;
            foreach ($kontraks as $kontrak) {
                $sks = (int) optional($kontrak->mk)->sks;
                $huruf = $resolveHuruf($kontrak);
                $bobot = $hurufToBobot($huruf);

                if ($sks > 0 && $bobot !== null) {
                    $totalSksIpk += $sks;
                    $totalMutu += $sks * $bobot;
                }
            }

            $ipk = $totalSksIpk > 0 ? round($totalMutu / $totalSksIpk, 2) : 0.0;
            $nilaiHurufMahasiswa = $poinToHuruf($ipk);
            $bobotHurufMahasiswa = (float) ($gradePointMap[$nilaiHurufMahasiswa] ?? 0);

            $detailMks = $kontraks
                ->sortBy(fn ($kontrak) => optional($kontrak->mk)->nama)
                ->values()
                ->map(function ($kontrak) use ($totalSks, $resolveHuruf, $hurufToBobot) {
                    $sks = (int) optional($kontrak->mk)->sks;
                    $kontribusi = $totalSks > 0 ? round(($sks / $totalSks) * 100, 2) : 0;
                    $huruf = $resolveHuruf($kontrak);
                    $bobot = $hurufToBobot($huruf);

                    return [
                        'kode' => optional($kontrak->mk)->kode,
                        'nama' => optional($kontrak->mk)->nama,
                        'sks' => $sks,
                        'nilai' => $kontrak->nilai_angka !== null ? round((float) $kontrak->nilai_angka, 2) : null,
                        'nilai_huruf' => $huruf,
                        'bobot_huruf' => $bobot,
                        'kontribusi' => $kontribusi,
                    ];
                });

            $kontrakByMk = $kontraks
                ->whereNotNull('nilai_angka')
                ->groupBy('mk_id')
                ->map(fn ($items) => round((float) $items->avg('nilai_angka'), 2));

            $cplScores = $cpls->map(function ($cpl) use ($kontrakByMk, $target) {
                $mksCpl = $cpl->joinCplBks
                    ->pluck('bk.joinBkMks')
                    ->flatten()
                    ->pluck('mk')
                    ->filter()
                    ->unique('id')
                    ->values();

                $totalSksCpl = (float) $mksCpl->sum(fn ($mk) => (int) ($mk->sks ?? 0));

                $nilai = 0.0;
                if ($totalSksCpl > 0) {
                    foreach ($mksCpl as $mk) {
                        $mkId = $mk->id;
                        $mkSks = (float) ($mk->sks ?? 0);
                        $bobot = $mkSks > 0 ? ($mkSks / $totalSksCpl) : 0.0;
                        $nilaiMk = (float) ($kontrakByMk->get($mkId) ?? 0);
                        $nilai += $bobot * $nilaiMk;
                    }
                }

                $nilai = round($nilai, 2);

                return [
                    'id' => $cpl->id,
                    'kode' => $cpl->kode,
                    'nama' => $cpl->nama,
                    'nilai' => $nilai,
                    'tercapai' => $nilai >= $target,
                ];
            });

            $cplScoreById = $cplScores->keyBy('id');
            $profilScores = $profils->map(function ($profil) use ($joinProfilCplsByProfil, $cplScoreById) {
                $cplIds = optional($joinProfilCplsByProfil->get($profil->id))
                    ->pluck('cpl_id')
                    ->filter()
                    ->unique() ?? collect();

                $jumlahCplPendukung = $cplIds->count();
                $totalNilaiCpl = $cplIds->sum(function ($cplId) use ($cplScoreById) {
                    return (float) data_get($cplScoreById->get($cplId), 'nilai', 0);
                });

                $nilai = $jumlahCplPendukung > 0
                    ? ($totalNilaiCpl / $jumlahCplPendukung)
                    : 0;

                return [
                    'kode' => $profil->kode,
                    'nama' => $profil->nama,
                    'nilai' => round((float) ($nilai ?? 0), 2),
                ];
            });

            return [
                'mahasiswa' => [
                    'id' => $mahasiswaId,
                    'nim' => $mahasiswa->nim,
                    'nama' => $mahasiswa->nama,
                    'sks_kontrak' => $totalSks,
                    'nilai_huruf' => $nilaiHurufMahasiswa,
                    'bobot_huruf' => $bobotHurufMahasiswa,
                    'ipk' => $ipk,
                ],
                'detail_mks' => $detailMks->values(),
                'cpl_scores' => $cplScores->values(),
                'profil_scores' => $profilScores->values(),
            ];
        });

        $mahasiswas = $detailPerMahasiswa
            ->pluck('mahasiswa')
            ->sortBy('nim')
            ->values();

        return view('obe.report.laporan-mahasiswa')
            ->with('kurikulum', $kurikulum)
            ->with('mahasiswas', $mahasiswas)
            ->with('detailPerMahasiswa', $detailPerMahasiswa)
            ->with('target', $target);
    }
}
