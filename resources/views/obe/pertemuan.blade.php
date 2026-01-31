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
                            <span class="h4 float-end">Total Pertemuan: {{ $pertemuans->count() }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <table class="table">
                                <tbody>
                                    @foreach ($subcpmks as $subcpmk)
                                    <tr>
                                        @php
                                            $kompetensi = [];
                                            if ($subcpmk->kompetensi_c) $kompetensi[] = $subcpmk->kompetensi_c;
                                            if ($subcpmk->kompetensi_a) $kompetensi[] = $subcpmk->kompetensi_a;
                                            if ($subcpmk->kompetensi_p) $kompetensi[] = $subcpmk->kompetensi_p;
                                        @endphp
                                        <td>
                                            {{ $subcpmk->kode }}
                                            <br>
                                            <span class="badge bg-info text-dark mb-3">
                                                [{{ implode(', ', $kompetensi) }}]
                                            </span>
                                            <br><strong>Indikator</strong>: {{ $subcpmk->indikator }}
                                            <br><strong>Evaluasi</strong>: {{ $subcpmk->evaluasi }}
                                            <br><strong>Bobot</strong>: {{ $subcpmk->bobot }}%
                                        </td>
                                        <td>
                                            <table class="table">
                                                <tbody>
                                                    @php
                                                        $pertemuans = \App\Models\Pertemuan::where('subcpmk_id',$subcpmk->id)->get();
                                                    @endphp
                                                    @forelse ($pertemuans as $pertemuan)
                                                    <tr>
                                                        <td>
                                                            @if ($pertemuan->ke)
                                                                Pertemuan ke-{{ $pertemuan->ke.': ' }}
                                                            @endif
                                                            {{-- tombol Edit --}}
                                                            <a href="{{ route('mks.pertemuans.edit', [$mk, $pertemuan]) }}" class="btn btn-sm btn-white text-primary"><i class="bi bi-pencil-square"></i></a>
                                                            <br>
                                                            @if ($pertemuan->materi)
                                                            {{ $pertemuan->materi }}
                                                            @endif
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
                                        </td>
                                    </tr>
                                    @endforeach
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
