@php
    use Illuminate\Support\Facades\DB;

    /** Same resolution order as ResolveMkSemester output: explicit > query > session */
    $semesterId = isset($selectedSemesterId) && $selectedSemesterId !== null && $selectedSemesterId !== ''
        ? (string) $selectedSemesterId
        : null;
    if ($semesterId === null) {
        $reqSem = request()->query('semester_id');
        if ($reqSem !== null && $reqSem !== '') {
            $semesterId = (string) $reqSem;
        }
    }
    if ($semesterId === null) {
        $stored = session('mk_semester_' . $mk->id);
        $semesterId = ($stored !== null && $stored !== '') ? (string) $stored : null;
    }

    $sp = $semesterId ? '?semester_id=' . $semesterId : '';

    // Step 1 — CPMK (MK-wide, no semester filter)
    $step1Done = $mk->cpmks()->exists();

    // Step 2 — SubCPMK (semester-aware)
    $step2Done = $step1Done && DB::table('subcpmks as s')
        ->join('join_cpl_cpmks as jcc', 'jcc.id', '=', 's.join_cpl_cpmk_id')
        ->where('jcc.mk_id', $mk->id)
        ->when($semesterId, fn ($q) => $q->where('s.semester_id', $semesterId))
        ->exists();

    // Step 3 — Pengisian Tugas / Penugasan (semester-aware)
    $step3Done = $step2Done && $mk->penugasans()
        ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
        ->exists();

    // Step 4 — Pembobotan Tugas (mapping per semester pada tabel join)
    $step4Done = $step3Done && DB::table('join_subcpmk_penugasans as jsp')
        ->where('jsp.mk_id', $mk->id)
        ->when($semesterId, fn ($q) => $q->where('jsp.semester_id', $semesterId))
        ->exists();

    // Step 5 — Pengisian Nilai (semester-aware)
    $step5Done = $step4Done && DB::table('nilais')
        ->where('mk_id', $mk->id)
        ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
        ->exists();

    $allDone = $step5Done;

    // Navigation URLs — carry semester context where applicable
    $cpmkUrl    = route('mks.cpmks.index', $mk->id);
    $subcpmkUrl = route('mks.subcpmks.index', $mk->id) . $sp;
    $tugasUrl   = route('mks.penugasans.index', $mk->id) . $sp;
    $bobotUrl   = route('mks.joinsubcpmkpenugasans.index', $mk->id) . $sp;
    $nilaiUrl   = route('mks.nilais.index', $mk->id) . $sp;

    // Progress fill — 5 nodes, 4 gaps, each gap = 20% of the container width
    if      ($step4Done) $progressPct = '80%';
    elseif  ($step3Done) $progressPct = '60%';
    elseif  ($step2Done) $progressPct = '40%';
    elseif  ($step1Done) $progressPct = '20%';
    else                 $progressPct = '0%';

    $flowSteps = [
        ['label' => 'Pengisian CPMK',    'url' => $cpmkUrl,    'done' => $step1Done, 'active' => !$step1Done],
        ['label' => 'Pengisian SubCPMK', 'url' => $subcpmkUrl, 'done' => $step2Done, 'active' => $step1Done  && !$step2Done],
        ['label' => 'Pengisian Tugas',   'url' => $tugasUrl,   'done' => $step3Done, 'active' => $step2Done  && !$step3Done],
        ['label' => 'Pembobotan Tugas',  'url' => $bobotUrl,   'done' => $step4Done, 'active' => $step3Done  && !$step4Done],
        ['label' => 'Pengisian Nilai',   'url' => $nilaiUrl,   'done' => $step5Done, 'active' => $step4Done  && !$step5Done],
    ];
@endphp

@if ($allDone)

    {{-- All 5 steps done: stepper hidden, laporan alert only --}}
    <div class="alert alert-success mt-2 mb-0 py-2 px-3">
        <div class="fw-semibold mb-1">Semua data sudah lengkap. Lihat laporan ketercapaian:</div>
        <div class="d-flex flex-wrap gap-2 mt-1">
            <a href="{{ route('mks.workclouds.index', $mk->id) }}{{ $sp }}"
               class="btn btn-success btn-sm rounded-pill fw-semibold">
                <i class="bi bi-grid-1x2-fill me-1"></i> Portofolio Penilaian
            </a>
            <a href="{{ route('mks.achievements.index', $mk->id) }}{{ $sp }}"
               class="btn btn-success btn-sm rounded-pill fw-semibold">
                <i class="bi bi-award-fill me-1"></i> Evaluasi CPL v1
            </a>
            <a href="{{ route('mks.ketercapaians.index', $mk->id) }}{{ $sp }}"
               class="btn btn-success btn-sm rounded-pill fw-semibold">
                <i class="bi bi-bar-chart-steps me-1"></i> Evaluasi CPL v2
            </a>
            <a href="{{ route('mks.spyderweb', $mk->id) }}{{ $sp }}"
               class="btn btn-success btn-sm rounded-pill fw-semibold">
                <i class="bi bi-bullseye me-1"></i> Jaring Laba-laba
            </a>
            <a href="{{ route('mks.laporan', $mk->id) }}{{ $sp }}"
               class="btn btn-success btn-sm rounded-pill fw-semibold">
                <i class="bi bi-journal-check me-1"></i> Laporan ke Prodi
            </a>
        </div>
    </div>

@else

    {{-- Progress stepper --}}
    <div class="position-relative mt-2">

        {{-- Gray full connector --}}
        <div class="position-absolute"
             style="top: 1rem; left: 10%; right: 10%; height: 2px; background-color: #dee2e6; z-index: 0;"></div>

        {{-- Green progress fill --}}
        <div class="position-absolute"
             style="top: 1rem; left: 10%; width: {{ $progressPct }}; height: 2px; background-color: #198754; z-index: 0; transition: width .4s ease;"></div>

        {{-- Step nodes --}}
        <div class="d-flex justify-content-between position-relative" style="z-index: 1;">
            @foreach ($flowSteps as $i => $step)
                @php
                    if ($step['done']) {
                        $bg = '#198754'; $border = '#198754'; $fg = 'white'; $lc = '#198754';
                    } elseif ($step['active']) {
                        $bg = '#fff3cd'; $border = '#ffc107'; $fg = '#664d03'; $lc = '#664d03';
                    } else {
                        $bg = '#f8f9fa'; $border = '#dee2e6'; $fg = '#adb5bd'; $lc = '#adb5bd';
                    }
                @endphp
                <a href="{{ $step['url'] }}"
                   class="d-flex flex-column align-items-center text-decoration-none"
                   style="width: 20%;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                         style="width: 2rem; height: 2rem; font-size: 0.8rem;
                                background-color: {{ $bg }}; border: 2px solid {{ $border }}; color: {{ $fg }};">
                        @if ($step['done']) &#10003; @else {{ $i + 1 }} @endif
                    </div>
                    <div class="text-center mt-1 fw-medium"
                         style="font-size: 0.7rem; line-height: 1.3; max-width: 80px; color: {{ $lc }};">
                        {{ $step['label'] }}
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Contextual next-action alert --}}
    @if (!$step1Done)
        <div class="alert alert-warning mt-2 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">Belum ada CPMK. Mulai dengan mengisi CPMK mata kuliah ini.</div>
            <a href="{{ $cpmkUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                <i class="bi bi-diagram-3 me-1"></i> Pengisian CPMK
            </a>
        </div>
    @elseif (!$step2Done)
        <div class="alert alert-warning mt-2 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">CPMK tersedia. Lanjutkan dengan pengisian SubCPMK{{ $semesterId ? ' untuk semester ini' : '' }}.</div>
            <a href="{{ $subcpmkUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                <i class="bi bi-list-nested me-1"></i> Pengisian SubCPMK
            </a>
        </div>
    @elseif (!$step3Done)
        <div class="alert alert-warning mt-2 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">SubCPMK tersedia. Lanjutkan dengan pengisian tugas{{ $semesterId ? ' untuk semester ini' : '' }}.</div>
            <a href="{{ $tugasUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                <i class="bi bi-clipboard2-check me-1"></i> Pengisian Tugas
            </a>
        </div>
    @elseif (!$step4Done)
        <div class="alert alert-warning mt-2 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">Tugas tersedia. Lanjutkan dengan pembobotan SubCPMK ke tugas{{ $semesterId ? ' untuk semester ini' : '' }}.</div>
            <a href="{{ $bobotUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                <i class="bi bi-sliders me-1"></i> Pembobotan Tugas
            </a>
        </div>
    @elseif (!$step5Done)
        <div class="alert alert-warning mt-2 mb-0 py-2 px-3">
            <div class="fw-semibold mb-1">Pembobotan selesai. Lanjutkan dengan pengisian nilai mahasiswa{{ $semesterId ? ' untuk semester ini' : '' }}.</div>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <a href="{{ $nilaiUrl }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                    <i class="bi bi-clipboard2-data me-1"></i> Pengisian Nilai
                </a>
                <a href="{{ route('settings.import.nilais', $mk->id) }}{{ $sp }}" class="btn btn-secondary btn-sm rounded-pill fw-semibold">
                    <i class="bi bi-upload me-1"></i> Upload Nilai
                </a>
            </div>
        </div>
    @endif

@endif
