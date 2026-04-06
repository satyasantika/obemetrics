@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <x-obe.header
                    title="Hasil Analisis Asesmen CPL"
                    subtitle="Ringkasan analisis asesmen berdasarkan kurikulum aktif"
                    icon="bi bi-clipboard-data-fill"
                    />
                <div class="card-body">

                    <div class="row">
                        <div class="col">
                            <div class="table-responsive rounded-3 border bg-white shadow-sm">
                            <table class="table table-hover align-top mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="3" class="align-middle">CAPAIAN PEMBELAJARAN LULUSAN</th>
                                        <th rowspan="3" class="align-middle">ASPEK MATA KULIAH</th>
                                        <th colspan="{{ 2 * $angkatan->count() }}" class="text-center">
                                            Rerata Nilai Angkatan dan Jumlah
                                        </th>
                                        <th rowspan="3" class="align-middle text-center">Rerata Nilai</th>
                                        <th rowspan="3" class="align-middle text-end">Bobot Kontribusi MK ke CPL</th>
                                        <th rowspan="3" class="align-middle text-center">Ketercapaian CPL</th>
                                    </tr>
                                    <tr>
                                        @foreach ($angkatan as $angk)
                                            <th colspan="2" class="text-center">{{ $angk }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach ($angkatan as $angk)
                                            <th class="text-center">Rerata</th>
                                            <th class="text-center">N</th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>
                                @forelse ($cpls as $cpl)
                                    @php
                                        // Kumpulan MK yang terkait CPL ini (pastikan unik per id)
                                        $matkuls   = $mksPerCpl->get($cpl->id, collect());
                                        $bobotPersenMk = $bobotPersenPerCplMk->get($cpl->id, collect());
                                        $rowspan   = max(1, $matkuls->count());
                                        $totalSks  = $matkuls->sum('sks');
                                    @endphp

                                    {{-- Jika BELUM ada MK terkait CPL --}}
                                    @if ($matkuls->isEmpty())
                                        <tr>
                                            <td width="30%" class="bg-light-subtle">
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="fs-5 fw-bold text-primary-emphasis">{{ $cpl->kode }}</span>
                                                    <span class="small text-muted">{{ $cpl->nama }}</span>
                                                </div>
                                            </td>
                                            <td class="text-muted fst-italic">Belum ada mata kuliah terkait</td>

                                            @foreach ($angkatan as $angk)
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                            @endforeach

                                            <td class="text-center">-</td>
                                            <td class="text-end">-</td>
                                            <td class="text-center">-</td>
                                        </tr>
                                    @else
                                        @php
                                            $persenCpl = 0;
                                        @endphp
                                        {{-- Jika ADA MK terkait CPL: cetak per MK satu baris --}}
                                        @foreach ($matkuls as $i => $matkul)
                                            <tr>
                                                @if ($i === 0)
                                                    {{-- Kolom CPL di-ROWSPAN sebanyak jumlah MK --}}
                                                    <td rowspan="{{ $rowspan }}" width="30%" class="bg-light-subtle">
                                                        <div class="d-flex flex-column gap-1">
                                                            <span class="fs-5 fw-bold text-primary-emphasis">{{ $cpl->kode }}</span>
                                                            <span class="small text-muted">{{ $cpl->nama }}</span>
                                                        </div>
                                                    </td>
                                                @endif
                                                {{-- Aspek Mata Kuliah --}}
                                                <td>
                                                    <div class="d-flex flex-column gap-2">
                                                        <span class="fw-semibold">{{ $matkul->nama }}</span>
                                                        <div>
                                                        <span class="small text-muted">{{ $matkul->kode }}</span>
                                                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis border border-primary-subtle align-self-start">{{ $matkul->sks }} SKS</span>
                                                    </div>
                                                    </div>
                                                </td>

                                                {{-- Kolom dinamis per angkatan: Rerata & N --}}
                                                @foreach ($angkatan as $angk)

                                                @php
                                                $stat = optional($statPerCplMkAngkatan->get($cpl->id))
                                                            ?->get($matkul->id)
                                                            ?->get($angk); // $angk adalah label angkatan di loop header
                                                @endphp

                                                <td class="text-center">
                                                    {{ isset($stat['rerata']) ? number_format($stat['rerata'], 2) : '-' }}
                                                </td>
                                                <td class="text-center">
                                                    @if (($stat['total'] ?? 0) > 0)
                                                        Total: {{ $stat['total'] }}<br>
                                                        <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis border border-danger-subtle">&lt;{{ $kurikulum->target_capaian_lulusan }}</span>: {{ $stat['n1'] ?? 0 }}<br>
                                                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">&ge;{{ $kurikulum->target_capaian_lulusan }}</span>: {{ $stat['n2'] ?? 0 }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                @endforeach

                                                @php
                                                $statMk = optional($statPerCplMk->get($cpl->id))
                                                            ?->get($matkul->id);
                                                @endphp

                                                {{-- Rerata Nilai keseluruhan MK (contoh menggunakan $nilais) --}}
                                                <td class="text-center">
                                                    @if (isset($avgPerCplPerMk[$cpl->id][$matkul->id]))
                                                        ({{ number_format($avgPerCplPerMk[$cpl->id][$matkul->id], 2) }})
                                                        dengan<br> <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">&lt;{{ $kurikulum->target_capaian_lulusan }}</span> {{ $statMk['p_tidaktercapai'] ?? 0 }}%
                                                        dan <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">&ge;{{ $kurikulum->target_capaian_lulusan }}</span> {{ $statMk['p_tercapai'] ?? 0 }}%
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                {{-- Bobot MK relatif terhadap total SKS pada CPL ini --}}
                                                <td class="text-end">

                                                    {{-- Kolom Bobot (persen) untuk tiap baris MK: --}}
                                                    @php
                                                    $bobotFraksi = optional($bobotFraksiPerCplMk->get($cpl->id))?->get($matkul->id) ?? 0;
                                                    $bobotPersen = (float) ($bobotPersenMk->get($matkul->id) ?? ($bobotFraksi * 100));
                                                    @endphp
                                                    {{ number_format($bobotPersen, 2) }}%
                                                </td>

                                                @if ($i === 0)
                                                    {{-- Di sisi ringkasan CPL: Nilai tertimbang CPL --}}
                                                    @php
                                                    $nilaiCpl = $nilaiCplTertimbang[$cpl->id] ?? null;
                                                    $statCPL = $ketercapaianCpl[$cpl->id] ?? ['tercapai' => false, 'p_tidaktercapai' => 0, 'p_tercapai' => 0];
                                                    @endphp

                                                    <td rowspan="{{ $rowspan }}" class="text-center">
                                                        @if ($nilaiCpl !== null)
                                                            {{ number_format($nilaiCpl, 2) }}%
                                                            dengan <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">&lt;{{ $kurikulum->target_capaian_lulusan }}</span> {{ $statCPL['p_tidaktercapai'] ?? 0 }}%
                                                            dan <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">&ge;{{ $kurikulum->target_capaian_lulusan }}</span> {{ $statCPL['p_tercapai'] ?? 0 }}%
                                                            <hr>
                                                            <strong>CPL</strong> <span class="badge rounded-pill {{ $statCPL['tercapai'] ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' }}">{{ $statCPL['tercapai'] ? 'tercapai' : 'tidak tercapai' }}</span>
                                                        @else
                                                            <div class="alert alert-warning mb-0 py-2 px-2 small">
                                                                menunggu selesai penilaian
                                                            </div>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                @empty
                                    @php $colCount = 5 + 2 * $angkatan->count(); @endphp
                                    <tr>
                                        <td colspan="{{ $colCount }}">
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle p-2 d-inline-block">
                                                Belum ada data CPL untuk kurikulum ini.
                                            </span>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
