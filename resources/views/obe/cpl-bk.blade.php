@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-11">
            <x-obe.menu-strip minWidth="800px">
                {{-- menu kurikulum --}}
                @include('components.menu-kurikulum',['kurikulum' => $kurikulum])
            </x-obe.menu-strip>
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Interaksi CPL dan Bahan Kajian"
                    subtitle="Pemetaan hubungan CPL terhadap bahan kajian"
                    icon="bi bi-diagram-3-fill"
                    :backUrl="route('home')" />
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_cpl_bks', 'return_url' => request()->fullUrl()]) }}" class="btn btn-success btn-sm float-end me-1"><i class="bi bi-upload"></i> Import Interaksi CPL >< BK</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th rowspan="2">CAPAIAN PEMBELAJARAN LULUSAN</th>
                                        <th colspan="{{ $bks->count()+1 }}">BAHAN KAJIAN</th>
                                    <tr>
                                        @forelse ($bks as $bk)
                                            <td>
                                                <strong data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $bk->nama }}">
                                                    {{ $bk->kode }}
                                                </strong><br>
                                                <small>{{ $bk->nama }}</small>
                                            </td>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @php
                                    $lockedCplBkPair = \App\Models\JoinCplBk::query()
                                        ->where('kurikulum_id', $kurikulum->id)
                                        ->whereHas('joinCplCpmks')
                                        ->get()
                                        ->mapWithKeys(fn ($row) => [($row->cpl_id.'|'.$row->bk_id) => true]);
                                @endphp
                                @forelse ($cpls as $cpl)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <strong data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $cpl->nama }}">
                                                {{ $cpl->kode }}
                                            </strong><br>
                                            <small>{{ $cpl->nama }}</small>
                                        </td>
                                        @forelse ($bks as $bk)
                                            <td>
                                                <form action="{{ route('joincplbks.update',[$cpl->id,$bk->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="cpl_id" value="{{ $cpl->id }}">
                                                    <input type="hidden" name="bk_id" value="{{ $bk->id }}">
                                                    <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                                    @php
                                                    $linkedCplBk = \App\Models\JoinCplBk::where('kurikulum_id',$kurikulum->id)->get();
                                                    $cek = $linkedCplBk->contains(
                                                        function($item) use ($cpl, $bk) {
                                                        return $item->cpl_id === $cpl->id && $item->bk_id === $bk->id;
                                                        });
                                                    $isLocked = $lockedCplBkPair->has($cpl->id.'|'.$bk->id);
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            id="is_linked_{{ $cpl->id }}_{{ $bk->id }}"
                                                            onchange="this.form.submit()"
                                                            @checked($cek)
                                                            @disabled($isLocked)
                                                        >
                                                    </div>
                                                </form>
                                                <span class="badge text-success" style="display: {{ $cek ? 'inline' : 'none' }};">{{ $cek ? $bk->kode : '' }}</span>
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
                                        <td colspan="{{ 2+$bks->count() }}"><span class="bg-warning text-dark p-2">
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


@endsection
