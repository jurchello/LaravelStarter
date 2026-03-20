@props([
    'label' => null,
])

<fieldset {{ $attributes->class(['admin-radio-group']) }}>
    @if ($label)
        <legend class="admin-radio-group__label">{{ $label }}</legend>
    @endif
    <div class="admin-radio-group__items">
        {{ $slot }}
    </div>
</fieldset>
