@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <x-obe.menu-strip minWidth="800px">
                {{-- menu kurikulum --}}
                @include('components.menu-kurikulum',['kurikulum' => $kurikulum])
            </x-obe.menu-strip>
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card">
                <x-obe.header
                    title="Hasil Analisis Asesmen CPL"
                    subtitle="Ringkasan analisis asesmen berdasarkan kurikulum aktif"
                    icon="bi bi-clipboard-data-fill"
                    :backUrl="route('home')" />
                <div class="card-body">

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-hover align-top">
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
                                            <td width="30%">
                                                <strong>{{ $cpl->kode }}</strong><br>
                                                <small>{{ $cpl->nama }}</small>
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
                                                    <td rowspan="{{ $rowspan }}" width="30%">
                                                        <strong>{{ $cpl->kode }}</strong><br>
                                                        <small>{{ $cpl->nama }}</small>
                                                    </td>
                                                @endif
                                                {{-- Aspek Mata Kuliah --}}
                                                <td>
                                                    <span class="badge bg-primary text-white">{{ $matkul->sks }} SKS</span>
                                                    {{ $matkul->nama }}
                                                    {{-- <span class="badge bg-secondary text-white">Bobot {{ $matkul->joinCplMk->joinCplBk->cpl->kode ?? 0 }}: {{ $matkul->joinCplMk->bobot ?? 0 }}%</span> --}}
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
                                                    Total: {{ $stat['total'] ?? 0 }}<br>
                                                    <span class="badge bg-warning text-white">&lt;{{ $kurikulum->target_capaian_lulusan }} </span>: {{ $stat['n1'] ?? 0 }}<br>
                                                    <span class="badge bg-success text-white">&ge;{{ $kurikulum->target_capaian_lulusan }} </span>: {{ $stat['n2'] ?? 0 }}
                                                </td>

                                                @endforeach

                                                @php
                                                $statMk = optional($statPerCplMk->get($cpl->id))
                                                            ?->get($matkul->id);
                                                @endphp

                                                {{-- Rerata Nilai keseluruhan MK (contoh menggunakan $nilais) --}}
                                                <td class="text-center">
                                                    ({{ isset($avgPerCplPerMk[$cpl->id][$matkul->id]) ? number_format($avgPerCplPerMk[$cpl->id][$matkul->id], 2) : '-' }})
                                                    dengan &lt;{{ $kurikulum->target_capaian_lulusan }} {{ $statMk['p_tidaktercapai'] ?? 0 }}%
                                                    dan &ge;{{ $kurikulum->target_capaian_lulusan }} {{ $statMk['p_tercapai'] ?? 0 }}%
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
                                                        {{ $nilaiCpl !== null ? number_format($nilaiCpl, 2) : '0.00' }}%
                                                        dengan &lt;{{ $kurikulum->target_capaian_lulusan }} {{ $statCPL['p_tidaktercapai'] ?? 0 }}%
                                                        dan &ge;{{ $kurikulum->target_capaian_lulusan }} {{ $statCPL['p_tercapai'] ?? 0 }}%
                                                        <hr>
                                                        <strong>CPL</strong> <span class="badge bg-{{ $statCPL['tercapai'] ? 'success' : 'danger' }}">{{ $statCPL['tercapai'] ? 'tercapai' : 'tidak tercapai' }}</span>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                @empty
                                    @php $colCount = 5 + 2 * $angkatan->count(); @endphp
                                    <tr>
                                        <td colspan="{{ $colCount }}">
                                            <span class="bg-warning text-dark p-2 d-inline-block">
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


@endsection
