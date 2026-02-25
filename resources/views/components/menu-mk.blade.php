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
        <a href="{{ route('mks.cpmks.index',[$mk->id]) }}" class="btn btn-sm {{ $isCpmk ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-sliders"></i> CPMK
        </a>
        <a href="{{ route('mks.joincplcpmks.index',[$mk->id]) }}" class="btn btn-sm {{ $isJoinCplCpmk ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-link-45deg"></i> Set CPL >< CPMK
        </a>
        <a href="{{ route('mks.subcpmks.index',[$mk->id]) }}" class="btn btn-sm {{ $isSubcpmk ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-list-nested"></i> SubCPMK
        </a>
        <a href="{{ route('setting.import.mk-master', ['mk' => $mk->id, 'target' => 'mk_bundle', 'return_url' => url()->current()]) }}" class="btn btn-outline-success btn-sm float-end"><i class="bi bi-upload"></i> Import Data Master</a>
    </div>
    <div class="tab-pane fade {{ $isPenilaianTab ? 'show active' : '' }}" id="{{ $tabId }}-penilaian" role="tabpanel" aria-labelledby="{{ $tabId }}-penilaian-tab" tabindex="0">
        <a href="{{ route('mks.penugasans.index',[$mk->id]) }}" class="btn btn-sm {{ $isPenugasan ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-list-task"></i> Tagihan Tugas
        </a>
        <a href="{{ route('mks.joinsubcpmkpenugasans.index',[$mk->id]) }}" class="btn btn-sm {{ $isJoinSubcpmkPenugasan ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-link-45deg"></i> Set SubCPMK >< Tugas
        </a>
        <a href="{{ route('mks.nilais.index',[$mk->id]) }}" class="btn btn-sm {{ $isNilai ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-clipboard-check"></i> Pengisian Nilai
        </a>
        <a href="{{ route('mks.workclouds.index',[$mk->id]) }}" class="btn btn-sm {{ $isWorkcloud ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-cloud-upload"></i> Portofolio Penilaian
        </a>
    </div>
    <div class="tab-pane fade {{ $isLaporanTab ? 'show active' : '' }}" id="{{ $tabId }}-laporan" role="tabpanel" aria-labelledby="{{ $tabId }}-laporan-tab" tabindex="0">
        <a href="{{ route('mks.achievements.index',[$mk->id]) }}" class="btn btn-sm {{ $isAchievement ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-graph-up"></i> Evaluasi Ketercapaian CPL
        </a>
        <a href="{{ route('mks.ketercapaians.index',[$mk->id]) }}" class="btn btn-sm {{ $isKetercapaian ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-graph-up"></i> Evaluasi Ketercapaian CPL 2.0
        </a>
        <a href="{{ route('mks.spyderweb',[$mk->id]) }}" class="btn btn-sm {{ $isSpyderweb ? 'btn-primary' : 'btn-outline-primary' }} mt-1">
            <i class="bi bi-graph-up"></i> Jaring Laba-laba Pencapaian Mahasiswa
        </a>
    </div>
</div>
