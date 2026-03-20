@props([
    'title' => null,
    'open' => false,
])

<section
    {{ $attributes->class(['admin-modal', 'is-open' => $open]) }}
    @if (! $open) hidden @endif
    role="dialog"
    aria-modal="true"
>
    <div class="admin-modal__backdrop"></div>
    <div class="admin-modal__dialog">
        @if ($title)
            <header class="admin-modal__header">
                <h3 class="admin-modal__title">{{ $title }}</h3>
            </header>
        @endif

        <div class="admin-modal__body">
            {{ $slot }}
        </div>
    </div>
</section>
