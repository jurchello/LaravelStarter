@props([
    'name',
    'size' => 'md',
])

@php
    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<span {{ $attributes->class([
    'admin-avatar',
    'admin-avatar--sm' => $size === 'sm',
    'admin-avatar--md' => $size === 'md',
    'admin-avatar--lg' => $size === 'lg',
]) }}>
    {{ $initials }}
</span>
