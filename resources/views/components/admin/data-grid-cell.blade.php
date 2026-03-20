@props([
    'head' => false,
])

@if ($head)
    <th {{ $attributes->class(['admin-data-grid__cell', 'admin-data-grid__cell--head']) }}>
        {{ $slot }}
    </th>
@else
    <td {{ $attributes->class(['admin-data-grid__cell']) }}>
        {{ $slot }}
    </td>
@endif
