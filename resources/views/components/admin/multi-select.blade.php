@props([
    'id' => null,
    'name' => null,
    'size' => 6,
])

<select
    @if ($id) id="{{ $id }}" @endif
    @if ($name) name="{{ $name }}" @endif
    size="{{ $size }}"
    multiple
    {{ $attributes->class(['admin-input', 'admin-select', 'admin-multi-select']) }}
>
    {{ $slot }}
</select>
