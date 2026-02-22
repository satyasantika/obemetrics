@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Hasil Analisis Asesmen CPL per Mahasiswa</strong>
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

                    <div class="row">
                        <div class="col">
                            {{-- tabel ditampilkan dalam format datatable dengan kolom: NPM, Nama, SKS Kontrak, IPK, dan action untuk melihat detail capaian CPL per mahasiswa. Setiap baris mahasiswa dapat diklik untuk melihat rincian capaian CPL mereka dalam bentuk tabel yang menunjukkan mata kuliah yang diambil, SKS, dan persentase kontribusi terhadap capaian CPL. --}}
                            <div class="table-responsive">
                            <table id="table-laporan-mahasiswa" class="table table-bordered table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>NPM<br>NAMA</th>
                                        <th>SKS KONTRAK</th>
                                        <th>NILAI HURUF</th>
                                        <th>BOBOT HURUF</th>
                                        <th>IPK</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($mahasiswas as $mahasiswa)
                                    <tr>
                                        <td>
                                            <strong>{{ $mahasiswa['nim'] ?? '-' }}</strong><br>
                                            {{ $mahasiswa['nama'] ?? '-' }}
                                        </td>
                                        <td class="text-end">{{ $mahasiswa['sks_kontrak'] ?? 0 }}</td>
                                        <td class="text-center">{{ $mahasiswa['nilai_huruf'] ?? '-' }}</td>
                                        <td class="text-end">{{ number_format((float) ($mahasiswa['bobot_huruf'] ?? 0), 2) }}</td>
                                        <td class="text-end">{{ number_format((float) ($mahasiswa['ipk'] ?? 0), 2) }}</td>
                                        <td>
                                            {{-- tombol ini difungsikan sebagai pemicu MODAL yang menampilkan detail capaian CPL mahasiswa tersebut. Modal ini akan menampilkan tabel yang menunjukkan mata kuliah yang diambil oleh mahasiswa, jumlah SKS dari masing-masing mata kuliah, dan persentase kontribusi setiap mata kuliah terhadap capaian CPL mahasiswa tersebut. Dengan demikian, dosen atau pihak terkait dapat dengan mudah melihat bagaimana setiap mata kuliah berkontribusi terhadap pencapaian CPL mahasiswa. --}}
                                            {{-- dalam modal tersebut juga ditampilkan jaring laba-laba yang menggambarkan capaian CPL mahasiswa tersebut secara visual, dengan setiap titik pada grafik mewakili satu CPL dan jarak dari pusat menunjukkan tingkat pencapaian CPL tersebut.  --}}
                                            {{-- ditampilkan pula dalam modal tersebut grafik jaring laba-laba yang menggambarkan ketercapaian profil lulusan  --}}
                                            {{-- tabel, dan grafik jaring laba-laba ditampilkan dalam card terpisah di dalam modal untuk memberikan visualisasi yang jelas tentang capaian CPL mahasiswa tersebut. --}}
                                            <button type="button" class="btn btn-sm btn-primary btn-detail-cpl" data-mahasiswa-id="{{ $mahasiswa['id'] }}">
                                                <i class="bi bi-eye"></i> Capaian
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 6 }}"><span class="bg-warning text-dark p-2">
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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">Grafik Ketercapaian CPL</div>
                            <div class="card-body">
                                <div style="height: 360px;">
                                    <canvas id="chartCplMahasiswa"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
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
                { orderable: false, targets: 5 }
            ]
        });
    }

    const modalEl = document.getElementById('modalDetailCplMahasiswa');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    const detailMahasiswaNama = document.getElementById('detailMahasiswaNama');
    const detailMkBody = document.getElementById('detailMkBody');
    const chartCplEl = document.getElementById('chartCplMahasiswa');
    const chartProfilEl = document.getElementById('chartProfilMahasiswa');

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

            modal.show();
        });
    });
});
</script>
@endpush


@endsection
