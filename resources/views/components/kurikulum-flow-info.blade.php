@php
    use App\States\Kurikulum\Draft as KurikulumDraft;
    use App\States\Kurikulum\BelumInteraksi as KurikulumBelumInteraksi;
    use App\States\Kurikulum\BelumBobot as KurikulumBelumBobot;
    use App\States\Kurikulum\BelumKontrak as KurikulumBelumKontrak;
    use App\States\Kurikulum\Aktif as KurikulumAktif;
    use App\States\Kurikulum\NonAktif as KurikulumNonAktif;

    $st = $kurikulum->status;

    $step1Done = $st instanceof KurikulumBelumInteraksi || $st instanceof KurikulumBelumBobot || $st instanceof KurikulumBelumKontrak || $st instanceof KurikulumAktif;
    $step2Done = $st instanceof KurikulumBelumBobot || $st instanceof KurikulumBelumKontrak || $st instanceof KurikulumAktif;
    $step3Done = $st instanceof KurikulumBelumKontrak || $st instanceof KurikulumAktif;
    $step4Done = $st instanceof KurikulumAktif;

    $done    = 'bg-success-subtle text-success-emphasis border border-success-subtle';
    $current = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    $idle    = 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle';
@endphp

{{-- <div class="card border-0 shadow-sm mb-3">
    <div class="card-body bg-light-subtle"> --}}
        {{-- <div class="fw-semibold">Panduan Alur Kurikulum</div> --}}
        {{-- <small class="text-muted">Ikuti urutan di sidebar: Data Master → Interaksi → Bobot CPL tiap MK → Laporan.</small> --}}

        <div class="position-relative mt-2">
            {{-- Gray background connector line --}}
            <div class="position-absolute"
                 style="top: 1rem; left: 10%; right: 10%; height: 2px; background-color: #dee2e6; z-index: 0;"></div>

            {{-- Colored progress fill --}}
            @php
                if ($step4Done)      $progressPct = '80%';
                elseif ($step3Done) $progressPct = '60%';
                elseif ($step2Done) $progressPct = '40%';
                elseif ($step1Done) $progressPct = '20%';
                else                $progressPct = '0%';
            @endphp
            <div class="position-absolute"
                 style="top: 1rem; left: 10%; width: {{ $progressPct }}; height: 2px; background-color: #198754; z-index: 0;"></div>

            {{-- Steps --}}
            <div class="d-flex justify-content-between position-relative" style="z-index: 1;">
                @php
                    $flowSteps = [
                        ['label' => 'Data Master',      'url' => route('kurikulums.profils.index', $kurikulum->id),       'done' => $step1Done, 'active' => !$step1Done],
                        ['label' => 'Interaksi',        'url' => route('kurikulums.profilcpls.index', $kurikulum->id), 'done' => $step2Done, 'active' => $step1Done && !$step2Done],
                        ['label' => 'Bobot CPL per MK', 'url' => route('kurikulums.joincplmks.index', $kurikulum->id),    'done' => $step3Done, 'active' => $step2Done && !$step3Done],
                        ['label' => 'Kontrak MK',       'url' => route('kontrakmks.index', ['kurikulum' => $kurikulum->id]), 'done' => $step4Done, 'active' => $step3Done && !$step4Done],
                        ['label' => 'Laporan',          'url' => route('kurikulums.analisis-asesmen', $kurikulum->id),    'done' => false,      'active' => $step4Done],
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
                    <a href="{{ $flowStep['url'] }}" class="d-flex flex-column align-items-center text-decoration-none" style="width: 20%;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                             style="width: 2rem; height: 2rem; font-size: 0.8rem;
                                    background-color: {{ $bg }}; border: 2px solid {{ $border }}; color: {{ $fg }};">
                            @if($flowStep['done']) &#10003; @else {{ $i + 1 }} @endif
                        </div>
                        <div class="text-center mt-1 fw-medium"
                             style="font-size: 0.7rem; line-height: 1.3; max-width: 120px; color: {{ $lc }};">
                            {{ $flowStep['label'] }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        @if ($st instanceof KurikulumAktif)
        <div class="alert alert-success mt-3 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">Semua data sudah lengkap. Lihat laporan ketercapaian CPL:</div>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <a href="{{ route('kurikulums.rencana-asesmen', $kurikulum->id) }}" class="btn btn-success btn-sm rounded-pill fw-semibold">
                    <i class="fas fa-project-diagram me-1"></i> Rencana Asesmen CPL
                </a>
                <a href="{{ route('kurikulums.analisis-asesmen', $kurikulum->id) }}" class="btn btn-success btn-sm rounded-pill fw-semibold">
                    <i class="fas fa-chart-line me-1"></i> Analisis Asesmen CPL
                </a>
                <a href="{{ route('kurikulums.spyderweb-cpl', $kurikulum->id) }}" class="btn btn-success btn-sm rounded-pill fw-semibold">
                    <i class="fas fa-bullseye me-1"></i> Grafik Spyderweb CPL
                </a>
                <a href="{{ route('kurikulums.laporan-mahasiswa', $kurikulum->id) }}" class="btn btn-success btn-sm rounded-pill fw-semibold">
                    <i class="fas fa-address-card me-1"></i> Resume Mahasiswa
                </a>
            </div>
        </div>
        @elseif ($st instanceof KurikulumNonAktif)
        <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
            Kurikulum ini berstatus <strong>Non Aktif</strong>. Aktifkan kembali untuk melanjutkan pengisian.
        </div>
        @elseif ($st instanceof KurikulumDraft)
        @php
            $missingItems = [];
            if (!$kurikulum->profils()->exists()) $missingItems[] = ['label' => 'Profil',           'url' => route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'profils',  'return_url' => url()->current()])];
            if (!$kurikulum->cpls()->exists())    $missingItems[] = ['label' => 'CPL',               'url' => route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'cpls',    'return_url' => url()->current()])];
            if (!$kurikulum->bks()->exists())     $missingItems[] = ['label' => 'BK',                'url' => route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'bks',     'return_url' => url()->current()])];
            if (!$kurikulum->mks()->exists())     $missingItems[] = ['label' => 'MK',                'url' => route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'mks',     'return_url' => url()->current()])];
            $allMasterMissing   = count($missingItems) === 4;
            $someMasterMissing  = count($missingItems) > 0 && count($missingItems) < 4;
            $joinMkUserMissing  = !$kurikulum->joinMkUsers()->exists();
            $bundleUrl          = route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'kurikulum_bundle', 'return_url' => url()->current()]);
            $joinMkUserUrl      = route('settings.import.joinmkusers');
        @endphp
        <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
            @if ($allMasterMissing)
                <div class="mb-1">Data master masih kosong. Upload semua sekaligus menggunakan template bundle:</div>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    <a href="{{ $bundleUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                        <i class="bi bi-upload me-1"></i> Upload Template Bundle (Profil, CPL, BK, MK)
                    </a>
                    @if ($joinMkUserMissing)
                    <a href="{{ $joinMkUserUrl }}" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold">
                        <i class="bi bi-upload me-1"></i> Upload Dosen Pengampu MK
                    </a>
                    <a href="{{ route('kurikulums.mks.index', $kurikulum->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold">
                        <i class="bi bi-person-badge me-1"></i> Set Dosen via Halaman MK
                    </a>
                    @endif
                </div>
            @elseif ($someMasterMissing || $joinMkUserMissing)
                <div class="fw-semibold mb-1">Data master belum lengkap. Upload data yang masih kosong:</div>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach ($missingItems as $item)
                        <a href="{{ $item['url'] }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                            <i class="bi bi-upload me-1"></i> Upload {{ $item['label'] }}
                        </a>
                    @endforeach
                    @if ($joinMkUserMissing)
                        <a href="{{ route('kurikulums.mks.index', $kurikulum->id) }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                            <i class="bi bi-person-badge me-1"></i> Set Dosen via Halaman MK
                        </a>
                    @endif
                </div>
            @endif
        </div>
        @elseif ($st instanceof KurikulumBelumInteraksi)
        @php
            $missingJoins = [];
            if (!$kurikulum->profilCpls()->exists()) $missingJoins[] = ['label' => 'Interaksi Profil – CPL', 'target' => 'profil_cpls'];
            if (!$kurikulum->joinCplBks()->exists())     $missingJoins[] = ['label' => 'Interaksi CPL – BK',     'target' => 'cpl_bks'];
            $allJoinsMissing = count($missingJoins) === 2;
            $bundleJoinUrl = route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_kurikulum_bundle', 'return_url' => url()->current()]);
        @endphp
        <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
            @if ($allJoinsMissing)
                <div class="mb-1">Data interaksi masih kosong. Upload keduanya sekaligus menggunakan template bundle interaksi:</div>
                <a href="{{ $bundleJoinUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                    <i class="bi bi-upload me-1"></i> Upload Template Bundle (Profil–CPL & CPL–BK)
                </a>
            @else
                <div class="fw-semibold mb-1">Data interaksi belum lengkap. Upload data yang masih kosong:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($missingJoins as $item)
                        <li>
                            <strong>{{ $item['label'] }}</strong> —
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => $item['target'], 'return_url' => url()->current()]) }}">
                                upload sesuai template
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        @elseif ($st instanceof KurikulumBelumBobot)
        @php
            $joinCplMkExists = $kurikulum->joinCplMks()->exists();
            $joinCplMkUrl    = route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'join_cpl_mks', 'return_url' => url()->current()]);
            $bobotPageUrl    = route('kurikulums.joincplmks.index', $kurikulum->id);
        @endphp
        <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
            @if (!$joinCplMkExists)
                <div class="mb-1">Interaksi sudah lengkap. Lanjutkan dengan mengunggah bobot CPL tiap MK:</div>
                <a href="{{ $joinCplMkUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                    <i class="bi bi-upload me-1"></i> Upload Bobot CPL per MK
                </a>
            @else
                <div class="fw-semibold mb-1">Masih ada mata kuliah yang belum dibobot terhadap CPL. Lengkapi melalui halaman bobot atau upload ulang:</div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a href="{{ $bobotPageUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                        <i class="bi bi-table me-1"></i> Halaman Bobot CPL per MK
                    </a>
                    <a href="{{ $joinCplMkUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                        <i class="bi bi-upload me-1"></i> Upload Bobot CPL per MK
                    </a>
                </div>
            @endif
        </div>
        @elseif ($st instanceof KurikulumBelumKontrak)
        @php
            $kontrakPageUrl   = route('kontrakmks.index', ['kurikulum' => $kurikulum->id]);
            $kontrakImportUrl = route('settings.import.kontrakmks', ['kurikulum' => $kurikulum->id, 'return_url' => url()->current()]);
        @endphp
        <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">Bobot sudah lengkap. Lengkapi data kontrak mata kuliah:</div>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <a href="{{ $kontrakPageUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                    <i class="bi bi-file-earmark-text me-1"></i> Halaman Kontrak MK
                </a>
                <a href="{{ $kontrakImportUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                    <i class="bi bi-upload me-1"></i> Upload Kontrak MK
                </a>
            </div>
        </div>
        @endif
    {{-- </div>
</div> --}}
