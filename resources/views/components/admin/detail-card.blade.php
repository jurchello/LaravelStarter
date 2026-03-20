@props([
    'title' => null,
])

<section {{ $attributes->class(['admin-detail-card']) }}>
    @if ($title)
        <h3 class="admin-detail-card__title">{{ $title }}</h3>
    @endif
    <div class="admin-detail-card__body">
        {{ $slot }}
    </div>
</section>
