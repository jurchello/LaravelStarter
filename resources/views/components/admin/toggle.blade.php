@props([
    'id' => null,
    'name' => null,
    'checked' => false,
    'label' => null,
])

<label {{ $attributes->class(['admin-toggle']) }}>
    <input
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        type="checkbox"
        class="admin-toggle__input"
        @checked($checked)
    >
    <span class="admin-toggle__track"></span>
    @if ($label || trim((string) $slot) !== '')
        <span class="admin-toggle__label">{{ $label ?? $slot }}</span>
    @endif
</label>
