@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <x-obe.menu-strip minWidth="960px">
                {{-- menu mata kuliah --}}
                @include('components.menu-mk',$mk)
            </x-obe.menu-strip>
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <div class="card">
                <x-obe.header
                    title="Interaksi CPL dan CPMK"
                    subtitle="Pemetaan hubungan CPL terhadap CPMK"
                    icon="bi bi-bezier2"
                    :backUrl="route('home')" />
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('setting.import.mk-master', ['mk' => $mk->id, 'target' => 'join_cpl_cpmks', 'return_url' => request()->fullUrl()]) }}" class="btn btn-sm btn-success mt-1 float-end"><i class="bi bi-upload"></i> Import Interaksi CPL-CPMK</a>
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
                                @php
                                    $lockedCplCpmkPair = \App\Models\JoinCplCpmk::query()
                                        ->where('mk_id', $mk->id)
                                        ->whereHas('subcpmks')
                                        ->get()
                                        ->mapWithKeys(fn ($row) => [($row->join_cpl_bk_id.'|'.$row->cpmk_id) => true]);
                                @endphp
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
                                                    <input type="hidden" name="cpmk_id" value="{{ $cpmk->id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    @php
                                                    $linkedCplCpmks = \App\Models\JoinCplCpmk::where('mk_id',$mk->id)->get();
                                                    $cek = $linkedCplCpmks->contains(
                                                        function($item) use ($joincplbk, $cpmk) {
                                                            return $item->join_cpl_bk_id === $joincplbk->id && $item->cpmk_id === $cpmk->id;
                                                        });
                                                    $isLocked = $lockedCplCpmkPair->has($joincplbk->id.'|'.$cpmk->id);
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
