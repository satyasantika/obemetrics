@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <x-obe.menu-strip minWidth="800px">
                {{-- menu kurikulum --}}
                @include('components.menu-kurikulum',['kurikulum' => $kurikulum])
            </x-obe.menu-strip>
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Interaksi Profil Lulusan dan CPL"
                    subtitle="Pemetaan profil lulusan terhadap CPL"
                    icon="bi bi-diagram-2-fill"
                    :backUrl="route('home')" />
                <div class="card-body">
                    {{-- @include('layouts.alert') --}}
                    <div class="row mb-2">
                        <div class="col">
                            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_profil_cpls', 'return_url' => request()->fullUrl()]) }}" class="btn btn-success btn-sm float-end me-1"><i class="bi bi-upload"></i> Import Interaksi Profil >< CPL</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th rowspan="2">CPL</th>
                                        <th colspan="{{ $profils->count()+1 }}">PROFIL LULUSAN</th>
                                    </tr>
                                    <tr>
                                        @forelse ($profils as $profil)
                                            <td>
                                                <strong>{{ $profil->nama }}</strong><br>
                                                <small>{{ $profil->deskripsi }}</small>
                                            </td>
                                        @empty
                                            <td></td>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($cpls as $cpl)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <strong>{{ $cpl->kode }}</strong>
                                            <br>
                                            <small>{{ $cpl->nama }}</small>
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
                                                    $cek = $linkedProfilCpl->contains(
                                                        function($item) use ($profil, $cpl) {
                                                        return $item->profil_id === $profil->id && $item->cpl_id === $cpl->id;
                                                        });
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            id="is_linked_{{ $profil->id }}_{{ $cpl->id }}"
                                                            onchange="this.form.submit()"
                                                            @checked($cek)
                                                        >
                                                        <label class="form-check-label" for="is_linked_{{ $profil->id }}_{{ $cpl->id }}">
                                                            <span class="badge text-success" style="display: {{ $cek ? 'inline' : 'none' }};"><i class="bi bi-check-circle-fill"></i></span>
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

@endsection
