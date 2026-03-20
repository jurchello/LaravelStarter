@props([
    'author',
    'meta' => null,
])

<article {{ $attributes->class(['admin-comment']) }}>
    <div class="admin-comment__header">
        <span class="admin-comment__author">{{ $author }}</span>
        @if ($meta)
            <span class="admin-comment__meta">{{ $meta }}</span>
        @endif
    </div>
    <div class="admin-comment__body">
        {{ $slot }}
    </div>
</article>
