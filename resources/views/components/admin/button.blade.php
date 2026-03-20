@props([
    'variant' => 'default',
    'type' => null,
    'href' => null,
])

@php
    $classes = [
        'admin-button',
        'admin-button--primary' => $variant === 'primary',
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
