@push('title')
    Dashboard ADMIN
@endpush

@push('styles')
<style>
    .admin-hero {
        border: 1px solid var(--bs-primary-border-subtle);
    }

    .admin-stat-link {
        display: block;
        border-radius: 1rem;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .admin-stat-link:hover {
        transform: translateY(-2px);
    }

    .admin-stat-card {
        border: 1px solid var(--bs-border-color);
        border-radius: 1rem;
        background: var(--bs-body-bg);
    }

    .admin-stat-value {
        font-size: 1.6rem;
        line-height: 1.1;
        letter-spacing: -.02em;
    }

    .admin-stat-icon {
        min-width: 2.1rem;
        min-height: 2.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
</style>
@endpush

<div class="row g-3 mt-1">
    @php
        $stats = [
            ['key' => 'users', 'label' => 'User', 'icon' => 'bi bi-people-fill', 'route' => 'users.index'],
            ['key' => 'roles', 'label' => 'Role', 'icon' => 'bi bi-person-badge-fill', 'route' => 'roles.index'],
            ['key' => 'permissions', 'label' => 'Permission', 'icon' => 'bi bi-shield-lock-fill', 'route' => 'permissions.index'],
            ['key' => 'prodis', 'label' => 'Prodi', 'icon' => 'bi bi-journal-bookmark-fill', 'route' => 'prodis.index'],
            ['key' => 'mahasiswas', 'label' => 'Mahasiswa', 'icon' => 'bi bi-mortarboard-fill', 'route' => 'mahasiswas.index'],
            ['key' => 'semesters', 'label' => 'Semester', 'icon' => 'bi bi-calendar3', 'route' => 'semesters.index'],
            ['key' => 'evaluasis', 'label' => 'Evaluasi', 'icon' => 'bi bi-clipboard2-check-fill', 'route' => 'evaluasis.index'],
            ['key' => 'kontrakmks', 'label' => 'Kontrak MK', 'icon' => 'bi bi-file-earmark-text-fill', 'route' => 'kontrakmks.index'],
        ];
    @endphp

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 border-bottom">
                <div class="fw-semibold">Statistik Data</div>
                <small class="text-muted">Ringkasan jumlah data utama ruang admin.</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($stats as $item)
                        @can('read '.strtolower(str_replace(' ', '', $item['label'])).'s')
                            <div class="col-12 col-sm-6 col-lg-3">
                                <div class="card h-100 shadow-sm admin-stat-card">
                                    <div class="card-body d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <div class="text-muted small">{{ $item['label'] }}</div>
                                            <div class="admin-stat-value mb-0 fw-bold">{{ number_format($adminStats[$item['key']] ?? 0) }}</div>
                                        </div>
                                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis border border-primary-subtle admin-stat-icon">
                                            <i class="{{ $item['icon'] }}"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endcan
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
