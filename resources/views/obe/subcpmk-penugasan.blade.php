@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <div class="card">
                <x-obe.header
                    title="Set SubCPMK untuk Setiap Tugas Mata Kuliah"
                    subtitle="Pemetaan SubCPMK pada komponen penugasan"
                    icon="bi bi-diagram-3" />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-3 g-3">
                        <div class="col-md-6 d-flex">
                            <div class="p-3 rounded-3 border bg-white d-flex flex-column align-items-start gap-2 h-100 w-100">
                                <span>Semester :</span>
                                <select id="semester-filter" name="semester_id" class="form-control form-control-sm w-100" style="max-width: 320px;">
                                    @foreach ($semesterOptions as $semester)
                                        <option value="{{ $semester->id }}" @selected((string) $semester->id === (string) $selectedSemesterId)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('settings.import.mk-master', ['mk' => $mk->id, 'target' => 'join_subcpmk_penugasans', 'semester_id' => $selectedSemesterId]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-upload"></i> Import banyak SubCPMK untuk Penugasan</a>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="p-3 p-lg-4 rounded-3 border border-primary-subtle bg-primary-subtle text-primary-emphasis h-100 w-100 d-flex flex-column justify-content-between text-md-end text-start">
                                <div>
                                    <span class="small text-uppercase fw-semibold d-block">Ringkasan Relasi</span>
                                    <span class="h5 mb-0 d-block mt-2">Total Tugas: {{ $penugasans->count() }}</span>
                                </div>
                                <small class="mt-2">Atur bobot relasi SubCPMK agar total tiap tugas = 100%</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-2">
                            <div class="p-3 rounded-3 border border-warning-subtle bg-warning-subtle text-warning-emphasis">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span class="fw-semibold">Petunjuk Pemetaan SubCPMK - Penugasan</span>
                                </div>
                                <div class="small">
                                    Bagian ini digunakan untuk mengaitkan SubCPMK dengan penugasan (tugas) yang ada pada mata kuliah ini.<br>
                                    Pada kondisi tertentu, satu penugasan bisa terkait dengan lebih dari satu SubCPMK, dan satu SubCPMK bisa terkait dengan lebih dari satu penugasan.<br>
                                    Silakan isi bobot (tanpa %) dan tekan Enter pada pasangan isian SubCPMK Penugasan. Pastikan tampil keterangan <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Terkait</span><br>
                                    Untuk menghapus keterkaitan, kosongkan nilai bobot dan tekan Enter.<br>
                                    Bobot total untuk setiap penugasan harus 100%. (perhatikan keterangan di setiap penugasan)
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="table-responsive rounded-3 border bg-white shadow-sm subcpmk-matrix-wrapper">
                            <table class="table table-hover align-middle mb-0 subcpmk-matrix-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-uppercase small text-muted fw-semibold sticky-col">Penugasan</th>
                                        @forelse ($subcpmks as $subcpmk)
                                            <th class="text-center" style="min-width: 90px;">
                                                <div class="d-flex flex-column align-items-center gap-1"
                                                     data-bs-toggle="popover"
                                                     data-bs-trigger="hover focus"
                                                     data-bs-placement="top"
                                                     data-bs-title="{{ $subcpmk->kode }}"
                                                     data-bs-content="{{ $subcpmk->nama }}">
                                                    <span class="badge rounded-pill bg-info-subtle text-info-emphasis fw-bold" style="font-size: 0.8rem;">{{ $subcpmk->kode }}</span>
                                                    {{-- @if($subcpmk->joinCplCpmk?->cplBk?->cpl?->kode)
                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill" style="font-size: 0.65rem;">{{ $subcpmk->joinCplCpmk->cplBk->cpl->kode }}</span>
                                                    @endif --}}
                                                </div>
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($penugasans as $penugasan)
                                    <tr class="align-top">
                                        <td class="bg-light-subtle sticky-col" >
                                            <span class="fw-semibold d-block">{{ $penugasan->kode }}</span>
                                            <span class="text-muted small d-block">{{ $penugasan->nama }}</span>
                                            @php
                                                $totalBobot = (float) ($bobotTotalByPenugasan[$penugasan->id] ?? 0);
                                            @endphp
                                            <span class="total-bobot-label badge rounded-pill mt-2 text-{{ $totalBobot==100 ? 'primary' : 'danger' }} bg-white border border-{{ $totalBobot==100 ? 'primary' : 'danger' }}">
                                                Bobot: <span class="total-bobot-value">{{ $totalBobot }}</span>%
                                            </span>
                                        </td>
                                        @forelse ($subcpmks as $subcpmk)
                                            <td class="p-2">
                                                @php
                                                    $cellKey = $penugasan->id . '_' . $subcpmk->id;
                                                    $linkedObj = $linkByKey[$cellKey] ?? null;
                                                    $bobot = $linkedObj?->bobot;
                                                @endphp
                                                <form action="{{ route('mks.joinsubcpmkpenugasans.update',[$mk->id,$subcpmk->id,$penugasan->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="penugasan_id" value="{{ $penugasan->id }}">
                                                    <input type="hidden" name="subcpmk_id" value="{{ $subcpmk->id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <input
                                                            class="form-control form-control-sm bobot-input text-end border-primary-subtle"
                                                            type="number"
                                                            name="bobot"
                                                            title="{{ $subcpmk->nama }}"
                                                            min="0"
                                                            max="100"
                                                            step="5"
                                                            placeholder="bobot %"
                                                            value="{{ $bobot !== null ? $bobot : '' }}"
                                                        >
                                                    </div>
                                                    <div class="mt-1 d-flex align-items-center justify-content-between gap-1">
                                                        <span class="badge {{ $linkedObj ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : '' }} link-status-badge">
                                                            {{ $linkedObj ? 'Terkait' : '' }}
                                                        </span>
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-danger btn-sm py-0 px-2 rounded-pill clear-bobot-btn {{ $linkedObj ? '' : 'd-none' }}"
                                                            title="Hapus relasi SubCPMK-Penugasan"
                                                            aria-label="Hapus relasi"
                                                        >
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>
                                                    <span class="save-status small text-muted"></span>
                                                </form>
                                            </td>
                                        @empty
                                            <td></td>
                                        @endforelse
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data Tugas untuk Mata Kuliah ini.</span>
                                        </td>
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
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
        new bootstrap.Popover(el);
    });

    const semesterFilter = document.getElementById('semester-filter');
    const forms = document.querySelectorAll('form[action*="joinsubcpmkpenugasans"]');

    if (semesterFilter) {
        semesterFilter.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('semester_id', semesterFilter.value || '');
            window.location.href = url.toString();
        });
    }

    forms.forEach(function (form) {
        const input = form.querySelector('input[name="bobot"]');
        const statusEl = form.querySelector('.save-status');
        const badge = form.querySelector('.link-status-badge');
        const clearBtn = form.querySelector('.clear-bobot-btn');
        let isSubmitting = false;

        if (!input) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submitLive();
        });

        const submitLive = function () {
            if (isSubmitting) {
                return;
            }

            const formData = new FormData(form);
            isSubmitting = true;

            if (statusEl) {
                statusEl.textContent = 'menyimpan...';
                statusEl.className = 'save-status small text-muted';
            }

            if (clearBtn) {
                clearBtn.disabled = true;
            }

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
                    return response.json().then(function (payload) {
                        throw new Error(payload.message || 'Gagal menyimpan');
                    }).catch(function () {
                        throw new Error('Gagal menyimpan');
                    });
                }
                return response.json();
            })
            .then(function (result) {
                if (statusEl) {
                    statusEl.textContent = 'tersimpan';
                    statusEl.className = 'save-status small text-success';
                    setTimeout(function () {
                        statusEl.textContent = '';
                    }, 1200);
                }

                if (badge) {
                    const linked = !!result.linked;
                    badge.textContent = linked ? 'Terkait' : '';
                    badge.className = 'badge ' + (linked
                        ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                        : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle') + ' link-status-badge';

                    if (clearBtn) {
                        clearBtn.classList.toggle('d-none', !linked);
                    }
                }

                updateRowTotal(form.closest('tr'));
            })
            .catch(function (error) {
                if (statusEl) {
                    statusEl.textContent = error?.message || 'gagal';
                    statusEl.className = 'save-status small text-danger';
                }
            })
            .finally(function () {
                isSubmitting = false;
                if (clearBtn) {
                    clearBtn.disabled = false;
                }
            });
        };

        const updateRowTotal = function (row) {
            if (!row) {
                return;
            }

            const totalLabel = row.querySelector('.total-bobot-label');
            const totalValueEl = row.querySelector('.total-bobot-value');

            if (!totalLabel || !totalValueEl) {
                return;
            }

            const total = Array.from(row.querySelectorAll('input[name="bobot"]')).reduce(function (sum, rowInput) {
                const value = Number((rowInput.value || '').trim());
                if (Number.isNaN(value)) {
                    return sum;
                }
                return sum + value;
            }, 0);

            const roundedTotal = Math.round(total * 100) / 100;
            totalValueEl.textContent = String(roundedTotal);

            totalLabel.classList.remove('text-primary', 'text-danger');
            totalLabel.classList.add(roundedTotal === 100 ? 'text-primary' : 'text-danger');
        };

        input.addEventListener('input', function () {
            updateRowTotal(form.closest('tr'));
        });

        input.addEventListener('change', submitLive);

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                submitLive();
            });
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.subcpmk-matrix-wrapper {
    max-height: 70vh;
    overflow: auto;
}

.subcpmk-matrix-table thead th {
    position: sticky;
    top: 0;
    background: var(--bs-light);
    z-index: 20;
}

.subcpmk-matrix-table .sticky-col {
    position: sticky;
    left: 0;
    z-index: 15;
}

.subcpmk-matrix-table thead .sticky-col {
    background: var(--bs-light);
    z-index: 25;
}

.subcpmk-matrix-table tbody .sticky-col {
    background: var(--bs-light-bg-subtle);
}
</style>
@endpush

@endsection
