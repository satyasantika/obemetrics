@php
    $tabId = 'menu-mk-' . $mk->id;

    $isCpmk = request()->routeIs('mks.cpmks.*');
    $isSubcpmk = request()->routeIs('mks.subcpmks.*');
    $isPenugasan = request()->routeIs('mks.penugasans.*');
    $isNilai = request()->routeIs('mks.nilais.*');

    $isJoinCplCpmk = request()->routeIs('mks.joincplcpmks.*');
    $isJoinSubcpmkPenugasan = request()->routeIs('mks.joinsubcpmkpenugasans.*');

    $isWorkcloud = request()->routeIs('mks.workclouds.*');
    $isAchievement = request()->routeIs('mks.achievements.*');
    $isKetercapaian = request()->routeIs('mks.ketercapaians.*');
    $isSpyderweb = request()->routeIs('mks.spyderweb');

    $isDataTab = $isCpmk || $isSubcpmk || $isJoinCplCpmk;
    $isPenilaianTab = $isWorkcloud || $isNilai || $isPenugasan || $isJoinSubcpmkPenugasan;
    $isLaporanTab = $isAchievement || $isKetercapaian || $isSpyderweb;

    if (!$isDataTab && !$isPenilaianTab && !$isLaporanTab) {
        $isDataTab = true;
    }

    $cpmkExists = $mk->cpmks()->exists();
    $joinCplCpmkExists = $mk->joinCplCpmks()->exists();
    $subcpmkExists = $mk->joinCplCpmks()->whereHas('subcpmks')->exists();

    $penugasanExists = $mk->penugasans()->exists();
    $joinSubcpmkPenugasanExists = $mk->joinsubcpmkpenugasans()->exists();
    $nilaiExists = $mk->nilais()->exists();

    $dataComplete = $cpmkExists && $joinCplCpmkExists && $subcpmkExists;
    $siapMenilai = $dataComplete && $penugasanExists && $joinSubcpmkPenugasanExists;
    $penilaianComplete = $dataComplete && $penugasanExists && $joinSubcpmkPenugasanExists && $nilaiExists;

    $warningDataIncomplete = 'Data CPMK, SubCPMK, dan relasi CPL-CPMK belum lengkap. Silakan upload datanya terlebih dahulu.';
    $warningPenugasanIncomplete = 'Lengkapi data tagihan penugasan mata kuliah ini.';
    $warningNilaiIncomplete = 'Silakan lengkapi nilai terlebih dahulu.';
    $warningJoinSubcpmkPenugasanIncomplete = 'Silakan lengkapi relasi SubCPMK-Penugasan terlebih dahulu.';

@endphp

<ul class="nav nav-tabs" id="{{ $tabId }}-tab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $isDataTab ? 'active' : '' }}" id="{{ $tabId }}-data-tab" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-data" type="button" role="tab" aria-controls="{{ $tabId }}-data" aria-selected="{{ $isDataTab ? 'true' : 'false' }}">
            Data
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $isPenilaianTab ? 'active' : '' }}" id="{{ $tabId }}-penilaian-tab" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-penilaian" type="button" role="tab" aria-controls="{{ $tabId }}-penilaian" aria-selected="{{ $isPenilaianTab ? 'true' : 'false' }}">
            Tugas & Penilaian
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $isLaporanTab ? 'active' : '' }}" id="{{ $tabId }}-laporan-tab" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-laporan" type="button" role="tab" aria-controls="{{ $tabId }}-laporan" aria-selected="{{ $isLaporanTab ? 'true' : 'false' }}">
            Laporan
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom p-2" id="{{ $tabId }}-tabContent">
    <div class="tab-pane fade {{ $isDataTab ? 'show active' : '' }}" id="{{ $tabId }}-data" role="tabpanel" aria-labelledby="{{ $tabId }}-data-tab" tabindex="0">
        @if ($dataComplete)
        <x-menu-link :href="route('mks.cpmks.index', [$mk->id])" :active="$isCpmk" icon="bi bi-sliders" class="mt-1">
            CPMK
        </x-menu-link>
        <x-menu-link :href="route('mks.joincplcpmks.index', [$mk->id])" :active="$isJoinCplCpmk" icon="bi bi-link-45deg" class="mt-1">
            Set CPL >< CPMK
        </x-menu-link>
        <x-menu-link :href="route('mks.subcpmks.index', [$mk->id])" :active="$isSubcpmk" icon="bi bi-list-nested" class="mt-1">
            SubCPMK
        </x-menu-link>
        @else
        {{-- jika data belum lengkap, upload data master --}}
        <x-menu-warning :message="$warningDataIncomplete" />
        @endif
        <x-menu-link :href="route('setting.import.mk-master', ['mk' => $mk->id, 'target' => 'mk_bundle', 'return_url' => url()->current()])" :active="false" variant="success" icon="bi bi-upload" class="mt-1 {{ !$dataComplete ? '' : 'float-end' }}">
            Import Data Master
        </x-menu-link>
    </div>
    <div class="tab-pane fade {{ $isPenilaianTab ? 'show active' : '' }}" id="{{ $tabId }}-penilaian" role="tabpanel" aria-labelledby="{{ $tabId }}-penilaian-tab" tabindex="0">
        @if(!$joinSubcpmkPenugasanExists)
        {{-- jika data sudah ada, tetapi belum set subcpmk-penugasan --}}
        <x-menu-warning :message="$warningJoinSubcpmkPenugasanIncomplete" />
        @endif

        {{-- jika data belum lengkap, upload data master --}}
        @if (!$dataComplete)
        <x-menu-warning :message="$warningDataIncomplete" />
        @else
            @if (!$penugasanExists)
            {{-- jika data sudah ada, tetapi belum lengkap tagihan tugasnya --}}
            <x-menu-warning :message="$warningPenugasanIncomplete" />
            @elseif(!$nilaiExists)
            {{-- jika data sudah ada, tetapi belum lengkap nilai --}}
            <x-menu-warning :message="$warningNilaiIncomplete" />
            @endif
            {{-- jika penugasan sudah ada, tampilkan menu untuk set subcpmk-penugasan dan pengisian nilai --}}
            <x-menu-link :href="route('mks.penugasans.index', [$mk->id])" :active="$isPenugasan" icon="bi bi-list-task" class="mt-1">
                Tagihan Tugas
            </x-menu-link>
            <x-menu-link :href="route('mks.joinsubcpmkpenugasans.index', [$mk->id])" :active="$isJoinSubcpmkPenugasan" icon="bi bi-link-45deg" class="mt-1">
                Set SubCPMK >< Tugas
            </x-menu-link>
        @endif
        @if($siapMenilai)
        {{-- jika sudah siap menilai --}}
        <x-menu-link :href="route('mks.nilais.index', [$mk->id])" :active="$isNilai" icon="bi bi-clipboard-check" class="mt-1">
            Pengisian Nilai
        </x-menu-link>
        @endif
        {{-- jika sudah ada penilaian --}}
        @if ($penilaianComplete)
        <x-menu-link :href="route('mks.workclouds.index', [$mk->id])" :active="$isWorkcloud" icon="bi bi-cloud-upload" class="mt-1">
            Portofolio Penilaian
        </x-menu-link>
        @endif
    </div>
    <div class="tab-pane fade {{ $isLaporanTab ? 'show active' : '' }}" id="{{ $tabId }}-laporan" role="tabpanel" aria-labelledby="{{ $tabId }}-laporan-tab" tabindex="0">
        {{-- jika data belum lengkap, upload data master --}}
        @if (!$dataComplete)
        <x-menu-warning :message="$warningDataIncomplete" />
        @endif
        @if (!$penugasanExists)
        {{-- jika data sudah ada, tetapi belum lengkap tagihan tugasnya --}}
        <x-menu-warning :message="$warningPenugasanIncomplete" />
        @endif
        @if(!$joinSubcpmkPenugasanExists)
        {{-- jika data sudah ada, tetapi belum set subcpmk-penugasan --}}
        <x-menu-warning :message="$warningJoinSubcpmkPenugasanIncomplete" />
        @endif
        @if(!$nilaiExists)
        {{-- jika data sudah ada, tetapi belum lengkap nilai --}}
        <x-menu-warning :message="$warningNilaiIncomplete" />
        @endif
        @if ($penilaianComplete)
        {{-- jika data sudah lengkap, tampilkan menu laporan evaluasi ketercapaian CPL --}}
        <x-menu-link :href="route('mks.achievements.index', [$mk->id])" :active="$isAchievement" icon="bi bi-graph-up" class="mt-1">
            Evaluasi Ketercapaian CPL
        </x-menu-link>
        <x-menu-link :href="route('mks.ketercapaians.index', [$mk->id])" :active="$isKetercapaian" icon="bi bi-graph-up" class="mt-1">
            Evaluasi Ketercapaian CPL 2.0
        </x-menu-link>
        <x-menu-link :href="route('mks.spyderweb', [$mk->id])" :active="$isSpyderweb" icon="bi bi-graph-up" class="mt-1">
            Jaring Laba-laba Pencapaian Mahasiswa
        </x-menu-link>
        @endif
    </div>
</div>
