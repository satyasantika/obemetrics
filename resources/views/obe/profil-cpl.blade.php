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
                    title="Interaksi Profil Lulusan dan CPL"
                    subtitle="Pemetaan profil lulusan terhadap CPL"
                    icon="bi bi-diagram-2-fill"
                    />
                <div class="card-body">
                    {{-- @include('layouts.alert') --}}
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_profil_cpls', 'return_url' => request()->fullUrl()]) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm float-end me-1"><i class="bi bi-upload"></i> Import Interaksi Profil >< CPL</a>
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
                            <table class="table table-hover align-middle profil-cpl-matrix mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="2" class="sticky-col text-uppercase small text-muted fw-semibold">CPL</th>
                                        <th colspan="{{ max(1, $profils->count()) }}" class="text-center text-uppercase small text-muted fw-semibold">PROFIL LULUSAN</th>
                                    </tr>
                                    <tr>
                                        @forelse ($profils as $profil)
                                            <th class="small fw-normal" style="min-width: 240px;">
                                                <strong class="d-block">{{ $profil->nama }}</strong>
                                                <small class="text-muted">{{ $profil->deskripsi }}</small>
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
                                            <strong>{{ $cpl->kode }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $cpl->nama }}</small>
                                        </td>
                                        @forelse ($profils as $profil)
                                            <td>
                                                <form action="{{ route('joinprofilcpls.update',[$profil->id,$cpl->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="profil_id" value="{{ $profil->id }}">
                                                    <input type="hidden" name="cpl_id" value="{{ $cpl->id }}">
                                                    <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                                    @php
                                                        $cek = isset($linkedPairMap[$profil->id . '|' . $cpl->id]);
                                                    @endphp
                                                    <div class="form-check form-switch d-flex align-items-center gap-2 mb-0">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            id="is_linked_{{ $profil->id }}_{{ $cpl->id }}"
                                                            onchange="this.form.submit()"
                                                            @checked($cek)
                                                        >
                                                        <label class="form-check-label mb-0" for="is_linked_{{ $profil->id }}_{{ $cpl->id }}">
                                                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle {{ $cek ? '' : 'd-none' }}">
                                                                <i class="bi bi-check-circle-fill"></i>
                                                            </span>
                                                        </label>
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
    min-width: 240px;
}

.profil-cpl-matrix thead .sticky-col {
    background: var(--bs-light);
    z-index: 26;
}
</style>
@endpush

@endsection
