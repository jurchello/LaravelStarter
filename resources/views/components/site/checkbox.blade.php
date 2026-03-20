@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'checked' => false,
    'value' => '1',
])

<label {{ $attributes->class(['form-check d-flex align-items-center gap-2']) }}>
    <input
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        type="checkbox"
        value="{{ $value }}"
        class="form-check-input mt-0"
        @checked($checked)
    >
    <span class="form-check-label">{{ $label ?? $slot }}</span>
</label>
