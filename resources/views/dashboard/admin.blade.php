@push('title')
    Dashboard ADMIN
@endpush

@push('styles')
    @include('dashboard.partials.dashboard-stats-styles')
@endpush

<div class="row g-3 mt-1">
    @php
        $stats = [
            ['key' => 'users', 'label' => 'User', 'icon' => 'fas fa-users-cog', 'route' => 'users.index'],
            ['key' => 'roles', 'label' => 'Role', 'icon' => 'fas fa-user-shield', 'route' => 'roles.index'],
            ['key' => 'permissions', 'label' => 'Permission', 'icon' => 'fas fa-key', 'route' => 'permissions.index'],
            ['key' => 'prodis', 'label' => 'Prodi', 'icon' => 'fas fa-book-reader', 'route' => 'prodis.index'],
            ['key' => 'mahasiswas', 'label' => 'Mahasiswa', 'icon' => 'fas fa-user-graduate', 'route' => 'mahasiswas.index'],
            ['key' => 'semesters', 'label' => 'Semester', 'icon' => 'fas fa-calendar-alt', 'route' => 'semesters.index'],
            ['key' => 'evaluasis', 'label' => 'Evaluasi', 'icon' => 'fas fa-clipboard-check', 'route' => 'evaluasis.index'],
            ['key' => 'kontrakmks', 'label' => 'Kontrak MK', 'icon' => 'fas fa-file-signature', 'route' => 'kontrakmks.index'],
        ];
    @endphp

    <div class="col-12">
        <div class="dashboard-stats-container">
            <div class="dashboard-stats-header">
                <div class="dashboard-stats-title">Statistik Data</div>
                <div class="dashboard-stats-subtitle">Ringkasan jumlah data utama ruang admin.</div>
            </div>
            <div class="dashboard-stats-content">
                <div class="dashboard-stats-grid">
                    @foreach($stats as $item)
                        @can('read '.strtolower(str_replace(' ', '', $item['label'])).'s')
                            <a href="{{ route($item['route']) }}" class="dashboard-stat-link">
                                <div class="dashboard-stat-card">
                                    <div class="dashboard-stat-icon">
                                        <i class="{{ $item['icon'] }}"></i>
                                    </div>
                                    <div class="dashboard-stat-card-content">
                                        <div class="dashboard-stat-label">{{ $item['label'] }}</div>
                                        <div class="dashboard-stat-value">{{ number_format($adminStats[$item['key']] ?? 0) }}</div>
                                        <div class="dashboard-stat-footer">
                                            <span>Lihat detail</span>
                                            <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endcan
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
