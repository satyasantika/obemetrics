@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Set Dosen ke Mata Kuliah</strong>
                    <a href="{{ route('kurikulums.mks.index',['kurikulum'=>$kurikulum->id]) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas kurikulum --}}
                    <div class="row">
                        <div class="col-md-4">Mata Kuliah</div>
                        <div class="col"><strong>{{ $mk->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">Nama Kurikulum</div>
                        <div class="col"><strong>{{ $kurikulum->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">Program Studi</div>
                        <div class="col"><strong>{{ $kurikulum->prodi->jenjang }} {{ $kurikulum->prodi->nama }}</strong></div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Set Dosen ke MK</th>
                                        <th>Set Koordinator</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php
                                    $lockedMkUserPair = \App\Models\KontrakMk::query()
                                        ->where('mk_id', $mk->id)
                                        ->whereNotNull('user_id')
                                        ->pluck('user_id')
                                        ->filter()
                                        ->unique()
                                        ->flip();
                                @endphp
                                @forelse ($join_prodi_users as $join_prodi_user)
                                    <tr style="vertical-align: text-top;">
                                        <th>
                                            {{ $join_prodi_user->user->name }}
                                        </th>
                                            <td>
                                                <form action="{{ route('mks.users.update',[$mk->id,$join_prodi_user->user_id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="user_id" value="{{ $join_prodi_user->user_id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                                    @php
                                                    $linked_dosen = \App\Models\JoinMkUser::where('kurikulum_id',$kurikulum->id)->get();
                                                    $cek = $linked_dosen->contains(
                                                        function($item) use ($mk, $join_prodi_user) {
                                                        return $item->mk_id === $mk->id && $item->user_id === $join_prodi_user->user_id;
                                                        });
                                                    $isLocked = $cek && $lockedMkUserPair->has($join_prodi_user->user_id);
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            id="is_linked_{{ $mk->id }}_{{ $join_prodi_user->user_id }}"
                                                            onchange="this.form.submit()"
                                                            @checked($cek)
                                                            @disabled($isLocked)
                                                        >
                                                        <span class="badge text-success" style="display: {{ $cek ? 'inline' : 'none' }};">pengampu</span>
                                                        @if ($isLocked)
                                                            <span class="badge bg-secondary">terkunci</span>
                                                        @endif
                                                    </div>
                                            </td>
                                            <td>
                                                    @php
                                                    $linked_koordinator = \App\Models\JoinMkUser::where('kurikulum_id',$kurikulum->id)->where('koordinator',true)->get();
                                                    $cek = $linked_koordinator->contains(
                                                        function($item) use ($mk, $join_prodi_user) {
                                                        return $item->mk_id === $mk->id && $item->user_id === $join_prodi_user->user_id;
                                                        });
                                                    $isLockedKoordinator = $cek && $lockedMkUserPair->has($join_prodi_user->user_id);
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_koordinator"
                                                            id="is_koordinator_{{ $mk->id }}_{{ $join_prodi_user->user_id }}"
                                                            onchange="this.form.submit()"
                                                            @checked($cek)
                                                            @disabled($isLockedKoordinator)
                                                        >
                                                        <span class="badge text-success" style="display: {{ $cek ? 'inline' : 'none' }};">koordinator</span>
                                                        @if ($isLockedKoordinator)
                                                            <span class="badge bg-secondary">terkunci</span>
                                                        @endif
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data Bahan Kajian untuk kurikulum ini.</span>
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
