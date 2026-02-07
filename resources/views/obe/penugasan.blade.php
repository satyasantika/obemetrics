@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Data Aktivitas Pertemuan</strong>
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
                            <div class="float-end">
                                <span class="h4">Total Tugas: {{ $penugasans->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('mks.penugasans.create',$mk) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambah Tugas</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>SubCPMK</th>
                                        <th>Nama Tugas</th>
                                        <th>Bobot (%)</th>
                                        <th>Bentuk Evaluasi</th>
                                        <th>Pertemuan Ke-</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($penugasans as $penugasan)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $penugasan->pertemuan->subcpmk->kode }}.
                                            </span>
                                        </td>
                                        <td>
                                            {{ $penugasan->nama }}
                                            <a href="{{ route('mks.penugasans.edit',[$mk->id,$penugasan->id]) }}" class="btn btn-sm btn-white text-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                        <td>{{ $penugasan->bobot }}</td>
                                        <td>{{ $penugasan->evaluasi->nama }}</td>
                                        <td class="text-end">{{ $penugasan->pertemuan->ke }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data Tugas untuk mata kuliah ini.</td>
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
