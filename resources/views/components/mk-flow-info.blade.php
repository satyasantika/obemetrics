@php
    use App\States\Mk\Draft as MkDraft;
    use App\States\Mk\MappingSubCPMK as MkMappingSubCPMK;
    use App\States\Mk\BelumNilai as MkBelumNilai;
    use App\States\Mk\Aktif as MkAktif;
    use App\States\Mk\NonAktif as MkNonAktif;

    $st = $mk->status;

    $step1Done = $st instanceof MkMappingSubCPMK || $st instanceof MkBelumNilai || $st instanceof MkAktif;
    $step2Done = $st instanceof MkBelumNilai || $st instanceof MkAktif;
    $step3Done = $st instanceof MkAktif;
    $step4Done = false; // Laporan selalu tersedia sebagai tindakan, tidak ada state setelahnya
@endphp

<div class="position-relative mt-2">
            {{-- Gray background connector --}}
            <div class="position-absolute"
                 style="top: 1rem; left: 12.5%; right: 12.5%; height: 2px; background-color: #dee2e6; z-index: 0;"></div>

            {{-- Colored progress fill --}}
            @php
                if ($step3Done)      $progressPct = '75%';
                elseif ($step2Done) $progressPct = '50%';
                elseif ($step1Done) $progressPct = '25%';
                else                $progressPct = '0%';
            @endphp
            <div class="position-absolute"
                 style="top: 1rem; left: 12.5%; width: {{ $progressPct }}; height: 2px; background-color: #198754; z-index: 0;"></div>

            {{-- Steps --}}
            <div class="d-flex justify-content-between position-relative" style="z-index: 1;">
                @php
                    $flowSteps = [
                        ['label' => 'Data MK',       'done' => $step1Done, 'active' => !$step1Done],
                        ['label' => 'Tugas & Mapping','done' => $step2Done, 'active' => $step1Done && !$step2Done],
                        ['label' => 'Penilaian',      'done' => $step3Done, 'active' => $step2Done && !$step3Done],
                        ['label' => 'Laporan',        'done' => $step4Done, 'active' => $step3Done],
                    ];
                @endphp
                @foreach($flowSteps as $i => $flowStep)
                    @php
                        if ($flowStep['done']) {
                            $bg = '#198754'; $border = '#198754'; $fg = 'white'; $lc = '#198754';
                        } elseif ($flowStep['active']) {
                            $bg = '#fff3cd'; $border = '#ffc107'; $fg = '#664d03'; $lc = '#664d03';
                        } else {
                            $bg = '#f8f9fa'; $border = '#dee2e6'; $fg = '#adb5bd'; $lc = '#adb5bd';
                        }
                    @endphp
                    <div class="d-flex flex-column align-items-center" style="width: 25%;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                             style="width: 2rem; height: 2rem; font-size: 0.8rem;
                                    background-color: {{ $bg }}; border: 2px solid {{ $border }}; color: {{ $fg }};">
                            @if($flowStep['done']) &#10003; @else {{ $i + 1 }} @endif
                        </div>
                        <div class="text-center mt-1 fw-medium"
                             style="font-size: 0.7rem; line-height: 1.3; max-width: 70px; color: {{ $lc }};">
                            {{ $flowStep['label'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($st instanceof MkAktif)
        <div class="alert alert-success mt-3 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">Semua data sudah lengkap. Lihat laporan ketercapaian:</div>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <a href="{{ route('mks.workclouds.index', $mk->id) }}" class="btn btn-success btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="fas fa-cloud-upload-alt me-1"></i> Portofolio Penilaian
                </a>
                <a href="{{ route('mks.achievements.index', $mk->id) }}" class="btn btn-success btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="fas fa-chart-line me-1"></i> Evaluasi CPL v1
                </a>
                <a href="{{ route('mks.ketercapaians.index', $mk->id) }}" class="btn btn-success btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="fas fa-chart-area me-1"></i> Evaluasi CPL v2
                </a>
                <a href="{{ route('mks.spyderweb', $mk->id) }}" class="btn btn-success btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="fas fa-bullseye me-1"></i> Jaring Laba-laba
                </a>
                <a href="{{ route('mks.laporan', $mk->id) }}" class="btn btn-success btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="fas fa-file-alt me-1"></i> Laporan ke Prodi
                </a>
            </div>
        </div>
        @elseif ($st instanceof MkNonAktif)
        <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
            Mata kuliah ini berstatus <strong>Non Aktif</strong>. Aktifkan kembali untuk melanjutkan pengisian.
        </div>
        @elseif ($st instanceof MkDraft)
        @php
            $cpmkExists        = $mk->cpmks()->exists();
            $joinCplCpmkExists = $mk->joinCplCpmks()->exists();
            $subcpmkExists     = $mk->joinCplCpmks()->whereHas('subcpmks')->exists();
            $penugasanExists   = $mk->penugasans()->exists();

            $missingItems = [];
            if (!$cpmkExists)        $missingItems[] = ['label' => 'CPMK',              'target' => 'cpmks'];
            if (!$joinCplCpmkExists) $missingItems[] = ['label' => 'Relasi CPL–CPMK',   'target' => 'join_cpl_cpmks'];
            if (!$subcpmkExists)     $missingItems[] = ['label' => 'SubCPMK',            'target' => 'subcpmks'];
            if (!$penugasanExists)   $missingItems[] = ['label' => 'Tagihan Tugas',      'target' => 'penugasans'];

            $allMissing = count($missingItems) === 4;
            $bundleUrl  = route('settings.import.mk-master', ['mk' => $mk->id, 'target' => 'mk_bundle', 'return_url' => url()->current()]);
        @endphp
        <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
            @if ($allMissing)
                <div class="mb-1">Data MK masih kosong. Upload sekaligus menggunakan template bundle:</div>
                <a href="{{ $bundleUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="bi bi-upload me-1"></i> Upload Template Bundle MK
                </a>
            @else
                <div class="fw-semibold mb-1">Data MK belum lengkap. Yang masih kosong:</div>
                <ul class="mb-2 ps-3">
                    @foreach ($missingItems as $item)
                        <li><strong>{{ $item['label'] }}</strong></li>
                    @endforeach
                </ul>
                <a href="{{ $bundleUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="bi bi-upload me-1"></i> Upload Template Bundle MK
                </a>
            @endif
        </div>
        @elseif ($st instanceof MkMappingSubCPMK)
        @php
            $mappingPageUrl   = route('mks.joinsubcpmkpenugasans.index', $mk->id);
        @endphp
        <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">Data MK sudah lengkap. Lanjutkan dengan mapping SubCPMK ke Tugas:</div>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <a href="{{ $mappingPageUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="bi bi-diagram-3 me-1"></i> Halaman Mapping SubCPMK–Tugas
                </a>
            </div>
        </div>
        @elseif ($st instanceof MkBelumNilai)
        @php
            $nilaiPageUrl   = route('mks.nilais.index', $mk->id);
            $nilaiImportUrl = route('settings.import.nilais', $mk->id);
        @endphp
        <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">Mapping sudah lengkap. Lanjutkan dengan pengisian nilai:</div>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <a href="{{ $nilaiPageUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="bi bi-clipboard2-data me-1"></i> Halaman Pengisian Nilai
                </a>
                <a href="{{ $nilaiImportUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold text-decoration-none">
                    <i class="bi bi-upload me-1"></i> Upload Nilai
                </a>
            </div>
        </div>
        @endif
