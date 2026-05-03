@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Interaksi Profil Lulusan dan CPL"
                    subtitle="Pemetaan profil lulusan terhadap CPL"
                    icon="bi bi-diagram-2-fill"
                    />
                <div class="card-body">
                    {{-- @include('layouts.alert') --}}
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_profil_cpls', 'return_url' => request()->fullUrl()]) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm float-end me-1"><i class="bi bi-upload"></i> Import Interaksi Profil >< CPL</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            @php
                                $linkedPairMap = \App\Models\JoinProfilCpl::where('kurikulum_id', $kurikulum->id)
                                    ->get(['profil_id', 'cpl_id'])
                                    ->mapWithKeys(function ($item) {
                                        return [$item->profil_id . '|' . $item->cpl_id => true];
                                    })
                                    ->all();
                            @endphp
                            <div class="table-responsive profil-cpl-matrix-wrapper rounded-3 border bg-white shadow-sm">
                            <table class="table table-hover align-middle profil-cpl-matrix mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sticky-col" rowspan="2">CAPAIAN PEMBELAJARAN LULUSAN</th>
                                        <th colspan="{{ max(1, $profils->count()) }}" class="text-center">PROFIL LULUSAN</th>
                                    </tr>
                                    <tr>
                                        @forelse ($profils as $profil)
                                            <th class="text-center">
                                                <div class="d-flex flex-column align-items-center gap-1"
                                                     data-bs-toggle="popover"
                                                     data-bs-trigger="hover focus"
                                                     data-bs-placement="top"
                                                     data-bs-title="{{ $profil->nama }}"
                                                     data-bs-content="{{ $profil->deskripsi ?: $profil->nama }}">
                                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis fw-bold" style="font-size: 0.8rem;">{{ $profil->nama }}</span>
                                                </div>
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($cpls as $cpl)
                                    <tr style="vertical-align: text-top;">
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
                                        @forelse ($profils as $profil)
                                            <td>
                                                <form action="{{ route('kurikulums.joinprofilcpls.update',[$kurikulum->id,$profil->id,$cpl->id]) }}" method="POST" class="live-profilcpl-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="profil_id" value="{{ $profil->id }}">
                                                    <input type="hidden" name="cpl_id" value="{{ $cpl->id }}">
                                                    <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                                    @php
                                                        $cek = isset($linkedPairMap[$profil->id . '|' . $cpl->id]);
                                                    @endphp
                                                    <div class="d-flex flex-column align-items-center justify-content-center gap-1 mb-0">
                                                        <div class="form-check form-switch mb-0">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                name="is_linked"
                                                                id="is_linked_{{ $profil->id }}_{{ $cpl->id }}"
                                                                onchange="this.form.requestSubmit()"
                                                                @checked($cek)
                                                            >
                                                        </div>
                                                        <span class="save-status small text-muted"></span>
                                                    </div>
                                                </form>
                                            </td>
                                        @empty
                                            <td></td>
                                        @endforelse
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ 1+$profils->count() }}"><span class="bg-warning text-dark p-2">
                                            Belum ada data CPL untuk kurikulum ini.</span>
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

    const forms = document.querySelectorAll('.live-profilcpl-form');

    forms.forEach(function (form) {
        const checkbox = form.querySelector('input[name="is_linked"]');
        const statusEl = form.querySelector('.save-status');
        const badge = form.querySelector('.link-status-badge');

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
            })
            .finally(function () {
                checkbox.disabled = false;
            });
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.profil-cpl-matrix-wrapper {
    max-height: 70vh;
    overflow: auto;
}

.profil-cpl-matrix thead tr:first-child th {
    position: sticky;
    top: 0;
    z-index: 25;
}

.profil-cpl-matrix thead tr:nth-child(2) th {
    position: sticky;
    top: 41px;
    z-index: 24;
}

.profil-cpl-matrix .sticky-col {
    position: sticky;
    left: 0;
    background: var(--bs-white);
    z-index: 23;
    width: 1%;
}

.profil-cpl-matrix thead .sticky-col {
    background: var(--bs-light);
    z-index: 26;
}

.profil-cpl-matrix tbody td:not(.sticky-col) {
    background: var(--bs-white);
}
</style>
@endpush

@endsection
