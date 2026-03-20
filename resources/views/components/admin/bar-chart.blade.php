@props([
    'label' => null,
])

<canvas
    {{ $attributes->class(['admin-chart-shell', 'admin-chart-shell--bar'])->merge([
        'data-admin-chart' => 'bar',
        'role' => 'img',
    ] + ($label ? ['aria-label' => $label] : [])) }}
></canvas>
