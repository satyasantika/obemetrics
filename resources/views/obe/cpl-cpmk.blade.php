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
                            <a href="{{ route('setting.import.mk-master', ['mk' => $mk->id, 'target' => 'join_cpl_cpmks', 'return_url' => request()->fullUrl()]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm mt-1 float-end"><i class="bi bi-upload"></i> Import Interaksi CPL-CPMK</a>
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
                                                <form action="{{ route('joincplcpmks.update',[$joincplbk->id,$cpmk->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="cpmk_id" value="{{ $cpmk->id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    @php
                                                    $pairKey = $joincplbk->id.'|'.$cpmk->id;
                                                    $cek = isset($linkedPairMap[$pairKey]);
                                                    $isLocked = isset($lockedPairMap[$pairKey]);
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            id="is_linked_{{ $joincplbk->id }}_{{ $cpmk->id }}"
                                                            onchange="this.form.submit()"
                                                            @checked($cek)
                                                            @disabled($isLocked)
                                                        >
                                                    </div>
                                                </form>
                                                <span class="badge text-success" style="display: {{ $cek ? 'inline' : 'none' }};">{{ $cek ? $joincplbk->cpl->kode : '' }}</span>
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


@endsection
