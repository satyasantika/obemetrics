@push('title')
    Dashboard Dosen
@endpush
<div class="row">
    <div class="col-12">
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Program Studi</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($dosenStats['prodis'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Kurikulum</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($dosenStats['kurikulums'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Mata Kuliah</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($dosenStats['mks'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Kontrak MK</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($dosenStats['kontrakmks'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
