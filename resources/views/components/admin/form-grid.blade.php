@props([
    'columns' => 2,
])

<div {{ $attributes->class([
    'admin-form-grid',
    'admin-form-grid--2' => $columns === 2,
    'admin-form-grid--3' => $columns === 3,
]) }}>
    {{ $slot }}
</div>
