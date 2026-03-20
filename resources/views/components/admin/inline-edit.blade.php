@props([
    'label' => null,
])

<div {{ $attributes->class(['admin-inline-edit']) }}>
    @if ($label)
        <span class="admin-inline-edit__label">{{ $label }}</span>
    @endif
    <div class="admin-inline-edit__control">
        {{ $slot }}
    </div>
</div>
