@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Grafik Jaring Laba-laba Ketercapaian CPL"
                    subtitle="Visualisasi ketercapaian CPL dari seluruh mata kuliah"
                    icon="bi bi-bullseye"
                    />
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        @forelse ($cpls as $cpl)
        @php
            $chart = $chartPerCpl[$cpl->id] ?? ['labels' => collect(), 'data' => collect()];
            $hasData = collect($chart['labels'] ?? [])->isNotEmpty();
            $hasPenilaian = (bool) ($chart['has_penilaian'] ?? false);
            $canvasId = 'chart-cpl-' . $cpl->id;
        @endphp
        <div class="col-md-6 col-sm-auto mt-3">
            <div class="card">
                <div class="card-header">
                    <strong>Grafik {{ $cpl->kode }}</strong>
                </div>
                <div class="card-body">
                    <p class="mb-2 text-muted">{{ $cpl->nama }}</p>
                    <div class="table-responsive">
                        @if ($hasData && $hasPenilaian)
                            <canvas id="{{ $canvasId }}" height="320"></canvas>
                        @elseif ($hasData)
                            <div class="alert alert-warning mb-0 py-2 px-3">
                                Grafik belum dapat ditampilkan karena mata kuliah pada CPL ini belum dinilai.
                            </div>
                        @else
                            <span class="badge bg-warning text-dark">Belum ada mata kuliah terkait CPL ini.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col">
            <span class="badge bg-warning text-dark">Belum ada data CPL untuk kurikulum ini.</span>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const target = {{ (float) ($kurikulum->target_capaian_lulusan ?? 0) }};
    const chartPerCpl = @json($chartPerCpl);

    Object.entries(chartPerCpl).forEach(([cplId, chart]) => {
        const canvasId = `chart-cpl-${cplId}`;
        const canvas = document.getElementById(canvasId);
        if (!canvas || !Array.isArray(chart.labels) || chart.labels.length === 0) {
            return;
        }

        const targetLine = chart.labels.map(() => target);

        new Chart(canvas, {
            type: 'radar',
            data: {
                labels: chart.labels,
                datasets: [
                    {
                        label: `Rerata MK (${chart.kode})`,
                        data: chart.data,
                        borderWidth: 2,
                        borderColor: 'rgba(13,110,253,1)',
                        backgroundColor: 'rgba(13,110,253,0.2)',
                        pointBackgroundColor: 'rgba(13,110,253,1)'
                    },
                    {
                        label: 'Target Capaian Lulusan',
                        data: targetLine,
                        borderWidth: 1.5,
                        borderColor: 'rgba(220,53,69,1)',
                        backgroundColor: 'rgba(220,53,69,0.08)',
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        min: 0,
                        max: 100,
                        ticks: {
                            stepSize: 10
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const value = Number(context.raw ?? 0).toFixed(2);
                                return `${context.dataset.label}: ${value}`;
                            }
                        }
                    }
                }
            }
        });
    });
});
</script>
@endpush


@endsection
