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
                    title="Pemetaan Rencana Asesmen CPL"
                    subtitle="Pemetaan kontribusi mata kuliah terhadap capaian CPL"
                    icon="bi bi-diagram-3-fill"
                    :backUrl="route('home')" />
                <div class="card-body">

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-hover align-top">
                                <thead>
                                    <tr>
                                        <th width="40%">CAPAIAN PEMBELAJARAN LULUSAN</th>
                                        <th>ASPEK MATA KULIAH</th>
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
                                        <td rowspan="{{ $rowspan }}">
                                            <strong>{{ $cpl->kode }}</strong><br>
                                            <small>{{ $cpl->nama }}</small>
                                        </td>

                                        @if ($matkuls->count() > 0)
                                            {{-- Cetak baris MK pertama --}}
                                            @php $firstMk = $matkuls->first(); @endphp
                                            <td>
                                                <span class="badge bg-primary">{{ $firstMk->sks }} SKS</span>
                                                {{ $firstMk->nama }}
                                            </td>
                                            <td class="text-end">
                                                {{ number_format((float) ($bobotPersenMk->get($firstMk->id) ?? 0), 2) }}%
                                            </td>
                                        @else
                                            {{-- Tidak ada MK terkait --}}
                                            <td class="text-muted fst-italic">Belum ada mata kuliah terkait</td>
                                            <td class="text-end">-</td>
                                        @endif
                                    </tr>

                                    {{-- Cetak MK pilihan kedua dst. --}}
                                    @if ($matkuls->count() > 1)
                                        @foreach ($matkuls->skip(1) as $mk)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">{{ $mk->sks }} SKS</span>
                                                    {{ $mk->nama }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) ($bobotPersenMk->get($mk->id) ?? 0), 2) }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif

                                @empty
                                    <tr>
                                        <td colspan="3">
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
