@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Pemetaan Rencana Asesmen CPL</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas kurikulum --}}
                    @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])
                    <hr>
                    {{-- menu kurikulum --}}
                    @include('components.menu-kurikulum',['kurikulum' => $kurikulum])
                    <hr>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-hover align-top">
                                <thead>
                                    <tr>
                                        <th width="40%">CAPAIAN PEMBELAJARAN LULUSAN</th>
                                        <th>ASPEK MATA KULIAH</th>
                                        <th class="text-end">BOBOT</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @forelse ($cpls as $cpl)

                                    @php
                                        // Kumpulkan MK yang terkait CPL ini, pastikan unik
                                        $matkuls = $cpl->joinCplBks
                                            ->pluck('bk.joinBkMks')->flatten()
                                            ->pluck('mk')->unique('id');

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
                                                {{ $totalSks > 0 ? number_format($firstMk->sks / $totalSks * 100, 2) : '0.00' }}%
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
                                                    {{ $totalSks > 0 ? number_format($mk->sks / $totalSks * 100, 2) : '0.00' }}%
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
