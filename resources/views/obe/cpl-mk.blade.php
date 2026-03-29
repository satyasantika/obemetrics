@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            @include('components.kurikulum-flow-info',['kurikulum' => $kurikulum])
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum', ['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Interaksi CPL dan Mata Kuliah"
                    subtitle="Pemetaan kontribusi CPL pada setiap mata kuliah"
                    icon="bi bi-link-45deg"
                    />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_cpl_mks', 'return_url' => request()->fullUrl()]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm mt-1 float-end"><i class="bi bi-upload"></i> Import Interaksi CPL >< MK</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="table-responsive nilai-matrix-wrapper rounded-3 border bg-white shadow-sm">
                            <table class="table table-hover align-middle nilai-matrix-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sticky-col bg-white" rowspan="2" width="500">MATA KULIAH</th>
                                        @forelse ($cplHeaderGroups as $group)
                                            <th colspan="{{ $group['colspan'] }}" class="text-center">
                                                <a tabindex="0" class="btn btn-sm btn-outline-primary btn-block" role="button" data-toggle="popover" data-bs-toggle="popover" data-trigger="focus" data-bs-trigger="focus" title="{{ $group['cpl_kode'] }}" data-content="{{ $group['cpl_nama'] }}" data-bs-content="{{ $group['cpl_nama'] }}">{{ $group['cpl_kode'] }}</a>
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                    <tr>
                                        @forelse ($cplBkColumns as $column)
                                            <th class="small fw-normal text-center">
                                                <a tabindex="0" class="btn btn-sm btn-outline-secondary btn-block" role="button" data-toggle="popover" data-bs-toggle="popover" data-trigger="focus" data-bs-trigger="focus" title="{{ $column['bk_kode'] }}" data-content="{{ $column['bk_nama'] }}" data-bs-content="{{ $column['bk_nama'] }}">{{ $column['bk_kode'] }}</a>
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($mks as $mk)
                                    <tr style="vertical-align: text-top;">
                                        <th class="sticky-col">
                                            <a tabindex="0" class="btn btn-sm btn-outline-secondary text-start" role="button" data-toggle="popover" data-bs-toggle="popover" data-trigger="focus" data-bs-trigger="focus" title="{{ $mk->kode }} ({{ $mk->sks }} SKS)" data-content="{{ $mk->nama }}" data-bs-content="{{ $mk->nama }}">{{ $mk->nama }}</a>
                                            <br>
                                            @php
                                                $totalBobot = (float) ($mkTotalBobotMap[$mk->id] ?? 0);
                                            @endphp
                                            <span class="total-bobot-label text-{{ $totalBobot == 100 ? 'primary' : 'danger' }}">
                                                (Rekap: <span class="total-bobot-value">{{ $totalBobot }}</span>%)
                                            </span>
                                        </th>
                                        @forelse ($cplBkColumns as $column)
                                            @php
                                                $pairKey = ($column['join_cpl_bk_id'] ?? 'na') . '|' . $mk->id;
                                                $isAvailable = $availablePairMap->has($pairKey);
                                                $isLinked = $linkedPairMap->has($pairKey);
                                                $isLocked = $lockedPairMap->has($pairKey);
                                                $bobot = $bobotPairMap->get($pairKey);
                                            @endphp
                                            <td>
                                                @if ($column['type'] === 'placeholder')
                                                    <span class="text-muted">-</span>
                                                @elseif (!$isAvailable)
                                                    <span class="text-muted">-</span>
                                                @else
                                                    <form action="{{ route('kurikulums.joincplmks.update', ['kurikulum' => $kurikulum->id, 'cpl' => $column['cpl_id'], 'mk' => $mk->id]) }}" method="POST" data-is-locked="{{ $isLocked ? '1' : '0' }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                                        <input type="hidden" name="join_cpl_bk_id" value="{{ $column['join_cpl_bk_id'] }}">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <input
                                                                class="form-control form-control-sm bobot-input"
                                                                type="number"
                                                                name="bobot"
                                                                min="0"
                                                                max="100"
                                                                step="5"
                                                                placeholder="bobot %"
                                                                value="{{ $bobot !== null ? $bobot : '' }}"
                                                                title="{{ $column['cpl_kode'] }} - BK {{ $column['bk_kode'] }}"
                                                            >
                                                        </div>
                                                        <div class="mt-1 d-flex align-items-center justify-content-between gap-1">
                                                            <span class="badge {{ $isLinked ? 'bg-success' : 'bg-white text-dark' }} link-status-badge">
                                                                {{ $isLinked ? 'Terkait' : '' }}
                                                            </span>
                                                            @if (!$isLocked)
                                                            <button
                                                                type="button"
                                                                class="btn btn-outline-danger rounded-pill btn-sm py-0 px-2 clear-bobot-btn {{ $isLinked ? '' : 'd-none' }}"
                                                                title="Hapus relasi CPL-BK-MK"
                                                                aria-label="Hapus relasi"
                                                            >
                                                                <i class="bi bi-x-lg"></i>
                                                            </button>
                                                            @endif
                                                        </div>
                                                        <span class="save-status small text-muted"></span>
                                                    </form>
                                                @endif
                                                @if ($isLocked)
                                                    <div class="mt-1">
                                                        <span class="badge bg-secondary" title="Dikunci" aria-label="Dikunci"><i class="bi bi-lock-fill"></i></span>
                                                    </div>
                                                @endif
                                            </td>
                                        @empty
                                            <td></td>
                                        @endforelse
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ 1 + $cplBkColumns->count() }}"><span class="bg-warning text-dark p-2">
                                            Belum ada data Mata Kuliah pada kurikulum ini.</span>
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
    if (window.bootstrap && typeof window.bootstrap.Popover === 'function') {
        document.querySelectorAll('[data-toggle="popover"]').forEach(function (el) {
            if (!el.getAttribute('data-bs-content') && el.getAttribute('data-content')) {
                el.setAttribute('data-bs-content', el.getAttribute('data-content'));
            }
            if (!el.getAttribute('data-bs-trigger') && el.getAttribute('data-trigger')) {
                el.setAttribute('data-bs-trigger', el.getAttribute('data-trigger'));
            }
            window.bootstrap.Popover.getOrCreateInstance(el);
        });
    }

    const forms = document.querySelectorAll('form[action*="joincplmks"]');

    forms.forEach(function (form) {
        const input = form.querySelector('input[name="bobot"]');
        const statusEl = form.querySelector('.save-status');
        const badge = form.querySelector('.link-status-badge');
        const clearBtn = form.querySelector('.clear-bobot-btn');
        const isLocked = form.getAttribute('data-is-locked') === '1';
        let lastSavedValue = (input.value || '').trim();
        let isSubmitting = false;

        if (!input) {
            return;
        }

        const setStatus = function (text, tone) {
            if (!statusEl) {
                return;
            }

            statusEl.textContent = text;
            statusEl.className = 'save-status small text-' + tone;

            if (tone === 'success') {
                setTimeout(function () {
                    statusEl.textContent = '';
                    statusEl.className = 'save-status small text-muted';
                }, 1200);
            }
        };

        const submitLive = function () {
            if (isSubmitting || input.disabled) {
                return;
            }

            const rawValue = (input.value || '').trim();
            if (rawValue !== '') {
                const numericValue = Number(rawValue);
                if (!Number.isFinite(numericValue) || numericValue < 0 || numericValue > 100) {
                    setStatus('Gagal menyimpan: bobot harus 0–100.', 'danger');
                    return;
                }
            }

            if (isLocked && rawValue === '') {
                input.value = lastSavedValue;
                setStatus('Gagal menyimpan: bobot tidak boleh kosong saat status dikunci.', 'danger');
                updateMkTotal(form.closest('tr'));
                return;
            }

            isSubmitting = true;

            setStatus('Menyimpan...', 'muted');

            if (clearBtn) {
                clearBtn.disabled = true;
            }

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
                    return response.text().then(function (bodyText) {
                        let message = 'Gagal menyimpan';

                        if (bodyText) {
                            try {
                                const payload = JSON.parse(bodyText);
                                message = payload?.message || message;
                            } catch (_) {
                                message = message;
                            }
                        }

                        throw new Error(message);
                    });
                }

                return response.json();
            })
            .then(function (result) {
                setStatus('Tersimpan', 'success');

                if (badge) {
                    const linked = !!result.linked;
                    badge.textContent = linked ? 'Terkait' : '';
                    badge.className = 'badge ' + (linked ? 'bg-success' : 'bg-white text-dark') + ' link-status-badge';

                    if (clearBtn) {
                        clearBtn.classList.toggle('d-none', !linked);
                    }
                }

                if (!result.linked) {
                    input.value = '';
                    lastSavedValue = '';
                } else if (typeof result.bobot !== 'undefined' && result.bobot !== null) {
                    input.value = result.bobot;
                    lastSavedValue = String(result.bobot);
                }

                updateMkTotal(form.closest('tr'));
            })
            .catch(function (error) {
                input.value = lastSavedValue;
                const rawMessage = String(error?.message || 'Terjadi kesalahan').trim();
                const formattedMessage = /^gagal menyimpan\s*:/i.test(rawMessage)
                    ? rawMessage
                    : ('Gagal menyimpan: ' + rawMessage);
                setStatus(formattedMessage, 'danger');
                updateMkTotal(form.closest('tr'));
            })
            .finally(function () {
                isSubmitting = false;
                if (clearBtn) {
                    clearBtn.disabled = false;
                }
            });
        };

        const updateMkTotal = function (row) {
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

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submitLive();
        });

        input.addEventListener('change', submitLive);
        input.addEventListener('input', function () {
            updateMkTotal(form.closest('tr'));
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (isLocked) {
                    setStatus('Gagal menyimpan: bobot tidak boleh kosong saat status dikunci.', 'danger');
                    return;
                }

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
.nilai-matrix-wrapper {
    max-height: 70vh;
    overflow: auto;
}

.nilai-matrix-table thead tr:first-child th {
    position: sticky;
    top: 0;
    background: var(--bs-light);
    z-index: 30;
}

.nilai-matrix-table thead tr:nth-child(2) th {
    position: sticky;
    top: 56px;
    background: var(--bs-light);
    z-index: 29;
}

.nilai-matrix-table .sticky-col {
    position: sticky;
    left: 0;
    background: var(--bs-white);
    z-index: 21;
    /* min-width: 280px; */
}

.nilai-matrix-table thead .sticky-col {
    z-index: 35;
    background: var(--bs-light);
}
</style>
@endpush


@endsection
