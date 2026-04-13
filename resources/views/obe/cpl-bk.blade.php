@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Interaksi CPL dan Bahan Kajian"
                    subtitle="Pemetaan hubungan CPL terhadap bahan kajian"
                    icon="bi bi-diagram-3-fill"
                    />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'cpl_bks', 'return_url' => request()->fullUrl()]) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm float-end me-1"><i class="bi bi-upload"></i> Import Interaksi CPL >< BK</a>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <div class="lock-hint-banner">
                                <div class="lock-hint-icon" aria-hidden="true">
                                    <i class="bi bi-lock-fill"></i>
                                </div>
                                <div class="lock-hint-copy">
                                    <div class="lock-hint-title">Interaksi tertentu sedang dikunci</div>
                                    <div class="lock-hint-text">
                                        Jika ingin mengubah interaksi yang dikunci, hapus terlebih dahulu bobot CPL pada MK di halaman Interaksi CPL >< MK.
                                    </div>
                                </div>
                                <a href="{{ route('kurikulums.cplmks.index', [$kurikulum->id]) }}" class="btn btn-sm lock-hint-link rounded-pill px-3 fw-semibold">
                                    <i class="bi bi-arrow-up-right-circle"></i> Buka Interaksi CPL >< MK
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="table-responsive nilai-matrix-wrapper rounded-3 border bg-white shadow-sm">
                                <table class="table table-hover align-middle nilai-matrix-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="sticky-col" rowspan="2">CAPAIAN PEMBELAJARAN LULUSAN</th>
                                            <th colspan="{{ max(1, $bks->count()) }}" class="text-center">BAHAN KAJIAN</th>
                                        </tr>
                                        <tr>
                                            @forelse ($bks as $bk)
                                                <th class="text-center">
                                                    <div class="d-flex flex-column align-items-center gap-1"
                                                         data-bs-toggle="popover"
                                                         data-bs-trigger="hover focus"
                                                         data-bs-placement="top"
                                                         data-bs-title="{{ $bk->kode }}"
                                                         data-bs-content="{{ $bk->nama }}">
                                                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis fw-bold" style="font-size: 0.8rem;">{{ $bk->kode }}</span>
                                                    </div>
                                                </th>
                                            @empty
                                                <th></th>
                                            @endforelse
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($cpls as $cpl)
                                        <tr class="matriks-row" style="vertical-align: text-top;">
                                            <td class="sticky-col">
                                                <div class="d-flex flex-column align-items-center gap-1"
                                                     data-bs-toggle="popover"
                                                     data-bs-trigger="hover focus"
                                                     data-bs-placement="right"
                                                     data-bs-title="{{ $cpl->kode }}"
                                                     data-bs-content="{{ $cpl->nama }}">
                                                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis fw-bold" style="font-size: 0.8rem;">{{ $cpl->kode }}</span>
                                                </div>
                                            </td>
                                            @forelse ($bks as $bk)
                                                @php
                                                    $pairKey = $cpl->id.'|'.$bk->id;
                                                    $cek = isset($linkedPairMap[$pairKey]);
                                                    $isLocked = isset($lockedPairMap[$pairKey]);
                                                @endphp
                                                <td>
                                                    <form action="{{ route('kurikulums.cplbks.update',[$kurikulum->id,$cpl->id,$bk->id]) }}" method="POST" class="live-cplbk-form" data-is-locked="{{ $isLocked ? '1' : '0' }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="cpl_id" value="{{ $cpl->id }}">
                                                        <input type="hidden" name="bk_id" value="{{ $bk->id }}">
                                                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                                        <div class="form-check form-switch d-flex align-items-center justify-content-center gap-2 mb-0">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                name="is_linked"
                                                                onchange="this.form.requestSubmit()"
                                                                @checked($cek)
                                                                @disabled($isLocked)
                                                            >
                                                        </div>
                                                        <span class="save-status small text-muted"></span>
                                                    </form>
                                                    @if ($isLocked)
                                                        <div class="d-flex justify-content-center mt-1">
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
                                            <td colspan="{{ max(2, 1 + $bks->count()) }}">
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle p-2">
                                                    Belum ada data CPL untuk kurikulum ini.
                                                </span>
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

    const forms = document.querySelectorAll('.live-cplbk-form');

    forms.forEach(function (form) {
        const checkbox = form.querySelector('input[name="is_linked"]');
        const statusEl = form.querySelector('.save-status');
        const badge = form.closest('td')?.querySelector('.link-status-badge');
        const isLocked = form.getAttribute('data-is-locked') === '1';

        if (!checkbox) {
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

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (checkbox.disabled) {
                return;
            }

            const previousValue = !checkbox.checked;
            const formData = new FormData(form);
            checkbox.disabled = true;
            setStatus('menyimpan...', 'muted');

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
                        throw new Error(payload?.message || 'Gagal menyimpan');
                    }).catch(function () {
                        throw new Error('Gagal menyimpan');
                    });
                }

                return response.json();
            })
            .then(function (result) {
                setStatus('tersimpan', 'success');

                if (badge) {
                    badge.classList.toggle('d-none', !result.linked);
                }
            })
            .catch(function (error) {
                checkbox.checked = previousValue;
                const message = String(error?.message || 'Gagal menyimpan');
                setStatus(message, 'danger');

                if (isLocked) {
                    checkbox.checked = true;
                }
            })
            .finally(function () {
                checkbox.disabled = isLocked;
            });
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.lock-hint-banner {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border: 1px solid rgba(38, 84, 124, 0.14);
    border-radius: 1rem;
    background:
        radial-gradient(circle at top left, rgba(255, 255, 255, 0.9), transparent 32%),
        linear-gradient(135deg, rgba(235, 244, 252, 0.96), rgba(248, 250, 252, 0.98));
    box-shadow: 0 14px 32px rgba(25, 58, 94, 0.08);
}

.lock-hint-icon {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: linear-gradient(135deg, #17324d, #35648f);
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.lock-hint-icon i {
    font-size: 1rem;
}

.lock-hint-copy {
    min-width: 0;
    flex: 1 1 auto;
}

.lock-hint-title {
    margin-bottom: 0.2rem;
    color: #17324d;
    font-size: 0.96rem;
    font-weight: 700;
    letter-spacing: 0.01em;
}

.lock-hint-text {
    color: #4a6178;
    font-size: 0.9rem;
    line-height: 1.55;
}

.lock-hint-link {
    flex: 0 0 auto;
    border: 1px solid rgba(23, 50, 77, 0.12);
    background: rgba(255, 255, 255, 0.82);
    color: #17324d;
    box-shadow: 0 8px 20px rgba(23, 50, 77, 0.08);
}

.lock-hint-link:hover,
.lock-hint-link:focus {
    background: #17324d;
    border-color: #17324d;
    color: #fff;
}

.nilai-matrix-wrapper {
    max-height: 70vh;
    overflow: auto;
}

.nilai-matrix-table thead tr:first-child th {
    position: sticky;
    top: 0;
    background: var(--bs-light);
    z-index: 20;
}

.nilai-matrix-table thead tr:nth-child(2) th {
    position: sticky;
    top: 41px;
    background: var(--bs-light);
    z-index: 19;
}

.nilai-matrix-table .sticky-col {
    position: sticky;
    left: 0;
    background: var(--bs-white);
    z-index: 15;
    width: 1%;
}

.nilai-matrix-table thead .sticky-col {
    z-index: 25;
}

.nilai-matrix-table tbody td:not(.sticky-col) {
    background: var(--bs-white);
}

@media (max-width: 767.98px) {
    .lock-hint-banner {
        align-items: flex-start;
        flex-direction: column;
    }

    .lock-hint-link {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush


@endsection
