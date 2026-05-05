@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas mata kuliah --}}
            @include('components.identitas-mk', $mk)

            <x-mk-semester-bar
                mode="server"
                :semesterOptions="$semesters"
                :selectedSemesterId="$selectedSemesterId" />
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <x-obe.header
                    title="Hasil Analisis MK Dosen per Mahasiswa"
                    subtitle="Ringkasan capaian mahasiswa pada mata kuliah terpilih"
                    icon="bi bi-mortarboard-fill" />
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
                                        <td class="text-center" width="120">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm btn-detail-cpl" data-mahasiswa-id="{{ $mahasiswa['id'] }}">
                                                <i class="bi bi-eye"></i> <span>Capaian</span>
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
                    <div class="col-12 d-none" id="chartNotReadyAlert">
                        <div class="alert alert-warning mb-0">
                            Grafik belum dapat ditampilkan karena penilaian mata kuliah mahasiswa ini belum tersedia.
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card" id="cardChartCpmk">
                            <div class="card-header">Grafik Ketercapaian CPMK</div>
                            <div class="card-body">
                                <div style="height: 360px;">
                                    <canvas id="chartCpmkMahasiswa"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card" id="cardChartSubcpmk">
                            <div class="card-header">Grafik Ketercapaian SubCPMK</div>
                            <div class="card-body">
                                <div style="height: 360px;">
                                    <canvas id="chartSubcpmkMahasiswa"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card" id="cardChartPenugasan">
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
    const detailMahasiswaNama = document.getElementById('detailMahasiswaNama');
    const chartCpmkEl = document.getElementById('chartCpmkMahasiswa');
    const chartSubcpmkEl = document.getElementById('chartSubcpmkMahasiswa');
    const chartPenugasanEl = document.getElementById('chartPenugasanMahasiswa');
    const chartNotReadyAlert = document.getElementById('chartNotReadyAlert');
    const cardChartCpmk = document.getElementById('cardChartCpmk');
    const cardChartSubcpmk = document.getElementById('cardChartSubcpmk');
    const cardChartPenugasan = document.getElementById('cardChartPenugasan');

    let chartCpmk = null;
    let chartSubcpmk = null;
    let chartPenugasan = null;

    function showDetailModal() {
        if (!modalEl) {
            return;
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return;
        }

        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery(modalEl).modal('show');
        }
    }

    function toNumber(value) {
        const num = Number(value ?? 0);
        return Number.isFinite(num) ? num : 0;
    }

    function renderRadar(canvas, oldChart, scores, datasetLabel) {
        if (!canvas) {
            return oldChart;
        }
        if (oldChart) {
            oldChart.destroy();
        }

        const safeScores = Array.isArray(scores) ? scores : [];
        const labels = safeScores.map(item => item.kode ?? '-');
        const values = safeScores.map(item => {
            if (item?.nilai === null || item?.nilai === undefined || item?.nilai === '') {
                return 0;
            }
            return toNumber(item.nilai);
        });
        const assessedFlags = safeScores.map(item => Boolean(item?.dinilai));

        const datasets = [{
            label: datasetLabel,
            data: values,
            borderWidth: 2,
            borderColor: 'rgba(13,110,253,1)',
            backgroundColor: 'rgba(13,110,253,0.2)',
            pointBackgroundColor: assessedFlags.map(flag => flag ? 'rgba(13,110,253,1)' : 'rgba(108,117,125,1)'),
            pointBorderColor: assessedFlags.map(flag => flag ? 'rgba(13,110,253,1)' : 'rgba(108,117,125,1)')
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
                                const isAssessed = assessedFlags[context.dataIndex] ?? false;
                                if (!isAssessed) {
                                    return `${context.dataset.label}: Belum dinilai`;
                                }
                                return `${context.dataset.label}: ${toNumber(context.raw).toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('click', function (event) {
        const btn = event.target.closest('.btn-detail-cpl');
        if (!btn) {
            return;
        }

        const mahasiswaId = btn.getAttribute('data-mahasiswa-id');
        const payload = detailPerMahasiswa[mahasiswaId];

        if (!payload) {
            return;
        }

            const mahasiswa = payload.mahasiswa || {};
            const detailMks = Array.isArray(payload.detail_mks) ? payload.detail_mks : [];
            const cpmkScores = Array.isArray(payload.cpmk_scores) ? payload.cpmk_scores : [];
            const subcpmkScores = Array.isArray(payload.subcpmk_scores) ? payload.subcpmk_scores : [];
            const penugasanScores = Array.isArray(payload.penugasan_scores) ? payload.penugasan_scores : [];
            const hasPenilaianMk = detailMks.some(function (item) {
                return item && item.nilai !== null && item.nilai !== undefined && String(item.nilai).trim() !== '';
            });

            if (detailMahasiswaNama) {
                detailMahasiswaNama.textContent = `${mahasiswa.nim ?? '-'} - ${mahasiswa.nama ?? '-'}`;
            }

            if (!hasPenilaianMk) {
                if (chartCpmk) {
                    chartCpmk.destroy();
                    chartCpmk = null;
                }
                if (chartSubcpmk) {
                    chartSubcpmk.destroy();
                    chartSubcpmk = null;
                }
                if (chartPenugasan) {
                    chartPenugasan.destroy();
                    chartPenugasan = null;
                }

                if (chartNotReadyAlert) {
                    chartNotReadyAlert.classList.remove('d-none');
                }
                if (cardChartCpmk) {
                    cardChartCpmk.classList.add('d-none');
                }
                if (cardChartSubcpmk) {
                    cardChartSubcpmk.classList.add('d-none');
                }
                if (cardChartPenugasan) {
                    cardChartPenugasan.classList.add('d-none');
                }
            } else {
                if (chartNotReadyAlert) {
                    chartNotReadyAlert.classList.add('d-none');
                }
                if (cardChartCpmk) {
                    cardChartCpmk.classList.remove('d-none');
                }
                if (cardChartSubcpmk) {
                    cardChartSubcpmk.classList.remove('d-none');
                }
                if (cardChartPenugasan) {
                    cardChartPenugasan.classList.remove('d-none');
                }

                chartCpmk = renderRadar(
                    chartCpmkEl,
                    chartCpmk,
                    cpmkScores,
                    'Capaian CPMK Mahasiswa'
                );

                chartSubcpmk = renderRadar(
                    chartSubcpmkEl,
                    chartSubcpmk,
                    subcpmkScores,
                    'Capaian SubCPMK Mahasiswa'
                );

                chartPenugasan = renderRadar(
                    chartPenugasanEl,
                    chartPenugasan,
                    penugasanScores,
                    'Besaran Nilai Penugasan'
                );
            }

        showDetailModal();
    });
});
</script>
@endpush


@endsection
