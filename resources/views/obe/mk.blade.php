@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Data Mata Kuliah (MK)</strong>
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
                            <table class="table table-bordered table-striped">
                                <thead></thead>
                                    <tr>
                                        <th class="text-center">Semester</th>
                                        <th>Kode MK</th>
                                        <th>Nama MK</th>
                                        <th class="text-center">SKS</th>
                                        <th class="text-center">SKS (teori-praktik-lapangan)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($mks as $mk)
                                    <tr style="vertical-align: text-top;">
                                        <td class="text-center">{{ $mk->semester }}</td>
                                        <td>
                                            {{ $mk->kodemk }}
                                        </td>
                                        <td style="text-align: justify">
                                            {{ $mk->nama }}
                                            {{-- Edit MK --}}
                                            <a href="{{ route('kurikulums.mks.edit',[$kurikulum->id,$mk->id]) }}" class="btn btn-sm btn-white text-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{ $mk->sks }}
                                        </td>
                                        <td class="text-center">
                                            ({{ $mk->sks_teori }}-{{ $mk->sks_praktik }}-{{ $mk->sks_lapangan }})
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data Mata Kuliah untuk kurikulum ini.</span>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('kurikulums.mks.create',$kurikulum) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambah Mata Kuliah</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
