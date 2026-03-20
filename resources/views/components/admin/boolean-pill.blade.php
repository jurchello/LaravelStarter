@props([
    'value' => false,
    'trueLabel' => 'Yes',
    'falseLabel' => 'No',
])

<x-admin.badge :variant="$value ? 'accent' : 'muted'" {{ $attributes }}>
    {{ $value ? $trueLabel : $falseLabel }}
</x-admin.badge>
