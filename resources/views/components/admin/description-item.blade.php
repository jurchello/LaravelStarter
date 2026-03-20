@props([
    'label',
    'value' => null,
])

<div {{ $attributes->class(['admin-description-list__item']) }}>
    <dt class="admin-description-list__label">{{ $label }}</dt>
    <dd class="admin-description-list__value">{{ $value ?? $slot }}</dd>
</div>
