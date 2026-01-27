@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Interaksi Profil Lulusan dan CPL</strong>
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

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>CPL</th>
                                        @forelse ($profils as $profil)
                                            <th>{{ $profil->nama }}</th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($cpls as $cpl)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <strong>{{ $cpl->kode }}</strong>
                                            <br>
                                            {{ $cpl->nama }}
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
                                                    $linkedProfilCpl = \App\Models\JoinProfilCpl::where('kurikulum_id',$kurikulum->id)->get();
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            id="is_linked_{{ $profil->id }}_{{ $cpl->id }}"
                                                            onchange="this.form.submit()"
                                                            {{ $linkedProfilCpl->contains(
                                                                function($item) use ($profil, $cpl) {
                                                                return $item->profil_id === $profil->id && $item->cpl_id === $cpl->id;
                                                                }) ? 'checked' : '' }}
                                                        >
                                                        <label class="form-check-label" for="is_linked_{{ $profil->id }}_{{ $cpl->id }}">
                                                            <span class="badge text-success" style="display: {{ $linkedProfilCpl->contains(function($item) use ($profil, $cpl) { return $item->profil_id === $profil->id && $item->cpl_id === $cpl->id; }) ? 'inline' : 'none' }};"><i class="bi bi-check-circle-fill"></i></span>
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
