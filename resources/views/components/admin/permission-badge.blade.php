@props([
    'variant' => 'muted',
])

<span {{ $attributes->class([
    'admin-permission-badge',
    'admin-permission-badge--accent' => $variant === 'accent',
    'admin-permission-badge--muted' => $variant === 'muted',
]) }}>
    {{ $slot }}
</span>
