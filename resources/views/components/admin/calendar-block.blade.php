@props([
    'title' => null,
])

<section {{ $attributes->class(['admin-calendar-block']) }}>
    @if ($title)
        <h3 class="admin-calendar-block__title">{{ $title }}</h3>
    @endif
    <div class="admin-calendar-block__body">
        {{ $slot }}
    </div>
</section>
