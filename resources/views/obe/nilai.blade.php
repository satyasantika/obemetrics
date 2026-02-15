@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Set Nilai Tagihan Mata Kuliah
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
                                $semesterOptions = $kontrakMks
                                    ->map(fn ($item) => $item->semester)
                                    ->filter()
                                    ->unique('id')
                                    ->sortBy('kode')
                                    ->values();
                            @endphp
                            <select id="semester-filter" class="form-control form-control-sm" style="max-width: 320px;">
                                <option value="">Semua Semester</option>
                                @foreach ($semesterOptions as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->kode }} - {{ $semester->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr>
                    {{-- menu mata kuliah --}}
                    @include('components.menu-mk',$mk)
                    <hr>

                    <div class="mb-3">
                        <a href="{{ route('setting.import.nilais', [$mk->id]) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-upload"></i> Import Nilai
                        </a>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="table-responsive nilai-matrix-wrapper">
                            <table class="table table-bordered table-striped nilai-matrix-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="sticky-col">Mahasiswa</th>
                                        @forelse ($penugasans as $penugasan)
                                            <th>
                                                <span class="fs-3">{{ $penugasan->bobot }}%</span>
                                                <span title="{{ $penugasan->nama }}">
                                                    {{ $penugasan->kode }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $penugasan->nama }}
                                                </small>
                                                @php
                                                    $cpl = $penugasan->joinSubcpmkPenugasans->pluck('subcpmk.joinCplCpmk.joinCplBk.Cpl.kode')
                                                                    ->flatten()
                                                                    ->filter()
                                                                    ->unique()
                                                                    ->sort()
                                                                    ->values()
                                                                    ->whenEmpty(fn () => collect(['-']))
                                                                    ->implode(', ');
                                                @endphp
                                                <span class="fs-6">({{ $cpl }})</span>
                                            </th>
                                        @empty
                                            <th>Belum ada penugasan</th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @if ($kontrakMks->isNotEmpty())
                                @foreach ($kontrakMks as $kontrakMk)
                                    <tr class="matriks-row" data-semester-id="{{ $kontrakMk->semester_id }}" style="vertical-align: text-top;">
                                        <td class="sticky-col">
                                            <small class="text-muted">{{ $kontrakMk->mahasiswa->nim }}</small><br>
                                            {{ $kontrakMk->mahasiswa->nama }}
                                            <br>
                                            <small class="text-muted">
                                                Nilai: {{ round($kontrakMk->nilai_angka, 2) ?? '-' }} ({{ $kontrakMk->nilai_huruf ?? '-' }})
                                            </small>
                                        </td>
                                        @forelse ($penugasans as $penugasan)
                                            <td>
                                                @php
                                                    $key = $kontrakMk->mahasiswa_id . '_' . $penugasan->id . '_' . $kontrakMk->semester_id;
                                                    $nilaiObj = $nilaisByKey[$key] ?? null;
                                                @endphp
                                                <form
                                                    action="{{ route('mks.nilais.live-update', [$mk->id]) }}"
                                                    method="POST"
                                                    class="live-nilai-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="penugasan_id" value="{{ $penugasan->id }}">
                                                    <input type="hidden" name="mahasiswa_id" value="{{ $kontrakMk->mahasiswa_id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    <input type="hidden" name="semester_id" value="{{ $kontrakMk->semester_id }}">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <input
                                                            type="number"
                                                            name="nilai"
                                                            class="form-control form-control-sm"
                                                            min="0"
                                                            max="100"
                                                            step="0.01"
                                                            placeholder="0-100"
                                                            value="{{ $nilaiObj->nilai ?? '' }}"
                                                        >
                                                        <span class="save-status small text-muted"></span>
                                                    </div>
                                                </form>
                                            </td>
                                        @empty
                                            <td><span class="text-muted">-</span></td>
                                        @endforelse
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>Rata-rata Kelas</td>
                                    @forelse ($penugasans as $penugasan)
                                        <td>{{ round($penugasan->nilais->average('nilai'), 2) }}</td>
                                    @empty
                                        <td><span class="text-muted">-</span></td>
                                    @endforelse
                                </tr>
                                <tr id="matrix-empty-row" style="display:none;">
                                    <td colspan="{{ max(2, $penugasans->count() + 1) }}"><span class="bg-warning text-dark p-2">
                                        Tidak ada data mahasiswa pada semester yang dipilih.</span>
                                    </td>
                                </tr>
                                @else
                                <tr>
                                    <td colspan="{{ max(2, $penugasans->count() + 1) }}"><span class="bg-warning text-dark p-2">
                                        Belum ada data kontrak mahasiswa untuk Mata Kuliah ini.</span>
                                    </td>
                                </tr>
                                @endif
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.live-nilai-form');
    const semesterFilter = document.getElementById('semester-filter');
    const matrixRows = document.querySelectorAll('.matriks-row');
    const matrixEmptyRow = document.getElementById('matrix-empty-row');

    forms.forEach(function (form) {
        const input = form.querySelector('input[name="nilai"]');
        const statusEl = form.querySelector('.save-status');

        const submitLive = function () {
            if (!input) {
                return;
            }

            statusEl.textContent = 'menyimpan...';
            statusEl.className = 'save-status small text-muted';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Gagal menyimpan');
                }
                return response.json();
            })
            .then(function () {
                statusEl.textContent = 'tersimpan';
                statusEl.className = 'save-status small text-success';
                setTimeout(function () {
                    statusEl.textContent = '';
                }, 1200);
            })
            .catch(function () {
                statusEl.textContent = 'gagal';
                statusEl.className = 'save-status small text-danger';
            });
        };

        if (input) {
            input.addEventListener('change', submitLive);
            input.addEventListener('blur', submitLive);
        }
    });

    if (semesterFilter && matrixRows.length > 0) {
        const applySemesterFilter = function () {
            const selectedSemesterId = semesterFilter.value;
            let visibleCount = 0;

            matrixRows.forEach(function (row) {
                const rowSemesterId = row.getAttribute('data-semester-id');
                const isVisible = !selectedSemesterId || selectedSemesterId === rowSemesterId;

                row.style.display = isVisible ? '' : 'none';
                if (isVisible) {
                    visibleCount++;
                }
            });

            if (matrixEmptyRow) {
                matrixEmptyRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        };

        semesterFilter.addEventListener('change', applySemesterFilter);
        applySemesterFilter();
    }

});
</script>
@endpush

@push('styles')
<style>
.nilai-matrix-wrapper {
    max-height: 70vh;
    overflow: auto;
}

.nilai-matrix-table thead th {
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 20;
}

.nilai-matrix-table .sticky-col {
    position: sticky;
    left: 0;
    background: #fff;
    z-index: 15;
    min-width: 240px;
}

.nilai-matrix-table thead .sticky-col {
    z-index: 25;
}
</style>
@endpush

@endsection
