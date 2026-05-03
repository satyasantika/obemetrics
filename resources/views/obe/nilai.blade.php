@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <div class="card">
                <x-obe.header
                    title="Set Nilai Tagihan Mata Kuliah"
                    subtitle="Pengaturan nilai mahasiswa per komponen penilaian"
                    icon="bi bi-calculator-fill"
                <div class="card-body bg-light-subtle">
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="p-3 rounded-3 border bg-white d-flex flex-row align-items-start gap-2">
                                <span>Semester :</span>
                                <select id="semester-filter" name="semester_id" class="form-control form-control-sm" style="max-width: 320px;">
                                    @foreach ($semesterOptions as $semester)
                                        <option value="{{ $semester->id }}" @selected((string) $semester->id === (string) $selectedSemesterId)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @if ($kontrakMks->isNotEmpty())
                        <ul class="nav nav-tabs" id="kelasTab" role="tablist">
                            @foreach ($kelasGroups as $kelasKey => $kelasRows)
                                @php
                                    $kelasSlug = \Illuminate\Support\Str::slug($kelasKey, '-');
                                    $kelasPaneId = 'kelas-' . ($kelasSlug !== '' ? $kelasSlug : 'tanpa-kelas');
                                    $kelasLabel = $kelasKey === '__SEMUA_KELAS__' ? 'Semua Kelas' : 'Kelas ' . $kelasKey;
                                @endphp
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link {{ $kelasKey === $defaultKelas ? 'active' : '' }}"
                                        id="{{ $kelasPaneId }}-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#{{ $kelasPaneId }}"
                                        type="button"
                                        role="tab"
                                        aria-controls="{{ $kelasPaneId }}"
                                        aria-selected="{{ $kelasKey === $defaultKelas ? 'true' : 'false' }}">
                                        {{ $kelasLabel }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content pt-3" id="kelasTabContent">
                            @foreach ($kelasGroups as $kelasKey => $kelasRows)
                                    @php
                                        $kelasSlug = \Illuminate\Support\Str::slug($kelasKey, '-');
                                        $kelasPaneId = 'kelas-' . ($kelasSlug !== '' ? $kelasSlug : 'tanpa-kelas');
                                        $kelasLabel = $kelasKey === '__SEMUA_KELAS__' ? 'Semua Kelas' : 'Kelas ' . $kelasKey;
                                        $kelasQuery = ['kelas' => $kelasKey];
                                        $importNilaiQuery = array_merge($kelasQuery, isset($selectedSemesterId) && $selectedSemesterId !== null && $selectedSemesterId !== '' ? ['semester_id' => $selectedSemesterId] : []);
                                    @endphp
                                <div
                                    class="tab-pane fade {{ $kelasKey === $defaultKelas ? 'show active' : '' }}"
                                    id="{{ $kelasPaneId }}"
                                    role="tabpanel"
                                    aria-labelledby="{{ $kelasPaneId }}-tab">

                                    <div class="mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm js-sort-tab" data-sort-key="nama" data-sort-label="Urutkan Nama" data-next-direction="asc">Urutkan Nama</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm js-sort-tab" data-sort-key="nim" data-sort-label="Urutkan NIM" data-next-direction="asc">Urutkan NIM</button>
                                        <a href="{{ route('settings.import.nilais', array_merge(['mk' => $mk->id], $importNilaiQuery)) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm float-end">
                                            <i class="bi bi-upload"></i> Import Nilai {{ $kelasLabel }}
                                        </a>
                                    </div>

                                    <div class="table-responsive nilai-matrix-wrapper rounded-3 border bg-white shadow-sm">
                                        <table class="table table-hover align-middle nilai-matrix-table mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="sticky-col">Mahasiswa</th>
                                                    @forelse ($penugasans as $penugasan)
                                                        <th class="text-center" style="min-width: 110px;">
                                                            <div class="d-flex flex-column align-items-center gap-1"
                                                                 data-bs-toggle="popover"
                                                                 data-bs-trigger="hover focus"
                                                                 data-bs-placement="top"
                                                                 data-bs-title="{{ $penugasan->kode }}"
                                                                 data-bs-content="{{ $penugasan->nama }}">
                                                                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis fw-bold" style="font-size: 0.85rem;">{{ $penugasan->bobot }}%</span>
                                                                <span class="fw-semibold text-body" style="font-size: 0.85rem;">{{ $penugasan->kode }}</span>
                                                                <div class="d-flex flex-wrap justify-content-center gap-1">
                                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill" style="font-size: 0.65rem;">{{ $penugasan->evaluasi->kode }}</span>
                                                                    <span class="badge bg-info-subtle text-info-emphasis rounded-pill" style="font-size: 0.65rem;">{{ $cplLabelByPenugasanId[$penugasan->id] ?? '-' }}</span>
                                                                </div>
                                                            </div>
                                                        </th>
                                                    @empty
                                                        <th>Belum ada penugasan</th>
                                                    @endforelse
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($kelasRows as $kontrakMk)
                                                <tr class="matriks-row"
                                                    data-semester-id="{{ $kontrakMk->semester_id }}"
                                                    data-mahasiswa-nim="{{ \Illuminate\Support\Str::lower((string) ($kontrakMk->mahasiswa->nim ?? '')) }}"
                                                    data-mahasiswa-nama="{{ \Illuminate\Support\Str::lower((string) ($kontrakMk->mahasiswa->nama ?? '')) }}"
                                                    style="vertical-align: text-top;">
                                                    <td class="sticky-col">
                                                        <small class="text-muted">{{ $kontrakMk->mahasiswa->nim }}</small><br>
                                                        {{ $kontrakMk->mahasiswa->nama }}
                                                        <br>
                                                        <small class="text-primary">
                                                            Nilai: <span class="kontrak-nilai-angka">{{ $kontrakMk->nilai_angka !== null ? round((float) $kontrakMk->nilai_angka, 2) : '-' }}</span> <span class="badge bg-primary kontrak-nilai-huruf">{{ $kontrakMk->nilai_huruf ?? '-' }}</span>
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
                                                                        class="form-control form-control-sm text-end live-nilai-input {{ isset($nilaiObj) && $nilaiObj && $nilaiObj->nilai !== null && $nilaiObj->nilai !== '' ? 'border-success-subtle' : 'border-warning-subtle' }}"
                                                                        min="0"
                                                                        max="100"
                                                                        step="0.01"
                                                                        placeholder="0-100"
                                                                        value="{{ $nilaiObj->nilai ?? '' }}"
                                                                    >
                                                                </div>
                                                                <span class="save-status small text-muted"></span>
                                                            </form>
                                                        </td>
                                                    @empty
                                                        <td><span class="text-muted">-</span></td>
                                                    @endforelse
                                                </tr>
                                            @endforeach
                                            <tr class="matrix-summary-row bg-light-subtle fw-semibold">
                                                <td>Rata-rata Kelas</td>
                                                @forelse ($penugasans as $penugasan)
                                                    @php
                                                        $kelasNilaiValues = $kelasRows->map(function ($row) use ($penugasan, $nilaisByKey) {
                                                            $avgKey = $row->mahasiswa_id . '_' . $penugasan->id . '_' . $row->semester_id;
                                                            return isset($nilaisByKey[$avgKey]) ? (float) ($nilaisByKey[$avgKey]->nilai ?? 0) : null;
                                                        })->filter(fn ($item) => $item !== null);
                                                        $kelasAvg = $kelasNilaiValues->count() > 0 ? round((float) $kelasNilaiValues->average(), 2) : 0;
                                                    @endphp
                                                    <td>{{ $kelasAvg }}</td>
                                                @empty
                                                    <td><span class="text-muted">-</span></td>
                                                @endforelse
                                            </tr>
                                            <tr class="matrix-empty-row" style="display:none;">
                                                <td colspan="{{ max(2, $penugasans->count() + 1) }}"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle p-2">
                                                    Tidak ada data mahasiswa pada semester yang dipilih.</span>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">Belum ada data kontrak mahasiswa untuk Mata Kuliah ini.</div>
                    @endif
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

    const forms = document.querySelectorAll('.live-nilai-form');
    const semesterFilter = document.getElementById('semester-filter');
    const kelasTab = document.getElementById('kelasTab');
    const tabStateKey = `nilai-active-tab:${window.location.pathname}:${window.location.search}`;

    const persistActiveTab = function (targetPane) {
        if (!targetPane) {
            return;
        }
        window.sessionStorage.setItem(tabStateKey, targetPane);
    };

    const restoreActiveTab = function () {
        if (!kelasTab) {
            return;
        }

        const savedTarget = window.sessionStorage.getItem(tabStateKey);
        if (!savedTarget) {
            return;
        }

        const trigger = kelasTab.querySelector(`[data-bs-target="${savedTarget}"]`);
        if (!trigger) {
            return;
        }

        if (window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(trigger).show();
            return;
        }

        trigger.click();
    };

    if (kelasTab) {
        kelasTab.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (tabBtn) {
            tabBtn.addEventListener('shown.bs.tab', function (event) {
                persistActiveTab(event.target.getAttribute('data-bs-target'));
            });
        });

        const initialActive = kelasTab.querySelector('.nav-link.active');
        if (initialActive) {
            persistActiveTab(initialActive.getAttribute('data-bs-target'));
        }

        restoreActiveTab();
    }

    forms.forEach(function (form) {
        const input = form.querySelector('input[name="nilai"]');
        const statusEl = form.querySelector('.save-status');
        let activeController = null;
        let lastSubmittedValue = (input?.value ?? '').trim();
        let isSubmitting = false;

        const updateInputBorderState = function () {
            if (!input) {
                return;
            }

            const currentValue = (input.value ?? '').trim();
            const isFilled = currentValue !== '';

            input.classList.remove('border-success-subtle', 'border-warning-subtle');
            input.classList.add(isFilled ? 'border-success-subtle' : 'border-warning-subtle');
        };

        const setStatus = function (text, tone) {
            if (!statusEl) {
                return;
            }

            statusEl.textContent = text;
            statusEl.className = 'save-status small text-' + tone;
        };

        const submitLive = function (force) {
            if (!input) {
                return;
            }

            const currentValue = (input.value ?? '').trim();
            if (!force && currentValue === lastSubmittedValue) {
                return;
            }

            if (isSubmitting && activeController) {
                activeController.abort();
            }

            isSubmitting = true;
            setStatus('menyimpan...', 'muted');

            const formData = new FormData(form);
            const controller = new AbortController();
            activeController = controller;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData,
                signal: controller.signal
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Gagal menyimpan');
                }
                return response.json();
            })
            .then(function (result) {
                lastSubmittedValue = (input.value ?? '').trim();
                setStatus('tersimpan', 'success');
                setTimeout(function () {
                    if (statusEl && statusEl.textContent === 'tersimpan') {
                        statusEl.textContent = '';
                    }
                }, 1200);

                const row = form.closest('.matriks-row');
                if (row && result && result.kontrak_nilai) {
                    const nilaiAngkaEl = row.querySelector('.kontrak-nilai-angka');
                    const nilaiHurufEl = row.querySelector('.kontrak-nilai-huruf');

                    if (nilaiAngkaEl) {
                        const angka = result.kontrak_nilai.nilai_angka;
                        nilaiAngkaEl.textContent = angka === null || angka === undefined || angka === '' ? '-' : String(angka);
                    }

                    if (nilaiHurufEl) {
                        const huruf = result.kontrak_nilai.nilai_huruf;
                        nilaiHurufEl.textContent = huruf === null || huruf === undefined || huruf === '' ? '-' : String(huruf);
                    }
                }
            })
            .catch(function (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }

                setStatus('gagal', 'danger');
            })
            .finally(function () {
                if (activeController === controller) {
                    activeController = null;
                    isSubmitting = false;
                }
            });
        };

        if (input) {
            updateInputBorderState();
            input.addEventListener('input', updateInputBorderState);
            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                updateInputBorderState();
                submitLive(true);
            });
            input.addEventListener('blur', function () {
                updateInputBorderState();
                submitLive(true);
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submitLive(true);
        });
    });

    if (semesterFilter) {
        semesterFilter.addEventListener('change', function () {
            const selectedSemesterId = (semesterFilter.value ?? '').trim();
            const url = new URL(window.location.href);

            if (selectedSemesterId === '') {
                url.searchParams.delete('semester_id');
            } else {
                url.searchParams.set('semester_id', selectedSemesterId);
            }

            window.location.assign(url.toString());
        });
    }

    const sortRowsInPane = function (pane, sortKey) {
        return sortRowsInPaneByDirection(pane, sortKey, 'asc');
    };

    const sortRowsInPaneByDirection = function (pane, sortKey, direction) {
        if (!pane || !sortKey) {
            return;
        }

        const table = pane.querySelector('.nilai-matrix-table');
        const tbody = table ? table.querySelector('tbody') : null;
        if (!tbody) {
            return;
        }

        const matrixRows = Array.from(tbody.querySelectorAll('.matriks-row'));
        if (matrixRows.length < 2) {
            return;
        }

        matrixRows.sort(function (a, b) {
            const aValue = (a.dataset[sortKey === 'nim' ? 'mahasiswaNim' : 'mahasiswaNama'] ?? '').trim();
            const bValue = (b.dataset[sortKey === 'nim' ? 'mahasiswaNim' : 'mahasiswaNama'] ?? '').trim();
            const cmp = aValue.localeCompare(bValue, 'id', { sensitivity: 'base', numeric: true });
            return direction === 'desc' ? -cmp : cmp;
        });

        const summaryRow = tbody.querySelector('.matrix-summary-row');
        const anchorNode = summaryRow ?? tbody.querySelector('.matrix-empty-row') ?? null;
        const fragment = document.createDocumentFragment();

        matrixRows.forEach(function (row) {
            fragment.appendChild(row);
        });

        if (anchorNode) {
            tbody.insertBefore(fragment, anchorNode);
            return;
        }

        tbody.appendChild(fragment);
    };

    const updateSortButtonLabel = function (button, appliedDirection) {
        if (!button) {
            return;
        }

        const baseLabel = button.getAttribute('data-sort-label') || 'Urutkan';
        const sortKey = button.getAttribute('data-sort-key') || '';
        const isDesc = appliedDirection === 'desc';
        const iconClass = sortKey === 'nim'
            ? (isDesc ? 'bi-sort-numeric-up' : 'bi-sort-numeric-down')
            : (isDesc ? 'bi-sort-alpha-up' : 'bi-sort-alpha-down');
        const directionText = isDesc ? 'turun' : 'naik';

        button.innerHTML = `<i class="bi ${iconClass}"></i> ${baseLabel} (${directionText})`;
    };

    document.querySelectorAll('.js-sort-tab').forEach(function (button) {
        updateSortButtonLabel(button, 'asc');
        button.setAttribute('data-next-direction', 'asc');
    });

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.js-sort-tab');
        if (!button) {
            return;
        }

        const pane = button.closest('.tab-pane');
        const sortKey = button.getAttribute('data-sort-key');
        const directionToApply = button.getAttribute('data-next-direction') === 'desc' ? 'desc' : 'asc';

        sortRowsInPaneByDirection(pane, sortKey, directionToApply);
        updateSortButtonLabel(button, directionToApply);
        button.setAttribute('data-next-direction', directionToApply === 'asc' ? 'desc' : 'asc');
    });

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
    background: var(--bs-light);
    z-index: 20;
}

.nilai-matrix-table .sticky-col {
    position: sticky;
    left: 0;
    background: var(--bs-white);
    z-index: 15;
    min-width: 240px;
}

.nilai-matrix-table thead .sticky-col {
    z-index: 25;
}
</style>
@endpush

@endsection
