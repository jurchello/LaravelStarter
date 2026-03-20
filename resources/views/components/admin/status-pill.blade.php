@props([
    'variant' => 'muted',
])

<span {{ $attributes->class([
    'admin-status-pill',
    'admin-status-pill--success' => $variant === 'success',
    'admin-status-pill--warning' => $variant === 'warning',
    'admin-status-pill--danger' => $variant === 'danger',
    'admin-status-pill--muted' => $variant === 'muted',
]) }}>
    {{ $slot }}
</span>
