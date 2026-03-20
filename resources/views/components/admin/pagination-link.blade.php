@props([
    'href' => '#',
    'active' => false,
    'disabled' => false,
])

<a
    href="{{ $disabled ? '#' : $href }}"
    aria-disabled="{{ $disabled ? 'true' : 'false' }}"
    {{ $attributes->class([
        'admin-pagination-link',
        'is-active' => $active,
        'is-disabled' => $disabled,
    ]) }}
>
    {{ $slot }}
</a>
