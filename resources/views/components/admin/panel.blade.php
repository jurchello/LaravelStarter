@props([
    'title' => null,
    'subtitle' => null,
])

<section {{ $attributes->class(['admin-panel']) }}>
    @if ($title || $subtitle)
        <header class="admin-panel__header">
            @if ($title)
                <h3 class="admin-panel__title">{{ $title }}</h3>
            @endif
            @if ($subtitle)
                <p class="admin-panel__subtitle">{{ $subtitle }}</p>
            @endif
        </header>
    @endif

    <div class="admin-panel__body">
        {{ $slot }}
    </div>
</section>
