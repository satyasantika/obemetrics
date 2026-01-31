@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Data Sub Capaian Pembelajaran Mata Kuliah (CPMK)</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas mata kuliah --}}
                    <div class="row">
                        <div class="col-md-3">Nama Mata Kuliah</div>
                        <div class="col"><strong>{{ $mk->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Nama Kurikulum</div>
                        <div class="col"><strong>{{ $mk->kurikulum->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Program Studi</div>
                        <div class="col"><strong>{{ $mk->kurikulum->prodi->jenjang }} {{ $mk->kurikulum->prodi->nama }}</strong></div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                @forelse ($cpmks as $cpmk)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <strong class="h4">{{ $cpmk->kode }}</strong><br>
                                            {{ $cpmk->nama }}<br>
                                            @php
                                                $JoinCplCpmk = \App\Models\JoinCplCpmk::where('cpmk_id',$cpmk->id)->pluck('id');
                                                $subcpmks = \App\Models\Subcpmk::whereIn('join_cpl_cpmk_id',$JoinCplCpmk)->get();
                                            @endphp
                                            <ul>
                                                @foreach ($subcpmks as $subcpmk)
                                                    <li>
                                                        <strong class="h5">{{ $subcpmk->kode }}</strong>
                                                        {{-- Edit SubCPMK --}}
                                                        <a href="{{ route('mks.subcpmks.edit',[$mk->id,$subcpmk->id]) }}" class="btn btn-sm btn-white text-primary">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        <br>
                                                        {{ $subcpmk->nama }}
                                                        @php
                                                            $kompetensi = [];
                                                            if ($subcpmk->kompetensi_c) $kompetensi[] = $subcpmk->kompetensi_c;
                                                            if ($subcpmk->kompetensi_a) $kompetensi[] = $subcpmk->kompetensi_a;
                                                            if ($subcpmk->kompetensi_p) $kompetensi[] = $subcpmk->kompetensi_p;
                                                        @endphp
                                                        [{{ implode(', ', $kompetensi) }}]
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data Sub CPMK untuk mata kuliah ini.</span>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('mks.subcpmks.create',$mk) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambah Sub CPMK</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
