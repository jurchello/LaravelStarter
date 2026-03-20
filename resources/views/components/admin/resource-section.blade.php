@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->class(['admin-resource-section']) }}>
    @if ($title || $description)
        <header class="admin-resource-section__header">
            @if ($title)
                <h3 class="admin-resource-section__title">{{ $title }}</h3>
            @endif
            @if ($description)
                <p class="admin-resource-section__description">{{ $description }}</p>
            @endif
        </header>
    @endif
    <div class="admin-resource-section__body">
        {{ $slot }}
    </div>
</section>
