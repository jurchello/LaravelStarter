@props([
    'label' => 'Loading table data',
])

<div {{ $attributes->class(['admin-table-state']) }}>
    <x-admin.loading-state :label="$label" />
</div>
