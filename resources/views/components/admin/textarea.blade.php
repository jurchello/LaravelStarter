@props([
    'id' => null,
    'name' => null,
    'rows' => 4,
])

<textarea
    @if ($id) id="{{ $id }}" @endif
    @if ($name) name="{{ $name }}" @endif
    rows="{{ $rows }}"
    {{ $attributes->class(['admin-input', 'admin-textarea']) }}
>{{ $slot }}</textarea>
