@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Rancangan Tugas</strong>
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
                            <div class="">
                                @if ($penugasans->count()>0)
                                <a href="{{ route('mks.joinsubcpmkpenugasans.index',$mk) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-link-45deg"></i> Kelola Hubungan SubCPMK & Tugas
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr>
                    <hr>
                    <div class="row">
                        <div class="col">
                            <div class="float-end">
                                <span class="h4">Banyak Tugas: {{ $penugasans->count() }}</span>
                                <br>
                                <span class="h4 {{ $penugasans->sum('bobot')!=100 ? 'text-danger' : '' }}">Total Bobot Tugas: {{ $penugasans->sum('bobot') }} %</span>
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
                                        <th>Kode</th>
                                        <th>SubCPMK</th>
                                        <th>Nama Tugas</th>
                                        <th>Bobot (%)</th>
                                        <th>Bentuk Evaluasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($penugasans as $penugasan)
                                    <tr>
                                        <td class="text-end">{{ $penugasan->kode }}</td>
                                        <td>
                                            @forelse ($penugasan->joinSubcpmkPenugasans as $item)
                                            <span class="badge bg-white text-dark border">
                                                {{ $item->subcpmk->kode }} (<span class="text-primary">{{ $item->bobot }}%</span>)
                                            </span>
                                            @empty
                                            <span class="text-muted">- Belum ada SubCPMK yang terkait -</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            {{ $penugasan->nama }}
                                            <a href="{{ route('mks.penugasans.edit',[$mk->id,$penugasan->id]) }}" class="btn btn-sm btn-white text-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                        <td>{{ $penugasan->bobot }}</td>
                                        <td>{{ $penugasan->evaluasi->nama }}</td>
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
