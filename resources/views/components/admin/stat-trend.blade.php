@props([
    'value',
    'direction' => 'neutral',
])

<span {{ $attributes->class([
    'admin-stat-trend',
    'admin-stat-trend--up' => $direction === 'up',
    'admin-stat-trend--down' => $direction === 'down',
    'admin-stat-trend--neutral' => $direction === 'neutral',
]) }}>
    @if ($direction === 'up')
        ↑
    @elseif ($direction === 'down')
        ↓
    @else
        →
    @endif
    {{ $value }}
</span>
