@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class(['admin-empty-state']) }}>
    <h3>{{ $title }}</h3>
    @if ($description)
        <p>{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
