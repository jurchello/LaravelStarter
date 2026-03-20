@props([
    'variant' => 'info',
    'title' => null,
])

<div {{ $attributes->class([
    'admin-alert',
    'admin-alert--info' => $variant === 'info',
    'admin-alert--success' => $variant === 'success',
    'admin-alert--warning' => $variant === 'warning',
    'admin-alert--danger' => $variant === 'danger',
]) }}>
    @if ($title)
        <strong class="admin-alert__title">{{ $title }}</strong>
    @endif
    <div class="admin-alert__body">{{ $slot }}</div>
</div>
