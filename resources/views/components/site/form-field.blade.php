@props([
    'for',
    'label',
    'linkHref' => null,
    'linkLabel' => null,
])

<div {{ $attributes->class(['mb-3']) }}>
    @if ($linkHref && $linkLabel)
        <div class="d-flex align-items-center justify-content-between gap-3">
            <label for="{{ $for }}" class="form-label mb-0">{{ $label }}</label>
            <a href="{{ $linkHref }}" class="link-secondary small">{{ $linkLabel }}</a>
        </div>
    @else
        <label for="{{ $for }}" class="form-label">{{ $label }}</label>
    @endif

    {{ $slot }}
</div>
