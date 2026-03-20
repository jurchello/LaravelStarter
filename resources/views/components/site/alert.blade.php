@props([
    'variant' => 'info',
    'title' => null,
])

<div {{ $attributes->class([
    'alert',
    'alert-info' => $variant === 'info',
    'alert-success' => $variant === 'success',
    'alert-warning' => $variant === 'warning',
    'alert-danger' => $variant === 'danger',
]) }}>
    @if ($title)
        <strong>{{ $title }}</strong>
    @endif
    <div>{{ $slot }}</div>
</div>
