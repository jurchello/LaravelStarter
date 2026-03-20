@props([
    'fromName' => 'from',
    'toName' => 'to',
    'fromValue' => null,
    'toValue' => null,
])

<x-admin.date-range
    :from-name="$fromName"
    :to-name="$toName"
    :from-value="$fromValue"
    :to-value="$toValue"
    {{ $attributes }}
/>
