@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Data Bahan Kajian (BK)</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas kurikulum --}}
                    <div class="row">
                        <div class="col-md-3">Nama Kurikulum</div>
                        <div class="col"><strong>{{ $kurikulum->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Program Studi</div>
                        <div class="col"><strong>{{ $kurikulum->prodi->jenjang }} {{ $kurikulum->prodi->nama }}</strong></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col">
                            @include('layouts.menu-kurikulum',$kurikulum)
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('kurikulums.bks.create',$kurikulum) }}" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Tambah Bahan Kajian</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                @forelse ($bks as $bk)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <strong>{{ $bk->kode }}</strong>
                                            {{-- Edit BK --}}
                                            <a href="{{ route('kurikulums.bks.edit',[$kurikulum->id,$bk->id]) }}" class="btn btn-sm btn-white text-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                        <td style="text-align: justify">
                                            {{ $bk->nama }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data Bahan Kajian untuk kurikulum ini.</span>
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
