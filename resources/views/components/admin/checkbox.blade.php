@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'checked' => false,
    'value' => '1',
])

<label class="admin-checkbox">
    <input
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        type="checkbox"
        value="{{ $value }}"
        class="admin-checkbox__input"
        {{ $attributes->except('class') }}
        @checked($checked)
    >
    @if ($label || trim((string) $slot) !== '')
        <span class="admin-checkbox__label">{{ $label ?? $slot }}</span>
    @endif
</label>
