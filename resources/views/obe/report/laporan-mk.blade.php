@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-11">
            <x-obe.menu-strip minWidth="960px">
                {{-- menu mata kuliah --}}
                @include('components.menu-mk',$mk)
            </x-obe.menu-strip>
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-11">
            <div class="card">
                <x-obe.header
                    title="Hasil Analisis MK Dosen per Mahasiswa"
                    subtitle="Ringkasan capaian mahasiswa pada mata kuliah terpilih"
                    icon="bi bi-mortarboard-fill"
                    :backUrl="route('home')" />
                <div class="card-body">

                    <div class="row">
                        <div class="col">
                            {{-- tabel ditampilkan dalam format datatable dengan kolom: NPM, Nama, nilai angka dan nilai huruf dari tabel kontrak_mks dan action untuk melihat detail capaian CPL per mahasiswa. Setiap baris mahasiswa dapat diklik untuk melihat rincian capaian CPL mereka dalam bentuk tabel yang menunjukkan mata kuliah yang diambil, SKS, dan persentase kontribusi terhadap capaian CPL. --}}
                            <div class="table-responsive">
                            <table id="table-laporan-mk" class="table table-bordered table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>NPM</th>
                                        <th>NAMA</th>
                                        <th>NILAI ANGKA</th>
                                        <th>NILAI HURUF</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($mahasiswas as $mahasiswa)
                                    <tr>
                                        <td><strong>{{ $mahasiswa['nim'] ?? '-' }}</strong></td>
                                        <td>{{ $mahasiswa['nama'] ?? '-' }}</td>
                                        <td class="text-end">{{ $mahasiswa['nilai_angka'] ?? 0 }}</td>
                                        <td class="text-center">{{ $mahasiswa['nilai_huruf'] ?? '-' }}</td>
                                        <td>
                                            {{-- tombol ini sebagai pemicu MODAL yang menampilkan detail capaian CPL mahasiswa tersebut.  --}}
                                            {{-- Modal ini akan menampilkan grafik jaring laba-laba ketercapaian CPMK, SubCPMK, dan besaran nilai penugasan  --}}
                                            {{-- masing-masing grafik ditampilkan dalam card terpisah di dalam modal untuk memberikan visualisasi yang jelas tentang capaian CPL mahasiswa tersebut. --}}
                                            <button type="button" class="btn btn-sm btn-primary btn-detail-cpl" data-mahasiswa-id="{{ $mahasiswa['id'] }}">
                                                <i class="bi bi-eye"></i> Capaian
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"><span class="bg-warning text-dark p-2">
                                            Belum ada data mahasiswa untuk mata kuliah ini.</span>
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
</div>

{{-- modal untuk menampilkan detail capaian CPMK, SubCPMK, dan rerata nilai penugasan (by kolom workcloud) mahasiswa tersebut --}}
<div class="modal fade" id="modalDetailCplMahasiswa" tabindex="-1" aria-labelledby="modalDetailCplMahasiswaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailCplMahasiswaLabel">
                    Detail Capaian Mahasiswa
                    <br>
                    <strong id="detailMahasiswaNama">-</strong>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">Grafik Ketercapaian CPMK</div>
                            <div class="card-body">
                                <div style="height: 360px;">
                                    <canvas id="chartCpmkMahasiswa"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">Grafik Ketercapaian SubCPMK</div>
                            <div class="card-body">
                                <div style="height: 360px;">
                                    <canvas id="chartSubcpmkMahasiswa"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">Grafik Besaran Nilai Penugasan</div>
                            <div class="card-body">
                                <div style="height: 360px;">
                                    <canvas id="chartPenugasanMahasiswa"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailPerMahasiswa = @json($detailPerMahasiswa);

    if (window.jQuery && $.fn.DataTable && $('#table-laporan-mk').length) {
        $('#table-laporan-mk').DataTable({
            pageLength: 10,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: 4 }
            ]
        });
    }

    const modalEl = document.getElementById('modalDetailCplMahasiswa');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    const detailMahasiswaNama = document.getElementById('detailMahasiswaNama');
    const chartCpmkEl = document.getElementById('chartCpmkMahasiswa');
    const chartSubcpmkEl = document.getElementById('chartSubcpmkMahasiswa');
    const chartPenugasanEl = document.getElementById('chartPenugasanMahasiswa');

    let chartCpmk = null;
    let chartSubcpmk = null;
    let chartPenugasan = null;

    function toNumber(value) {
        const num = Number(value ?? 0);
        return Number.isFinite(num) ? num : 0;
    }

    function renderRadar(canvas, oldChart, labels, data, datasetLabel) {
        if (!canvas) {
            return oldChart;
        }
        if (oldChart) {
            oldChart.destroy();
        }

        const datasets = [{
            label: datasetLabel,
            data: data,
            borderWidth: 2,
            borderColor: 'rgba(13,110,253,1)',
            backgroundColor: 'rgba(13,110,253,0.2)',
            pointBackgroundColor: 'rgba(13,110,253,1)'
        }];

        return new Chart(canvas, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        min: 0,
                        max: 100,
                        ticks: { stepSize: 10 }
                    }
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.dataset.label}: ${toNumber(context.raw).toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });
    }

    document.querySelectorAll('.btn-detail-cpl').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const mahasiswaId = btn.getAttribute('data-mahasiswa-id');
            const payload = detailPerMahasiswa[mahasiswaId];

            if (!payload || !modal) {
                return;
            }

            const mahasiswa = payload.mahasiswa || {};
            const cpmkScores = Array.isArray(payload.cpmk_scores) ? payload.cpmk_scores : [];
            const subcpmkScores = Array.isArray(payload.subcpmk_scores) ? payload.subcpmk_scores : [];
            const penugasanScores = Array.isArray(payload.penugasan_scores) ? payload.penugasan_scores : [];

            if (detailMahasiswaNama) {
                detailMahasiswaNama.textContent = `${mahasiswa.nim ?? '-'} - ${mahasiswa.nama ?? '-'}`;
            }

            chartCpmk = renderRadar(
                chartCpmkEl,
                chartCpmk,
                cpmkScores.map(item => item.kode ?? '-'),
                cpmkScores.map(item => toNumber(item.nilai)),
                'Capaian CPMK Mahasiswa'
            );

            chartSubcpmk = renderRadar(
                chartSubcpmkEl,
                chartSubcpmk,
                subcpmkScores.map(item => item.kode ?? '-'),
                subcpmkScores.map(item => toNumber(item.nilai)),
                'Capaian SubCPMK Mahasiswa'
            );

            chartPenugasan = renderRadar(
                chartPenugasanEl,
                chartPenugasan,
                penugasanScores.map(item => item.kode ?? '-'),
                penugasanScores.map(item => toNumber(item.nilai)),
                'Besaran Nilai Penugasan'
            );

            modal.show();
        });
    });
});
</script>
@endpush


@endsection
