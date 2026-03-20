@props([
    'href' => null,
    'variant' => 'default',
    'type' => 'button',
])

@php
    $classes = [
        'admin-action-item',
        'admin-action-item--danger' => $variant === 'danger',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
