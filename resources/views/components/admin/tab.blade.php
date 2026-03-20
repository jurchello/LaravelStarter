@props([
    'href' => '#',
    'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->class(['admin-tab', 'is-active' => $active]) }}
>
    {{ $slot }}
</a>
