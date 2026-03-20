@props([
    'label' => null,
])

<canvas
    {{ $attributes->class(['admin-chart-shell', 'admin-chart-shell--donut'])->merge([
        'data-admin-chart' => 'donut',
        'role' => 'img',
    ] + ($label ? ['aria-label' => $label] : [])) }}
></canvas>
