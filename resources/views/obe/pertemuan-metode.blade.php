@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Set Metode Perkuliahan untuk Setiap Pertemuan</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas mata kuliah --}}
                    <div class="row">
                        <div class="col-md-3">Nama Mata Kuliah</div>
                        <div class="col"><strong>{{ $mk->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Nama Kurikulum</div>
                        <div class="col"><strong>{{ $mk->kurikulum->nama }}</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">Program Studi</div>
                        <div class="col"><strong>{{ $mk->kurikulum->prodi->jenjang }} {{ $mk->kurikulum->prodi->nama }}</strong></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('mks.pertemuans.index',[$mk->id]) }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-easel"></i> Kelola Pertemuan Mata Kuliah
                            </a>
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        @forelse ($metodes as $metode)
                                            <th>
                                                {{ $metode->nama }}
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($pertemuans as $pertemuan)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            Pertemuan ke-{{ $pertemuan->ke }}:<br>
                                            {{ $pertemuan->materi }}
                                        </td>
                                        @forelse ($metodes as $metode)
                                            <td>
                                                <form action="{{ route('joinpertemuanmetodes.update',[$pertemuan->id,$metode->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="pertemuan_id" value="{{ $pertemuan->id }}">
                                                    <input type="hidden" name="metode_id" value="{{ $metode->id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    @php
                                                    $linkedPertemuanMetodes = \App\Models\JoinPertemuanMetode::where('mk_id',$mk->id)->get();
                                                    $cek = $linkedPertemuanMetodes->contains(
                                                        function($item) use ($pertemuan, $metode) {
                                                            return $item->pertemuan_id === $pertemuan->id && $item->metode_id === $metode->id;
                                                        });
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            id="is_linked_{{ $pertemuan->id }}_{{ $metode->id }}"
                                                            onchange="this.form.submit()"
                                                            @checked($cek)
                                                        >
                                                    </div>
                                                </form>
                                                <span class="badge text-success" style="display: {{ $cek ? 'inline' : 'none' }};">{{ $cek ? $metode->kode : '' }}</span>
                                            </td>
                                        @empty
                                            <td></td>
                                        @endforelse
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data Pertemuan untuk Mata Kuliah ini.</span>
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
