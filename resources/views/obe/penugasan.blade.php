@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <div class="card">
                <x-obe.header
                    title="Rancangan Tugas"
                    subtitle="Kelola komponen tugas dan bobot penilaian"
                    icon="bi bi-journal-richtext" />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-3">
                        <div class="col-md-6">Semester :
                            <select id="semester-filter" name="semester_id" class="form-control form-control-sm" style="max-width: 320px;">
                                @foreach ($semesterOptions as $semester)
                                    <option value="{{ $semester->id }}" @selected((string) $semester->id === (string) $selectedSemesterId)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalCreatePenugasan"><i class="bi bi-plus-circle"></i> Tambah Tagihan</button>
                            <a href="{{ route('settings.import.mk-master', ['mk' => $mk->id, 'target' => 'penugasans']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm mt-2"><i class="bi bi-upload"></i> Import Banyak Tagihan</a>
                            <script>
                                document.getElementById('semester-filter').addEventListener('change', function () {
                                    const url = new URL(window.location.href);
                                    url.searchParams.set('semester_id', this.value);
                                    window.location.href = url.toString();
                                });
                            </script>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="p-3 p-lg-4 rounded-3 border border-primary-subtle bg-primary-subtle text-primary-emphasis h-100 w-100 d-flex flex-column justify-content-between text-md-end text-start">
                                <div>
                                    <span class="small text-uppercase fw-semibold d-block">Ringkasan Tugas</span>
                                    <span class="h5 mb-0 d-block mt-2">Banyak Tugas: {{ $penugasans->count() }}</span>
                                </div>
                                <span class="h5 mb-0 d-block fw-bold {{ $penugasans->sum('bobot') != 100 ? 'text-danger' : 'text-success' }}">
                                    Total Bobot Tugas: {{ $penugasans->sum('bobot') }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="table-responsive rounded-3 border bg-white shadow-sm">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-uppercase small text-muted">Kode</th>
                                        <th class="text-uppercase small text-muted">SubCPMK</th>
                                        <th class="text-uppercase small text-muted">Nama Tugas</th>
                                        <th class="text-uppercase small text-muted">Bobot (%)</th>
                                        <th class="text-uppercase small text-muted">Bentuk Evaluasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($penugasans as $penugasan)
                                    <tr>
                                        <td class="fw-semibold">{{ $penugasan->kode }}</td>
                                        <td>
                                            @forelse ($penugasan->joinSubcpmkPenugasans as $item)
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle me-1 mb-1">
                                                {{ $item->subcpmk->kode }} (<span class="text-primary">{{ $item->bobot }}%</span>)
                                            </span>
                                            @empty
                                            <span class="text-muted">- Belum ada SubCPMK yang terkait -</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            {{ $penugasan->nama }}
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#modalEditPenugasan-{{ $penugasan->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                        <td class="fw-semibold">{{ $penugasan->bobot }}</td>
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
    <div class="row justify-content-center mt-3">
        <div class="col-12">
            <div class="card">
                <x-obe.header
                    title="Tabel Rencana Evaluasi"
                    subtitle="Assessment Plan berdasarkan komponen evaluasi"
                    icon="bi bi-clipboard2-data" />
                <div class="card-body bg-light-subtle">
                    <div class="table-responsive rounded-3 border bg-white shadow-sm">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-uppercase small text-muted">Komponen Evaluasi</th>
                                <th class="text-uppercase small text-muted">Bentuk Asesmen</th>
                                <th class="text-uppercase small text-muted">Bobot (%)</th>
                                <th class="text-uppercase small text-muted">Mengukur CPL</th>
                                <th class="text-uppercase small text-muted">Mengukur CPMK</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($evaluasis->pluck('kategori')->unique() as $kategori_evaluasi)
                                <tr class="table-secondary">
                                    <th colspan="5" class="table-secondary text-center">
                                        <strong>{{ $kategori_evaluasi }} ({{ $evaluasis->where('kategori', $kategori_evaluasi)->map(function($evaluasi) use ($penugasans){
                                            return $penugasans->where('evaluasi_id', $evaluasi->id)->sum('bobot');
                                        })->sum() }}%)</strong>
                                    </th>
                                </tr>
                                @forelse ($evaluasis->where('kategori', $kategori_evaluasi) as $evaluasi)
                                @php
                                    $asesmens = $penugasans->where('evaluasi_id', $evaluasi->id);
                                @endphp
                                <tr>
                                    <td>{{ $evaluasi->nama }}</td>
                                    <td>
                                        @forelse ($asesmens as $tugas)
                                            <table>
                                                <tbody>
                                                    <tr>
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
</div>

<div class="modal fade" id="modalCreatePenugasan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('mks.penugasans.store', $mk->id) }}" method="post">
                @csrf
                <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tagihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Nama Tugas <span class="text-danger">(*)</span></label>
                            <textarea name="nama" rows="3" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Kode</label>
                            <input type="text" name="kode" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">Bobot (%) <span class="text-danger">(*)</span></label>
                            <input type="number" step="1" name="bobot" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Bentuk Evaluasi <span class="text-danger">(*)</span></label>
                            <select name="evaluasi_id" class="form-select" required>
                                <option value="">-Pilih Evaluasi-</option>
                                @foreach ($evaluasis as $evaluasi)
                                    <option value="{{ $evaluasi->id }}">{{ $evaluasi->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="8" class="form-control"></textarea>
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

@foreach ($penugasans as $penugasan)
<div class="modal fade" id="modalEditPenugasan-{{ $penugasan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('mks.penugasans.update',[$mk->id,$penugasan->id]) }}" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                <input type="hidden" name="semester_id" value="{{ $penugasan->semester_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tagihan: {{ $penugasan->kode }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Nama Tugas <span class="text-danger">(*)</span></label>
                            <textarea name="nama" rows="3" class="form-control" required>{{ $penugasan->nama }}</textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Kode</label>
                            <input type="text" name="kode" class="form-control" value="{{ $penugasan->kode }}">
                        </div>
                        <div class="col">
                            <label class="form-label">Bobot (%) <span class="text-danger">(*)</span></label>
                            <input type="number" step="1" name="bobot" class="form-control" value="{{ $penugasan->bobot }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Bentuk Evaluasi <span class="text-danger">(*)</span></label>
                            <select name="evaluasi_id" class="form-select" required>
                                <option value="">-Pilih Evaluasi-</option>
                                @foreach ($evaluasis as $evaluasi)
                                    <option value="{{ $evaluasi->id }}" @selected($penugasan->evaluasi_id == $evaluasi->id)>{{ $evaluasi->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="8" class="form-control">{{ $penugasan->deskripsi }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @php
                        $canDeletePenugasan = !$penugasan->joinSubcpmkPenugasans()->exists() && !$penugasan->nilais()->exists();
                    @endphp
                    @if ($canDeletePenugasan)
                        <button type="button" class="btn btn-outline-danger btn-sm me-auto" onclick="if(confirm('Yakin akan menghapus tagihan {{ $penugasan->kode }}: {{ $penugasan->nama }}?')){ document.getElementById('delete-penugasan-{{ $penugasan->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
                    @else
                        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            @if ($canDeletePenugasan)
                <form id="delete-penugasan-{{ $penugasan->id }}" action="{{ route('mks.penugasans.destroy',[$mk->id,$penugasan->id]) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
            @endif
        </div>
    </div>
</div>
@endforeach

@endsection
