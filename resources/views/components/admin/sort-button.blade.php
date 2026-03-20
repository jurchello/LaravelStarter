@props([
    'active' => false,
    'direction' => null,
])

<button
    type="button"
    {{ $attributes->class([
        'admin-sort-button',
        'is-active' => $active,
        'is-asc' => $direction === 'asc',
        'is-desc' => $direction === 'desc',
    ]) }}
>
    <span class="admin-sort-button__label">{{ $slot }}</span>
    <span class="admin-sort-button__icon">
        <span class="admin-sort-button__arrow admin-sort-button__arrow--up" aria-hidden="true"></span>
        <span class="admin-sort-button__arrow admin-sort-button__arrow--down" aria-hidden="true"></span>
    </span>
</button>
