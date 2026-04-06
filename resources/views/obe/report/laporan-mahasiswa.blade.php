@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Hasil Analisis Asesmen CPL per Mahasiswa"
                    subtitle="Informasi pencapaian CPL setiap mahasiswa"
                    icon="bi bi-bar-chart-line-fill"
                    />
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            {{-- data mahasiswa --}}
                            <div class="table-responsive">
                            <table id="table-laporan-mahasiswa" class="table table-bordered table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>NPM</th>
                                        <th>NAMA</th>
                                        <th>SKS DIKONTRAK</th>
                                        <th>NILAI ANGKA</th>
                                        <th>NILAI HURUF</th>
                                        <th>BOBOT HURUF</th>
                                        <th>IPK</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($mahasiswas as $mahasiswa)
                                    <tr>
                                        <td>{{ $mahasiswa['nim'] ?? '-' }}</td>
                                        <td>{{ $mahasiswa['nama'] ?? '-' }}</td>
                                        <td class="text-end">{{ $mahasiswa['sks_kontrak'] ?? 0 }}</td>
                                        <td class="text-center">{{ $mahasiswa['nilai_angka'] ?? '-' }}</td>
                                        <td class="text-center">{{ $mahasiswa['nilai_huruf'] ?? '-' }}</td>
                                        <td class="text-end">{{ number_format((float) ($mahasiswa['bobot_huruf'] ?? 0), 2) }}</td>
                                        <td class="text-end">{{ number_format((float) ($mahasiswa['ipk'] ?? 0), 2) }}</td>
                                        <td>
                                            {{-- detail capaian CPL mahasiswa ini --}}
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-detail-cpl d-inline-flex align-items-center gap-1" data-mahasiswa-id="{{ $mahasiswa['id'] }}">
                                                <i class="bi bi-eye"></i> <span>Grafik</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 8 }}"><span class="bg-warning text-dark p-2">
                                            Belum ada data mahasiswa untuk kurikulum ini.</span>
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

{{-- modal untuk menampilkan detail capaian CPL mahasiswa tersebut --}}
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
                        <div class="card" id="cardChartCpl">
                            <div class="card-header">Grafik Ketercapaian CPL</div>
                            <div class="card-body">
                                <div style="height: 360px;">
                                    <canvas id="chartCplMahasiswa"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card" id="cardChartProfil">
                            <div class="card-header">Grafik Map Profil Lulusan</div>
                            <div class="card-body">
                                <div style="height: 360px;">
                                    <canvas id="chartProfilMahasiswa"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">Kontrak Mata Kuliah</div>
                            <div class="card-body table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Mata Kuliah</th>
                                            <th class="text-end">SKS</th>
                                            <th class="text-end">Nilai</th>
                                            <th class="text-end">Nilai Huruf</th>
                                            <th class="text-end">Kontribusi (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailMkBody"></tbody>
                                </table>
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
    const target = {{ (float) ($target ?? 0) }};

    if (window.jQuery && $.fn.DataTable && $('#table-laporan-mahasiswa').length) {
        $('#table-laporan-mahasiswa').DataTable({
            pageLength: 10,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: 7 }
            ],
            lengthMenu: [[10, 25, 50, 100, 200, 500, -1],
                            [10, 25, 50, 100, 200, 500, "All"]],
        });
    }

    const modalEl = document.getElementById('modalDetailCplMahasiswa');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    const detailMahasiswaNama = document.getElementById('detailMahasiswaNama');
    const detailMkBody = document.getElementById('detailMkBody');
    const chartCplEl = document.getElementById('chartCplMahasiswa');
    const chartProfilEl = document.getElementById('chartProfilMahasiswa');
    const chartNotReadyAlert = document.getElementById('chartNotReadyAlert');
    const cardChartCpl = document.getElementById('cardChartCpl');
    const cardChartProfil = document.getElementById('cardChartProfil');

    let chartCpl = null;
    let chartProfil = null;

    function toNumber(value) {
        const num = Number(value ?? 0);
        return Number.isFinite(num) ? num : 0;
    }

    function renderRadar(canvas, oldChart, labels, data, datasetLabel, targetLineEnabled) {
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

        if (targetLineEnabled) {
            datasets.push({
                label: 'Target Capaian Lulusan',
                data: labels.map(() => target),
                borderWidth: 1.5,
                borderColor: 'rgba(220,53,69,1)',
                backgroundColor: 'rgba(220,53,69,0.08)',
                pointRadius: 0
            });
        }

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

            if (!payload || !modal || !detailMkBody) {
                return;
            }

            const mahasiswa = payload.mahasiswa || {};
            const detailMks = Array.isArray(payload.detail_mks) ? payload.detail_mks : [];
            const cplScores = Array.isArray(payload.cpl_scores) ? payload.cpl_scores : [];
            const profilScores = Array.isArray(payload.profil_scores) ? payload.profil_scores : [];
            const hasPenilaianMk = detailMks.some(function (item) {
                return item && item.nilai !== null && item.nilai !== undefined && String(item.nilai).trim() !== '';
            });

            if (detailMahasiswaNama) {
                detailMahasiswaNama.textContent = `${mahasiswa.nim ?? '-'} - ${mahasiswa.nama ?? '-'}`;
            }

            detailMkBody.innerHTML = detailMks.length
                ? detailMks.map(function (item) {
                    return `<tr>
                        <td><strong>${item.kode ?? '-'}</strong><br>${item.nama ?? '-'}</td>
                        <td class="text-end">${toNumber(item.sks)}</td>
                        <td class="text-end">${item.nilai === null ? '-' : toNumber(item.nilai).toFixed(2)}</td>
                        <td class="text-end">${item.nilai_huruf ?? '-'}</td>
                        <td class="text-end">${toNumber(item.kontribusi).toFixed(2)}%</td>
                    </tr>`;
                }).join('')
                : '<tr><td colspan="5"><span class="badge bg-warning text-dark">Tidak ada kontrak mata kuliah.</span></td></tr>';

            if (!hasPenilaianMk) {
                if (chartCpl) {
                    chartCpl.destroy();
                    chartCpl = null;
                }
                if (chartProfil) {
                    chartProfil.destroy();
                    chartProfil = null;
                }

                if (chartNotReadyAlert) {
                    chartNotReadyAlert.classList.remove('d-none');
                }
                if (cardChartCpl) {
                    cardChartCpl.classList.add('d-none');
                }
                if (cardChartProfil) {
                    cardChartProfil.classList.add('d-none');
                }
            } else {
                if (chartNotReadyAlert) {
                    chartNotReadyAlert.classList.add('d-none');
                }
                if (cardChartCpl) {
                    cardChartCpl.classList.remove('d-none');
                }
                if (cardChartProfil) {
                    cardChartProfil.classList.remove('d-none');
                }

                chartCpl = renderRadar(
                    chartCplEl,
                    chartCpl,
                    cplScores.map(item => item.kode ?? '-'),
                    cplScores.map(item => toNumber(item.nilai)),
                    'Capaian CPL Mahasiswa',
                    true
                );

                chartProfil = renderRadar(
                    chartProfilEl,
                    chartProfil,
                    profilScores.map(item => item.nama ?? '-'),
                    profilScores.map(item => toNumber(item.nilai)),
                    'Ketercapaian Profil Lulusan',
                    false
                );
            }

            modal.show();
        });
    });
});
</script>
@endpush


@endsection
