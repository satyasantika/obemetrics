@props([
    'minWidth' => '800px',
])

<div class="overflow-auto">
    <div style="min-width: {{ $minWidth }};">
        {{ $slot }}
    </div>
</div>
