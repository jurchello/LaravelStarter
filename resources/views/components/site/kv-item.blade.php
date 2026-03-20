@props([
    'label',
    'value',
])

<div {{ $attributes->class(['col']) }}>
    <dt class="small text-body-secondary">{{ $label }}</dt>
    <dd class="mb-0 fw-medium">{{ $value }}</dd>
</div>
