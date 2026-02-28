@push('title')
    Dashboard Program Studi
@endpush
<div class="row">
    <div class="col-12">
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Program Studi</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($prodiStats['prodis'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Kurikulum</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($prodiStats['kurikulums'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">CPL</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($prodiStats['cpls'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Mata Kuliah</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($prodiStats['mks'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
