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
                    title="Pemetaan Rencana Asesmen CPL"
                    subtitle="Pemetaan kontribusi mata kuliah terhadap capaian CPL"
                    icon="bi bi-diagram-3-fill"
                    />
                <div class="card-body">

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-hover align-top">
                                <thead>
                                    <tr>
                                        <th width="40%">CAPAIAN PEMBELAJARAN LULUSAN</th>
                                        <th>NAMA MATA KULIAH (KODE)</th>
                                        <th class="text-center">SKS</th>
                                        <th class="text-end">KONTRIBUSI MK</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @forelse ($cpls as $cpl)

                                    @php
                                        // Kumpulkan MK yang terkait CPL ini, pastikan unik
                                        $matkuls = $mksPerCpl->get($cpl->id, collect());
                                        $bobotPersenMk = $bobotPersenPerCplMk->get($cpl->id, collect());

                                        $totalSks = $matkuls->sum('sks');
                                        $rowspan  = max(1, $matkuls->count());
                                    @endphp

                                    {{-- Baris pertama CPL --}}
                                    <tr style="vertical-align: top;">
                                        <td rowspan="{{ $rowspan }}" class="bg-light-subtle">
                                            <div class="d-flex flex-column gap-1">
                                                <span class="fs-5 fw-bold text-primary-emphasis">{{ $cpl->kode }}</span>
                                                <span class="small text-muted">{{ $cpl->nama }}</span>
                                            </div>
                                        </td>

                                        @if ($matkuls->count() > 0)
                                            {{-- Cetak baris MK pertama --}}
                                            @php $firstMk = $matkuls->first(); @endphp
                                            <td>
                                                {{ $firstMk->nama }} ({{ $firstMk->kode }})
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis border border-primary-subtle">{{ $firstMk->sks }}</span>
                                            </td>
                                            <td class="text-end">
                                                {{ number_format((float) ($bobotPersenMk->get($firstMk->id) ?? 0), 2) }}%
                                            </td>
                                        @else
                                            {{-- Tidak ada MK terkait --}}
                                            <td class="text-muted fst-italic">Belum ada mata kuliah terkait</td>
                                            <td class="text-center">-</td>
                                            <td class="text-end">-</td>
                                        @endif
                                    </tr>

                                    {{-- Cetak MK pilihan kedua dst. --}}
                                    @if ($matkuls->count() > 1)
                                        @foreach ($matkuls->skip(1) as $mk)
                                            <tr>
                                                <td>
                                                    {{ $mk->nama }} ({{ $mk->kode }})
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis border border-primary-subtle">{{ $mk->sks }}</span>
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) ($bobotPersenMk->get($mk->id) ?? 0), 2) }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif

                                @empty
                                    <tr>
                                        <td colspan="4">
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
