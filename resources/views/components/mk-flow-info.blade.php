@php
    $cpmkExists = $mk->cpmks()->exists();
    $joinCplCpmkExists = $mk->joinCplCpmks()->exists();
    $subcpmkExists = $mk->joinCplCpmks()->whereHas('subcpmks')->exists();

    $penugasanExists = $mk->penugasans()->exists();
    $joinSubcpmkPenugasanExists = $mk->joinsubcpmkpenugasans()->exists();
    $nilaiExists = $mk->nilais()->exists();

    $dataComplete = $cpmkExists && $joinCplCpmkExists && $subcpmkExists;
    $tugasComplete = $dataComplete && $penugasanExists && $joinSubcpmkPenugasanExists;
    $penilaianComplete = $tugasComplete && $nilaiExists;
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body bg-light-subtle">
        <div class="fw-semibold">Panduan Alur Pengisian Mata Kuliah</div>
        <small class="text-muted">Ikuti urutan di sidebar: Data MK → Tugas & Mapping → Penilaian → Laporan.</small>

        <div class="d-flex flex-wrap gap-2 mt-2">
            <span class="badge {{ $dataComplete ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }}">1. Data MK</span>
            <span class="badge {{ $tugasComplete ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle' }}">2. Tugas & Mapping</span>
            <span class="badge {{ $penilaianComplete ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle' }}">3. Penilaian</span>
            <span class="badge {{ $penilaianComplete ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle' }}">4. Laporan</span>
        </div>

        @if (!$dataComplete)
            <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
                Lengkapi data MK terlebih dahulu (CPMK, relasi CPL-CPMK, dan SubCPMK).
            </div>
        @elseif (!$tugasComplete)
            <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
                Data MK sudah lengkap. Lanjutkan dengan Tagihan Tugas dan set SubCPMK ke Tugas.
            </div>
        @elseif (!$nilaiExists)
            <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
                Tugas dan mapping sudah siap. Lanjutkan ke pengisian nilai.
            </div>
        @else
            <div class="alert alert-success mt-3 mb-0 py-2 px-3">
                Seluruh prasyarat terpenuhi. Menu laporan siap digunakan.
            </div>
        @endif
    </div>
</div>
