@push('title')
    Dashboard Program Studi
@endpush

@push('styles')
    @include('dashboard.partials.dashboard-stats-styles')
@endpush

<div class="row g-3 mt-1">
    @forelse($prodiDetails as $detail)
        <div class="col-12">
            <div class="dashboard-stats-container">
                <div class="dashboard-stats-header">
                    <div class="dashboard-stats-title">{{ $detail['nama'] }}</div>
                    <div class="dashboard-stats-subtitle">Ringkasan data program studi {{ $detail['nama'] }}.</div>
                </div>
                <div class="dashboard-stats-content">
                    <div class="dashboard-stats-grid">
                        {{-- Kurikulum Card --}}
                        <a href="{{ route('ruang.prodi', ['prodi_id' => $detail['id']]) }}" class="dashboard-stat-link">
                            <div class="dashboard-stat-card">
                                <div class="dashboard-stat-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="dashboard-stat-card-content">
                                    <div class="dashboard-stat-label">Kurikulum</div>
                                    <div class="dashboard-stat-value">{{ number_format($detail['kurikulums']) }}</div>
                                    <div class="dashboard-stat-footer">
                                        <span>Lihat detail</span>
                                        <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </a>

                        {{-- Mahasiswa Card --}}
                        <a href="{{ route('mahasiswas.index', ['prodi_id' => $detail['id']]) }}" class="dashboard-stat-link">
                            <div class="dashboard-stat-card">
                                <div class="dashboard-stat-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="dashboard-stat-card-content">
                                    <div class="dashboard-stat-label">Mahasiswa</div>
                                    <div class="dashboard-stat-value">{{ number_format($detail['mahasiswas']) }}</div>
                                    <div class="dashboard-stat-footer">
                                        <span>Lihat detail</span>
                                        <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </a>

                        {{-- Dosen Card --}}
                        <a href="{{ route('prodis.joinprodiusers.index', ['prodi' => $detail['id']]) }}" class="dashboard-stat-link">
                            <div class="dashboard-stat-card">
                                <div class="dashboard-stat-icon">
                                    <i class="fas fa-chalkboard-user"></i>
                                </div>
                                <div class="dashboard-stat-card-content">
                                    <div class="dashboard-stat-label">Dosen</div>
                                    <div class="dashboard-stat-value">{{ number_format($detail['dosen']) }}</div>
                                    <div class="dashboard-stat-footer">
                                        <span>Lihat detail</span>
                                        <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle"></i>
                Anda belum ditugaskan sebagai pimpinan program studi. Hubungi administrator untuk ditugaskan.
            </div>
        </div>
    @endforelse
</div>
