@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            @include('components.kurikulum-flow-info',['kurikulum' => $kurikulum])
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <div class="card">
                <x-obe.header
                    title="Set Dosen ke Mata Kuliah"
                    subtitle="Pengaturan dosen pengampu dan koordinator mata kuliah"
                    icon="bi bi-person-workspace"
                    />
                <div class="card-body">
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
                                                    $cek = isset($linkedUserMap[$join_prodi_user->user_id]);
                                                    $isLocked = $cek && isset($lockedUserMap[$join_prodi_user->user_id]);
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
                                                            <span class="badge bg-secondary" title="Dikunci" aria-label="Dikunci"><i class="bi bi-lock-fill"></i></span>
                                                        @endif
                                                    </div>
                                            </td>
                                            <td>
                                                    @php
                                                    $cek = isset($koordinatorUserMap[$join_prodi_user->user_id]);
                                                    $isLockedKoordinator = $cek && isset($lockedUserMap[$join_prodi_user->user_id]);
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
                                                            <span class="badge bg-secondary" title="Dikunci" aria-label="Dikunci"><i class="bi bi-lock-fill"></i></span>
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
