@props([
    'variant' => 'default',
    'type' => null,
    'href' => null,
])

@php
    $classes = [
        'btn',
        $variant === 'primary' ? 'btn-primary' : 'btn-outline-secondary',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button @if ($type) type="{{ $type }}" @endif {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
