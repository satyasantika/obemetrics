@props([
    'message' => null,
])

<div {{ $attributes->merge(['class' => 'alert alert-warning']) }}>
    <i class="bi bi-exclamation-triangle"></i> {{ $message ?? $slot }}
</div>
