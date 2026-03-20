@props([
    'id' => null,
    'name' => null,
    'value' => null,
    'checked' => false,
    'label',
])

<label {{ $attributes->class(['admin-radio-option']) }}>
    <input
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        @if (! is_null($value)) value="{{ $value }}" @endif
        type="radio"
        class="admin-radio-option__input"
        @checked($checked)
    >
    <span class="admin-radio-option__label">{{ $label }}</span>
</label>
