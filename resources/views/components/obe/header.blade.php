@props([
    'title',
    'subtitle' => null,
    'icon' => 'bi bi-grid-1x2-fill',
    'backUrl' => null,
    'backLabel' => 'Kembali',
])

<div class="card-header bg-white border-0 border-bottom py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary px-3" style="min-height: 56px;">
                <i class="{{ $icon }} fs-4"></i>
            </div>
            <div>
                <div class="fw-semibold fs-5 mb-0">{{ $title }}</div>
                @if($subtitle)
                    <small class="text-muted">{{ $subtitle }}</small>
                @endif
            </div>
        </div>

        @if($backUrl)
            <a href="{{ $backUrl }}" class="btn btn-primary btn-sm"><i class="bi bi-arrow-left"></i> {{ $backLabel }}</a>
        @endif
    </div>
</div>
