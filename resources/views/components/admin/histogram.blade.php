@props([
    'label' => null,
])

<canvas
    {{ $attributes->class(['admin-chart-shell', 'admin-chart-shell--histogram'])->merge([
        'data-admin-chart' => 'histogram',
        'role' => 'img',
    ] + ($label ? ['aria-label' => $label] : [])) }}
></canvas>
