@props([
    'title' => null,
    'subtitle' => null,
])

<section {{ $attributes->class(['mb-4']) }}>
    @if ($title)
        <h2 class="h3">{{ $title }}</h2>
    @endif
    @if ($subtitle)
        <p class="text-body-secondary">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</section>
