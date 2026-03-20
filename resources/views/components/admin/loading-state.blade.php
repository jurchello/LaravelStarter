@props([
    'label' => 'Loading',
])

<div {{ $attributes->class(['admin-loading-state']) }}>
    <span class="admin-loading-state__spinner"></span>
    <span class="admin-loading-state__label">{{ $label }}</span>
</div>
