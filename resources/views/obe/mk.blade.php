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
                                        <th>Kode & Nama MK (SKS)</th>
                                        <th class="text-center">Dosen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($mks as $mk)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <span class="badge bg-{{ $mk->semester % 2 == 0 ? 'primary' : 'secondary' }}">semester {{ $mk->semester }}</span>
                                            <br>
                                            {{-- Edit MK --}}
                                            <a href="{{ route('kurikulums.mks.edit',[$kurikulum->id,$mk->id]) }}" class="btn btn-sm btn-white text-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                        <td style="text-align: justify">
                                            {{ $mk->kodemk }} - {{ $mk->nama }}
                                            <br>
                                            <strong>{{ $mk->sks }} SKS</strong>
                                            (T: {{ $mk->sks_teori }}, P: {{ $mk->sks_praktik }}, L: {{ $mk->sks_lapangan }})
                                        </td>
                                        <td>
                                            @php
                                                $assignedUsers = \App\Models\JoinMkUser::where('kurikulum_id',$kurikulum->id)
                                                    ->where('mk_id',$mk->id)
                                                    ->get();

                                            @endphp
                                            @forelse ($assignedUsers as $user)
                                                <span class="badge bg-{{ $user->koordinator == true ? 'primary':'secondary' }}">{{ $user->user->name }}</span>
                                            @empty
                                                <span class="badge bg-warning text-dark">Belum ada</span>
                                            @endforelse
                                            <a href="{{ route('mks.users.index',$mk->id) }}" class="btn btn-white text-success btn-sm">
                                                <i class="bi bi-plus-circle"></i> Dosen
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
