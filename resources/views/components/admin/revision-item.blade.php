@props([
    'title',
    'meta' => null,
])

<article {{ $attributes->class(['admin-revision-item']) }}>
    <p class="admin-revision-item__title">{{ $title }}</p>
    @if ($meta)
        <p class="admin-revision-item__meta">{{ $meta }}</p>
    @endif
    @if (trim((string) $slot) !== '')
        <div class="admin-revision-item__body">{{ $slot }}</div>
    @endif
</article>
