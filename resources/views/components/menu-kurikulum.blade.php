@php
    $tabId = 'menu-kurikulum-' . $kurikulum->id;

    $isProfil = request()->routeIs('kurikulums.profils.*');
    $isCpl = request()->routeIs('kurikulums.cpls.*');
    $isBk = request()->routeIs('kurikulums.bks.*');
    $isMk = request()->routeIs('kurikulums.mks.*');
    $isImportMaster = request()->routeIs('setting.import.kurikulum-master') && request()->query('target') === 'kurikulum_bundle';

    $isJoinProfilCpl = request()->routeIs('kurikulums.joinprofilcpls.*');
    $isJoinCplBk = request()->routeIs('kurikulums.joincplbks.*');
    $isJoinCplMk = request()->routeIs('kurikulums.joincplmks.*');
    $isImportJoinMaster = request()->routeIs('setting.import.kurikulum-master') && request()->query('target') === 'join_kurikulum_bundle';

    $isRencana = request()->routeIs('kurikulums.rencana-asesmen');
    $isAnalisis = request()->routeIs('kurikulums.analisis-asesmen');
    $isSpyderweb = request()->routeIs('kurikulums.spyderweb-cpl');
    $isResume = request()->routeIs('kurikulums.laporan-mahasiswa');

    $isDataTab = $isProfil || $isCpl || $isBk || $isMk || $isImportMaster;
    $isInteraksiTab = $isJoinProfilCpl || $isJoinCplBk || $isJoinCplMk || $isImportJoinMaster;
    $isLaporanTab = $isRencana || $isAnalisis || $isSpyderweb || $isResume;

    if (!$isDataTab && !$isInteraksiTab && !$isLaporanTab) {
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
        <button class="nav-link {{ $isInteraksiTab ? 'active' : '' }}" id="{{ $tabId }}-interaksi-tab" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-interaksi" type="button" role="tab" aria-controls="{{ $tabId }}-interaksi" aria-selected="{{ $isInteraksiTab ? 'true' : 'false' }}">
            Interaksi
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
        <a href="{{ route('kurikulums.profils.index',[$kurikulum->id]) }}" class="btn btn-sm {{ $isProfil ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-mortarboard"></i> Profil Lulusan
        </a>
        <a href="{{ route('kurikulums.cpls.index',[$kurikulum->id]) }}" class="btn btn-sm {{ $isCpl ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-bullseye"></i> CPL
        </a>
        <a href="{{ route('kurikulums.bks.index',[$kurikulum->id]) }}" class="btn btn-sm {{ $isBk ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-book"></i> BK
        </a>
        <a href="{{ route('kurikulums.mks.index',[$kurikulum->id]) }}" class="btn btn-sm {{ $isMk ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-journal-bookmark"></i> MK
        </a>
        <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'kurikulum_bundle', 'return_url' => url()->current()]) }}" class="btn btn-sm {{ $isImportMaster ? 'btn-success' : 'btn-outline-success' }}"><i class="bi bi-upload"></i> Import Data Master</a>
    </div>

    <div class="tab-pane fade {{ $isInteraksiTab ? 'show active' : '' }}" id="{{ $tabId }}-interaksi" role="tabpanel" aria-labelledby="{{ $tabId }}-interaksi-tab" tabindex="0">
        <a href="{{ route('kurikulums.joinprofilcpls.index',[$kurikulum->id]) }}" class="btn btn-sm {{ $isJoinProfilCpl ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-link-45deg"></i> Interaksi Profil >< CPL
        </a>
        <a href="{{ route('kurikulums.joincplbks.index',[$kurikulum->id]) }}" class="btn btn-sm {{ $isJoinCplBk ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-link-45deg"></i> Interaksi CPL >< BK
        </a>
        <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_kurikulum_bundle', 'return_url' => url()->current()]) }}" class="btn btn-sm {{ $isImportJoinMaster ? 'btn-success' : 'btn-outline-success' }}"><i class="bi bi-upload"></i> Import Join Data Master</a>
        <a href="{{ route('kurikulums.joincplmks.index',[$kurikulum->id]) }}" class="btn btn-sm {{ $isJoinCplMk ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-gear"></i> Bobot CPL tiap MK
        </a>
    </div>

    <div class="tab-pane fade {{ $isLaporanTab ? 'show active' : '' }}" id="{{ $tabId }}-laporan" role="tabpanel" aria-labelledby="{{ $tabId }}-laporan-tab" tabindex="0">
        <a href="{{ route('kurikulums.rencana-asesmen',[$kurikulum->id]) }}" class="btn btn-sm {{ $isRencana ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-diagram-3"></i> Pemetaan Rencana Asesmen CPL
        </a>
        <a href="{{ route('kurikulums.analisis-asesmen',[$kurikulum->id]) }}" class="btn btn-sm {{ $isAnalisis ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-graph-up"></i> Hasil Analisis Asesmen CPL
        </a>
        <a href="{{ route('kurikulums.spyderweb-cpl',[$kurikulum->id]) }}" class="btn btn-sm {{ $isSpyderweb ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-bullseye"></i> Grafik Jaring Laba-laba CPL
        </a>
        <a href="{{ route('kurikulums.laporan-mahasiswa',[$kurikulum->id]) }}" class="btn btn-sm {{ $isResume ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-person-lines-fill"></i> Resume Mahasiswa
        </a>
    </div>
</div>
{{-- <div class="row">
    <div class="col">
        <a href="{{ route('kurikulums.profils.index',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isProfil ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-mortarboard"></i> Profil Lulusan
        </a>
        <a href="{{ route('kurikulums.cpls.index',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isCpl ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-bullseye"></i> CPL
        </a>
        <a href="{{ route('kurikulums.bks.index',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isBk ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-book"></i> BK
        </a>
        <a href="{{ route('kurikulums.mks.index',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isMk ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-journal-bookmark"></i> MK
        </a>
        <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'kurikulum_bundle', 'return_url' => url()->current()]) }}" class="btn btn-sm mt-1 {{ $isImportMaster ? 'btn-success' : 'btn-outline-success' }}"><i class="bi bi-upload"></i> Import Data Master</a>

        @if ($kurikulum->profils()->exists() && $kurikulum->cpls()->exists() && $kurikulum->bks()->exists() && $kurikulum->mks()->exists())
            <a href="{{ route('kurikulums.joinprofilcpls.index',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isJoinProfilCpl ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="bi bi-link-45deg"></i> Interaksi Profil >< CPL
            </a>
            <a href="{{ route('kurikulums.joincplbks.index',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isJoinCplBk ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="bi bi-link-45deg"></i> Interaksi CPL >< BK
            </a>
            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_kurikulum_bundle', 'return_url' => url()->current()]) }}" class="btn btn-sm mt-1 {{ $isImportJoinMaster ? 'btn-success' : 'btn-outline-success' }}"><i class="bi bi-upload"></i> Import Join Data Master</a>

            @if ($kurikulum->joinProfilCpls()->exists() && $kurikulum->joinCplBks()->exists())

                <a href="{{ route('kurikulums.joincplmks.index',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isJoinCplMk ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-gear"></i> Bobot CPL tiap MK
                </a>
            @endif
            @if ($kurikulum->joinCplMks()->exists())
                <a href="{{ route('kurikulums.rencana-asesmen',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isRencana ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-diagram-3"></i> Pemetaan Rencana Asesmen CPL
                </a>
                <a href="{{ route('kurikulums.analisis-asesmen',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isAnalisis ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-graph-up"></i> Hasil Analisis Asesmen CPL
                </a>
                <a href="{{ route('kurikulums.spyderweb-cpl',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isSpyderweb ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-bullseye"></i> Grafik Jaring Laba-laba CPL
                </a>
                <a href="{{ route('kurikulums.laporan-mahasiswa',[$kurikulum->id]) }}" class="btn btn-sm mt-1 {{ $isResume ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-person-lines-fill"></i> Resume Mahasiswa
                </a>
            @endif
        @endif

    </div>
</div> --}}
