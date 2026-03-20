@props([
    'title' => 'No rows found',
    'description' => 'There are no records to display.',
])

<div {{ $attributes->class(['admin-table-state']) }}>
    <x-admin.empty-state :title="$title" :description="$description" />
</div>
