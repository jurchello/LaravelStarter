@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class(['py-5', 'text-center']) }}>
    <h3 class="h4">{{ $title }}</h3>
    @if ($description)
        <p class="text-body-secondary mb-0">{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
