@props([
    'name' => null,
    'value' => null,
])

<x-admin.input type="date" :name="$name" :value="$value" {{ $attributes }} />
