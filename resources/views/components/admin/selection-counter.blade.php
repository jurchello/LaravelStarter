@props([
    'count' => 0,
    'label' => 'selected',
])

<div {{ $attributes->class(['admin-selection-counter']) }}>
    <strong>{{ $count }}</strong>
    <span>{{ $label }}</span>
</div>
