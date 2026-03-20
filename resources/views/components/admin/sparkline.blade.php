@props([
    'label' => null,
])

<canvas
    {{ $attributes->class(['admin-chart-shell', 'admin-chart-shell--sparkline'])->merge([
        'data-admin-chart' => 'sparkline',
        'role' => 'img',
    ] + ($label ? ['aria-label' => $label] : [])) }}
></canvas>
