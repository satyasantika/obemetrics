@props([
    'href',
    'active' => false,
    'variant' => 'primary',
    'icon' => null,
])

@php
    $buttonVariant = $active ? "btn-{$variant}" : "btn-outline-{$variant}";
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "btn btn-sm {$buttonVariant} mt-1"]) }}>
    @if ($icon)
        <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
</a>
