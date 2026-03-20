@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->class(['admin-form-section']) }}>
    @if ($title || $description)
        <header class="admin-form-section__header">
            @if ($title)
                <h3 class="admin-form-section__title">{{ $title }}</h3>
            @endif
            @if ($description)
                <p class="admin-form-section__description">{{ $description }}</p>
            @endif
        </header>
    @endif
    <div class="admin-form-section__body">
        {{ $slot }}
    </div>
</section>
