@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
])

<header {{ $attributes->class(['admin-page-header']) }}>
    <div>
        @if ($eyebrow)
            <p class="admin-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="admin-page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="admin-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if (trim((string) $slot) !== '')
        <div>
            {{ $slot }}
        </div>
    @endif
</header>
