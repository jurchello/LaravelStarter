@props([
    'label',
    'value',
    'tone' => 'neutral',
])

<article {{ $attributes->class(['admin-stat-card'])->merge(['data-tone' => $tone]) }}>
    <p class="admin-stat-card__label">{{ $label }}</p>
    <p class="admin-stat-card__value">{{ $value }}</p>
</article>
