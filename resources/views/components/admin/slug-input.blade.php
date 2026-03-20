@props([
    'id' => null,
    'name' => null,
    'value' => null,
    'prefix' => null,
])

<div {{ $attributes->class(['admin-slug-input']) }}>
    @if ($prefix)
        <span class="admin-slug-input__prefix">{{ $prefix }}</span>
    @endif
    <x-admin.input
        :id="$id"
        :name="$name"
        :value="$value"
        class="admin-slug-input__control"
    />
</div>
