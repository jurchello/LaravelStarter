@props([
    'variant' => 'success',
])

<x-admin.alert :variant="$variant" {{ $attributes }}>
    {{ $slot }}
</x-admin.alert>
