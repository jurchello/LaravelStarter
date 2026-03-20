@props(['testid' => null])

<section {{ $attributes->class(['admin-page'])->merge($testid ? ['data-testid' => $testid] : []) }}>
    {{ $slot }}
</section>
