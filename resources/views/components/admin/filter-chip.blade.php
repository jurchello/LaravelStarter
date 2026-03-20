@props([
    'active' => false,
])

<button
    type="button"
    {{ $attributes->class([
        'admin-filter-chip',
        'is-active' => $active,
    ]) }}
>
    {{ $slot }}
</button>
