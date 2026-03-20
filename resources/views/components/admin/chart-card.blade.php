@props([
    'title',
    'subtitle' => null,
])

<section {{ $attributes->class(['admin-chart-card']) }}>
    <header class="admin-chart-card__header">
        <h3 class="admin-chart-card__title">{{ $title }}</h3>
        @if ($subtitle)
            <p class="admin-chart-card__subtitle">{{ $subtitle }}</p>
        @endif
    </header>
    <div class="admin-chart-card__body">
        {{ $slot }}
    </div>
</section>
