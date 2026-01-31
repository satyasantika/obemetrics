@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
                            <table class="table">
                                <thead></thead>
                                <tr>
                                    <th>Pertemuan</th>
                                    <th>Sub CPMK</th>
                                    <th>Indikator</th>
                                    <th>Metode</th>
                                    <th>Evaluasi</th>
                                    <th>Bobot</th>
                                    <th>Materi</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @forelse ($pertemuans as $pertemuan)
                                    <tr>
                                        <td>
                                            <span class="h1 text-primary">
                                                {{ $pertemuan->ke }}
                                            </span>
                                            {{-- tombol Edit --}}
                                            <a href="{{ route('mks.pertemuans.edit', [$mk, $pertemuan]) }}" class="btn btn-sm btn-white text-primary"><i class="bi bi-pencil-square"></i></a>
                                            {{-- tanggal dan waktu --}}
                                            <br>
                                            @if ($pertemuan->tanggal)
                                                <i class="bi bi-calendar-event"></i>
                                                {{ \Carbon\Carbon::parse($pertemuan->tanggal)->locale('id')->translatedFormat('l, d F Y') }}
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-calendar-event"></i>
                                                    Tanggal belum diatur
                                                </span>
                                            @endif
                                            <br>
                                            @if ($pertemuan->jam_mulai && $pertemuan->jam_selesai)
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-clock"></i>
                                                    {{ \Carbon\Carbon::parse($pertemuan->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($pertemuan->jam_selesai)->format('H:i') }} WIB
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-clock"></i>
                                                    Waktu belum diatur
                                                </span>
                                            @endif
                                        </td>
                                        @php
                                            $kompetensi = [];
                                            if ($pertemuan->subcpmk && $pertemuan->subcpmk->kompetensi_c) $kompetensi[] = $pertemuan->subcpmk->kompetensi_c;
                                            if ($pertemuan->subcpmk && $pertemuan->subcpmk->kompetensi_a) $kompetensi[] = $pertemuan->subcpmk->kompetensi_a;
                                            if ($pertemuan->subcpmk && $pertemuan->subcpmk->kompetensi_p) $kompetensi[] = $pertemuan->subcpmk->kompetensi_p;
                                        @endphp
                                        <td>
                                            {{ $pertemuan->subcpmk->kode }}
                                            <br>
                                            <span class="badge bg-info text-dark mb-3">
                                                [{{ implode(', ', $kompetensi) }}]
                                            </span>
                                        </td>
                                        <td>{{ $pertemuan->subcpmk->indikator }}</td>
                                        <td></td>
                                        <td>{{ $pertemuan->subcpmk->evaluasi }}</td>
                                        <td>{{ $pertemuan->subcpmk->bobot }}</td>
                                        <td>{{ $pertemuan->materi }}</td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2"><span class="bg-warning text-dark p-2">
                                                Belum ada aktivitas perkuliahan untuk mata kuliah ini.</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('mks.pertemuans.create',$mk) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambah Pertemuan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
