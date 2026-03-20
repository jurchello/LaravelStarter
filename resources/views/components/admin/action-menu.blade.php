@props([
    'label' => 'Actions',
])

<x-admin.dropdown :label="$label" {{ $attributes }}>
    <div class="admin-action-menu">
        {{ $slot }}
    </div>
</x-admin.dropdown>
