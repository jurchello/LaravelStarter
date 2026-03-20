@props([
    'title' => null,
    'subtitle' => null,
    'meta' => null,
])

<div {{ $attributes->class(['admin-toolbar']) }}>
    <div>
        @if ($title)
            <h2 class="admin-section-title">{{ $title }}</h2>
        @endif
        @if ($subtitle)
            <p class="admin-section-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($meta || trim((string) $slot) !== '')
        <div class="admin-toolbar-meta">
            {{ $meta ?? $slot }}
        </div>
    @endif
</div>
