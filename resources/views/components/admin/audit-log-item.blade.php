@props([
    'title',
    'meta' => null,
])

<article {{ $attributes->class(['admin-audit-log__item']) }}>
    <p class="admin-audit-log__title">{{ $title }}</p>
    @if ($meta)
        <p class="admin-audit-log__meta">{{ $meta }}</p>
    @endif
    @if (trim((string) $slot) !== '')
        <div class="admin-audit-log__body">{{ $slot }}</div>
    @endif
</article>
