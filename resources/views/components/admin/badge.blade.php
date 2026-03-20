@props([
    'variant' => 'muted',
])

<span {{ $attributes->class([
    'admin-badge',
    'admin-badge--accent' => $variant === 'accent',
    'admin-badge--muted' => $variant === 'muted',
]) }}>
    {{ $slot }}
</span>
