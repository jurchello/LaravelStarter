@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
])

<div {{ $attributes->class([]) }}>
    @if ($eyebrow)
        <p class="text-uppercase small fw-semibold text-body-secondary mb-2">{{ $eyebrow }}</p>
    @endif
    <h1 class="display-5 fw-semibold">{{ $title }}</h1>
    @if ($subtitle)
        <p class="lead text-body-secondary">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</div>
