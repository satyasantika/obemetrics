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
                    @include('components.identitas-mk', $mk)
                    <hr>
                    {{-- menu mata kuliah --}}
                    @include('components.menu-mk',$mk)
                    <hr>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('setting.import.mk-master', ['mk' => $mk->id, 'target' => 'subcpmks']) }}" class="btn btn-sm btn-success mt-1"><i class="bi bi-upload"></i> Import banyak SubCPMK</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="float-end">
                                <span class="h4">Total bobot evaluasi: {{ $total_bobot }}%</span>
                                <br>
                                <small class="text-primary">bobot akan otomatis dihitung jika sudah set Tagihan Tugas</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            @forelse ($cpmks as $cpmk)
                            <div class="card mb-3">
                                <div class="card-header bg-dark text-white">
                                    <strong class="h4">{{ $cpmk->kode }}</strong><br>
                                    <span class="h5">{{ $cpmk->nama }}</span>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        @foreach ($cpmk->joinCplCpmks->pluck('subcpmks')->flatten() as $subcpmk)
                                            <li>
                                                <strong class="h5">{{ $subcpmk->kode }}</strong>
                                                {{-- Edit SubCPMK --}}
                                                <a href="{{ route('mks.subcpmks.edit',[$mk->id,$subcpmk->id]) }}" class="btn btn-sm btn-white text-primary">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <br>
                                                <span class="h5">{{ $subcpmk->nama }}</span>
                                                @php
                                                    $kompetensi = [];
                                                    if ($subcpmk->kompetensi_c) $kompetensi[] = $subcpmk->kompetensi_c;
                                                    if ($subcpmk->kompetensi_a) $kompetensi[] = $subcpmk->kompetensi_a;
                                                    if ($subcpmk->kompetensi_p) $kompetensi[] = $subcpmk->kompetensi_p;
                                                @endphp
                                                <span class="badge bg-info text-dark mb-3">
                                                    [{{ implode(', ', $kompetensi) }}]
                                                </span>
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th class="bg-secondary text-white">Indikator</th>
                                                            <th class="bg-secondary text-white">Evaluasi</th>
                                                            <th class="bg-secondary text-white">Bobot</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{{ $subcpmk->indikator }}</td>
                                                            <td>
                                                                {{ $subcpmk->evaluasi }}<hr>
                                                                <strong>Tagihan: </strong>
                                                                {{ $subcpmk->joinSubcpmkPenugasans
                                                                    ->groupBy(fn($t) => $t->penugasan->evaluasi->nama ?? '-')
                                                                    ->map(fn($group) =>
                                                                        $group->sum(fn($t) =>
                                                                            (float)($t->penugasan->bobot ?? 0) * ((float)($t->bobot ?? 0) / 100)
                                                                        )
                                                                    )
                                                                    ->filter(fn($total) => $total > 0) // opsional: buang total 0
                                                                    ->map(fn($total, $nama) =>
                                                                        // tampilkan tanpa desimal jika bilangan bulat, else 2 desimal
                                                                        $nama.' ('.(intval($total) == $total ? intval($total) : number_format($total, 2)).'%)'
                                                                    )
                                                                    ->values()
                                                                    ->whenEmpty(fn () => collect(['-'])) // fallback jika tidak ada data
                                                                    ->implode(', ')
                                                                }}
                                                            </td>
                                                            <td>
                                                                {{ $subcpmk->joinSubcpmkPenugasans->sum(fn ($row) => (float)($row->penugasan->bobot ?? 0) * (float)($row->bobot ?? 0)/100);
                                                                }}%
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                                @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data Sub CPMK untuk mata kuliah ini.</span>
                                        </td>
                                    </tr>
                                @endforelse
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
