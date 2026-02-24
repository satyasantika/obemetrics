@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Interaksi CPL dan Mata kuliah</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas mata kuliah --}}
                    @include('components.identitas-kurikulum', ['kurikulum' => $kurikulum])
                    <hr>
                    {{-- menu mata kuliah --}}
                    @include('components.menu-kurikulum', ['kurikulum' => $kurikulum])
                    <hr>
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_cpl_mks', 'return_url' => request()->fullUrl()]) }}" class="btn btn-sm btn-success mt-1 float-end"><i class="bi bi-upload"></i> Import Interaksi CPL >< MK</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th rowspan="2">MATA KULIAH</th>
                                        @forelse ($cplHeaderGroups as $group)
                                            <th colspan="{{ $group['colspan'] }}" class="text-center">
                                                <strong data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $group['cpl_nama'] }}">
                                                    {{ $group['cpl_kode'] }}
                                                </strong>
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                    <tr>
                                        @forelse ($cplBkColumns as $column)
                                            <th class="small fw-normal text-center">
                                                <strong data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $column['bk_nama'] }}">
                                                {{ $column['bk_kode'] }}
                                                </strong>
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($mks as $mk)
                                    <tr style="vertical-align: text-top;">
                                        <th>
                                            <span class="text-secondary fw-lighter">{{ $mk->kode }}</span><br>
                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $mk->nama }}">
                                                {{ $mk->nama }}
                                            </span>
                                            <br>
                                            @php
                                                $totalBobot = (float) ($mkTotalBobotMap[$mk->id] ?? 0);
                                            @endphp
                                            <span class="total-bobot-label text-{{ $totalBobot == 100 ? 'primary' : 'danger' }}">
                                                (Rekap Bobot: <span class="total-bobot-value">{{ $totalBobot }}</span>%)
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
                                                    <form action="{{ route('joincplmks.update', ['cpl' => $column['cpl_id'], 'mk' => $mk->id]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                                        <input type="hidden" name="join_cpl_bk_id" value="{{ $column['join_cpl_bk_id'] }}">
                                                        <div class="mb-1 d-flex align-items-center justify-content-between gap-1">
                                                            <span class="badge {{ $isLinked ? 'bg-success' : 'bg-white text-dark' }} link-status-badge">
                                                                {{ $isLinked ? 'Terkait' : 'x' }}
                                                            </span>
                                                            <button
                                                                type="button"
                                                                class="btn btn-outline-danger btn-sm py-0 px-2 clear-bobot-btn {{ $isLinked ? '' : 'd-none' }}"
                                                                title="Hapus relasi CPL-BK-MK"
                                                                aria-label="Hapus relasi"
                                                                @disabled($isLocked)
                                                            >
                                                                <i class="bi bi-x-lg"></i>
                                                            </button>
                                                        </div>
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
                                                                @disabled($isLocked)
                                                            >
                                                            <span class="save-status small text-muted"></span>
                                                        </div>
                                                    </form>
                                                @endif
                                                @if ($isLocked)
                                                    <div class="mt-1">
                                                        <span class="badge bg-secondary">terkunci</span>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form[action*="joincplmks"]');

    forms.forEach(function (form) {
        const input = form.querySelector('input[name="bobot"]');
        const statusEl = form.querySelector('.save-status');
        const badge = form.querySelector('.link-status-badge');
        const clearBtn = form.querySelector('.clear-bobot-btn');
        let isSubmitting = false;

        if (!input) {
            return;
        }

        const submitLive = function () {
            if (isSubmitting || input.disabled) {
                return;
            }

            isSubmitting = true;

            if (statusEl) {
                statusEl.textContent = 'menyimpan...';
                statusEl.className = 'save-status small text-muted';
            }

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
                    badge.textContent = linked ? 'Terkait' : 'x';
                    badge.className = 'badge ' + (linked ? 'bg-success' : 'bg-white text-dark') + ' link-status-badge';

                    if (clearBtn) {
                        clearBtn.classList.toggle('d-none', !linked);
                    }
                }

                if (!result.linked) {
                    input.value = '';
                } else if (typeof result.bobot !== 'undefined' && result.bobot !== null) {
                    input.value = result.bobot;
                }

                updateMkTotal(form.closest('tr'));
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
                input.value = '';
                submitLive();
            });
        }
    });
});
</script>
@endpush


@endsection
