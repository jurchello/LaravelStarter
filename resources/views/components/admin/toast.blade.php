@props([
    'variant' => 'info',
    'title' => null,
])

<div {{ $attributes->class([
    'admin-toast',
    'admin-toast--info' => $variant === 'info',
    'admin-toast--success' => $variant === 'success',
    'admin-toast--warning' => $variant === 'warning',
    'admin-toast--danger' => $variant === 'danger',
]) }}>
    @if ($title)
        <strong class="admin-toast__title">{{ $title }}</strong>
    @endif
    <div class="admin-toast__body">{{ $slot }}</div>
</div>
