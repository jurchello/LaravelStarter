@props([
    'title',
    'meta' => null,
])

<article {{ $attributes->class(['admin-activity-item']) }}>
    <p class="admin-activity-item__title">{{ $title }}</p>
    @if ($meta)
        <p class="admin-activity-item__meta">{{ $meta }}</p>
    @endif
    @if (trim((string) $slot) !== '')
        <div class="admin-activity-item__body">{{ $slot }}</div>
    @endif
</article>
