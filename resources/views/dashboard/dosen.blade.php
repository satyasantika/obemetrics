@push('title')
    Dashboard Dosen
@endpush

@push('styles')
    @include('dashboard.partials.dashboard-stats-styles')
@endpush

<div class="row g-3 mt-1">
    @php
        $stats = [
            ['key' => 'prodis', 'label' => 'Program Studi', 'icon' => 'fas fa-book-reader', 'route' => 'prodis.index'],
            ['key' => 'kurikulums', 'label' => 'Kurikulum', 'icon' => 'fas fa-book', 'route' => 'ruang.dosen'],
            ['key' => 'mks', 'label' => 'Mata Kuliah', 'icon' => 'fas fa-chalkboard-user', 'route' => 'ruang.dosen'],
            ['key' => 'kontrakmks', 'label' => 'Kontrak MK', 'icon' => 'fas fa-file-signature', 'route' => 'kontrakmks.index'],
        ];
    @endphp

    <div class="col-12">
        <div class="dashboard-stats-container">
            <div class="dashboard-stats-header">
                <div class="dashboard-stats-title">Data Pengajaran Dosen</div>
                <div class="dashboard-stats-subtitle">Ringkasan data pengajaran dan kontrak Anda.</div>
            </div>
            <div class="dashboard-stats-content">
                <div class="dashboard-stats-grid">
                    @foreach($stats as $item)
                        <a href="{{ route($item['route']) }}" class="dashboard-stat-link">
                            <div class="dashboard-stat-card">
                                <div class="dashboard-stat-icon">
                                    <i class="{{ $item['icon'] }}"></i>
                                </div>
                                <div class="dashboard-stat-card-content">
                                    <div class="dashboard-stat-label">{{ $item['label'] }}</div>
                                    <div class="dashboard-stat-value">{{ number_format($dosenStats[$item['key']] ?? 0) }}</div>
                                    <div class="dashboard-stat-footer">
                                        <span>Lihat detail</span>
                                        <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
