@props([
    'label',
    'value',
])

<div {{ $attributes->class(['admin-key-stat']) }}>
    <span class="admin-key-stat__label">{{ $label }}</span>
    <strong class="admin-key-stat__value">{{ $value }}</strong>
</div>
