@props([
    'value' => 0,
    'max' => 100,
])

@php
    $percent = $max > 0 ? max(0, min(100, (int) round(($value / $max) * 100))) : 0;
@endphp

<div {{ $attributes->class(['admin-progress']) }}>
    <progress class="admin-progress__track" max="100" value="{{ $percent }}">{{ $percent }}%</progress>
    <span class="admin-progress__label">{{ $percent }}%</span>
</div>
