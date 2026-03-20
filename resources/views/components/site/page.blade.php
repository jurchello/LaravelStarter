@props(['testid' => null])

<section {{ $attributes->class([])->merge($testid ? ['data-testid' => $testid] : []) }}>
    {{ $slot }}
</section>
