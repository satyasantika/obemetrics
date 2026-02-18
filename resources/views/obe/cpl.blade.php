@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Data Capaian Pembelajaran Lulusan (CPL)</strong>
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
                            <a href="{{ route('kurikulums.cpls.create',$kurikulum) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-plus-circle"></i> Tambah CPL
                            </a>
                            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'cpls']) }}" class="btn btn-sm btn-success mt-1 float-end">
                                <i class="bi bi-upload"></i> Upload Banyak CPL
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                 @forelse ($cpls as $cpl)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <strong>{{ $cpl->kode }}</strong>
                                            {{-- Edit CPL --}}
                                            <a href="{{ route('kurikulums.cpls.edit',[$kurikulum->id,$cpl->id]) }}" class="btn btn-sm btn-white text-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <span class="badge bg-{{ $cpl->cakupan == 'Universitas' ? 'success' : '' }}{{ $cpl->cakupan == 'Fakultas' ? 'primary' : '' }}{{ $cpl->cakupan == 'Program Studi' ? 'dark' : '' }}">{{ $cpl->cakupan }}</span>
                                        </td>
                                        <td style="text-align: justify">
                                            {{ $cpl->nama }}
                                        </td>
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
