@props([
    'fromName' => 'from',
    'toName' => 'to',
    'fromValue' => null,
    'toValue' => null,
])

<div {{ $attributes->class(['admin-date-range']) }}>
    <x-admin.input type="date" :name="$fromName" :value="$fromValue" />
    <span class="admin-date-range__separator">to</span>
    <x-admin.input type="date" :name="$toName" :value="$toValue" />
</div>
