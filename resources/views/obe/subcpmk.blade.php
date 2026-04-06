@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <div class="card">
                <x-obe.header
                    title="Data Sub Capaian Pembelajaran Mata Kuliah"
                    subtitle="Kelola SubCPMK pada mata kuliah terpilih"
                    icon="bi bi-diagram-3" />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-3">
                        <div class="col-md-6 d-flex">
                            <div class="d-flex flex-column align-items-start gap-2 h-100 w-100">
                                <span>Semester :</span>
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
                                <select id="semester-filter" name="semester_id" class="form-control form-control-sm w-100" style="max-width: 320px;">
                                    @foreach ($semesterOptions as $semester)
                                    <option value="{{ $semester->id }}" @selected($semester->status_aktif)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                    @endforeach
                                </select>
                                @php
                                    $joinCplCpmkOptions = \App\Models\JoinCplCpmk::where('mk_id', $mk->id)->with('cpmk')->get();
                                @endphp
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateSubcpmk"><i class="bi bi-plus-circle"></i> Tambah Sub CPMK</button>
                                    <a href="{{ route('settings.import.mk-master', ['mk' => $mk->id, 'target' => 'subcpmks']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-upload"></i> Import banyak SubCPMK</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="p-3 p-lg-4 rounded-3 border border-primary-subtle bg-primary-subtle text-primary-emphasis h-100 w-100 d-flex flex-column justify-content-between text-md-end text-start">
                                <div>
                                    <span class="small text-uppercase fw-semibold d-block">Total bobot evaluasi</span>
                                    <span class="display-6 fw-bold lh-1 d-block mt-2">{{ $total_bobot }}%</span>
                                </div>
                                <small class="mt-2">Bobot akan otomatis dihitung jika sudah set Tagihan Tugas</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            @forelse ($cpmks as $cpmk)
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-header border-0 border-bottom py-3">
                                    <div class="d-flex align-items-start gap-2 text-dark">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="d-inline-flex align-items-center justify-content-center rounded-start-5 bg-info-subtle text-info-emphasis border border-info-subtle px-3" style="min-height: 45px;">
                                                <i class="bi bi-sliders fs-4"></i> &nbsp;{{ $cpmk->kode }}
                                            </div>
                                            <div>
                                                <span class="fs-5">{{ $cpmk->nama }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body bg-white">
                                    <ul class="list-unstyled mb-0">
                                        @forelse ($cpmk->joinCplCpmks->pluck('subcpmks')->flatten() as $subcpmk)
                                        <li class="border rounded-3 p-3 mb-3">
                                            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="d-inline-flex align-items-center justify-content-center rounded-end-5 bg-success-subtle text-success-emphasis border border-success-subtle px-3" style="min-height: 35px;">
                                                        <i class="bi bi-list-nested fs-4"></i>  &nbsp;{{ $subcpmk->kode }}
                                                    </div>
                                                    <div>
                                                        <div class="text-muted">{{ $subcpmk->nama }}
                                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalEditSubcpmk-{{ $subcpmk->id }}" title="Edit SubCPMK">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                    <div class="fs-6">
                                                        @php
                                                            $kompetensi = [];
                                                            if ($subcpmk->kompetensi_c) $kompetensi[] = $subcpmk->kompetensi_c;
                                                            if ($subcpmk->kompetensi_a) $kompetensi[] = $subcpmk->kompetensi_a;
                                                            if ($subcpmk->kompetensi_p) $kompetensi[] = $subcpmk->kompetensi_p;
                                                        @endphp
                                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle fs-6 px-3 py-1">
                                                            [{{ implode(', ', $kompetensi) }}]
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            @php
                                                $tagihanList = $subcpmk->joinSubcpmkPenugasans
                                                    ->groupBy(fn($t) => $t->penugasan->evaluasi->nama ?? '-')
                                                    ->map(fn($group) =>
                                                        $group->sum(fn($t) =>
                                                            (float)($t->penugasan->bobot ?? 0) * ((float)($t->bobot ?? 0) / 100)
                                                        )
                                                    )
                                                    ->filter(fn($total) => $total > 0)
                                                    ->map(fn($total, $nama) => [
                                                        'nama' => $nama,
                                                        'total' => $total,
                                                    ])
                                                    ->values();

                                                $totalBobotSubcpmk = $subcpmk->joinSubcpmkPenugasans
                                                    ->sum(fn ($row) => (float)($row->penugasan->bobot ?? 0) * (float)($row->bobot ?? 0)/100);
                                            @endphp

                                            <div class="row g-3 mt-1">
                                                <div class="col-12 col-lg-4">
                                                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                        <div class="small text-uppercase text-muted mb-1">Indikator</div>
                                                        <div>{{ $subcpmk->indikator }}</div>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-lg-6">
                                                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                        <div class="small text-uppercase text-muted mb-1">Evaluasi</div>
                                                        <div class="mb-2">{{ $subcpmk->evaluasi }}</div>
                                                        <div class="small text-uppercase text-muted mb-2">Bentuk Tagihan</div>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @forelse ($tagihanList as $tagihan)
                                                                <span class="badge text-bg-light border">
                                                                    {{ $tagihan['nama'] }} ({{ intval($tagihan['total']) == $tagihan['total'] ? intval($tagihan['total']) : number_format($tagihan['total'], 2) }}%)
                                                                </span>
                                                            @empty
                                                                <span class="badge text-bg-danger border">belum ada</span>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-lg-2">
                                                    <div class="border rounded-3 p-3 h-100 bg-light-subtle d-flex flex-column justify-content-center align-items-lg-end align-items-start">
                                                        <div class="small text-uppercase text-muted mb-1">Bobot</div>
                                                        <span class="badge bg-primary-subtle text-primary fs-3">
                                                            {{ intval($totalBobotSubcpmk) == $totalBobotSubcpmk ? intval($totalBobotSubcpmk) : number_format($totalBobotSubcpmk, 2) }}%
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li>
                                                <span class="alert alert-warning p-2">
                                                    <i class="bi bi-exclamation-triangle"></i> Belum ada data SubCPMK untuk {{ $cpmk->kode }}.</span>
                                            </li>
                                        @endforelse
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
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-save"></i> Save</button>
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
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-save"></i> Save</button>
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
