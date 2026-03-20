@props([
    'for' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
])

<div {{ $attributes->class(['admin-field']) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="admin-field__label">{{ $label }}</label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="admin-field__hint">{{ $hint }}</p>
    @endif

    @if ($error)
        <x-admin.field-error>{{ $error }}</x-admin.field-error>
    @endif
</div>
