@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
                    <div class="row">
                        <div class="col-md-3">Nama Kurikulum</div>
                        <div class="col"><strong>{{ $kurikulum->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Program Studi</div>
                        <div class="col"><strong>{{ $kurikulum->prodi->jenjang }} {{ $kurikulum->prodi->nama }}</strong></div>
                    </div>
                    <hr>
                    @include('layouts.menu-kurikulum',$kurikulum)
                    <hr>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        @forelse ($bks as $bk)
                                            <th>
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $bk->nama }}">
                                                    {{ $bk->kode }}
                                                </span>
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($cpls as $cpl)
                                    <tr style="vertical-align: text-top;">
                                        <th>
                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $cpl->nama }}">
                                                {{ $cpl->kode }}
                                            </span>
                                        </th>
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
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
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
