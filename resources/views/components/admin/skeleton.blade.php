@props([
    'lines' => 3,
])

<div {{ $attributes->class(['admin-skeleton']) }} aria-hidden="true">
    @for ($i = 0; $i < $lines; $i++)
        <span class="admin-skeleton__line"></span>
    @endfor
</div>
