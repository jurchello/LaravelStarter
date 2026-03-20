@props([
    'id' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
])

<input
    @if ($id) id="{{ $id }}" @endif
    @if ($name) name="{{ $name }}" @endif
    type="{{ $type }}"
    @if (! is_null($value)) value="{{ $value }}" @endif
    {{ $attributes->class(['form-control']) }}
>
