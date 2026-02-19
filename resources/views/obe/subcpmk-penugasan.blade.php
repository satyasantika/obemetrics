@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Set SubCPMK untuk Setiap Tugas Mata Kuliah</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas mata kuliah --}}
                    @include('components.identitas-mk', $mk)
                    <div class="row">
                        <div class="col-md-3">Semester</div>
                        <div class="col">
                            <select id="semester-filter" name="semester_id" class="form-control form-control-sm" style="max-width: 320px;">
                                @foreach ($semesterOptions as $semester)
                                    <option value="{{ $semester->id }}" @selected((string) $semester->id === (string) $selectedSemesterId)>{{ $semester->kode }} - {{ $semester->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr>
                    {{-- menu mata kuliah --}}
                    @include('components.menu-mk',$mk)
                    <hr>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('setting.import.mk-master', ['mk' => $mk->id, 'target' => 'join_subcpmk_penugasans', 'semester_id' => $selectedSemesterId]) }}" class="btn btn-sm btn-success mb-2 float-end"><i class="bi bi-upload"></i> Import banyak SubCPMK untuk Penugasan</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col bg-warning text-dark p-2 mb-2">
                            Bagian ini digunakan untuk mengaitkan SubCPMK dengan penugasan (tugas) yang ada pada mata kuliah ini.<br>
                            Pada kondisi tertentu, satu penugasan bisa terkait dengan lebih dari satu SubCPMK, dan satu SubCPMK bisa terkait dengan lebih dari satu penugasan.<br>
                            Silakan isi bobot (tanpa %) dan tekan Enter pada pasangan isian SubCPMK Penugasan. Pastikan tampil keterangan <span class="badge bg-success text-white">Terkait</span><br>
                            Untuk menghapus keterkaitan, kosongkan nilai bobot dan tekan Enter.<br>
                            Bobot total untuk setiap penugasan harus 100%. (perhatikan keterangan di setiap penugasan)
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        @forelse ($subcpmks as $subcpmk)
                                            <th>
                                                <span title="{{ $subcpmk->nama }}">
                                                    {{ $subcpmk->kode }}
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
                                            <strong>{{ $penugasan->kode }}:</strong><br>
                                            {{ $penugasan->nama }}
                                            <br>
                                            @php
                                                $totalBobot = (float) ($bobotTotalByPenugasan[$penugasan->id] ?? 0);
                                            @endphp
                                            <span class="text-{{ $totalBobot==100 ? 'primary' : 'danger' }}">
                                                (Bobot:
                                                {{ $totalBobot }}% )
                                            </span>
                                        </td>
                                        @forelse ($subcpmks as $subcpmk)
                                            <td>
                                                @php
                                                    $cellKey = $penugasan->id . '_' . $subcpmk->id;
                                                    $linkedObj = $linkByKey[$cellKey] ?? null;
                                                    $bobot = $linkedObj?->bobot;
                                                @endphp
                                                <form action="{{ route('joinsubcpmkpenugasans.update',[$subcpmk->id,$penugasan->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="penugasan_id" value="{{ $penugasan->id }}">
                                                    <input type="hidden" name="subcpmk_id" value="{{ $subcpmk->id }}">
                                                    <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                                    <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                                                    <div class="mb-1">
                                                        <span class="badge {{ $linkedObj ? 'bg-success' : 'bg-white text-dark' }} link-status-badge">
                                                            {{ $linkedObj ? 'Terkait' : 'x' }}
                                                        </span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <input
                                                            class="form-control form-control-sm bobot-input"
                                                            type="number"
                                                            name="bobot"
                                                            title="{{ $subcpmk->nama }}"
                                                            min="0"
                                                            max="100"
                                                            step="0.01"
                                                            placeholder="bobot %"
                                                            value="{{ $bobot !== null ? $bobot : '' }}"
                                                        >
                                                        <span class="save-status small text-muted"></span>
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
document.addEventListener('DOMContentLoaded', function () {
    const semesterFilter = document.getElementById('semester-filter');
    const forms = document.querySelectorAll('form[action*="joinsubcpmkpenugasans"]');

    if (semesterFilter) {
        semesterFilter.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('semester_id', semesterFilter.value || '');
            window.location.href = url.toString();
        });
    }

    forms.forEach(function (form) {
        const input = form.querySelector('input[name="bobot"]');
        const statusEl = form.querySelector('.save-status');
        const badge = form.querySelector('.link-status-badge');

        if (!input) {
            return;
        }

        const submitLive = function () {
            const formData = new FormData(form);

            if (statusEl) {
                statusEl.textContent = 'menyimpan...';
                statusEl.className = 'save-status small text-muted';
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Gagal menyimpan');
                }
                return response.json();
            })
            .then(function (result) {
                if (statusEl) {
                    statusEl.textContent = 'tersimpan';
                    statusEl.className = 'save-status small text-success';
                    setTimeout(function () {
                        statusEl.textContent = '';
                    }, 1200);
                }

                if (badge) {
                    const linked = !!result.linked;
                    badge.textContent = linked ? 'Terkait' : 'Belum terkait';
                    badge.className = 'badge ' + (linked ? 'bg-success' : 'bg-secondary') + ' link-status-badge';
                }
            })
            .catch(function () {
                if (statusEl) {
                    statusEl.textContent = 'gagal';
                    statusEl.className = 'save-status small text-danger';
                }
            });
        };

        input.addEventListener('change', submitLive);
        input.addEventListener('blur', submitLive);
    });
});
</script>
@endpush

@endsection
