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
                    @include('components.identitas-mk', $mk)
                    <div class="row">
                        <div class="col-md-3">Semester</div>
                        <div class="col">
                            @php
                                $semesterOptions = $mk->kontrakMks()
                                    ->whereNotNull('semester_id')
                                    ->with('semester')
                                    ->get()
                                    ->pluck('semester')
                                    ->filter()
                                    ->unique('id')
                                    ->sortByDesc('status_aktif')
                                    ->sortByDesc('kode')
                                    ->values();
                            @endphp
                            <select id="semester-filter" name="semester_id" class="form-control form-control-sm" style="max-width: 320px;">
                                @foreach ($semesterOptions as $semester)
                                    <option value="{{ $semester->id }}" @selected($semester->status_aktif)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr>
                    {{-- menu mata kuliah --}}
                    @include('components.menu-mk',$mk)
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
                            <a href="{{ route('setting.import.mk-master', ['mk' => $mk->id, 'target' => 'penugasans']) }}" class="btn btn-sm btn-success mt-1 float-end"><i class="bi bi-upload"></i> Import Banyak Tagihan</a>
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
    <div class="row justify-content-center mt-3">
        <div class="col-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <span class="h5">Tabel Rencana Evaluasi (<i>Assessment Plan</i>)</span>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Komponen Evaluasi</th>
                                <th>Bentuk Asesmen</th>
                                <th>Bobot (%)</th>
                                <th>Mengukur CPL</th>
                                <th>Mengukur CPMK</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($evaluasis->pluck('kategori')->unique() as $kategori_evaluasi)
                                <tr class="table-secondary">
                                    <th colspan="5" class="table-secondary text-center">
                                        <strong>{{ $kategori_evaluasi }} ({{ $evaluasis->where('kategori', $kategori_evaluasi)->map(function($evaluasi) use ($mk){
                                            return $evaluasi->penugasans->where('mk_id', $mk->id)->sum('bobot');
                                        })->sum() }}%)</strong>
                                    </th>
                                </tr>
                                @forelse ($evaluasis->where('kategori', $kategori_evaluasi) as $evaluasi)
                                @php
                                    $asesmens = $evaluasi->penugasans->where('mk_id', $mk->id);
                                @endphp
                                <tr>
                                    <td>{{ $evaluasi->nama }}</td>
                                    <td>
                                        @forelse ($asesmens as $tugas)
                                            <table>
                                                <tbody>
                                                    <tr style="vertical-align: top">
                                                        <td>{{ $tugas->kode }}:</td>
                                                        <td>{{ $tugas->nama }} (bobot: {{ $tugas->bobot }}%)</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        @empty
                                            -
                                        @endforelse
                                    </td>
                                    <td class="text-end">
                                        {{ $asesmens->sum('bobot') }}%
                                    </td>
                                    <td>
                                        {{ $asesmens
                                            ->pluck('joinSubcpmkPenugasans.*.subcpmk.joinCplCpmk.joinCplBk.Cpl.kode')
                                            ->flatten()
                                            ->filter()
                                            ->unique()
                                            ->sort()
                                            ->values()
                                            ->whenEmpty(fn () => collect(['-']))
                                            ->implode(', ')
                                        }}
                                    </td>
                                    <td>
                                        {{ $asesmens
                                            ->pluck('joinSubcpmkPenugasans.*.subcpmk.joinCplCpmk.cpmk.kode')
                                            ->flatten()
                                            ->filter()
                                            ->unique()
                                            ->sort()
                                            ->values()
                                            ->whenEmpty(fn () => collect(['-']))
                                            ->implode(', ')
                                        }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data Tugas untuk mata kuliah ini.</td>
                                </tr>
                                @endforelse
                            @endforeach
                            <tr class="table-secondary">
                                <th colspan="2" class="text-end">Total Bobot</th>
                                <th class="text-end">
                                    {{ $penugasans->sum('bobot') }}%
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
