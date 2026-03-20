@props([
    'id' => null,
    'name' => null,
])

<select
    @if ($id) id="{{ $id }}" @endif
    @if ($name) name="{{ $name }}" @endif
    {{ $attributes->class(['admin-input', 'admin-select']) }}
>
    {{ $slot }}
</select>
