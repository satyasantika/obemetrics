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

    $profilExists = $kurikulum->profils()->exists();
    $cplExists = $kurikulum->cpls()->exists();
    $bkExists = $kurikulum->bks()->exists();
    $mkExists = $kurikulum->mks()->exists();

    $joinProfilCplExists = $kurikulum->joinProfilCpls()->exists();
    $joinCplBkExists = $kurikulum->joinCplBks()->exists();
    $joinCplMkExists = $kurikulum->joinCplMks()->exists();

    $dataComplete = $profilExists && $cplExists && $bkExists && $mkExists;
    $mustImportJoinMaster = $dataComplete && (!$joinProfilCplExists || !$joinCplBkExists);
    $joinProfilCplBKExists = $joinProfilCplExists && $joinCplBkExists;
    $interaksiComplete = $joinProfilCplExists && $joinCplBkExists && $joinCplMkExists;

    $warningDataIncomplete = 'Data Profil Lulusan, CPL, Bahan Kajian dan Mata Kuliah belum lengkap. Silakan upload datanya terlebih dahulu.';
    $warningInteraksiIncomplete = 'Data Interaksi Profil Lulusan dengan CPL, dan Interksi CPL dengan Bahan Kajian belum lengkap. Silakan upload datanya terlebih dahulu.';
    $warningBobotMissing = 'Isi Bobot CPL untuk setiap mata kuliah terlebih dahulu.';


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
        @if ($dataComplete)
        <x-menu-link :href="route('kurikulums.profils.index', [$kurikulum->id])" :active="$isProfil" icon="bi bi-mortarboard">
            Profil Lulusan
        </x-menu-link>
        <x-menu-link :href="route('kurikulums.cpls.index', [$kurikulum->id])" :active="$isCpl" icon="bi bi-bullseye">
            CPL
        </x-menu-link>
        <x-menu-link :href="route('kurikulums.bks.index', [$kurikulum->id])" :active="$isBk" icon="bi bi-book">
            BK
        </x-menu-link>
        <x-menu-link :href="route('kurikulums.mks.index', [$kurikulum->id])" :active="$isMk" icon="bi bi-journal-bookmark">
            MK
        </x-menu-link>
        @else
        {{-- jika data belum lengkap, upload data master --}}
        <x-menu-warning message="Data Profil, CPL, BK dan Mata Kuliah belum lengkap. Silakan upload datanya terlebih dahulu." />
        @endif
        <x-menu-link :href="route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'kurikulum_bundle', 'return_url' => url()->current()])" :active="$isImportMaster" variant="success" icon="bi bi-upload" :class="$dataComplete ? 'float-end' : ''">
            Import Data Master
        </x-menu-link>
    </div>

    <div class="tab-pane fade {{ $isInteraksiTab ? 'show active' : '' }}" id="{{ $tabId }}-interaksi" role="tabpanel" aria-labelledby="{{ $tabId }}-interaksi-tab" tabindex="0">
                {{-- jika data belum lengkap, upload data master --}}
        @if (!$dataComplete)
        <x-menu-warning :message="$warningDataIncomplete" />
        @else
            @if ($mustImportJoinMaster)
            <x-menu-warning :message="$warningInteraksiIncomplete" />
            @endif
            <x-menu-link :href="route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_kurikulum_bundle', 'return_url' => url()->current()])" :active="$isImportJoinMaster" variant="success" icon="bi bi-upload">
                Import Join Data Master
            </x-menu-link>
            @if ($joinProfilCplBKExists)
                @if (!$joinCplMkExists)
                <x-menu-warning :message="$warningBobotMissing" />
                @endif
            <x-menu-link :href="route('kurikulums.joinprofilcpls.index', [$kurikulum->id])" :active="$isJoinProfilCpl" icon="bi bi-link-45deg">
                Interaksi Profil >< CPL
            </x-menu-link>
            <x-menu-link :href="route('kurikulums.joincplbks.index', [$kurikulum->id])" :active="$isJoinCplBk" icon="bi bi-link-45deg">
                Interaksi CPL >< BK
            </x-menu-link>
            <x-menu-link :href="route('kurikulums.joincplmks.index', [$kurikulum->id])" :active="$isJoinCplMk" icon="bi bi-gear">
                Bobot CPL tiap MK
            </x-menu-link>
            @endif
        @endif
    </div>

    <div class="tab-pane fade {{ $isLaporanTab ? 'show active' : '' }}" id="{{ $tabId }}-laporan" role="tabpanel" aria-labelledby="{{ $tabId }}-laporan-tab" tabindex="0">
        {{-- jika data belum lengkap, upload data master --}}
        @if (!$dataComplete)
        <x-menu-warning :message="$warningDataIncomplete" />
        @elseif (!$joinProfilCplBKExists)
        {{-- jika data sudah ada, tetapi belum lengkap diinteraksikan --}}
        <x-menu-warning :message="$warningInteraksiIncomplete" />
        @elseif(!$joinCplMkExists)
        {{-- jika data sudah ada, tetapi belum lengkap bobot CPL tiap MK --}}
        <x-menu-warning :message="$warningBobotMissing" />
        @else
        <x-menu-link :href="route('kurikulums.rencana-asesmen', [$kurikulum->id])" :active="$isRencana" icon="bi bi-diagram-3">
            Pemetaan Rencana Asesmen CPL
        </x-menu-link>
        <x-menu-link :href="route('kurikulums.analisis-asesmen', [$kurikulum->id])" :active="$isAnalisis" icon="bi bi-graph-up">
            Hasil Analisis Asesmen CPL
        </x-menu-link>
        <x-menu-link :href="route('kurikulums.spyderweb-cpl', [$kurikulum->id])" :active="$isSpyderweb" icon="bi bi-bullseye">
            Grafik Jaring Laba-laba CPL
        </x-menu-link>
        <x-menu-link :href="route('kurikulums.laporan-mahasiswa', [$kurikulum->id])" :active="$isResume" icon="bi bi-person-lines-fill">
            Resume Mahasiswa
        </x-menu-link>
        @endif
    </div>
</div>
