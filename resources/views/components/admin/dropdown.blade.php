@props([
    'label' => 'Menu',
])

<div {{ $attributes->class(['admin-dropdown']) }}>
    <button type="button" class="admin-dropdown__trigger">
        {{ $label }}
    </button>
    <div class="admin-dropdown__menu">
        {{ $slot }}
    </div>
</div>
