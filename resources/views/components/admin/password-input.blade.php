@props([
    'id' => null,
    'name' => null,
    'value' => null,
])

<x-admin.input
    :id="$id"
    :name="$name"
    type="password"
    :value="$value"
    {{ $attributes }}
/>
