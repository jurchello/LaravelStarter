@props([
    'title',
    'time' => null,
])

<li {{ $attributes->class(['admin-timeline__item']) }}>
    <div class="admin-timeline__dot"></div>
    <div class="admin-timeline__content">
        <p class="admin-timeline__title">{{ $title }}</p>
        @if ($time)
            <p class="admin-timeline__time">{{ $time }}</p>
        @endif
        @if (trim((string) $slot) !== '')
            <div class="admin-timeline__body">{{ $slot }}</div>
        @endif
    </div>
</li>
