@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            @include('components.kurikulum-flow-info',['kurikulum' => $kurikulum])
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
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_cpl_bks', 'return_url' => request()->fullUrl()]) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm float-end me-1"><i class="bi bi-upload"></i> Import Interaksi CPL >< BK</a>
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
                                                <th>
                                                    <a tabindex="0" class="btn btn-sm btn-outline-secondary" role="button" data-toggle="popover"  data-bs-toggle="popover" data-trigger="focus" data-bs-trigger="focus" title="{{ $bk->kode }}" data-content="{{ $bk->nama }}" data-bs-content="{{ $bk->nama }}">{{ $bk->kode }}</a><br>
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
                                                <a tabindex="0" class="btn btn-sm btn-outline-secondary" role="button" data-toggle="popover" data-bs-toggle="popover" data-trigger="focus" data-bs-trigger="focus" title="{{ $cpl->kode }}" data-content="{{ $cpl->nama }}" data-bs-content="{{ $cpl->nama }}">{{ $cpl->kode }}</a><br>
                                            </td>
                                            @forelse ($bks as $bk)
                                                @php
                                                    $pairKey = $cpl->id.'|'.$bk->id;
                                                    $cek = isset($linkedPairMap[$pairKey]);
                                                    $isLocked = isset($lockedPairMap[$pairKey]);
                                                @endphp
                                                <td>
                                                    <form action="{{ route('kurikulums.joincplbks.update',[$kurikulum->id,$cpl->id,$bk->id]) }}" method="POST" class="live-cplbk-form" data-is-locked="{{ $isLocked ? '1' : '0' }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="cpl_id" value="{{ $cpl->id }}">
                                                        <input type="hidden" name="bk_id" value="{{ $bk->id }}">
                                                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="form-check form-switch mb-0">
                                                                <input
                                                                    class="form-check-input"
                                                                    type="checkbox"
                                                                    name="is_linked"
                                                                    onchange="this.form.requestSubmit()"
                                                                    @checked($cek)
                                                                    @disabled($isLocked)
                                                                >
                                                            </div>
                                                        </div>
                                                        <span class="save-status small text-muted"></span>
                                                    </form>
                                                    <div class="mt-1 d-flex align-items-center gap-1 flex-wrap">
                                                        {{-- <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle link-status-badge {{ $cek ? '' : 'd-none' }}">{{ $bk->kode }}</span> --}}
                                                        @if ($isLocked)
                                                            <span class="badge bg-secondary">terkunci</span>
                                                        @endif
                                                    </div>
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
    min-width: 260px;
}

.nilai-matrix-table thead .sticky-col {
    z-index: 25;
}
</style>
@endpush


@endsection
