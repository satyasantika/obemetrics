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
                            @php
                                $joinCplCpmkOptions = \App\Models\JoinCplCpmk::where('mk_id', $mk->id)->with('cpmk')->get();
                            @endphp
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateSubcpmk"><i class="bi bi-plus-circle"></i> Tambah Sub CPMK</button>
                            <a href="{{ route('setting.import.mk-master', ['mk' => $mk->id, 'target' => 'subcpmks']) }}" class="btn btn-sm btn-success"><i class="bi bi-upload"></i> Import banyak SubCPMK</a>
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
                                                <button type="button" class="btn btn-sm btn-white text-primary" data-bs-toggle="modal" data-bs-target="#modalEditSubcpmk-{{ $subcpmk->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
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
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCreateSubcpmk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('mks.subcpmks.store', $mk) }}" method="post">
                @csrf
                <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sub CPMK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label"><strong>CPMK</strong><span class="text-danger">(*)</span></label>
                            <select name="join_cpl_cpmk_id" class="form-control" required>
                                <option value="">Pilih CPMK</option>
                                @foreach ($joinCplCpmkOptions as $joinOption)
                                    <option value="{{ $joinOption->id }}">{{ $joinOption->cpmk->kode }} - {{ $joinOption->cpmk->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label"><strong>Kode</strong> Sub CPMK <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label"><strong>Nama</strong> Sub CPMK <span class="text-danger">(*)</span></label>
                            <textarea name="nama" rows="3" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Kognitif</label>
                            <select name="kompetensi_c" class="form-control">
                                <option value="">Pilih Kognitif</option>
                                <option value="C1">C1 - Mengingat</option>
                                <option value="C2">C2 - Memahami</option>
                                <option value="C3">C3 - Menerapkan</option>
                                <option value="C4">C4 - Menganalisis</option>
                                <option value="C5">C5 - Mengevaluasi</option>
                                <option value="C6">C6 - Menciptakan</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Afektif</label>
                            <select name="kompetensi_a" class="form-control">
                                <option value="">Pilih Afektif</option>
                                <option value="A1">A1 - Menerima</option>
                                <option value="A2">A2 - Merespon</option>
                                <option value="A3">A3 - Menghargai</option>
                                <option value="A4">A4 - Mengorganisasikan</option>
                                <option value="A5">A5 - Karakterisasi Menurut Nilai</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Psikomotor</label>
                            <select name="kompetensi_p" class="form-control">
                                <option value="">Pilih Psikomotor</option>
                                <option value="P1">P1 - Meniru</option>
                                <option value="P2">P2 - Memanipulasi</option>
                                <option value="P3">P3 - Presisi</option>
                                <option value="P4">P4 - Artikulasi</option>
                                <option value="P5">P5 - Naturalisasi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Indikator dari SubCPMK <span class="text-danger">(*)</span></label>
                            <textarea name="indikator" rows="3" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Deskripsi Rencana Evaluasi dari SubCPMK <span class="text-danger">(*)</span></label>
                            <textarea name="evaluasi" rows="3" class="form-control" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($cpmks as $cpmk)
    @foreach ($cpmk->joinCplCpmks->pluck('subcpmks')->flatten() as $subcpmk)
    <div class="modal fade" id="modalEditSubcpmk-{{ $subcpmk->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('mks.subcpmks.update',[$mk->id,$subcpmk->id]) }}" method="post">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit SubCPMK: {{ $subcpmk->kode }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label"><strong>CPMK</strong><span class="text-danger">(*)</span></label>
                                <select name="join_cpl_cpmk_id" class="form-control" required>
                                    <option value="">Pilih CPMK</option>
                                    @foreach ($joinCplCpmkOptions as $joinOption)
                                        <option value="{{ $joinOption->id }}" @selected($subcpmk->join_cpl_cpmk_id == $joinOption->id)>{{ $joinOption->cpmk->kode }} - {{ $joinOption->cpmk->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label"><strong>Kode</strong> Sub CPMK <span class="text-danger">(*)</span></label>
                                <input type="text" name="kode" class="form-control" value="{{ $subcpmk->kode }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label"><strong>Nama</strong> Sub CPMK <span class="text-danger">(*)</span></label>
                                <textarea name="nama" rows="3" class="form-control" required>{{ $subcpmk->nama }}</textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Kognitif</label>
                                <select name="kompetensi_c" class="form-control">
                                    <option value="">Pilih Kognitif</option>
                                    <option value="C1" @selected($subcpmk->kompetensi_c == 'C1')>C1 - Mengingat</option>
                                    <option value="C2" @selected($subcpmk->kompetensi_c == 'C2')>C2 - Memahami</option>
                                    <option value="C3" @selected($subcpmk->kompetensi_c == 'C3')>C3 - Menerapkan</option>
                                    <option value="C4" @selected($subcpmk->kompetensi_c == 'C4')>C4 - Menganalisis</option>
                                    <option value="C5" @selected($subcpmk->kompetensi_c == 'C5')>C5 - Mengevaluasi</option>
                                    <option value="C6" @selected($subcpmk->kompetensi_c == 'C6')>C6 - Menciptakan</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Afektif</label>
                                <select name="kompetensi_a" class="form-control">
                                    <option value="">Pilih Afektif</option>
                                    <option value="A1" @selected($subcpmk->kompetensi_a == 'A1')>A1 - Menerima</option>
                                    <option value="A2" @selected($subcpmk->kompetensi_a == 'A2')>A2 - Merespon</option>
                                    <option value="A3" @selected($subcpmk->kompetensi_a == 'A3')>A3 - Menghargai</option>
                                    <option value="A4" @selected($subcpmk->kompetensi_a == 'A4')>A4 - Mengorganisasikan</option>
                                    <option value="A5" @selected($subcpmk->kompetensi_a == 'A5')>A5 - Karakterisasi Menurut Nilai</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Psikomotor</label>
                                <select name="kompetensi_p" class="form-control">
                                    <option value="">Pilih Psikomotor</option>
                                    <option value="P1" @selected($subcpmk->kompetensi_p == 'P1')>P1 - Meniru</option>
                                    <option value="P2" @selected($subcpmk->kompetensi_p == 'P2')>P2 - Memanipulasi</option>
                                    <option value="P3" @selected($subcpmk->kompetensi_p == 'P3')>P3 - Presisi</option>
                                    <option value="P4" @selected($subcpmk->kompetensi_p == 'P4')>P4 - Artikulasi</option>
                                    <option value="P5" @selected($subcpmk->kompetensi_p == 'P5')>P5 - Naturalisasi</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Indikator dari SubCPMK <span class="text-danger">(*)</span></label>
                                <textarea name="indikator" rows="3" class="form-control" required>{{ $subcpmk->indikator }}</textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Deskripsi Rencana Evaluasi dari SubCPMK <span class="text-danger">(*)</span></label>
                                <textarea name="evaluasi" rows="3" class="form-control" required>{{ $subcpmk->evaluasi }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @php
                            $canDeleteSubcpmk = !$subcpmk->joinSubcpmkPenugasans()->exists();
                        @endphp
                        @if ($canDeleteSubcpmk)
                            <button type="button" class="btn btn-outline-danger btn-sm me-auto" onclick="if(confirm('Yakin akan menghapus SubCPMK {{ $subcpmk->kode }}?')){ document.getElementById('delete-subcpmk-{{ $subcpmk->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
                        @else
                            <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                        @endif
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                    </div>
                </form>
                @if ($canDeleteSubcpmk)
                    <form id="delete-subcpmk-{{ $subcpmk->id }}" action="{{ route('mks.subcpmks.destroy',[$mk->id,$subcpmk->id]) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
@endforeach
@endsection
