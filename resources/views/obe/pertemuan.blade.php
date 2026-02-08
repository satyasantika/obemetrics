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
                            <a href="{{ route('mks.joinpertemuanmetodes.index',[$mk->id]) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-link-45deg"></i> Hubungkan Metode pada Pertemuan
                            </a>
                            <a href="{{ route('mks.penugasans.index',[$mk->id]) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-list-task"></i> Kelola Tugas pada Pertemuan
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col">
                            <div class="float-end">
                                <span class="h4">
                                    Total Pertemuan: {{ $pertemuans->count() }}
                                </span>
                                <br>
                                <span class="h4 {{ $subcpmks->sum('bobot') != 100 ? 'text-danger' : '' }}">
                                    Total Bobot Tugas: {{ $subcpmks->sum('bobot') }} %
                                </span>
                            </div>
                        </div>
                    </div>
                    <hr>
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
                                            <span class="h4">
                                                {{ $subcpmk->kode }}
                                            </span>
                                            <br>
                                            <span class="badge bg-info text-dark mb-3">
                                                [{{ implode(', ', $kompetensi) }}]
                                            </span>
                                            <br><strong>Indikator</strong>: {{ $subcpmk->indikator }}
                                            <br><strong>Evaluasi</strong>: {{ $subcpmk->evaluasi }}
                                            <br><strong>Komponen Tugas</strong>:
                                            {{-- menampilkan komponen tugas beserta bobotnya --}}
                                            @php
                                                $penugasanByEvaluasi = [];
                                                foreach($subcpmk->joinSubcpmkPenugasans as $item) {
                                                    $evaluasiId = $item->penugasan->evaluasi_id;
                                                    if (!isset($penugasanByEvaluasi[$evaluasiId])) {
                                                        $penugasanByEvaluasi[$evaluasiId] = [
                                                            'nama' => $item->penugasan->evaluasi->nama,
                                                            'bobot' => 0
                                                        ];
                                                    }
                                                    $penugasanByEvaluasi[$evaluasiId]['bobot'] += $item->penugasan->bobot * $item->bobot/100;
                                                }
                                                $penugasanText = [];
                                                foreach($penugasanByEvaluasi as $eval) {
                                                    $penugasanText[] = $eval['nama'] . ' (' . $eval['bobot'] . '%)';
                                                }
                                            @endphp
                                            {{ implode(', ', $penugasanText) }}
                                            <br><strong>Bobot SubCPMK</strong>: {{ array_sum(array_column($penugasanByEvaluasi, 'bobot')) }}%
                                        </td>
                                        <td>
                                            <table class="table">
                                                <tbody>
                                                    @forelse ($subcpmk->pertemuans as $pertemuan)
                                                    <tr>
                                                        <td>
                                                            <span class="h5">
                                                                @if ($pertemuan->ke)
                                                                    Pertemuan ke-{{ $pertemuan->ke.': ' }}
                                                                @endif
                                                            </span>
                                                            {{-- tombol Edit --}}
                                                            <a href="{{ route('mks.pertemuans.edit', [$mk, $pertemuan]) }}" class="btn btn-sm btn-white text-primary"><i class="bi bi-pencil-square"></i></a>
                                                            <br>
                                                            {{-- materi --}}
                                                            {{ $pertemuan->materi ?? '' }}
                                                            <br>
                                                            <div class="bg-primary text-white opacity-25"></div>
                                                                {{-- metode --}}
                                                                <span class="badge bg-primary text-white">
                                                                    <i class="bi bi-people"></i>
                                                                    metode:
                                                                </span>
                                                                @php
                                                                    $cekMetode = $pertemuan->joinPertemuanMetodes->count() > 0;
                                                                    $joinPertemuanMetodes = $pertemuan->joinPertemuanMetodes->map(function($item) {
                                                                        return $item->metode->nama;
                                                                    })->toArray();
                                                                    $cekPenugasan = $pertemuan->penugasans->count() > 0;
                                                                @endphp
                                                                @if ($cekMetode)
                                                                    {{ implode(', ', $joinPertemuanMetodes) }}
                                                                @endif

                                                                {{-- penugasan --}}
                                                                @if ($cekPenugasan)
                                                                    <br>
                                                                    <span class="badge bg-secondary text-white">
                                                                        <i class="bi bi-list-task"></i>
                                                                        tagihan:
                                                                    </span>
                                                                    @php
                                                                        $penugasanList = $pertemuan->penugasans->map(function($item) {
                                                                            return $item->evaluasi->nama.' ('.$item->bobot.'%)';
                                                                        })->toArray();
                                                                    @endphp
                                                                    {{ implode(', ', $penugasanList) }}
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
                                                        </div>
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
