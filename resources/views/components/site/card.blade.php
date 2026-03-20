@props([
    'soft' => false,
    'padded' => false,
])

<section {{ $attributes->class(['card', 'shadow-sm']) }}>
    @if ($padded)
        <div class="{{ $soft ? 'card-body bg-body-tertiary' : 'card-body' }}">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</section>
