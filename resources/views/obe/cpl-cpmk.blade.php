@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            @include('components.mk-flow-info', ['mk' => $mk])
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <div class="card">
                <x-obe.header
                    title="Interaksi CPL dan CPMK"
                    subtitle="Pemetaan hubungan CPL terhadap CPMK"
                    icon="bi bi-bezier2" />
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('settings.import.mk-master', ['mk' => $mk->id, 'target' => 'join_cpl_cpmks', 'return_url' => request()->fullUrl()]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm mt-1 float-end"><i class="bi bi-upload"></i> Import Interaksi CPL-CPMK</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        @forelse ($joincplbks as $joincplbk)
                                            <th>
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $joincplbk->cpl->nama }}">
                                                    {{ $joincplbk->cpl->kode }}
                                                </span>
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($cpmks as $cpmk)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            {{ $cpmk->kode }}
                                            <br>{{ $cpmk->nama }}
                                        </td>
                                        @forelse ($joincplbks as $joincplbk)
                                            <td>
                                                @php
                                                $pairKey = $joincplbk->id.'|'.$cpmk->id;
                                                $cek = isset($linkedPairMap[$pairKey]);
                                                $isLocked = isset($lockedPairMap[$pairKey]);
                                                @endphp
                                                <form action="{{ route('mks.joincplcpmks.update', ['mk' => $mk->id, 'joincplbk' => $joincplbk->id, 'cpmk' => $cpmk->id]) }}" method="POST" class="live-cplcpmk-form" data-is-locked="{{ $isLocked ? '1' : '0' }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="cpmk_id" value="{{ $cpmk->id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="form-check form-switch mb-0">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            id="is_linked_{{ $joincplbk->id }}_{{ $cpmk->id }}"
                                                            onchange="this.form.requestSubmit()"
                                                            @checked($cek)
                                                            @disabled($isLocked)
                                                        >
                                                        </div>
                                                        <span class="save-status small text-muted"></span>
                                                    </div>
                                                </form>
                                                <div class="mt-1">
                                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle link-status-badge {{ $cek ? '' : 'd-none' }}">{{ $joincplbk->cpl->kode }}</span>
                                                </div>
                                                @if ($isLocked)
                                                    <span class="badge bg-secondary">terkunci</span>
                                                @endif
                                            </td>
                                        @empty
                                            <td></td>
                                        @endforelse
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ $joincplbks->count()+1 }}"><span class="bg-warning text-dark p-2">
                                            Belum ada data CPMK untuk Mata Kuliah ini.</span>
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
    const forms = document.querySelectorAll('.live-cplcpmk-form');

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
                setStatus(String(error?.message || 'Gagal menyimpan'), 'danger');

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


@endsection
