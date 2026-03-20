@props([
    'label',
])

<div {{ $attributes->class(['admin-sidebar-group']) }}>
    <p class="admin-sidebar-group__label">{{ $label }}</p>
    <div class="admin-sidebar-group__items">
        {{ $slot }}
    </div>
</div>
