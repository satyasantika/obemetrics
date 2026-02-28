@php
    $profilExists = $kurikulum->profils()->exists();
    $cplExists = $kurikulum->cpls()->exists();
    $bkExists = $kurikulum->bks()->exists();
    $mkExists = $kurikulum->mks()->exists();

    $joinProfilCplExists = $kurikulum->joinProfilCpls()->exists();
    $joinCplBkExists = $kurikulum->joinCplBks()->exists();
    $joinCplMkExists = $kurikulum->joinCplMks()->exists();

    $dataComplete = $profilExists && $cplExists && $bkExists && $mkExists;
    $joinProfilCplBKExists = $joinProfilCplExists && $joinCplBkExists;
    $reportsReady = $joinCplMkExists;
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body bg-light-subtle">
        <div class="fw-semibold">Panduan Alur Kurikulum</div>
        <small class="text-muted">Ikuti urutan di sidebar: Data Master → Interaksi → Bobot CPL tiap MK → Laporan.</small>

        <div class="d-flex flex-wrap gap-2 mt-2">
            <span class="badge {{ $dataComplete ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }}">1. Data Master</span>
            <span class="badge {{ $joinProfilCplBKExists ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle' }}">2. Interaksi</span>
            <span class="badge {{ $joinCplMkExists ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle' }}">3. Bobot CPL per MK</span>
            <span class="badge {{ $reportsReady ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle' }}">4. Laporan</span>
        </div>

        @if (!$dataComplete)
            <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
                Lengkapi data master terlebih dahulu (Profil, CPL, BK, dan MK) melalui menu sidebar.
            </div>
        @elseif (!$joinProfilCplBKExists)
            <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
                Data master sudah lengkap. Lanjutkan dengan upload data interaksi pada menu sidebar.
            </div>
        @elseif (!$joinCplMkExists)
            <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
                Interaksi sudah lengkap. Lanjutkan dengan pengisian bobot CPL tiap MK.
            </div>
        @else
            <div class="alert alert-success mt-3 mb-0 py-2 px-3">
                Seluruh prasyarat sudah terpenuhi. Menu laporan kini siap digunakan.
            </div>
        @endif
    </div>
</div>
