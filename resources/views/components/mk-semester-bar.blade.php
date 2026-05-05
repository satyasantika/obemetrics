@props([
    'semesterOptions',      // Illuminate\Support\Collection of Semester models
    'selectedSemesterId',   // string|null — current selection
    'mode' => 'server',     // 'server' = reload page on change | 'client' = JS in page handles it
])

@if ($semesterOptions->isNotEmpty())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3 bg-white rounded-3">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small fw-semibold text-uppercase flex-shrink-0">
                <i class="bi bi-calendar3 me-1"></i>Semester
            </span>
            <div class="vr"></div>
            <select id="semester-filter" name="semester_id" class="form-select form-select-sm" style="max-width: 300px;">
                @foreach ($semesterOptions as $semester)
                    <option value="{{ $semester->id }}" @selected((string) $semester->id === (string) $selectedSemesterId)>
                        {{ $semester->kode }} - {{ $semester->nama }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

@if ($mode === 'server')
<script>
    document.getElementById('semester-filter')?.addEventListener('change', function () {
        const url = new URL(window.location.href);
        url.searchParams.set('semester_id', this.value);
        window.location.href = url.toString();
    });
</script>
@endif
@endif
