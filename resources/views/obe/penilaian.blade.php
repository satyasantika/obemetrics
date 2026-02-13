@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Penilaian Tugas Mata Kuliah</strong>
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
                    @include('layouts.menu-mk',$mk)
                    <hr>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        @forelse ($penugasans as $penugasan)
                                            <th>
                                                <span title="{{ $penugasan->nama }}">
                                                    {{ $penugasan->kode }}
                                                </span>
                                            </th>
                                        @empty
                                            <th></th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($penugasans as $penugasan)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            {{ $penugasan->kode }}:<br>
                                            {{ $penugasan->nama }}
                                            <br>
                                            @php
                                                $totalBobot = $penugasan->joinSubcpmkPenugasans->where('mk_id', $mk->id)->sum('bobot');
                                            @endphp
                                            <span class="text-{{ $totalBobot==100 ? 'primary' : 'danger' }}">
                                                (Bobot:
                                                {{ $totalBobot }}% )
                                            </span>
                                        </td>
                                        @forelse ($subcpmks as $subcpmk)
                                            <td>
                                                <form action="{{ route('joinsubcpmkpenugasans.update',[$subcpmk->id,$penugasan->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="penugasan_id" value="{{ $penugasan->id }}">
                                                    <input type="hidden" name="subcpmk_id" value="{{ $subcpmk->id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    @php
                                                    $linkedSubcpmkPenugasans = \App\Models\JoinSubcpmkPenugasan::where('mk_id',$mk->id)->get();
                                                    $cek = $linkedSubcpmkPenugasans->contains(
                                                        function($item) use ($penugasan, $subcpmk) {
                                                            return $item->penugasan_id === $penugasan->id && $item->subcpmk_id === $subcpmk->id;
                                                        });
                                                    @endphp
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="is_linked"
                                                            title="{{ $subcpmk->nama }}"
                                                            id="is_linked_{{ $penugasan->id }}_{{ $subcpmk->id }}"
                                                            onchange="this.form.submit()"
                                                            @checked($cek)
                                                        >
                                                    </div>
                                                </form>
                                                @if ($cek)
                                                @php
                                                    $bobot = $linkedSubcpmkPenugasans->where('subcpmk_id', $subcpmk->id)->where('penugasan_id', $penugasan->id)->first()->bobot;
                                                @endphp
                                                <span class="badge text-success">{{ $subcpmk->kode }}</span>
                                                <span
                                                    style="display: inline-block; margin-top: 5px;">
                                                    bobot: <span id="bobot-display-{{ $penugasan->id }}_{{ $subcpmk->id }}">{{ $bobot ?? '' }}</span> %
                                                </span>
                                                <a
                                                    href="#"
                                                    class="btn btn-sm btn-white text-primary"
                                                    style="display: inline;"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Atur Bobot SubCPMK untuk Tugas ini"
                                                    onclick="event.preventDefault(); toggleBobotForm('{{ $penugasan->id }}', '{{ $subcpmk->id }}');">
                                                    <i class="bi bi-pencil-square"></i> edit
                                                </a>
                                                {{-- form bobot --}}
                                                <form
                                                    action="{{ route('joinsubcpmkpenugasans.update',[$subcpmk->id,$penugasan->id]) }}"
                                                    method="POST"
                                                    class="mt-1"
                                                    id="form-bobot-{{ $penugasan->id }}_{{ $subcpmk->id }}"
                                                    style="display: none;">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="penugasan_id" value="{{ $penugasan->id }}">
                                                    <input type="hidden" name="subcpmk_id" value="{{ $subcpmk->id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    <div class="form-group d-flex align-items-center">
                                                        <input
                                                            type="number"
                                                            name="bobot"
                                                            class="form-control col-md-12 me-2"
                                                            placeholder="Bobot (%)"
                                                            value="{{ $bobot ?? '' }}"
                                                            required
                                                        >
                                                        <button class="btn btn-primary col-md-12" type="submit">Simpan</button>
                                                    </div>
                                                </form>
                                                @endif
                                            </td>
                                        @empty
                                            <td></td>
                                        @endforelse
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2"><span class="bg-warning text-dark p-2">
                                            Belum ada data Tugas untuk Mata Kuliah ini.</span>
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

@push('scripts')
<script>
function toggleBobotForm(penugasanId, subcpmkId) {
    const formId = 'form-bobot-' + penugasanId + '_' + subcpmkId;
    const form = document.getElementById(formId);

    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
</script>
@endpush

@endsection
