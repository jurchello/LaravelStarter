@props([
    'label' => null,
])

<li {{ $attributes->class(['admin-data-list__item']) }}>
    @if ($label)
        <span class="admin-data-list__label">{{ $label }}</span>
    @endif
    <span class="admin-data-list__value">{{ $slot }}</span>
</li>
