@props(['padded' => false, 'soft' => false])

<section {{ $attributes->class([
    'admin-surface',
    'admin-surface--padded' => $padded,
    'admin-surface--soft' => $soft,
]) }}>
    {{ $slot }}
</section>
