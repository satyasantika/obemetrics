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
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>CAPAIAN PEMBELAJARAN LULUSAN</th>
                                        <th>ASPEK MATA KULIAH</th>
                                        <th class="text-end">BOBOT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($cpls as $cpl)
                                    @php
                                        $matkuls = $cpl->joinCplBks->pluck('bk.joinBkMks')->flatten()->pluck('mk');
                                    @endphp
                                    <tr style="vertical-align: text-top;">
                                        <td rowspan="{{ $matkuls->count() + 1 }}" width="40%">
                                            <strong>{{ $cpl->kode }}</strong>
                                            <br>
                                            <small>{{ $cpl->nama }}</small>
                                        </td>
                                        @forelse ($matkuls as $matkul)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary text-white">{{ $matkul->sks }} SKS</span>
                                                {{ $matkul->nama }}
                                            </td>
                                            <td class="text-end">{{ number_format($matkul->sks/$matkuls->sum('sks')*100, 2) }}%</td>
                                        </tr>
                                    </tr>
                                    @empty
                                    @endforelse

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 2+$bks->count() }}"><span class="bg-warning text-dark p-2">
                                            Belum ada data CPL untuk kurikulum ini.</span>
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
