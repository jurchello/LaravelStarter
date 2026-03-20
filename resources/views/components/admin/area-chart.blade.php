@props([
    'label' => null,
])

<canvas
    {{ $attributes->class(['admin-chart-shell', 'admin-chart-shell--area'])->merge([
        'data-admin-chart' => 'area',
        'role' => 'img',
    ] + ($label ? ['aria-label' => $label] : [])) }}
></canvas>
