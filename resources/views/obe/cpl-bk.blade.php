@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Interaksi CPL dan Bahan Kajian</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas kurikulum --}}
                    @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])
                    <hr>
                    {{-- menu kurikulum --}}
                    @include('components.menu-kurikulum',['kurikulum' => $kurikulum])
                    <hr>
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
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            id="is_linked_{{ $cpl->id }}_{{ $bk->id }}"
                                                            onchange="this.form.submit()"
                                                            @checked($cek)
                                                        >
                                                    </div>
                                                </form>
                                                <span class="badge text-success" style="display: {{ $cek ? 'inline' : 'none' }};">{{ $cek ? $bk->kode : '' }}</span>
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
